<?php

namespace Freyo\LaravelQueueCMQ\Queue\Connectors;

use Freyo\LaravelQueueCMQ\Queue\RabbitMqQueue;
use PhpAmqpLib\Connection\AbstractConnection;

class RabbitMQConnector extends \W7\Mq\Connector\RabbitMQConnector {
	protected function createQueue(string $worker, AbstractConnection $connection, string $queue, array $options = []) {
		return new RabbitMqQueue($connection, $queue, $options);
	}
}