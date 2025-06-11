<?php

namespace Prahsys\Supabase\Database\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    /**
     * The list of UUID columns for type casting.
     * This can be set by models using the CastsUuidColumns trait.
     *
     * @var array
     */
    public $uuidColumns = [];
}
