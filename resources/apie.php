<?php
// apie config file.

use Apie\AiInstructor\AiInstructor;
use Apie\ApieCommonPlugin\ApieCommonPlugin;
use Apie\Cms\RouteDefinitions\CmsRouteDefinitionProvider;
use Apie\CmsApiDropdownOption\RouteDefinitions\DropdownOptionsForExistingObjectRouteDefinition;
use Apie\Common\Wrappers\RequestAwareInMemoryDatalayer;
use Apie\Console\ConsoleCommandFactory;
use Apie\DoctrineEntityConverter\OrmBuilder;
use Apie\DoctrineEntityDatalayer\DoctrineEntityDatalayer;
use Apie\Export\ExportServiceProvider;
use Apie\Faker\ApieObjectFaker;
use Apie\FtpServer\FtpServerCommand;
use Apie\Graphql\RouteDefinitions\GraphqlRouteDefinition;
use Apie\LaravelApie\Config\ValidateAndSanitizeConfig;
use Apie\Maker\Utils;
use Apie\McpServer\Controllers\RemoteMcpController;
use Apie\RestApi\OpenApi\OpenApiGenerator;
use Apie\TwigTemplateLayoutRenderer\TwigRenderer;
use Apie\TypescriptClientBuilder\RouteDefinitions\CodeRouteDefinitionProvider;
use Apie\Webdav\Dav\ApieDirectory;
use Symfony\Component\Lock\Store\FlockStore;

return ValidateAndSanitizeConfig::process([
    'cms' => [
        'base_url' => '/cms',
        'dashboard_template' => 'apie::dashboard',
        'error_template' => 'apie::error',
        'asset_folders' => [
            // storage_path('overrides')
        ],
        'laravel_middleware' => [],
    ],
    'lock_store' => FlockStore::class,
    'rest_api' => [
        'base_url' => '/api',
        'laravel_middleware' => [],
    ],
    'datalayers' => [
        'default_datalayer' => RequestAwareInMemoryDatalayer::class,
        'context_mapping' => [
            // 'bounded context id' => [
            //  'default_datalayer' => DataLayer::class,
            //  'entity_mapping' => [
            //    ClassName::class => DataLayer::class,
            //  ]
            //]
        ]
    ],
    'doctrine' => [
        'build_once' => false,
        'run_migrations' => false,
        'connection_params' => [],
    ],
    'storage' => null,
    'maker' => [
        'target_path' => false,
        'target_namespace' => 'App\Apie',
    ],
    'bounded_contexts' => [
        'default' => [
            'entities_folder' => app_path('Apie/Entities'),
            'entities_namespace' => 'App\\Apie\\Entities\\',
            'actions_folder' => app_path('Apie/Actions'),
            'actions_namespace' => 'App\\Apie\\Actions\\',
            'policies_folder' => app_path('Apie/Policies'),
            'policies_namespace' => 'App\\Apie\\Policies\\',
        ]
    ],
    'enable_ai_instructor' => class_exists(AiInstructor::class),
    'enable_core' => true,
    'enable_common_plugin' => class_exists(ApieCommonPlugin::class),
    'enable_cms' => class_exists(CmsRouteDefinitionProvider::class),
    'enable_cms_dropdown' => class_exists(DropdownOptionsForExistingObjectRouteDefinition::class),
    'enable_doctrine_entity_converter' => class_exists(OrmBuilder::class),
    'enable_doctrine_entity_datalayer' => class_exists(DoctrineEntityDatalayer::class),
    /* 'enable_doctrine_bundle_connection'  symfony only*/
    'enable_export' => class_exists(ExportServiceProvider::class),
    'enable_faker' => class_exists(ApieObjectFaker::class),
    'enable_ftp' => class_exists(FtpServerCommand::class),
    'enable_graphql' => class_exists(GraphqlRouteDefinition::class),
    'enable_maker' => class_exists(Utils::class),
    'enable_mcp_server' => class_exists(RemoteMcpController::class),
    'remote_mcp_path' => null,
    'enable_rest_api' => class_exists(OpenApiGenerator::class),
    'enable_security' => true,
    'enable_console' => class_exists(ConsoleCommandFactory::class),
    'enable_twig_template_layout_renderer' => class_exists(TwigRenderer::class),
    'enable_typescript_client_builder' => class_exists(CodeRouteDefinitionProvider::class),
    'enable_webdav' => class_exists(ApieDirectory::class),
]);
