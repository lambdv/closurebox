<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationMember extends Pivot
{
    /** @use HasFactory<\Database\Factories\OrganizationMemberFactory> */
    use HasFactory;

    protected $table = 'organization_members';

    protected $fillable = [
        'user_id',
        'organization_id',
        'role',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
