<?php

namespace acarlosos\SimpleAcl;

use Illuminate\Support\ServiceProvider;
use RuntimeException;
class SimpleACLServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom( __DIR__ . '/../config.php', 'simple-acl' );
        $this->setDatabaseConnection();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $configPath = $this->app[ 'path.config' ] . DIRECTORY_SEPARATOR . 'simple-acl.php';
        $this->publishes( [ __DIR__ . '/../config.php' => $configPath ] );

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

    }

    private function setDatabaseConnection()
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app[ 'config' ];

        $connection = $config->get( 'simple-acl.db-connection' ) ?: $config->get( 'database.default' );
        $settings   = $config->get( "database.connections.{$connection}", null );

        if (is_null( $settings )) {
            throw new RuntimeException( 'Invalid database connection' );
        }

        $config->set( [
            'simple-acl.db-connection'        => $connection,
            'database.connections.simple-acl' => $settings
        ] );
    }
}
