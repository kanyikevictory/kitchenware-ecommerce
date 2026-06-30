<?php

namespace App\Models;

use Database\Factories\PermissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    /** @use HasFactory<PermissionFactory> */
    use HasFactory;

    public const DEFINITIONS = [
        'admin.access' => 'Access the administration API',
        'dashboard.view' => 'View dashboard metrics',
        'users.view' => 'View users',
        'users.update-status' => 'Activate and deactivate users',
        'categories.manage' => 'Manage categories',
        'products.manage' => 'Manage products',
        'orders.manage' => 'Manage orders',
        'payments.manage' => 'Manage payments',
        'reviews.manage' => 'Moderate reviews',
        'coupons.manage' => 'Manage coupons',
    ];

    protected $fillable = ['name', 'slug', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
