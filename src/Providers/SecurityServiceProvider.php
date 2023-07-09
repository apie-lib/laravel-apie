<?php
namespace Apie\LaravelApie\Providers;

use Apie\ApieBundle\Security\ApieUserProvider;
use Apie\LaravelApie\Wrappers\Security\ApieUserAuthenticator;
use Apie\LaravelApie\Wrappers\Security\UserAuthenticationContextBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;

class SecurityServiceProvider extends ServiceProvider
{
    public function register()
    {
        // sf variation: security.yaml
        $this->app->bind(UserAuthenticationContextBuilder::class);
        $this->app->tag(UserAuthenticationContextBuilder::class, ['apie.core.context_builder']);
        Auth::provider('apie', function ($app) {
            return new ApieUserProvider($app->get('apie'));
        });
        Auth::viaRequest('apie', function () {
            $psrRequest = $this->app->get(ServerRequestInterface::class);
            $userAuthenticator = $this->app->get(ApieUserAuthenticator::class);
            return $userAuthenticator->authenticate($psrRequest);
        });
    }
}
