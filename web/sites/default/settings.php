<?php

/**
 * @file
 * Lagoon Drupal 8 configuration file.
 *
 * You should not edit this file, please use environment-specific files!
 * They are loaded in this order:
 * - all.settings.php
 *   For settings that should be applied to all environments (dev, prod, staging, docker, etc).
 * - all.services.yml
 *   For services that should be applied to all environments (dev, prod, staging, docker, etc).
 * - production.settings.php
 *   For settings only for the production environment.
 * - production.services.yml
 *   For services only for the production environment.
 * - development.settings.php
 *   For settings only for the development environment (development sites, Docker).
 * - development.services.yml
 *   For services only for the development environment (development sites, Docker).
 * - settings.local.php
 *   For settings only for the local environment, this file will not be committed in Git!
 * - services.local.yml
 *   For services only for the local environment, this file will not be committed in Git!
 *
 */

### Lagoon database connection.
if(getenv('LAGOON')){
  $databases['default']['default'] = array(
    'driver' => 'mysql',
    'database' => getenv('MARIADB_DATABASE') ?: 'drupal',
    'username' => getenv('MARIADB_USERNAME') ?: 'drupal',
    'password' => getenv('MARIADB_PASSWORD') ?: 'drupal',
    'host' => getenv('MARIADB_HOST') ?: 'mariadb',
    'port' => 3306,
    'prefix' => '',
  );
}

### Lagoon Solr connection.
// WARNING: you have to create a search_api server having "solr" machine name at
// /admin/config/search/search-api/add-server to make this work.
if (getenv('LAGOON')) {
  $config['search_api.server.solr']['backend_config']['connector_config']['host'] = getenv('SOLR_HOST') ?: 'solr';
  $config['search_api.server.solr']['backend_config']['connector_config']['path'] = '/solr/';
  $config['search_api.server.solr']['backend_config']['connector_config']['core'] = getenv('SOLR_CORE') ?: 'drupal';
  $config['search_api.server.solr']['backend_config']['connector_config']['port'] = 8983;
  $config['search_api.server.solr']['backend_config']['connector_config']['http_user'] = (getenv('SOLR_USER') ?: '');
  $config['search_api.server.solr']['backend_config']['connector_config']['http']['http_user'] = (getenv('SOLR_USER') ?: '');
  $config['search_api.server.solr']['backend_config']['connector_config']['http_pass'] = (getenv('SOLR_PASSWORD') ?: '');
  $config['search_api.server.solr']['backend_config']['connector_config']['http']['http_pass'] = (getenv('SOLR_PASSWORD') ?: '');
  $config['search_api.server.solr']['name'] = 'Lagoon Solr - Environment: ' . getenv('LAGOON_PROJECT');
}

// Redis configuration.
if (getenv('LAGOON')) {
  $redis = new \Redis();
  $redis_host = getenv('REDIS_HOST') ?: 'redis';
  $redis_port = getenv('REDIS_SERVICE_PORT') ?: 6379;
  try {
    if (drupal_installation_attempted()) {
      # Do not set the cache during installations of Drupal
      throw new \Exception('Drupal installation underway.');
    }

    $redis->connect($redis_host, $redis_port);
    $response = $redis->ping();

    if (strpos($response, 'PONG') === FALSE) {
      throw new \Exception('Redis could be reached but is not responding correctly.');
    }

    $settings['redis.connection']['interface'] = 'PhpRedis';
    $settings['redis.connection']['host'] = $redis_host;
    $settings['redis.connection']['port'] = $redis_port;
    $settings['cache_prefix']['default'] = getenv('REDIS_CACHE_PREFIX') ?: getenv('LAGOON_PROJECT') . '_' . getenv('LAGOON_GIT_SAFE_BRANCH');

    $settings['cache']['default'] = 'cache.backend.redis';

    // Include the default example.services.yml from the module, which will
    // replace all supported backend services (that currently includes the cache tags
    // checksum service and the lock backends, check the file for the current list).
    $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';

    // Allow the services to work before the Redis module itself is enabled.
    $settings['container_yamls'][] = 'modules/contrib/redis/redis.services.yml';

    // Manually add the classloader path, this is required for the container cache bin definition below
    // and allows to use it without the redis module being enabled.
    $class_loader->addPsr4('Drupal\\redis\\', 'modules/contrib/redis/src');

    // Use redis for container cache.
    // The container cache is used to load the container definition itself, and
    // thus any configuration stored in the container itself is not available
    // yet. These lines force the container cache to use Redis rather than the
    // default SQL cache.
    $settings['bootstrap_container_definition'] = [
      'parameters' => [],
      'services' => [
        'redis.factory' => [
          'class' => 'Drupal\redis\ClientFactory',
        ],
        'cache.backend.redis' => [
          'class' => 'Drupal\redis\Cache\CacheBackendFactory',
          'arguments' => ['@redis.factory', '@cache_tags_provider.container', '@serialization.phpserialize'],
        ],
        'cache.container' => [
          'class' => '\Drupal\redis\Cache\PhpRedis',
          'factory' => ['@cache.backend.redis', 'get'],
          'arguments' => ['container'],
        ],
        'cache_tags_provider.container' => [
          'class' => 'Drupal\redis\Cache\RedisCacheTagsChecksum',
          'arguments' => ['@redis.factory'],
        ],
        'serialization.phpserialize' => [
          'class' => 'Drupal\Component\Serialization\PhpSerialize',
        ],
      ],
    ];
  }
  catch (\Exception $error) {
    $settings['container_yamls'][] = 'sites/default/redis-unavailable.services.yml';
    $settings['cache']['default'] = 'cache.backend.null';
  }
}

### Lagoon reverse proxy settings.
if (getenv('LAGOON')) {
  $settings['reverse_proxy'] = TRUE;
}

### Trusted Host Patterns, see https://www.drupal.org/node/2410395 for more information.
### If your site runs on multiple domains, you need to add these domains here.
if (getenv('LAGOON_ROUTES')) {
  $settings['trusted_host_patterns'] = array(
    '^' . str_replace(['.', 'https://', 'http://', ','], ['\.', '', '', '|'], getenv('LAGOON_ROUTES')) . '$', // escape dots, remove schema, use commas as regex separator
   );
}

### Temp directory.
if (getenv('TMP')) {
  $config['system.file']['path']['temporary'] = getenv('TMP');
}

### Hash salt.
if (getenv('LAGOON')) {
  $settings['hash_salt'] = hash('sha256', getenv('LAGOON_PROJECT'));
}

// Settings for all environments.
if (file_exists(__DIR__ . '/all.settings.php')) {
  include __DIR__ . '/all.settings.php';
}

// Services for all environments.
if (file_exists(__DIR__ . '/all.services.yml')) {
  $settings['container_yamls'][] = __DIR__ . '/all.services.yml';
}

if(getenv('LAGOON_ENVIRONMENT_TYPE')){
  // Environment specific settings files.
  if (file_exists(__DIR__ . '/' . getenv('LAGOON_ENVIRONMENT_TYPE') . '.settings.php')) {
    include __DIR__ . '/' . getenv('LAGOON_ENVIRONMENT_TYPE') . '.settings.php';
  }

  // Environment specific services files.
  if (file_exists(__DIR__ . '/' . getenv('LAGOON_ENVIRONMENT_TYPE') . '.services.yml')) {
    $settings['container_yamls'][] = __DIR__ . '/' . getenv('LAGOON_ENVIRONMENT_TYPE') . '.services.yml';
  }
}

// Last: this servers specific settings files.
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
// Last: This server specific services file.
if (file_exists(__DIR__ . '/services.local.yml')) {
  $settings['container_yamls'][] = __DIR__ . '/services.local.yml';
}
