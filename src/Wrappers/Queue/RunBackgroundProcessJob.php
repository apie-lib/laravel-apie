<?php
namespace Apie\LaravelApie\Wrappers\Queue;

use Apie\Core\BackgroundProcess\SequentialBackgroundProcessIdentifier;
use Apie\Core\BackgroundProcess\Utils;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\Core\Datalayers\ApieDatalayer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunBackgroundProcessJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private SequentialBackgroundProcessIdentifier $processId,
        private ?BoundedContextId $boundedContextId = null,
    ) {
    }

    public function getProcessId(): SequentialBackgroundProcessIdentifier
    {
        return $this->processId;
    }

    public function getBoundedContextId(): ?BoundedContextId
    {
        return $this->boundedContextId;
    }

    public function handle(
        ApieDatalayer $apieDatalayer,
        ContextBuilderFactory $contextBuilderFactory
    ): void {
        Utils::runBackgroundProcess(
            $this->getProcessId(),
            $this->getBoundedContextId(),
            $apieDatalayer,
            $contextBuilderFactory
        );
    }
}
