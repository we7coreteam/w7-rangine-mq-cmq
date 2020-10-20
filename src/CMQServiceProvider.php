<?php

namespace Freyo\LaravelQueueCMQ;

use Freyo\LaravelQueueCMQ\Queue\CMQQueueConsumer;
use Freyo\LaravelQueueCMQ\Queue\Connectors\CMQConnector;
use W7\Core\Events\Dispatcher;
use W7\Core\Exception\HandlerExceptions;
use W7\Core\Provider\ProviderAbstract;
use W7\Mq\Facade\Queue;
use W7\Mq\QueueManager;

class CMQServiceProvider extends ProviderAbstract
{
	public function boot() {
		$this->registerCmq();
	}

	protected function registerCmq() {
		/**
		 * @var QueueManager $manager
		 */
		$manager = Queue::getFacadeRoot();
		$manager->addConnector('cmq', function () {
			return new CMQConnector();
		});
		$manager->addConsumer('cmq', function ($options = []) use ($manager) {
			return new CMQQueueConsumer($manager, $this->container->singleton(Dispatcher::class), $this->container->singleton(HandlerExceptions::class)->getHandler());
		});
	}
}
