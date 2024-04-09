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

namespace Playcat\Queue\Webman;

use Playcat\Queue\Protocols\ConsumerDataInterface;
use Playcat\Queue\Driver\DriverInterface;
use Playcat\Queue\Protocols\ProducerDataInterface;
use Playcat\Queue\TimerClient\TimerClientInterface;
use Playcat\Queue\TimerClient\StreamSocket;

class Manager extends \Playcat\Queue\Manager
{
    public function __construct()
    {
        $this->setConf(Config('plugin.playcat.queue.app.Manager'));
    }
}
