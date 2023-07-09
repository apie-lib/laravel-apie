<?php
namespace Apie\LaravelApie\Providers;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: services.yaml
 * @codecoverageIgnore
 */
class ApieServicesServiceProvider extends ServiceProvider
{
    use UseGeneratedMethods;

    public function register()
    {
        $this->app->singleton(
            \Apie\ApieBundle\Routing\ApieRouteLoader::class,
            function ($app) {
                return new \Apie\ApieBundle\Routing\ApieRouteLoader(
                    $app->make('apie.route_definitions.provider'),
                    $app->make('apie.bounded_context.hashmap'),
                    $app->make(\Apie\Common\RouteDefinitions\PossibleRoutePrefixProvider::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\ApieBundle\Routing\ApieRouteLoader::class,
            array(
              0 => 'routing.loader',
            )
        );
        $this->app->tag([\Apie\ApieBundle\Routing\ApieRouteLoader::class], 'routing.loader');
        $this->app->singleton(
            \Apie\ApieBundle\ContextBuilders\ServiceContextBuilder::class,
            function ($app) {
                return new \Apie\ApieBundle\ContextBuilders\ServiceContextBuilder(
                    $this->getTaggedServicesServiceLocator('apie.context')
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\ApieBundle\ContextBuilders\ServiceContextBuilder::class,
            array(
              0 => 'apie.core.context_builder',
            )
        );
        $this->app->tag([\Apie\ApieBundle\ContextBuilders\ServiceContextBuilder::class], 'apie.core.context_builder');
        $this->app->singleton(
            \Apie\ApieBundle\ContextBuilders\SessionContextBuilder::class,
            function ($app) {
                return new \Apie\ApieBundle\ContextBuilders\SessionContextBuilder(
                    $app->make('request_stack')
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\ApieBundle\ContextBuilders\SessionContextBuilder::class,
            array(
              0 => 'apie.core.context_builder',
            )
        );
        $this->app->tag([\Apie\ApieBundle\ContextBuilders\SessionContextBuilder::class], 'apie.core.context_builder');
        $this->app->singleton(
            \Apie\ApieBundle\ContextBuilders\CsrfTokenContextBuilder::class,
            function ($app) {
                return new \Apie\ApieBundle\ContextBuilders\CsrfTokenContextBuilder(
                    $app->bound(\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface::class) ? $app->make(\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface::class) : null
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\ApieBundle\ContextBuilders\CsrfTokenContextBuilder::class,
            array(
              0 => 'apie.core.context_builder',
            )
        );
        $this->app->tag([\Apie\ApieBundle\ContextBuilders\CsrfTokenContextBuilder::class], 'apie.core.context_builder');
        $this->app->bind(\Apie\Common\Interfaces\BoundedContextSelection::class, \Apie\ApieBundle\Wrappers\BoundedContextSelected::class);
        
        $this->app->singleton(
            \Apie\ApieBundle\Wrappers\BoundedContextSelected::class,
            function ($app) {
                return new \Apie\ApieBundle\Wrappers\BoundedContextSelected(
                    $app->make('request_stack'),
                    $app->make(\Apie\Core\BoundedContext\BoundedContextHashmap::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\ApieBundle\Wrappers\BoundedContextSelected::class,
            array(
              0 => 'apie.context',
            )
        );
        $this->app->tag([\Apie\ApieBundle\Wrappers\BoundedContextSelected::class], 'apie.context');
        $this->app->singleton(
            \Apie\ApieBundle\EventListeners\RenderErrorListener::class,
            function ($app) {
                return new \Apie\ApieBundle\EventListeners\RenderErrorListener(
                    $app->bound(\Apie\HtmlBuilders\Factories\ComponentFactory::class) ? $app->make(\Apie\HtmlBuilders\Factories\ComponentFactory::class) : null,
                    $app->bound(\Apie\HtmlBuilders\Interfaces\ComponentRendererInterface::class) ? $app->make(\Apie\HtmlBuilders\Interfaces\ComponentRendererInterface::class) : null,
                    $app->bound(\Twig\Environment::class) ? $app->make(\Twig\Environment::class) : null,
                    $this->parseArgument('%apie.cms.base_url%'),
                    $this->parseArgument('%apie.cms.error_template%')
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\ApieBundle\EventListeners\RenderErrorListener::class,
            array(
              0 => 'kernel.event_subscriber',
            )
        );
        $this->app->tag([\Apie\ApieBundle\EventListeners\RenderErrorListener::class], 'kernel.event_subscriber');
        
    }
}
