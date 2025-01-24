<?php

namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Core\Entities\EntityInterface;
use Apie\Core\Permissions\PermissionInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class LaravelUserDecoratorFactory
{
    public function create(Authenticatable $user): LaravelUserDecorator|EntityInterface
    {
        if ($user instanceof EntityInterface) {
            return $user;
        }
        if ($user instanceof PermissionInterface) {
            return new LaravelUserWithPermissionDecorator($user);
        }
        return new LaravelUserDecorator($user);
    }
}
