<?php

namespace Freyo\LaravelQueueCMQ;

use Freyo\LaravelQueueCMQ\Queue\CMQQueueConsumer;
use Freyo\LaravelQueueCMQ\Queue\Connectors\CMQConnector;
use Freyo\LaravelQueueCMQ\Queue\Connectors\RabbitMQConnector;
use Illuminate\Container\Container;
use W7\Contract\Queue\QueueFactoryInterface;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Provider\ProviderAbstract;
use W7\Mq\Consumer\RabbitMQConsumer;
use W7\Mq\QueueManager;

class CMQServiceProvider extends ProviderAbstract {
	public function boot() {
		$this->registerCmq();
	}

	protected function registerCmq() {
		/**
		 * @var QueueManager $manager
		 */
		$manager = $this->container->get(QueueFactoryInterface::class);
		$manager->addConnector('cmq', function () {
			return new CMQConnector();
		});
		$manager->addConsumer('cmq', function ($options = []) use ($manager) {
			return new CMQQueueConsumer($manager, $this->getEventDispatcher(), $this->container->get(HandlerExceptions::class)->getHandler());
		});

		$manager->addConnector('tdmq_cmq', function () {
			return new CMQConnector();
		});
		$manager->addConsumer('tdmq_cmq', function ($options = []) use ($manager) {
			return new CMQQueueConsumer($manager, $this->getEventDispatcher(), $this->container->get(HandlerExceptions::class)->getHandler());
		});

		$manager->addConnector('tdmq_rabbit_mq', function () {
			return new RabbitMQConnector($this->getEventDispatcher());
		});
		$manager->addConsumer('tdmq_rabbit_mq', function ($options = []) use ($manager) {
			$consumer = new RabbitMQConsumer($manager, $this->getEventDispatcher(), $this->container->get(HandlerExceptions::class)->getHandler());
			$consumer->setContainer(Container::getInstance());
			$consumer->setConsumerTag($this->container->get('rabbit-mq-server-tag-resolver')($options));
			$consumer->setPrefetchCount($options['prefetch_count'] ?? 0);
			$consumer->setPrefetchSize($options['prefetch_size'] ?? 0);

			return $consumer;
		});
	}
}
