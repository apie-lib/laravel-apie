<?php
namespace Apie\LaravelApie\Wrappers\Routing;

use Apie\Common\Enums\UrlPrefix;
use Apie\Common\Interfaces\HasRouteDefinition;
use Apie\Common\Interfaces\RouteDefinitionProviderInterface;
use Apie\Common\RouteDefinitions\PossibleRoutePrefixProvider;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\LaravelApie\Wrappers\Security\VerifyApieUser;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Session\Middleware\StartSession;

class ApieRouteLoader
{
    private bool $loaded = false;

    public function __construct(
        private readonly RouteDefinitionProviderInterface $routeProvider,
        private readonly BoundedContextHashmap $boundedContextHashmap,
        private readonly PossibleRoutePrefixProvider $routePrefixProvider
    ) {
    }

    public function loadRoutes(RouteRegistrar $routeRegistrar): void
    {
        if ($this->loaded === true) {
            throw new \RuntimeException('Do not load the "ApieRouteLoader" twice!');
        }
        $this->loaded = true;
        $apieContext = new ApieContext([]);
        foreach ($this->boundedContextHashmap as $boundedContextId => $boundedContext) {
            foreach ($this->routeProvider->getActionsForBoundedContext($boundedContext, $apieContext) as $routeDefinition) {
                /** @var HasRouteDefinition $routeDefinition */
                $prefix = $this->routePrefixProvider->getPossiblePrefixes($routeDefinition);

                $path = $prefix . $boundedContextId . '/' . ltrim($routeDefinition->getUrl(), '/');

                $method = $routeDefinition->getMethod();
                $defaults = $routeDefinition->getRouteAttributes()
                    + [
                        '_is_apie' => true,
                        'uses' => $routeDefinition->getController(),
                    ];
                /** @var \Illuminate\Routing\Route $route */
                $route = $routeRegistrar->{strtolower($method->value)}($path, $routeDefinition->getController());
                $route->defaults += $defaults;
                $route->name('apie.' . $boundedContextId . '.' . $routeDefinition->getOperationId());
                foreach ($routeDefinition->getUrlPrefixes() as $urlPrefix) {
                    if ($urlPrefix === UrlPrefix::CMS) {
                        $route->middleware([StartSession::class, VerifyApieUser::class]);
                    } else {
                        $route->middleware([VerifyApieUser::class]);
                    }
                }
                $route->wheres = $prefix->getRouteRequirements();
            }
        }
    }
}
