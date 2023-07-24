<?php

namespace Stats4sd\OdkLink\Tests\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stats4sd\OdkLink\Tests\UserFactory;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $guarded = [];
    protected $table = "users";

    protected static function newFactory(): UserFactory
    {
        return new UserFactory();
    }
}
