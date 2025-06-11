<?php

use Prahsys\Supabase\Database\PostgresConnection;

it('registers the supabase driver', function () {
    // Check that our test connection has the right driver
    $this->assertEquals('supabase', config('database.connections.supabase_test.driver'));

    // Get a connection to see if it works
    $connection = $this->app['db']->connection('supabase_test');

    // Check that it's our custom PostgresConnection
    $this->assertInstanceOf(PostgresConnection::class, $connection);
});
