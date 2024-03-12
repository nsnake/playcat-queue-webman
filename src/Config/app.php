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
    'enable' => true,
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
