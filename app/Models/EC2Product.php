<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EC2Product extends Model
{
    use HasFactory; /** @use HasFactory<\Database\Factories\EC2ProductFactory> */
    protected $table = 'ec2_products';
    protected $fillable = [
        //id
        'organization_id',
        'instance_id',
        'status',
        'details'
        //timestamp
    ];
    protected $casts = [
        'details' => 'array',
    ];

    public function organization(): BelongsTo {
        return $this->belongsTo(Organization::class);
    }

    public function invoices(): HasMany {
        return $this->hasMany(Invoice::class);
    }
}
