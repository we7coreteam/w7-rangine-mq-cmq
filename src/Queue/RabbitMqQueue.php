<?php

namespace Freyo\LaravelQueueCMQ\Queue;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMqQueue extends \W7\Mq\Queue\RabbitMQQueue {
	public function laterRaw($delay, $payload, $queue = null, $attempts = 0)
	{
		$ttl = $this->secondsUntil($delay) * 1000;

		// When no ttl just publish a new message to the exchange or queue
		if ($ttl <= 0) {
			return $this->pushRaw($payload, $queue, ['delay' => $delay, 'attempts' => $attempts]);
		}

		$queue = $this->getQueue($queue);
		$destination = $queue.'.delay';

		if (!$this->isExchangeExists($destination)) {
			$this->declareExchange($destination, 'x-delayed-message', false, false, [
				'x-delayed-type' => $this->getExchangeType()
			]);
			$this->bindQueue($queue, $destination, $this->getRoutingKey($queue));
		}

		/**
		 * @var AMQPMessage $message
		 */
		[$message, $correlationId] = $this->createMessage($payload, $attempts);
		/**
		 * @var  AMQPTable $headers
		 */
		$headers = $message->get('application_headers');
		$headers->set('x-delay', $ttl);
		$message->set('application_headers', $headers);

		// Publish directly on the delayQueue, no need to publish trough an exchange.
		$this->channel->basic_publish($message, $destination, $this->getRoutingKey($queue), true, false);

		return $correlationId;
	}
}