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

namespace Playcat\Queue\Webman\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Playcat\Queue\Install\InitDB;

class TimerserverInitdb extends Command
{
    protected static $defaultName = 'timerserver:initdb';
    protected static $defaultDescription = 'Database Initial';
    private $config = [];

    /**
     * @return void
     */
    protected function configure()
    {
        $this->config = Config('plugin.playcat.queue.process.TimerServer.constructor');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting playcat queue database initial...');
        $db = new InitDB($this->config['storage']);
        if ($this->config['storage']['type'] == strtolower('mysql')) {
            $result = $db->initMysql();
        } elseif ($this->config['storage']['type'] == strtolower('sqlite')) {
            $result = $db->initSqlite();
        } else {
            $output->writeln("Unsupported database");
            return self::FAILURE;
        }
        if ($result) {
            $output->writeln('Initialized successfully！');
            return self::SUCCESS;
        } else {
            $output->writeln("Initialized failed！ Maybe already have it！");
            return self::FAILURE;
        }

    }

}
