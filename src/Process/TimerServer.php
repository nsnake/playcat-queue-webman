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

use ErrorException;
use Playcat\Queue\Protocols\ProducerData;
use Playcat\Queue\TimerClient\TimerClientProtocols;
use Playcat\Queue\Webman\Manager;
use Playcat\Queue\TimerServer\Storage;
use Workerman\Timer;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Playcat\Queue\Log\Log;

class TimerServer
{
    private $manager;
    private $storage;
    private $iconic_id;

    public function __construct(array $conf = [])
    {
        Log::setLogHandle(\support\Log::class);
        $this->storage = new Storage();
        $this->storage->setDriver($conf);
    }

    /**
     * onWorkerStart.
     */
    public function onWorkerStart(Worker $worker)
    {
        Log::info('Start Timerserver!');
        $this->manager = Manager::getInstance();
        $this->iconic_id = $worker->id;
        $this->loadUndoJobs();
    }

    public function onMessage(TcpConnection $connection, $data)
    {
        try {
            $result = '';
            $data = trim($data);
            if ($data === '') {
                throw new ErrorException('Unsupported protocols!', 401);
            }

            $protocols = unserialize($data);
            if ($protocols instanceof TimerClientProtocols) {
                switch ($protocols->getCMD()) {
                    case TimerClientProtocols::CMD_PING:
                        $result = 'pong';
                        break;
                    case TimerClientProtocols::CMD_PUSH:
                        $result = $this->cmdPush($protocols->getPayload());
                        break;
                    case TimerClientProtocols::CMD_DEL:
                        $result = $this->cmdDel($protocols->getPayload());
                        break;
                }

            }
            $connection->send(json_encode(['code' => 200, 'msg' => 'ok', 'data' => $result]));
        } catch (ErrorException $e) {
            Log::critical($e->getMessage());
            $connection->send($this->resultData($result, $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @param ProducerData $payload
     * @return int
     */
    private function cmdPush(ProducerData $payload): int
    {
        $jid = $this->storage->addData($this->iconic_id, $payload->getDelayTime(), $payload);
        $timer_id = Timer::add($payload->getDelayTime(), function (int $jid, Storage $storage) {
            $db_data = $storage->getDataById($jid);
            if ($db_data) {
                $payload = $db_data['data'];
                $payload->setDelayTime();
                if ($this->manager->push($payload)) {
                    $storage->delData($jid);
                }
            }
        }, [$jid, $this->storage], false);
        $this->storage->upData($jid, $timer_id);
        return $jid;
    }

    /**
     * @param ProducerData $payload
     * @return int
     */
    private function cmdDel(ProducerData $payload): int
    {
        $jid = intval($payload->getID());
        $result = 1;
        if ($jid && $jid > 0) {
            $db_data = $this->storage->getDataById($jid);
            if ($db_data && $db_data['timerid']) {
                Timer::del($db_data['timerid']);
                $result = $this->storage->delData($jid);
            }
        }
        return $result;
    }

    /**
     * @param int $code
     * @param string $msg
     * @param string $data
     * @return string
     */
    private function resultData(string $data = '', int $code = 200, string $msg = 'ok'): string
    {
        return json_encode(['code' => $code, 'msg' => $msg, 'data' => $data]) . "\r\n";
    }

    /**
     * @return void
     */
    private function loadUndoJobs(): void
    {
        Log::info('PQ: Load unfinished jobs！');
        $jobs = $this->storage->getHistoryJobs();
        Log::debug('PQ: Unfinished jobs' . count($jobs));
        foreach ($jobs as $job) {
            $left_time = $job['expiration'] - time();
            $payload = $job['data'];
            if ($left_time < 5) {
                $payload->setDelayTime();
                $this->manager->push($payload);
            } else {
                $payload->setDelayTime($left_time);
                $this->cmdPush($payload);
            }
            $this->storage->delData($job['jid']);
        }
        Log::info('PQ: Load unfinished jobs complete！');
    }

}
