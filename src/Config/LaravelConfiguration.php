<?php

namespace Apie\LaravelApie\Config;

use Apie\Common\Config\Configuration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class LaravelConfiguration extends Configuration
{
    protected function addCmsOptions(ArrayNodeDefinition $arrayNode): void
    {
        $arrayNode->children()
            ->scalarNode('dashboard_template')->defaultValue('apie::dashboard')->end()
            ->scalarNode('error_template')->defaultValue('apie::error')->end()
            ->arrayNode('laravel_middleware')->defaultValue([])->scalarPrototype()->end();
    }

    protected function addApiOptions(ArrayNodeDefinition $arrayNode): void
    {
        $arrayNode->children()
            ->arrayNode('laravel_middleware')->defaultValue([])->scalarPrototype()->end();
    }
}
