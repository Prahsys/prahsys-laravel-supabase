<?php

namespace Prahsys\Supabase\Database;

use Illuminate\Database\PostgresConnection as BaseConnection;
use Prahsys\Supabase\Database\Query\Builder;
use Prahsys\Supabase\Database\Query\Grammars\PostgresGrammar;

class PostgresConnection extends BaseConnection
{
    /**
     * Get a new query builder instance.
     *
     * @return \Prahsys\Supabase\Database\Query\Builder
     */
    public function query()
    {
        return new Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Determine if the given table exists.
     *
     * @param  string  $table
     * @return bool
     */
    public function tableExists($table)
    {
        try {
            return parent::tableExists($table);
        } catch (\Exception $e) {
            // If we get a UUID conversion error, handle it by using a simpler query
            if (str_contains($e->getMessage(), 'uuid')) {
                $schema = $this->getDefaultSchemaName();

                $result = $this->select(
                    "SELECT EXISTS(
                        SELECT 1
                        FROM pg_catalog.pg_class c
                        JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
                        WHERE n.nspname = ?
                        AND c.relname = ?
                        AND c.relkind IN ('r', 'p')
                    ) AS exists",
                    [$schema, $table]
                );

                return $result[0]->exists;
            }

            throw $e;
        }
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        try {
            return parent::statement($query, $bindings);
        } catch (\Exception $e) {
            // Handle specific migration/test-related queries that might fail with UUID type issues
            if (str_contains($e->getMessage(), 'operator does not exist: uuid =') ||
                str_contains($e->getMessage(), 'uuid')) {

                // Replace the UUID comparison with a text cast
                $query = preg_replace(
                    '/([a-zA-Z0-9_\.]+)(\.|\s)([a-zA-Z0-9_]+) = \?/',
                    'CAST($1$2$3 AS TEXT) = ?',
                    $query
                );

                // Try again with the modified query
                return parent::statement($query, $bindings);
            }

            throw $e;
        }
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        try {
            return parent::select($query, $bindings, $useReadPdo);
        } catch (\Exception $e) {
            // Handle specific UUID type issues
            if (str_contains($e->getMessage(), 'operator does not exist: uuid =') ||
                str_contains($e->getMessage(), 'uuid')) {

                // Replace the UUID comparison with a text cast
                $query = preg_replace(
                    '/([a-zA-Z0-9_\.]+)(\.|\s)([a-zA-Z0-9_]+) = \?/',
                    'CAST($1$2$3 AS TEXT) = ?',
                    $query
                );

                // Try again with the modified query
                return parent::select($query, $bindings, $useReadPdo);
            }

            throw $e;
        }
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Query\Grammars\PostgresGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new PostgresGrammar);
    }
}
