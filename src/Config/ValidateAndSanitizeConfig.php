<?php
namespace Apie\LaravelApie\Config;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Resource\ReflectionClassResource;

final class ValidateAndSanitizeConfig
{
    /** @var array<string, bool> */
    private static array $alreadyProcessing = [];

    /** @var array<string, array<string, mixed>> */
    private static array $cache = [];

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $rawConfig
     * @return array<string, mixed>
     */
    public static function process(array $rawConfig): array
    {
        $key = md5(json_encode($rawConfig));
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        if (!empty(self::$alreadyProcessing[$key])) {
            return $rawConfig;
        }

        self::$alreadyProcessing[$key] = true;
        try {
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

        self::$cache[$key] = $processedConfig;
        return $processedConfig;
        } finally {
            unset(self::$alreadyProcessing[$key]);
        }
    }
}