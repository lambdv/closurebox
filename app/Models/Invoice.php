<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;
    protected $fillable = [
        'organization_id',
        'ec2_product_id',
        'type',
        'amount',
        'due_date',
        'status',
    ];
    protected $casts = [
        'due_date' => 'date',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function ec2Product(): BelongsTo
    {
        return $this->belongsTo(Ec2Product::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
