<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PGDBProduct extends Model
{
    protected $table = 'pgdb_products';
    protected $fillable = [
        'name',
        'db_name',
        'status',
        'user_id',
    ];
}
