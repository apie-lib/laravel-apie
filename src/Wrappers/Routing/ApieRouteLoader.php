<?php
namespace Apie\LaravelApie\Wrappers\Routing;

use Apie\Common\Enums\UrlPrefix;
use Apie\Common\Interfaces\GlobalRouteDefinitionProviderInterface;
use Apie\Common\Interfaces\HasRouteDefinition;
use Apie\Common\Interfaces\RouteDefinitionProviderInterface;
use Apie\Common\RouteDefinitions\PossibleRoutePrefixProvider;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\Core\Enums\RequestMethod;
use Apie\Core\ValueObjects\Utils;
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
        $cmsMiddleware = Utils::toArray(config('apie.cms.laravel_middleware') ?? []);
        $apiMiddleware = Utils::toArray(config('apie.rest_api.laravel_middleware') ?? []);
        
        if ($this->routeProvider instanceof GlobalRouteDefinitionProviderInterface) {
            foreach ($this->routeProvider->getGlobalRoutes() as $routeDefinition) {
                /** @var HasRouteDefinition $routeDefinition */
                $url = $routeDefinition->getUrl();
                $placeholders = $url->getPlaceholders();
                $path = ltrim($url->toNative(), '/');
                $method = $routeDefinition->getMethod();
                $defaults = $routeDefinition->getRouteAttributes()
                    + [
                        '_is_apie' => true,
                        'uses' => $routeDefinition->getController(),
                    ];
                /** @var \Illuminate\Routing\Route $route */
                if ($method === RequestMethod::ANY) {
                    $route = $routeRegistrar->match(
                        array_map(fn ($s) => $s->value, RequestMethod::cases()),
                        $path,
                        $routeDefinition->getController()
                    );
                } else {
                    // @phpstan-ignore-next-line method.notFound
                    $route = $routeRegistrar->{strtolower($method->value)}($path, $routeDefinition->getController());
                }
                $route->defaults += $defaults;
                $route->name('apie.global.' . $routeDefinition->getOperationId());
                $route->middleware([StartSession::class, VerifyApieUser::class, ...$cmsMiddleware]);
                if (in_array('properties', $placeholders)) {
                    $route->where('properties', '[a-zA-Z0-9]+(/[a-zA-Z0-9]+)*');
                }
                if (in_array('path', $placeholders)) {
                    $route->where('path', '.*');
                }
            }
        }
        
        foreach ($this->boundedContextHashmap as $boundedContextId => $boundedContext) {
            foreach ($this->routeProvider->getActionsForBoundedContext($boundedContext, $apieContext) as $routeDefinition) {
                /** @var HasRouteDefinition $routeDefinition */
                $prefix = $this->routePrefixProvider->getPossiblePrefixes($routeDefinition);
                $url = $routeDefinition->getUrl();
                $placeholders = $url->getPlaceholders();
                $path = $prefix . $boundedContextId . '/' . ltrim($url->toNative(), '/');

                $method = $routeDefinition->getMethod();
                $defaults = $routeDefinition->getRouteAttributes()
                    + [
                        '_is_apie' => true,
                        'uses' => $routeDefinition->getController(),
                    ];
                /** @var \Illuminate\Routing\Route $route */
                if ($method === RequestMethod::ANY) {
                    $route = $routeRegistrar->match(
                        array_map(fn ($s) => $s->value, RequestMethod::cases()),
                        $path,
                        $routeDefinition->getController()
                    );
                } else {
                    // @phpstan-ignore-next-line method.notFound
                    $route = $routeRegistrar->{strtolower($method->value)}($path, $routeDefinition->getController());
                }
                $route->defaults += $defaults;
                $route->name('apie.' . $boundedContextId . '.' . $routeDefinition->getOperationId());
                foreach ($routeDefinition->getUrlPrefixes() as $urlPrefix) {
                    if ($urlPrefix === UrlPrefix::CMS) {
                        $route->middleware([StartSession::class, VerifyApieUser::class, ...$cmsMiddleware]);
                    } else {
                        $route->middleware([StartSession::class, VerifyApieUser::class, ...$apiMiddleware]);
                    }
                }
                $route->wheres = $prefix->getRouteRequirements();
                if (in_array('properties', $placeholders)) {
                    $route->where('properties', '[a-zA-Z0-9]+(/[a-zA-Z0-9]+)*');
                }
                if (in_array('path', $placeholders)) {
                    $route->where('path', '.*');
                }
            }
        }
    }
}
