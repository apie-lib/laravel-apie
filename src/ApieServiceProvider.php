<?php
namespace Apie\LaravelApie;

use Apie\ApieCommonPlugin\ApieCommonPluginServiceProvider;
use Apie\CmsApiDropdownOption\CmsDropdownServiceProvider;
use Apie\Common\CommonServiceProvider;
use Apie\Common\Interfaces\BoundedContextSelection;
use Apie\Common\Interfaces\DashboardContentFactoryInterface;
use Apie\Common\Wrappers\BoundedContextHashmapFactory;
use Apie\Common\Wrappers\ConsoleCommandFactory as CommonConsoleCommandFactory;
use Apie\DoctrineEntityDatalayer\Commands\ApieUpdateIdfCommand;
use Apie\DoctrineEntityDatalayer\EntityReindexer;
use Apie\DoctrineEntityDatalayer\IndexStrategy\BackgroundIndexStrategy;
use Apie\DoctrineEntityDatalayer\IndexStrategy\DirectIndexStrategy;
use Apie\DoctrineEntityDatalayer\IndexStrategy\IndexAfterResponseIsSentStrategy;
use Apie\DoctrineEntityDatalayer\IndexStrategy\IndexStrategyInterface;
use Apie\LaravelApie\Config\LaravelConfiguration;
use Illuminate\Config\Repository;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Definition\Processor;

;
use Apie\Console\ConsoleServiceProvider;
use Apie\Core\CoreServiceProvider;
use Apie\Core\Session\CsrfTokenProvider;
use Apie\DoctrineEntityConverter\DoctrineEntityConverterProvider;
use Apie\DoctrineEntityDatalayer\DoctrineEntityDatalayerServiceProvider;
use Apie\Faker\FakerServiceProvider;
use Apie\HtmlBuilders\ErrorHandler\CmsErrorRenderer;
use Apie\HtmlBuilders\HtmlBuilderServiceProvider;
use Apie\LaravelApie\ContextBuilders\CsrfTokenContextBuilder;
use Apie\LaravelApie\ContextBuilders\RegisterBoundedContextActionContextBuilder;
use Apie\LaravelApie\ContextBuilders\SessionContextBuilder;
use Apie\LaravelApie\ErrorHandler\ApieErrorRenderer;
use Apie\LaravelApie\ErrorHandler\Handler;
use Apie\LaravelApie\Providers\CmsServiceProvider;
use Apie\LaravelApie\Providers\SecurityServiceProvider;
use Apie\LaravelApie\Wrappers\Cms\DashboardContentFactory;
use Apie\LaravelApie\Wrappers\Core\BoundedContextSelected;
use Apie\Maker\MakerServiceProvider;
use Apie\RestApi\RestApiServiceProvider;
use Apie\SchemaGenerator\SchemaGeneratorServiceProvider;
use Apie\Serializer\SerializerServiceProvider;
use Apie\ServiceProviderGenerator\TagMap;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Config\Resource\ReflectionClassResource;
use Symfony\Component\Console\Application;

class ApieServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, array<int, class-string<ServiceProvider>>> $dependencies
     */
    private array $dependencies = [
        'enable_common_plugin' => [
            ApieCommonPluginServiceProvider::class,
        ],
        'enable_cms' => [
            CommonServiceProvider::class,
            HtmlBuilderServiceProvider::class, // it's important that this loads before CmsServiceProvider!!!
            CmsServiceProvider::class,
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
        'enable_maker' => [
            MakerServiceProvider::class,
        ],
    ];

    private function autoTagHashmapActions(): void
    {
        $boundedContextConfig = config('apie.bounded_contexts');
        $scanBoundedContextConfig = config('apie.scan_bounded_contexts');
        $factory = new BoundedContextHashmapFactory($boundedContextConfig ?? [], $scanBoundedContextConfig ?? []);
        $hashmap = $factory->create();
        foreach ($hashmap as $boundedContext) {
            foreach ($boundedContext->actions as $action) {
                $class = $action->getDeclaringClass();
                if (!$class->isInstantiable()) {
                    continue;
                }
                $className = $class->name;
                TagMap::register(
                    $this->app,
                    $className,
                    ['apie.context']
                );
            }
        }
    }

    public function boot(): void
    {
        $this->autoTagHashmapActions();
        $this->loadViewsFrom(__DIR__ . '/../templates', 'apie');
        $this->loadRoutesFrom(__DIR__.'/../resources/routes.php');
        TagMap::registerEvents($this->app);

        if ($this->app->runningInConsole()) {
            $commands = [];
            $commands[] = ApieUpdateIdfCommand::class;
            // for some reason these are not called in integration tests without re-registering them
            foreach (TagMap::getServiceIdsWithTag($this->app, 'console.command') as $taggedCommand) {
                $serviceId = 'apie.console.tagged.' . $taggedCommand;
                $this->app->singleton($serviceId, function () use ($taggedCommand) {
                    return $this->app->get($taggedCommand);
                });
                $commands[] = $serviceId;
            }
            /** @var CommonConsoleCommandFactory $factory */
            $factory = $this->app->get('apie.console.factory');
            foreach ($factory->create($this->app->get(Application::class)) as $command) {
                $serviceId = 'apie.console.registered.' . $command->getName();
                $this->app->instance($serviceId, $command);
                $commands[] = $serviceId;
            }
            $this->commands($commands);
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

        $this->app->bind(IndexStrategyInterface::class, function () {
            $config = config();
            if ($config->get('apie.enable_doctrine_entity_datalayer')) {
                $type = $config->get('apie.doctrine.indexing.type', 'direct');
                return match ($type) {
                    'direct' => new DirectIndexStrategy($this->app->get(EntityReindexer::class)),
                    'late' => new IndexAfterResponseIsSentStrategy($this->app->get(EntityReindexer::class)),
                    'background' => new BackgroundIndexStrategy(),
                    default => $this->app->get(config('apie.doctrine.indexing.service', DirectIndexStrategy::class)),
                };
            }

            return new DirectIndexStrategy($this->app->get(EntityReindexer::class));
        });

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
        //$this->app->bind(CsrfTokenProvider::class, CsrfTokenContextBuilder::class);
        TagMap::register($this->app, CsrfTokenContextBuilder::class, ['apie.core.context_builder']);
        $this->app->tag(CsrfTokenContextBuilder::class, ['apie.core.context_builder']);

        // this has to be added after CsrfTokenContextBuilder!
        $this->app->bind(SessionContextBuilder::class);
        TagMap::register($this->app, SessionContextBuilder::class, ['apie.core.context_builder']);
        $this->app->tag(SessionContextBuilder::class, ['apie.core.context_builder']);

        TagMap::register($this->app, RegisterBoundedContextActionContextBuilder::class, ['apie.core.context_builder']);
        $this->app->tag(RegisterBoundedContextActionContextBuilder::class, ['apie.core.context_builder']);
        $this->app->extend('config', function (Repository $config) {
            $this->sanitizeConfig($config);
            return $config;
        });
    }

    private function sanitizeConfig(Repository $config): void
    {
        $rawConfig = $config->get('apie');
        $path = storage_path('framework/cache/apie-config' . md5(json_encode($rawConfig)) . '.php');
        $resources = [
            new ReflectionClassResource(new \ReflectionClass(LaravelConfiguration::class)),
            new ReflectionClassResource(new \ReflectionClass(static::class)),
        ];
        $configCache = new ConfigCache($path, true);
        if ($configCache->isFresh()) {
            $processedConfig = require $path;
        } else {
            $configuration = new LaravelConfiguration();

            $processor = new Processor();

            $processedConfig = $processor->processConfiguration($configuration, ['apie' => $rawConfig]);

            if (!isset($processedConfig['scan_bounded_contexts'])) {
                $processedConfig['scan_bounded_contexts'] = [];
            }
            if (empty($processedConfig['storage'])) {
                $processedConfig['storage'] = null;
            }
            $code = '<?php' . PHP_EOL . 'return ' . var_export($processedConfig, true) . ';';
            $configCache->write($code, $resources);
        }

        $config->set('apie', $processedConfig);
    }
}
