<?php

namespace Freyo\LaravelQueueCMQ\Queue;

use Illuminate\Queue\WorkerOptions;
use W7\Mq\Consumer\ConsumerAbstract;

class CMQQueueConsumer extends ConsumerAbstract {
	public function consume($connectionName, $queue, WorkerOptions $options) {
		itimeTick($options->sleep * 1000, function () use ($connectionName, $queue, $options) {
			$this->runNextJob($connectionName, $queue, $options);
		});
	}
}