<?php

namespace Playcat\Queue\Webman;


use Playcat\Queue\TimerClient\TimerClientInterface;
use Playcat\Queue\TimerClient\StreamSocket;
use Playcat\Queue\Manager\Base;
use Playcat\Queue\Log\Log;

class Manager extends Base
{
    final public static function getInstance(): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->manager_config = Config('plugin.playcat.queue.app.Manager');
        Log::setLogHandle(\support\Log::class);
    }

    protected function getTimeClient(): StreamSocket
    {
        if (!$this->tc) {
            $this->tc = new StreamSocket([
                'timerserver' => $this->manager_config['timerserver']
            ]);
        }
        return $this->tc;
    }

}
