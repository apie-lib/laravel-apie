<?php
namespace Apie\LaravelApie\ContextBuilders;

use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;

class RegisterBoundedContextActionContextBuilder implements ContextBuilderInterface
{
    public function process(ApieContext $context): ApieContext
    {
        if (!$context->hasContext(BoundedContextHashmap::class)) {
            return $context;
        }
        $hashmap = $context->getContext(BoundedContextHashmap::class);
        foreach ($hashmap as $boundedContext) {
            foreach ($boundedContext->actions as $action) {
                $className = $action->getDeclaringClass()->name;
                $context = $context->withContext($className, app($className));
            }
        }
        return $context;
    }
}
