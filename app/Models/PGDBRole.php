<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class PGDBRole extends Model
{
    protected $table = 'pgdb_roles';
    protected $fillable = [
        'user_id',
        'pgdb_product_id',
        'username',
        'password',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pgdbRole) {
            // Ensure username follows PostgreSQL naming conventions
            $pgdbRole->username = static::sanitizeUsername($pgdbRole->username);
        });

        static::updating(function ($pgdbRole) {
            // Ensure username follows PostgreSQL naming conventions
            $pgdbRole->username = static::sanitizeUsername($pgdbRole->username);
        });
    }

    /**
     * Sanitize username to follow PostgreSQL naming conventions
     */
    protected static function sanitizeUsername(string $username): string
    {
        // Remove any invalid characters and ensure it starts with a letter
        $sanitized = preg_replace('/[^a-zA-Z0-9_]/', '_', $username);
        
        // Ensure it doesn't start with a number
        if (preg_match('/^\d/', $sanitized)) {
            $sanitized = 'user_' . $sanitized;
        }
        
        // Ensure it doesn't exceed PostgreSQL's 63 character limit
        if (strlen($sanitized) > 63) {
            $sanitized = substr($sanitized, 0, 63);
        }
        
        return $sanitized;
    }

    /**
     * Get validation rules for the model
     */
    public static function getValidationRules(): array
    {
        return [
            'user_id' => 'required|string|exists:users,id',
            'username' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
                Rule::unique('pgdb_roles')->ignore(request()->route('pgdb_role')),
            ],
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Get the user that owns this role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products associated with this role.
     */
    public function products(): HasMany
    {
        return $this->hasMany(PGDBProduct::class, 'pgdb_role_id');
    }
}
