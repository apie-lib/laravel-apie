<?php
namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Common\Interfaces\UserDecorator;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;

class UserAuthenticationContextBuilder implements ContextBuilderInterface
{
    public function __construct(
        private readonly LaravelUserDecoratorFactory $decoratorFactory
    ) {
    }
    public function process(ApieContext $context): ApieContext
    {
        // @phpstan-ignore method.notFound
        $user = auth()->user();
        if ($user) {
            $context = $context->registerInstance($user);

            if ($user instanceof UserDecorator) {
                $context = $context->withContext(ContextConstants::AUTHENTICATED_USER, $user->getEntity());
            } else {
                $context = $context->withContext(ContextConstants::AUTHENTICATED_USER, $this->decoratorFactory->create($user));
            }
        }

        return $context;
    }
}
