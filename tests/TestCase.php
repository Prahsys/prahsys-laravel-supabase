<?php

namespace Prahsys\Supabase\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Prahsys\Supabase\SupabaseServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->createModels();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return [
            SupabaseServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Check if we're running in a real Supabase test environment
        $useRealSupabase = env('TEST_DB_CONNECTION') === 'supabase' && env('TEST_DB_HOST');

        // Setup default database to use sqlite in memory for most tests
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup Supabase test connection
        $supabaseConfig = [
            'driver' => 'supabase',
            'host' => env('TEST_DB_HOST', 'localhost'),
            'port' => env('TEST_DB_PORT', '5432'),
            'database' => env('TEST_DB_DATABASE', 'postgres'),
            'username' => env('TEST_DB_USERNAME', 'postgres'),
            'password' => env('TEST_DB_PASSWORD', 'postgres'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ];

        // If we're using a real Supabase connection
        if ($useRealSupabase) {
            // Allow use of a database URL if provided
            if (env('TEST_DATABASE_URL')) {
                $supabaseConfig['url'] = env('TEST_DATABASE_URL');
            }

            // Use as the default connection for all tests
            $app['config']->set('database.default', 'supabase_test');
        }

        // Register the supabase_test connection
        $app['config']->set('database.connections.supabase_test', $supabaseConfig);
    }

    /**
     * Set up the database.
     */
    protected function setUpDatabase()
    {
        $this->createTables();
    }

    /**
     * Create test tables.
     */
    protected function createTables()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('post_id');
            $table->uuid('user_id');
            $table->text('content');
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Create test models.
     */
    protected function createModels()
    {
        include_once __DIR__.'/Database/Models/User.php';
        include_once __DIR__.'/Database/Models/Post.php';
        include_once __DIR__.'/Database/Models/Comment.php';
    }
}
