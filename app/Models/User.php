<?php

namespace App\Models;

use App\Enums\RoleEnum;
use App\Enums\GenderEnum;
use Illuminate\Support\Str;
use App\Models\Traits\HasAvatar;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasAvatar;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'slug',
        'use_gravatar',
        'display_favorites',
        'display_comments',
        'display_gender',
        'about',
        'gender'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at'     => 'datetime',
        'use_gravatar'          => 'boolean',
        'display_favorites'     => 'boolean',
        'display_comments'      => 'boolean',
        'display_gender'        => 'boolean',
        'gender'                => GenderEnum::class
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'avatar',
    ];

    public static function boot()
    {
        static::saving(function (User $user) {
            if (! empty($user->slug)) {
                return;
            }
            $user->slug = Str::slug($user->name, '-') . '-' . bin2hex(openssl_random_pseudo_bytes(5));
        });

        parent::boot();
    }

    public function getShowLinkAttribute(): string
    {
        $route = route('api.users.show', [
            'slug' => $this->slug,
        ]);

        return $route;
    }

    public function getShowLinkCommentsAttribute(): string
    {
        $route = route('api.users.comments', [
            'slug' => $this->slug,
        ]);

        return $route;
    }

    public function getShowLinkFavoritesAttribute(): string
    {
        $route = route('api.users.favorites', [
            'slug' => $this->slug,
        ]);

        return $route;
    }

    public function hasRole(RoleEnum $role_to_verify): bool
    {
        $roles = [];
        foreach ($this->roles as $key => $role) {
            array_push($roles, $role->name->value);
        }

        if (in_array($role_to_verify->value, $roles)) {
            return true;
        }

        return false;
    }

    public function favorites()
    {
        return Favoritable::where('user_id', $this->id)->orderBy('created_at')->get();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function books(): MorphToMany
    {
        return $this->morphedByMany(Book::class, 'favoritable');
    }

    public function comments()
    {
        return $this->hasMany(Commentable::class);
    }
}
