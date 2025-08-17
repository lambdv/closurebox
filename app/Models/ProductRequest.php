<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRequest extends Model{
    protected $fillable = [
        'type',
        'status',
        'organization_id',
        'user_id',
    ];

    public function organization(): BelongsTo {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
