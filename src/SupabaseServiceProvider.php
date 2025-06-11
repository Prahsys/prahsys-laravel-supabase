<?php

namespace Prahsys\Supabase;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use Prahsys\Supabase\Database\PostgresConnection;

class SupabaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register the driver in register() so it's available during migrations
        $this->registerSupabaseDriver();
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register again in boot() to ensure it's available for all database connections
        $this->registerSupabaseDriver();
    }

    /**
     * Register the Supabase database driver.
     *
     * @return void
     */
    protected function registerSupabaseDriver()
    {
        // Register a connection resolver for the 'supabase' driver
        Connection::resolverFor('supabase', function ($connection, $database, $prefix, $config) {
            // Use our custom PostgresConnection class which handles UUID columns
            return new PostgresConnection($connection, $database, $prefix, $config);
        });

        // Bind a custom connector for the 'supabase' driver that uses the PostgreSQL connector
        $this->app->bind('db.connector.supabase', function ($app) {
            return new \Illuminate\Database\Connectors\PostgresConnector;
        });
    }
}
