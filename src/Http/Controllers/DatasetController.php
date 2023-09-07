<?php

namespace Stats4sd\OdkLink\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Stats4sd\OdkLink\Models\Dataset;

class DatasetController
{
    public function index(): Collection
    {
        return Dataset::all();
    }
}
