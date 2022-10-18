<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\GenderEnum;
use App\Enums\UserRole;
use App\Traits\HasAvatar;
use App\Traits\HasUsername;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;

class User extends Authenticatable implements HasMedia, FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasUsername;
    use HasAvatar;

    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
        'is_blocked',
        'role',
        'use_gravatar',
        'display_favorites',
        'display_reviews',
        'display_gender',
        'about',
        'gender',
        'pronouns',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'is_editor',
        'is_super_admin',
        'is_admin',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_blocked' => 'boolean',
        'role' => UserRole::class,
        'display_favorites' => 'boolean',
        'display_reviews' => 'boolean',
        'display_gender' => 'boolean',
        'gender' => GenderEnum::class,
    ];

    public function canAccessFilament(): bool
    {
        return $this->is_editor || $this->is_admin || $this->is_super_admin && ! $this->is_blocked;
    }

    public function canManageSettings(): bool
    {
        // return $this->can('manage.settings');
        return true;
    }

    public function scopeWhereHasBackEndAccess(Builder $query): Builder
    {
        return $query->where('role', '=', UserRole::editor)
            ->orWhere('role', '=', UserRole::admin)
            ->orWhere('role', '=', UserRole::super_admin)
        ;
    }

    protected function getIsEditorAttribute(): bool
    {
        return UserRole::editor === $this->role;
    }

    protected function getIsAdminAttribute(): bool
    {
        return UserRole::admin === $this->role;
    }

    protected function getIsSuperAdminAttribute(): bool
    {
        return UserRole::super_admin === $this->role;
    }
}
