<?php
namespace Apie\LaravelApie;

use Apie\CmsApiDropdownOption\CmsDropdownServiceProvider;
use Apie\Common\CommonServiceProvider;
use Apie\Common\Interfaces\BoundedContextSelection;
use Apie\Console\ConsoleServiceProvider;
use Apie\Core\CoreServiceProvider;
use Apie\DoctrineEntityConverter\DoctrineEntityConverterProvider;
use Apie\DoctrineEntityDatalayer\DoctrineEntityDatalayerServiceProvider;
use Apie\Faker\FakerServiceProvider;
use Apie\HtmlBuilders\HtmlBuilderServiceProvider;
use Apie\LaravelApie\Providers\CmsServiceProvider;
use Apie\LaravelApie\Providers\SecurityServiceProvider;
use Apie\LaravelApie\Wrappers\Core\BoundedContextSelected;
use Apie\RestApi\RestApiServiceProvider;
use Apie\SchemaGenerator\SchemaGeneratorServiceProvider;
use Apie\Serializer\SerializerServiceProvider;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;

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
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../resources/apie.php', 'apie');

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
