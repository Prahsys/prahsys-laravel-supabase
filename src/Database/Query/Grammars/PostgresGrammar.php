<?php

namespace Prahsys\Supabase\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\PostgresGrammar as BaseGrammar;

class PostgresGrammar extends BaseGrammar
{
    /**
     * A callback that can be used to customize UUID column detection.
     *
     * @var callable|null
     */
    public static $uuidColumnDetector = null;

    /**
     * Set a custom detector function for UUID columns.
     *
     * @return void
     */
    public static function detectUuidColumnsWith(callable $callback)
    {
        static::$uuidColumnDetector = $callback;
    }

    /**
     * Compile a join clause.
     */
    public function compileJoins($query, $joins)
    {
        $sql = [];

        foreach ($joins as $join) {
            $table = $this->wrapTable($join->table);
            $type = $join->type;

            // Process each join clause
            $clauses = [];
            foreach ($join->wheres as $where) {
                if ($where['type'] === 'Column') {
                    $first = $this->wrap($where['first']);
                    $second = $this->wrap($where['second']);
                    $operator = $where['operator'];

                    // Check if we need UUID casting for joins
                    if ($this->isIdOrUuidColumn($where['first']) || $this->isIdOrUuidColumn($where['second'])) {
                        $clauses[] = "CAST({$first} AS TEXT) {$operator} CAST({$second} AS TEXT)";
                    } else {
                        $clauses[] = "{$first} {$operator} {$second}";
                    }
                } else {
                    $clauses[] = $this->{"where{$where['type']}"}($query, $where);
                }
            }

            $sql[] = "{$type} join {$table} on ".implode(' and ', $clauses);
        }

        return implode(' ', $sql);
    }

    /**
     * Compile a "where column" clause.
     */
    protected function whereColumn($query, $where)
    {
        $first = $this->wrap($where['first']);
        $second = $this->wrap($where['second']);

        // Check if we need UUID casting
        if ($this->isIdOrUuidColumn($where['first']) || $this->isIdOrUuidColumn($where['second'])) {
            return "CAST({$first} AS TEXT) {$where['operator']} CAST({$second} AS TEXT)";
        }

        return parent::whereColumn($query, $where);
    }

    /**
     * Compile a basic where clause.
     */
    protected function whereBasic($query, $where)
    {
        $column = $this->wrap($where['column']);
        $value = $this->parameter($where['value']);

        // Check if this is a UUID column that needs casting
        if ($this->isIdOrUuidColumn($where['column'])) {
            return "CAST({$column} AS TEXT) {$where['operator']} {$value}";
        }

        return parent::whereBasic($query, $where);
    }

    /**
     * Compile a "where in" clause.
     */
    protected function whereIn($query, $where)
    {
        if (empty($where['values'])) {
            return '0 = 1';
        }

        $column = $this->wrap($where['column']);

        if ($this->isIdOrUuidColumn($where['column'])) {
            $column = "CAST({$column} AS TEXT)";
        }

        return $column.' in ('.$this->parameterize($where['values']).')';
    }

    /**
     * Check if a column is a UUID column that needs casting
     *
     * This method uses multiple strategies to detect UUID columns:
     * 1. Custom detector function if set
     * 2. Explicitly defined UUID columns in the model
     * 3. Common naming patterns as a fallback
     */
    protected function isIdOrUuidColumn($column)
    {
        // Get the clean column name for checks
        $clean = trim($column, '"\'`');
        $parts = explode('.', $clean);
        $columnName = end($parts);

        // 1. Use custom detector if available
        if (static::$uuidColumnDetector !== null) {
            if (call_user_func(static::$uuidColumnDetector, $columnName, $this->query)) {
                return true;
            }
        }

        // 2. Check if we have explicitly defined UUID columns from the model
        if (isset($this->query->uuidColumns) && is_array($this->query->uuidColumns)) {
            if (in_array($columnName, $this->query->uuidColumns)) {
                return true;
            }
        }

        // 3. Fall back to naming convention detection
        return $columnName === 'id' ||
               $columnName === 'uuid' ||
               str_ends_with($columnName, '_id') ||
               str_ends_with($columnName, '_uuid') ||
               str_contains($columnName, 'uuid') ||
               str_ends_with($columnName, 'able_id') || // For Laravel polymorphic relations
               str_ends_with($columnName, 'able_uuid');
    }
}
