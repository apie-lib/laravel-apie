<?php
// apie config file.

use Apie\Cms\RouteDefinitions\CmsRouteDefinitionProvider;
use Apie\CmsApiDropdownOption\RouteDefinitions\DropdownOptionsForExistingObjectRouteDefinition;
use Apie\Common\Wrappers\RequestAwareInMemoryDatalayer;
use Apie\Console\ConsoleCommandFactory;
use Apie\DoctrineEntityConverter\EntityBuilder;
use Apie\DoctrineEntityDatalayer\DoctrineEntityDatalayer;
use Apie\Faker\ApieObjectFaker;
use Apie\RestApi\OpenApi\OpenApiGenerator;

return [
    'cms' => [
        'base_url' => '/cms',
        'dashboard_template' => 'apie::dashboard',
        'error_template' => 'apie::error',
        'asset_folders' => [
            // storage_path('overrides')
        ]
    ],
    'rest_api' => [
        'base_url' => '/api',
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
    'bounded_contexts' => [
        'default' => [
            'entities_folder' => app_path('Apie/Entities'),
            'entities_namespace' => 'App\\Apie\\Entities\\',
            'actions_folder' => app_path('Apie/Actions'),
            'actions_namespace' => 'App\\Apie\\Actions\\',
        ]
    ],
    'enable_core' => true,
    'enable_cms' => class_exists(CmsRouteDefinitionProvider::class),
    'enable_cms_dropdown' => class_exists(DropdownOptionsForExistingObjectRouteDefinition::class),
    'enable_doctrine_entity_converter' => class_exists(EntityBuilder::class),
    'enable_doctrine_entity_datalayer' => class_exists(DoctrineEntityDatalayer::class),
    /* 'enable_doctrine_bundle_connection'  symfony only*/
    'enable_faker' => class_exists(ApieObjectFaker::class),
    'enable_rest_api' => class_exists(OpenApiGenerator::class),
    'enable_console' => class_exists(ConsoleCommandFactory::class),
    'enable_security' => true,
];
