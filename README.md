
# **Playcat\Queue\Webman**

#### 简单易用的消息队列处理系统

## 特性

- 支持Redis单机或集群 (redis >= 5.0)
- 支持Kafka
- 支持RabbitMQ
- 支持延迟消息数据持久化
- 自定义异常与重试流程

## 依赖版本

- PHP >= 8.1
- webman >= 1.6
- msgpack
- Redis
- RdKafka
- php-amqplib/php-amqplib

### 依赖安装

```shell
#RH,CentOS 7 
sudo yum install php-pecl-redis5 php-pecl-msgpack
sudo yum install php-pecl-rdkafka5 #(使用Kafka必须)

#RH,CentOS,Rock 8及以上
sudo dnf install php-pecl-redis6 php-pecl-msgpack
sudo dnf install php-pecl-rdkafka6 #(使用Kafka必须)
```

## 安装
在Webman项目下执行
```shell
$ composer require "playcat/queue-webman"
```

## 使用方法

### 1.配置

#### 1.1
编辑 **config\plugin\playcat\queue\** 目录下的**app.php**和*process.php*。修改相应内容为自己环境使用的配置。

#### 1.2 初始化数据库(只需一次)

```
php webman timerserver:initdb
```

### 2.创建消费任务

#### 创建一个php的文件并添加以下内容:

```php
<?php

namespace app\playcat\queue;
use Playcat\Queue\Protocols\ConsumerBase;
use Playcat\Queue\Protocols\ConsumerData;

class SmsSend extends ConsumerBase
{
    //2.0新增,可以针对不同的队列设置最大重试次数和重试间隔时间,如不设置则使用全局配置
    //public const MAX_ATTEMPTS = 3;
    //public const RETRY_SECONDS = 1;
    
    //消费队列别名,如果不指定则使用类名作为队列名称(不建议继续使用)
    //public $queue = 'playcatqueue';
    
    /**
    * 1.12新增初始化执行,只在该类首次加载时执行一次,以便执行一些耗时间的操作，例数据库连接或者初始化一些数据等,该方法不接收和返回参数,可不写。
    */
    public function onInit():void
    {
        //...你自己的初始化逻辑
    }
    
    public function consume(ConsumerData $payload)
    {
        //获取发布到队列时传入的内容
        $data = $payload->getQueueData();
        ...你自己的业务逻辑
        //休息10s
        sleep(10);
        echo('done!');
    }
}

```

### ConsumerData方法

- getID: 当前消息的id
- getRetryCount(): 当前任务已经重试过的次数
- getQueueData():  当前任务传入的参数
- getChannel(): 当前所执行的任务名称
- - -

#### 将上面编写好的任务文件保存项目中'*app/queue/playcat/*'下(如果目录不存在则自己手动创建)


#### 启动服务:

启动:
`php start.php start`

重载:可在不重启服务的情况下更新业务
`php start.php reload`

停止：
`php start.php stop`

如果没有错误出现则表示启动完成

### 添加任务并且提交到队列中

```php
use Playcat\Queue\Webman\Manager;
use Playcat\Queue\Protocols\ProducerData;
  //即时消费消息
  $payload = new ProducerData();
  //对应消费队列里的任务名称
  $payload->setChannel('test');
  //对应消费队列里的任务使用的数据
  $payload->setQueueData([1,2,3,4]);
  //推入队列并且获取消息的唯一id
  $id = Manager::getInstance()->push($payload);

  //延迟消费消息
  $payload_delay = new ProducerData();
  $payload_delay->setChannel('test');
  $payload_delay->setQueueData([6,7,8,9]);
  //设置60秒后执行的任务
  $payload_delay->setDelayTime(60);
  //推入队列并且获取消息的唯一id
  $id = Manager::getInstance()->push($payload_delay);
  //取消延迟消息
  Manager::getInstance()->del($id);
```


### ProducerData
#### 方法:
- setChannel: 设置推入消息的队列名称
- setQueueData: 设置传入到消费任务的数据
- setDelayTime: 设置延迟时间(秒)
- - -

### Manager
#### 方法:
- getInstance: 创建一个单例模式
- push(ProducerData): 推入生产者数据
- del

### 异常与重试机制

任务在执行过程中未抛出异常则默认执行成功，否则则进入重试阶段.
重试次数和时间由配置控制，重试间隔时间为当前重试次数的幂函数。
**Playcat\Queue\Exceptions\DontRetry**异常会忽略掉重试


### 其它

基于tp和swoole的队列系统
[playcat-queue-tpswoole](https://github.com/nsnake/playcat-queue-tpswoole)

基于webman的队列系统
[playcat-queue-webman](https://github.com/nsnake/playcat-queue-webman)

更多说明: https://github.com/nsnake/playcat-queue-doc

### 联系
QQ:318274085

## License

MIT
