<?php

namespace App\Models;

use Aliziodev\LaravelTaxonomy\Models\Taxonomy as baseTaxonomy;

class Taxonomy extends baseTaxonomy
{
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
