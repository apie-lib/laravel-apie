<?php
// apie config file.

use Apie\ApieCommonPlugin\ApieCommonPlugin;
use Apie\Cms\RouteDefinitions\CmsRouteDefinitionProvider;
use Apie\CmsApiDropdownOption\RouteDefinitions\DropdownOptionsForExistingObjectRouteDefinition;
use Apie\Common\Wrappers\RequestAwareInMemoryDatalayer;
use Apie\Console\ConsoleCommandFactory;
use Apie\DoctrineEntityConverter\OrmBuilder;
use Apie\DoctrineEntityDatalayer\DoctrineEntityDatalayer;
use Apie\Faker\ApieObjectFaker;
use Apie\Maker\Utils;
use Apie\RestApi\OpenApi\OpenApiGenerator;
use Apie\TwigTemplateLayoutRenderer\TwigRenderer;

return [
    'cms' => [
        'base_url' => '/cms',
        'dashboard_template' => 'apie::dashboard',
        'error_template' => 'apie::error',
        'asset_folders' => [
            // storage_path('overrides')
        ],
        'laravel_middleware' => [],
    ],
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
        ]
    ],
    'enable_core' => true,
    'enable_common_plugin' => class_exists(ApieCommonPlugin::class),
    'enable_cms' => class_exists(CmsRouteDefinitionProvider::class),
    'enable_cms_dropdown' => class_exists(DropdownOptionsForExistingObjectRouteDefinition::class),
    'enable_doctrine_entity_converter' => class_exists(OrmBuilder::class),
    'enable_doctrine_entity_datalayer' => class_exists(DoctrineEntityDatalayer::class),
    /* 'enable_doctrine_bundle_connection'  symfony only*/
    'enable_faker' => class_exists(ApieObjectFaker::class),
    'enable_maker' => class_exists(Utils::class),
    'enable_rest_api' => class_exists(OpenApiGenerator::class),
    'enable_console' => class_exists(ConsoleCommandFactory::class),
    'enable_twig_template_layout_renderer' => class_exists(TwigRenderer::class),
];
