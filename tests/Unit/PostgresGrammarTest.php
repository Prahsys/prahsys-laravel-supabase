<?php

use Illuminate\Database\Query\Builder;
use Prahsys\Supabase\Database\Query\Grammars\PostgresGrammar;

it('casts UUID columns in where clauses', function () {
    $connection = app('db')->connection();
    $grammar = new PostgresGrammar($connection);

    // Create a query builder with a where clause on a UUID column
    $query = new Builder(app('db')->connection());
    $query->from('users')->where('id', '=', generateUuid());

    // Generate the SQL
    $sql = $grammar->compileSelect($query);

    // Check that the UUID column was cast to TEXT
    expect($sql)->toContain('CAST("id" AS TEXT)');
});

it('casts UUID columns in where-in clauses', function () {
    $connection = app('db')->connection();
    $grammar = new PostgresGrammar($connection);

    // Create a query builder with a whereIn clause on a UUID column
    $query = new Builder(app('db')->connection());
    $query->from('users')->whereIn('id', [generateUuid(), generateUuid()]);

    // Generate the SQL
    $sql = $grammar->compileSelect($query);

    // Check that the UUID column was cast to TEXT
    expect($sql)->toContain('CAST("id" AS TEXT)');
});

it('casts UUID columns in joins', function () {
    $connection = app('db')->connection();
    $grammar = new PostgresGrammar($connection);

    // Create a query builder with a join on UUID columns
    $query = new Builder(app('db')->connection());
    $query->from('posts')
        ->join('users', 'posts.user_id', '=', 'users.id');

    // Generate the SQL
    $sql = $grammar->compileSelect($query);

    // Check that the UUID column was cast to TEXT
    expect($sql)->toContain('CAST("posts"."user_id" AS TEXT)');
    expect($sql)->toContain('CAST("users"."id" AS TEXT)');
});
