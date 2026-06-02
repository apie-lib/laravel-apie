<?php
namespace Apie\LaravelApie\ContextBuilders;

use Apie\Common\Events\AddAuthenticationCookie;
use Apie\Common\Events\ApieResponseCreated;
use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\LaravelApie\Apie;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApieCurrentUserContextBuilder implements ContextBuilderInterface, EventSubscriberInterface
{
    public function __construct(private readonly AddAuthenticationCookie $addAuthenticationCookie)
    {
    }

    /**
     * The problem here is that we are not certain what Laravel middleware is setup to get the current user from the Laravel
     * session. We could set the auth middleware in the Apie config, but then all endpoints would require authentication.
     *
     * So as a workaround we store the current user in the Apie facade, even though it is a terrible solution.
     */
    public function process(ApieContext $context): ApieContext
    {
        $currentUser = Apie::getCurrentUser();
        if ($currentUser) {
            $context = $context->withContext(DecryptedAuthenticatedUser::class, $currentUser);
        }
        return $context;
    }

    public function onApieResponseCreated(ApieResponseCreated $event): void
    {
        if (!$event->context->hasContext(DecryptedAuthenticatedUser::class)) {
            $currentUser = Apie::getCurrentUser();
            $event->context = $event->context->withContext(DecryptedAuthenticatedUser::class, $currentUser);
            $this->addAuthenticationCookie->onApieResponseCreated(
                $event
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApieResponseCreated::class => 'onApieResponseCreated',
        ];
    }
}
