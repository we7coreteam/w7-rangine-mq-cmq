<?php

/**
 * WeEngine Api System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace Freyo\LaravelQueueCMQ\Queue;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMqQueue extends \W7\Mq\Queue\RabbitMQQueue {
	/**
	 * Create a AMQP message.
	 *
	 * @param $payload
	 * @param int $attempts
	 * @return array
	 */
	protected function createMessage($payload, int $attempts = 0): array {
		$properties = [
			'content_type' => 'application/json',
			'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
		];

		if ($correlationId = json_decode($payload, true)['id'] ?? null) {
			$properties['correlation_id'] = $correlationId;
		}

		if ($this->isPrioritizeDelayed()) {
			$properties['priority'] = $attempts;
		}

		$message = new AMQPMessage($payload, $properties);

		$message->set('application_headers', new AMQPTable([
			'laravel.attempts' => $attempts,
			'laravel' => [
				'attempts' => $attempts,
			],
		]));

		return [
			$message,
			$correlationId,
		];
	}

	public function laterRaw($delay, $payload, $queue = null, $attempts = 0) {
		$ttl = $this->secondsUntil($delay) * 1000;

		// When no ttl just publish a new message to the exchange or queue
		if ($ttl <= 0) {
			return $this->pushRaw($payload, $queue, ['delay' => $delay, 'attempts' => $attempts]);
		}

		$queue = $this->getQueue($queue);
		$destination = $queue . '.delay';

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
