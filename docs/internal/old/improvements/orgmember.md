Why OrganizationMember should be a full model:
Rich relationship data: You already have role, created_at, updated_at - this suggests it's more than just a simple join
Future extensibility: You'll likely need more attributes like:
invited_by (who invited this member)
invited_at / accepted_at
status (pending, active, suspended)
permissions (JSON field for granular permissions)
billing_access (boolean)
notification_preferences
last_active_at
Business logic: Organization memberships often have complex business rules
Audit trails: You'll want to track membership changes over time


Best Architecture for Roles & Permissions:
// 1. OrganizationMember as a full model
class OrganizationMember extends Model
{
    protected $fillable = [
        'user_id', 'organization_id', 'role', 'status',
        'invited_by', 'accepted_at', 'permissions'
    ];

    protected $casts = [
        'permissions' => 'array',
        'accepted_at' => 'datetime',
    ];
}

// 2. Role-based permissions with granular control
class Role extends Model
{
    protected $fillable = ['name', 'organization_id', 'permissions'];
    
    protected $casts = ['permissions' => 'array'];
}

// 3. Permission system
class Permission extends Model
{
    // Define granular permissions
    const PERMISSIONS = [
        'ec2.create', 'ec2.read', 'ec2.update', 'ec2.delete',
        'billing.view', 'billing.manage',
        'members.invite', 'members.remove',
        'settings.manage'
    ];
}


Updated Migration Structure:
// organization_members table
Schema::create('organization_members', function (Blueprint $table) {
    $table->id();
    $table->timestamps();
    
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->enum('role', ['owner', 'admin', 'manager', 'member'])->default('member');
    $table->enum('status', ['pending', 'active', 'suspended'])->default('active');
    
    $table->foreignId('invited_by')->nullable()->constrained('users');
    $table->timestamp('accepted_at')->nullable();
    
    $table->json('permissions')->nullable(); // Granular permissions
    $table->boolean('billing_access')->default(false);
    
    $table->unique(['user_id', 'organization_id']);
});

Updated Model Relationships:

// User.php
public function organizationMemberships(): HasMany
{
    return $this->hasMany(OrganizationMember::class);
}

public function organizations()
{
    return $this->belongsToMany(Organization::class, 'organization_members')
        ->withPivot(['role', 'status', 'permissions'])
        ->withTimestamps();
}

// Organization.php  
public function members(): HasMany
{
    return $this->hasMany(OrganizationMember::class);
}

public function users()
{
    return $this->belongsToMany(User::class, 'organization_members')
        ->withPivot(['role', 'status', 'permissions'])
        ->withTimestamps();
}




Permission Checking:
// In OrganizationMember model
public function hasPermission(string $permission): bool
{
    if ($this->role === 'owner') return true;
    
    return in_array($permission, $this->permissions ?? []);
}

public function canManageBilling(): bool
{
    return $this->hasPermission('billing.manage') || $this->role === 'owner';
}