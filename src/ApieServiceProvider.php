<?php
namespace Apie\LaravelApie;

use Apie\CmsApiDropdownOption\CmsDropdownServiceProvider;
use Apie\Common\CommonServiceProvider;
use Apie\Common\Interfaces\BoundedContextSelection;
use Apie\Common\Interfaces\DashboardContentFactoryInterface;
use Apie\Common\Wrappers\ConsoleCommandFactory as CommonConsoleCommandFactory;
use Apie\Console\ConsoleServiceProvider;
use Apie\Core\CoreServiceProvider;
use Apie\DoctrineEntityConverter\DoctrineEntityConverterProvider;
use Apie\DoctrineEntityDatalayer\DoctrineEntityDatalayerServiceProvider;
use Apie\Faker\FakerServiceProvider;
use Apie\HtmlBuilders\ErrorHandler\CmsErrorRenderer;
use Apie\HtmlBuilders\HtmlBuilderServiceProvider;
use Apie\LaravelApie\ErrorHandler\ApieErrorRenderer;
use Apie\LaravelApie\ErrorHandler\Handler;
use Apie\LaravelApie\Providers\CmsServiceProvider;
use Apie\LaravelApie\Providers\SecurityServiceProvider;
use Apie\LaravelApie\Wrappers\Cms\DashboardContentFactory;
use Apie\LaravelApie\Wrappers\Core\BoundedContextSelected;
use Apie\RestApi\RestApiServiceProvider;
use Apie\SchemaGenerator\SchemaGeneratorServiceProvider;
use Apie\Serializer\SerializerServiceProvider;
use Apie\ServiceProviderGenerator\TagMap;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Application;

class ApieServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, array<int, class-string<ServiceProvider>>> $dependencies
     */
    private array $dependencies = [
        'enable_cms' => [
            CommonServiceProvider::class,
            CmsServiceProvider::class,
            HtmlBuilderServiceProvider::class,
            SerializerServiceProvider::class,
        ],
        'enable_cms_dropdown' => [
            CommonServiceProvider::class,
            CmsDropdownServiceProvider::class,
        ],
        'enable_core' => [
            CoreServiceProvider::class,
        ],
        'enable_console' => [
            CommonServiceProvider::class,
            ConsoleServiceProvider::class,
            SerializerServiceProvider::class,
        ],
        'enable_doctrine_entity_converter' => [
            CoreServiceProvider::class,
            DoctrineEntityConverterProvider::class,
        ],
        'enable_doctrine_entity_datalayer' => [
            CoreServiceProvider::class,
            DoctrineEntityConverterProvider::class,
            DoctrineEntityDatalayerServiceProvider::class,
        ],
        'enable_security' => [
            CommonServiceProvider::class,
            SerializerServiceProvider::class,
            SecurityServiceProvider::class,
        ],
        'enable_rest_api' => [
            CommonServiceProvider::class,
            RestApiServiceProvider::class,
            SchemaGeneratorServiceProvider::class,
            SerializerServiceProvider::class,
        ],
        'enable_faker' => [
            FakerServiceProvider::class,
        ],
    ];

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../templates', 'apie');
        $this->loadRoutesFrom(__DIR__.'/../resources/routes.php');
        TagMap::registerEvents($this->app);
        if ($this->app->runningInConsole()) {
            $this->commands(TagMap::getServiceIdsWithTag($this->app, 'console.command'));
            /** @var CommonConsoleCommandFactory $factory */
            $factory = $this->app->get('apie.console.factory');
            $this->commands(iterator_to_array($factory->create($this->app->get(Application::class))));
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../resources/apie.php', 'apie');

        // add PSR-14 support if needed:
        if (!$this->app->bound(EventDispatcherInterface::class)) {
            $this->app->bind(EventDispatcherInterface::class, function () {
                return new class($this->app->make(Dispatcher::class)) implements EventDispatcherInterface {
                    public function __construct(private readonly Dispatcher $dispatcher)
                    {
                    }

                    public function dispatch(object $event): object
                    {
                        $this->dispatcher->dispatch($event);
                        return $event;
                    }
                };
            });
        }

        // fix for https://github.com/laravel/framework/issues/30415
        $this->app->extend(
            ServerRequestInterface::class,
            function (ServerRequestInterface $psrRequest) {
                $route = $this->app->make('request')->route();
                if ($route) {
                    $parameters = $route->parameters();
                    foreach ($parameters as $key => $value) {
                        $psrRequest = $psrRequest->withAttribute($key, $value);
                    }
                }
                return $psrRequest;
            }
        );

        $this->app->bind(ApieErrorRenderer::class, function () {
            return new ApieErrorRenderer(
                $this->app->bound(CmsErrorRenderer::class) ? $this->app->make(CmsErrorRenderer::class) : null,
                $this->app->make(\Apie\Common\ErrorHandler\ApiErrorRenderer::class),
                config('apie.cms.base_url')
            );
        });

        $this->app->extend(ExceptionHandler::class, function (ExceptionHandler $service) {
            return new Handler($this->app, $service);
        });
        
        $this->app->bind(DashboardContentFactoryInterface::class, DashboardContentFactory::class);
        $this->app->bind(BoundedContextSelection::class, BoundedContextSelected::class);

        $alreadyRegistered = [];
        foreach ($this->dependencies as $configKey => $dependencies) {
            if (config('apie.' . $configKey, false)) {
                foreach ($dependencies as $dependency) {
                    if (!isset($alreadyRegistered[$dependency])) {
                        $alreadyRegistered[$dependency] = $dependency;
                        $this->app->register($dependency);
                    }
                }
            }
        }
    }
}
