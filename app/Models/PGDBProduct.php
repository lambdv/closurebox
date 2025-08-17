<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PGDBProduct extends Model
{
    protected $table = 'pgdb_products';
    protected $fillable = [
        //'pgdb_role_id',
        'user_id',
        'name',
        'instance_id',
        'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pgdbRole(): BelongsTo
    {
        return $this->belongsTo(PGDBRole::class, 'pgdb_product_id');
    }
}
