<?php
namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Common\Interfaces\UserDecorator;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;

class UserAuthenticationContextBuilder implements ContextBuilderInterface
{
    public function process(ApieContext $context): ApieContext
    {
        // @phpstan-ignore method.notFound
        $user = auth()->user();
        if ($user) {
            $context = $context->registerInstance($user);

            if ($user instanceof UserDecorator) {
                $context = $context->withContext('authenticated', $user->getEntity());
            }
        }

        return $context;
    }
}
