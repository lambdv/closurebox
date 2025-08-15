<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PGDBRole extends Model
{
    protected $table = 'pgdb_roles';
    protected $fillable = [
        'pgdb_oid',
        'user_id',
    ];
}
