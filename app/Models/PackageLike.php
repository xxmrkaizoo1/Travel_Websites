<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageLike extends Model
{
    protected $fillable = ['package_key', 'visitor_key'];
}
