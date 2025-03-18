<?php
namespace Apie\LaravelApie\Wrappers\Core;

use Apie\Common\Interfaces\BoundedContextSelection;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\ContextConstants;

final class BoundedContextSelected implements BoundedContextSelection
{
    public function __construct(private readonly BoundedContextHashmap $boundedContextHashmap)
    {
    }

    public function getBoundedContextFromRequest(): ?BoundedContext
    {
        $request = app('request');
        if (!$request) {
            return null;
        }
        $route = $request->route();
        if ($route) {
            $parameters = $route->parameters();
            if (isset($parameters[ContextConstants::BOUNDED_CONTEXT_ID])) {
                return $this->boundedContextHashmap[$parameters[ContextConstants::BOUNDED_CONTEXT_ID]];
            }
            if (isset($parameters[ContextConstants::RESOURCE_NAME])) {
                return $this->getBoundedContextFromClassName($parameters[ContextConstants::RESOURCE_NAME]);
            }
        }
        return null;
    }

    public function getBoundedContextFromClassName(string $className): ?BoundedContext
    {
        return null;
    }
}
