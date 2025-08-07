<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model{
    use HasFactory; /** @use HasFactory<\Database\Factories\OrganizationFactory> */
    protected $fillable = [
        'name',
    ];

    public function members(): HasMany {
        return $this->hasMany(OrganizationMember::class, 'organization_id');
    }

    public function users(): BelongsToMany {
        return $this
            ->belongsToMany(User::class, 'organization_members')
            ->using(OrganizationMember::class)
            ->withTimestamps()
            ->withPivot('role');
    }

    public function ec2Products(): HasMany{
        return $this->hasMany(Ec2Product::class);
    }

    public function invoices(): HasMany{
        return $this->hasMany(Invoice::class);
    }


}
