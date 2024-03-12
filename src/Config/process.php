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
return [
    'ConsumerService' => [
        'handler' => \Playcat\Queue\Webman\Process\ConsumerService::class,
        'count' => cpu_count() * 2, // 可以设置多进程同时消费
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path() . '/playcat/queue',
            'max_attempts' => 3, // 消费失败后，重试次数
            'retry_seconds' => 60, // 重试间隔，单位秒
        ]
    ],
    'TimerServer' => [
        'handler' => \Playcat\Queue\Webman\Process\TimerServer::class,
        'listen' => 'tcp://127.0.0.1:6678',
        'reloadable' => false,
        'constructor' => [
            'storage' => [
                //存储支持,可选sqlite或mysql
                'type' => 'mysql',
                //存储支持服务地址,如果为sqlite则写完整路径即可
                'hostname' => '127.0.0.1',
                //数据库名
                'database' => 'playcatqueue',
                //数据库用户名
                'username' => 'root',
                //数据库密码
                'password' => '',
                //数据库连接端口
                'hostport' => ''
            ]
        ]
    ],
];
