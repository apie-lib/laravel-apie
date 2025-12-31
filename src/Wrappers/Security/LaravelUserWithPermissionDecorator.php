<?php

namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Core\Lists\PermissionList;
use Apie\Core\Permissions\PermissionInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class LaravelUserWithPermissionDecorator extends LaravelUserDecorator implements PermissionInterface
{
    public function __construct(Authenticatable&PermissionInterface $user)
    {
        parent::__construct($user);
    }

    public function getPermissionIdentifiers(): PermissionList
    {
        $user = $this->getUser();
        assert($user instanceof PermissionInterface);
        return $user->getPermissionIdentifiers();
    }
}
