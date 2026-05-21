<?php
namespace Apie\Tests\LaravelApie\Fixtures\Actions;

use Apie\Core\Attributes\Context;
use Apie\Core\Attributes\Route;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Enums\RequestMethod;
use Apie\LaravelApie\Apie;
use Apie\Tests\LaravelApie\Fixtures\Entities\User;
use Apie\Tests\LaravelApie\Fixtures\ValueObjects\UserIdentifier;

class Authentication
{
    public static function verifyAuthentication(string $username, string $password): ?User
    {
        if ($username === 'admin' && $password === 'admin') {
            return new User(UserIdentifier::fromNative('admin'));
        }

        return null;
    }

    #[Route('/mockLogin')]
    public static function createUserAndLogin(
        #[Context()] ApieDatalayer $apieDatalayer,
        User $user
    ): User {
        $user = $apieDatalayer->persistNew($user);
        Apie::loginAs($user, 'default');
        return $user;
    }

    #[Route('/me', requestMethod: RequestMethod::GET)]
    public static function currentUser(#[Context] ?User $authenticated = null): ?User
    {
        return $authenticated;;
    }
}
