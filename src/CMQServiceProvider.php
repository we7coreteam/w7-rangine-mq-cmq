<?php

namespace Freyo\LaravelQueueCMQ;

use Freyo\LaravelQueueCMQ\Queue\CMQQueueConsumer;
use Freyo\LaravelQueueCMQ\Queue\Connectors\CMQConnector;
use W7\Contract\Queue\QueueFactoryInterface;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Provider\ProviderAbstract;
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
	}
}
