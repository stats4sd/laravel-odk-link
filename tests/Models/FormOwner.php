<?php


namespace Stats4sd\OdkLink\Tests\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stats4sd\OdkLink\Traits\HasXlsforms;

class FormOwner extends Model
{
    use CrudTrait;
    use HasXlsforms;
    use HasFactory;

    protected $table = 'form_owners';
    protected $guarded = [];


}
