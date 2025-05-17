<?php
namespace Apie\LaravelApie\Wrappers\Queue;

use Apie\ApieBundle\Messenger\RunSequentialProcessMessage;
use Apie\Core\BackgroundProcess\SequentialBackgroundProcess;
use Apie\Core\Datalayers\Events\EntityPersisted;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BackgroundProcessPersistListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            EntityPersisted::class => 'onApieResourceUpdated'
        ];
    }

    public function onApieResourceUpdated(EntityPersisted $apieResourceCreated): void
    {
        $resource = $apieResourceCreated->entity;
        if ($resource instanceof SequentialBackgroundProcess) {
            RunBackgroundProcessJob::dispatch(new RunSequentialProcessMessage(
                $resource->getId(),
                $apieResourceCreated->boundedContextId
            ));
        }
    }
}
