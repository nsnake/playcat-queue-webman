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
        // 消费进程数量，默认为cpu数 * 2
        'count' => cpu_count() * 2,
        // 执行消费任务的程序目录
        'consumer_dir' => app_path() . 'playcat/queue',
        // 消费失败后最多重试几次
        'max_attempts' => 3,
        // 最小重试间隔时长(单位秒),次数越多重试间隔时间越长
        'retry_seconds' => 60,
        // pid文件完整路径
        'pid_file' => '/tmp/playcat_queue.pid'
    ],
    'TimerServer' => [
        //TS服务监听地址,支持uninx sock或tcp
        'bind_ip' => '127.0.0.1',
        //TS服务监听端口,
        'bind_port' => 6678,
        'pid_file' => '/tmp/playcat_timeserver.pid',
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
        ],
        // 当前进程是否支持reload （可选，默认true）
        'reloadable' => false,
    ],
    'Manager' => [
        /**
         * 使用消息队列
         * 可选: Redis(默认),Rediscluster,Kafka,RabbitMQ
         */
        'driver' => \Playcat\Queue\Driver\Redis::class,
        // TS服务端地址
        'timerserver' => '127.0.0.1:6678',

        // Kafka配置
        'Kafka' => [
            'host' => '127.0.0.1:9092',
            'options' => []
        ],
        // Rabbitmq配置
        'Rabbitmq' => [
            'host' => '127.0.0.1:9092',
            'options' => [
                'user' => 'guest',
                'password' => 'guest',
                'vhost' => '/'
            ]
        ],
        // redis配置
        'Redis' => [
            'host' => '127.0.0.1:6379',
            'options' => [
                // 密码，字符串类型，可选参数
                'auth' => '',
            ]
        ],
        // redis集群配置
        'Rediscluster' => [
            'host' => [
                '127.0.0.1:7000',
                '127.0.0.1:7001',
                '127.0.0.1:7002'
            ],
            'options' => [
                // 密码，字符串类型，可选参数
                'auth' => '',
            ]
        ]
    ]
];
