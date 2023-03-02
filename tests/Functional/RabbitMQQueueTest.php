<?php

namespace VladimirYuldashev\LaravelQueueRabbitMQ\Tests\Functional;

use Illuminate\Support\Str;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;
use VladimirYuldashev\LaravelQueueRabbitMQ\Tests\Functional\TestCase as BaseTestCase;

class RabbitMQQueueTest extends BaseTestCase
{
    public function testConnection(): void
    {
        $queue = $this->connection();
        $this->assertInstanceOf(RabbitMQQueue::class, $queue);

        $queue = $this->connection('rabbitmq-with-options');
        $this->assertInstanceOf(RabbitMQQueue::class, $queue);

        $queue = $this->connection('rabbitmq-with-options-empty');
        $this->assertInstanceOf(RabbitMQQueue::class, $queue);
    }

    public function testConfigRerouteFailed(): void
    {
        $config = $this->connection()->getConfig();
        $this->assertFalse($this->callMethod($config, 'isRerouteFailed'));

        $config = $this->connection('rabbitmq-with-options')->getConfig();
        $this->assertTrue($this->callMethod($config, 'isRerouteFailed'));

        $config = $this->connection('rabbitmq-with-options-empty')->getConfig();
        $this->assertFalse($this->callMethod($config, 'isRerouteFailed'));
    }

    public function testConfigPrioritizeDelayed(): void
    {
        $config = $this->connection()->getConfig();
        $this->assertFalse($this->callMethod($config, 'isPrioritizeDelayed'));

        $config = $this->connection('rabbitmq-with-options')->getConfig();
        $this->assertTrue($this->callMethod($config, 'isPrioritizeDelayed'));

        $config = $this->connection('rabbitmq-with-options-empty')->getConfig();
        $this->assertFalse($this->callMethod($config, 'isPrioritizeDelayed'));
    }

    public function testQueueMaxPriority(): void
    {
        $config = $this->connection()->getConfig();
        $this->assertIsInt($this->callMethod($config, 'getQueueMaxPriority'));
        $this->assertSame(2, $this->callMethod($config, 'getQueueMaxPriority'));

        $config = $this->connection('rabbitmq-with-options')->getConfig();
        $this->assertIsInt($this->callMethod($config, 'getQueueMaxPriority'));
        $this->assertSame(20, $this->callMethod($config, 'getQueueMaxPriority'));

        $config = $this->connection('rabbitmq-with-options-empty')->getConfig();
        $this->assertIsInt($this->callMethod($config, 'getQueueMaxPriority'));
        $this->assertSame(2, $this->callMethod($config, 'getQueueMaxPriority'));
    }

    public function testConfigExchangeType(): void
    {
        $queue = $this->connection();
        $this->assertSame(AMQPExchangeType::DIRECT, $this->callMethod($queue, 'getExchangeType'));
        $this->assertSame(AMQPExchangeType::DIRECT, $this->callMethod($queue, 'getExchangeType', ['']));
        $this->assertSame(AMQPExchangeType::TOPIC, $this->callMethod($queue, 'getExchangeType', ['topic']));

        $queue = $this->connection('rabbitmq-with-options');
        $this->assertSame(AMQPExchangeType::TOPIC, $this->callMethod($queue, 'getExchangeType'));

        $queue = $this->connection('rabbitmq-with-options-empty');
        $this->assertSame(AMQPExchangeType::DIRECT, $this->callMethod($queue, 'getExchangeType'));
    }

    public function testExchange(): void
    {
        $queue = $this->connection();
        $this->assertSame('test', $this->callMethod($queue, 'getExchange', ['test']));
        $this->assertNull($this->callMethod($queue, 'getExchange', ['']));
        $this->assertNull($this->callMethod($queue, 'getExchange'));

        $queue = $this->connection('rabbitmq-with-options');
        $this->assertNotNull($this->callMethod($queue, 'getExchange'));
        $this->assertSame('application-x', $this->callMethod($queue, 'getExchange'));

        $queue = $this->connection('rabbitmq-with-options-empty');
        $this->assertNull($this->callMethod($queue, 'getExchange'));
    }

    public function testFailedExchange(): void
    {
        $queue = $this->connection();
        $this->assertSame('test', $this->callMethod($queue, 'getFailedExchange', ['test']));
        $this->assertNull($this->callMethod($queue, 'getExchange', ['']));
        $this->assertNull($this->callMethod($queue, 'getFailedExchange'));

        $queue = $this->connection('rabbitmq-with-options');
        $this->assertNotNull($this->callMethod($queue, 'getFailedExchange'));
        $this->assertSame('failed-exchange', $this->callMethod($queue, 'getFailedExchange'));

        $queue = $this->connection('rabbitmq-with-options-empty');
        $this->assertNull($this->callMethod($queue, 'getFailedExchange'));
    }

    public function testRoutingKey(): void
    {
        $queue = $this->connection();
        $this->assertSame('test', $this->callMethod($queue, 'getRoutingKey', ['test']));
        $this->assertSame('', $this->callMethod($queue, 'getRoutingKey', ['']));

        $queue = $this->connection('rabbitmq-with-options');
        $this->assertSame('process.test', $this->callMethod($queue, 'getRoutingKey', ['test']));

        $queue = $this->connection('rabbitmq-with-options-empty');
        $this->assertSame('test', $this->callMethod($queue, 'getRoutingKey', ['test']));
    }

    public function testFailedRoutingKey(): void
    {
        $queue = $this->connection();

        $this->assertSame('test.failed', $this->callMethod($queue, 'getFailedRoutingKey', ['test']));
        $this->assertSame('failed', $this->callMethod($queue, 'getFailedRoutingKey', ['']));

        $queue = $this->connection('rabbitmq-with-options');
        $this->assertSame('application-x.test.failed', $this->callMethod($queue, 'getFailedRoutingKey', ['test']));

        $queue = $this->connection('rabbitmq-with-options-empty');
        $this->assertSame('test.failed', $this->callMethod($queue, 'getFailedRoutingKey', ['test']));
    }

    public function testConfigQuorum(): void
    {
        $config = $this->connection()->getConfig();
        $this->assertFalse($this->callMethod($config, 'isQuorum'));

        $config = $this->connection('rabbitmq-with-options')->getConfig();
        $this->assertFalse($this->callMethod($config, 'isQuorum'));

        $config = $this->connection('rabbitmq-with-options-empty')->getConfig();
        $this->assertFalse($this->callMethod($config, 'isQuorum'));

        $config = $this->connection('rabbitmq-with-quorum-options')->getConfig();
        $this->assertTrue($this->callMethod($config, 'isQuorum'));
    }

    public function testDeclareDeleteExchange(): void
    {
        $queue = $this->connection();

        $name = Str::random();

        $this->assertFalse($queue->isExchangeExists($name));

        $queue->declareExchange($name);
        $this->assertTrue($queue->isExchangeExists($name));

        $queue->deleteExchange($name);
        $this->assertFalse($queue->isExchangeExists($name));
    }

    public function testDeclareDeleteQueue(): void
    {
        $queue = $this->connection();

        $name = Str::random();

        $this->assertFalse($queue->isQueueExists($name));

        $queue->declareQueue($name);
        $this->assertTrue($queue->isQueueExists($name));

        $queue->deleteQueue($name);
        $this->assertFalse($queue->isQueueExists($name));
    }

    public function testQueueArguments(): void
    {
        $name = Str::random();

        $queue = $this->connection();
        $actual = $this->callMethod($queue, 'getQueueArguments', [$name]);
        $expected = [];
        $this->assertEquals(array_keys($expected), array_keys($actual));
        $this->assertEquals(array_values($expected), array_values($actual));

        $queue = $this->connection('rabbitmq-with-options');
        $actual = $this->callMethod($queue, 'getQueueArguments', [$name]);
        $expected = [
            'x-max-priority' => 20,
            'x-dead-letter-exchange' => 'failed-exchange',
            'x-dead-letter-routing-key' => sprintf('application-x.%s.failed', $name),
        ];

        $this->assertEquals(array_keys($expected), array_keys($actual));
        $this->assertEquals(array_values($expected), array_values($actual));

        $queue = $this->connection('rabbitmq-with-quorum-options');
        $actual = $this->callMethod($queue, 'getQueueArguments', [$name]);
        $expected = [
            'x-dead-letter-exchange' => 'failed-exchange',
            'x-dead-letter-routing-key' => sprintf('application-x.%s.failed', $name),
            'x-queue-type' => 'quorum',
        ];

        $this->assertEquals(array_keys($expected), array_keys($actual));
        $this->assertEquals(array_values($expected), array_values($actual));

        $queue = $this->connection('rabbitmq-with-options-empty');
        $actual = $this->callMethod($queue, 'getQueueArguments', [$name]);
        $expected = [];

        $this->assertEquals(array_keys($expected), array_keys($actual));
        $this->assertEquals(array_values($expected), array_values($actual));
    }

    public function testDelayQueueArguments(): void
    {
        $name = Str::random();
        $ttl = 12000;

        $queue = $this->connection();
        $actual = $this->callMethod($queue, 'getDelayQueueArguments', [$name, $ttl]);
        $expected = [
            'x-dead-letter-exchange' => '',
            'x-dead-letter-routing-key' => $name,
            'x-message-ttl' => $ttl,
            'x-expires' => $ttl * 2,
        ];
        $this->assertEquals(array_keys($expected), array_keys($actual));
        $this->assertEquals(array_values($expected), array_values($actual));

        $queue = $this->connection('rabbitmq-with-options');
        $actual = $this->callMethod($queue, 'getDelayQueueArguments', [$name, $ttl]);
        $expected = [
            'x-dead-letter-exchange' => 'application-x',
            'x-dead-letter-routing-key' => sprintf('process.%s', $name),
            'x-message-ttl' => $ttl,
            'x-expires' => $ttl * 2,
        ];
        $this->assertEquals(array_keys($expected), array_keys($actual));
        $this->assertEquals(array_values($expected), array_values($actual));

        $queue = $this->connection('rabbitmq-with-options-empty');
        $actual = $this->callMethod($queue, 'getDelayQueueArguments', [$name, $ttl]);
        $expected = [
            'x-dead-letter-exchange' => '',
            'x-dead-letter-routing-key' => $name,
            'x-message-ttl' => $ttl,
            'x-expires' => $ttl * 2,
        ];
        $this->assertEquals(array_keys($expected), array_keys($actual));
        $this->assertEquals(array_values($expected), array_values($actual));
    }
}
