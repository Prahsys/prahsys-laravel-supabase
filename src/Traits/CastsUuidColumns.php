<?php

namespace Prahsys\Supabase\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Simple trait to define UUID columns in a model.
 *
 * This trait provides a standard way to specify which columns in a model
 * should be treated as UUIDs when using the Supabase driver.
 */
trait CastsUuidColumns
{
    /**
     * Boot the trait
     */
    public static function bootCastsUuidColumns()
    {
        // Add a query tag that identifies columns that require UUID handling
        static::addGlobalScope('uuid-columns', function (Builder $builder) {
            // Tag the query with the UUID columns
            $builder->withUuidColumns();
        });
    }

    /**
     * Add the UUID column information to the query builder
     */
    public function scopeWithUuidColumns(Builder $builder)
    {
        $uuidColumns = $this->getUuidColumns();

        // Get the underlying query builder and set the UUID columns
        $query = $builder->getQuery();

        // Make sure we're using our custom query builder
        if (! property_exists($query, 'uuidColumns')) {
            // This shouldn't happen with our setup, but just in case
            $query->uuidColumns = [];
        }

        $query->uuidColumns = $uuidColumns;

        return $builder;
    }

    /**
     * Get UUID columns for this model
     */
    public function getUuidColumns()
    {
        $columns = [];

        // Primary key is typically a UUID
        $columns[] = $this->getKeyName();

        // Add explicitly defined UUID columns
        if (property_exists($this, 'uuidColumns') && is_array($this->uuidColumns)) {
            $columns = array_merge($columns, $this->uuidColumns);
        }

        return array_unique($columns);
    }
}
