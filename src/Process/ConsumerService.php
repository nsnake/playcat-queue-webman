<?php
/**
 *
 *
 * @license MIT License (MIT)
 *
 * For full copyright and license information, please see the LICENCE files.
 *
 * @author CGI.NET
 */

namespace Playcat\Queue\Webman\Process;


use Playcat\Queue\Util\Container;
use Playcat\Queue\Exceptions\QueueDontRetry;
use Playcat\Queue\Protocols\ConsumerData;
use Playcat\Queue\Protocols\ProducerData;
use Playcat\Queue\Webman\Manager;
use Exception;
use Workerman\Worker;
use Workerman\Timer;
use Playcat\Queue\Log\Log;

class ConsumerService
{

    private $pull_timing;
    private $config;

    /**
     * @param string $consumer_dir
     * @param int $max_attempts
     * @param int $retry_seconds
     */
    public function __construct(string $consumer_dir = '', int $max_attempts = 0, int $retry_seconds = 5)
    {
        $this->config['consumer_dir'] = $consumer_dir;
        $this->config['max_attempts'] = $max_attempts;
        $this->config['retry_seconds'] = $retry_seconds;
        Log::setLogHandle(\support\Log::class);
    }

    /**
     * onWorkerStart.
     */
    public function onWorkerStart(Worker $worker)
    {
        if (!is_dir($this->config['consumer_dir'])) {
            Log::emergency('Consumer directory' . $this->config['consumer_dir'] . ' not exists');
            return;
        }
        $manager = Manager::getInstance();
        $manager->setIconicId($worker->id);

        try {
            $consumers = $this->loadWorkTask($this->config['consumer_dir']);
        } catch (Exception $e) {
            Log::emergency('Error while loading consumers: ' . $e->getMessage());
            return;
        }

        $manager->subscribe(array_keys($consumers));

        Log::info('Start Playcat Queue Consumer Service!');
        $this->pull_timing = Timer::add(0.1, function ($config) use ($manager, $consumers) {
            $payload = $manager->shift();
            if ($payload instanceof ConsumerData) {
                if (!empty ($consumers[$payload->getChannel()])) {
                    try {
                        call_user_func([$consumers[$payload->getChannel()], 'consume'], $payload);
                    } catch (QueueDontRetry $e) {
                        Log::alert('Caught an exception but not need retry it!', $payload->getQueueData());
                    } catch (Exception $e) {
                        if (
                            isset ($config['max_attempts'])
                            && $config['max_attempts'] > 0
                            && $config['max_attempts'] > $payload->getRetryCount()
                        ) {
                            $producer_data = new ProducerData();
                            $producer_data->setChannel($payload->getChannel());
                            $producer_data->setQueueData($payload->getQueueData());
                            $producer_data->setRetryCount($payload->getRetryCount() + 1);
                            $producer_data->setDelayTime(
                                pow($config['retry_seconds'], $producer_data->getRetryCount())
                            );
                            $manager->push($producer_data);
                        }
                    } finally {
                        $manager->consumerFinished();
                    }
                }
            } elseif ($payload) {
                $manager->consumerFinished();
            }
        }, [$this->config]);
    }

    /**
     * @param Worker $worker
     * @return void
     */
    public function onWorkerStop(Worker $worker): void
    {
        if ($this->pull_timing) {
            Timer::del($this->pull_timing);
        }
    }

    /**
     * @param string $dir
     * @return array
     * @throws Exception
     */
    protected function loadWorkTask(string $dir = ''): array
    {
        $consumers = [];
        foreach (glob($dir . '/*.php') as $file) {
            $class = str_replace('/', "\\", substr(substr($file, strlen(base_path())), 0, -4));
            if (is_a($class, 'Playcat\Queue\Protocols\ConsumerInterface', true)) {
                $consumer = Container::instance()->get($class);
                $channel = $consumer->queue;
                $consumers[$channel] = $consumer;
                Log::debug('Load task name:' . $channel);
            }
        }
        return $consumers;
    }
}
