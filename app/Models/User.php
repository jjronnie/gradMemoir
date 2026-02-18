<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Scopes\ScopeActive;
use App\Support\UsernameGenerator;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, InteractsWithMedia, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'google_id',
        'username',
        'username_updated_at',
        'role',
        'status',
        'is_verified',
        'bio',
        'profession',
        'location',
        'phone',
        'facebook_username',
        'x_username',
        'tiktok_username',
        'instagram_username',
        'website',
        'email_public',
        'onboarding_completed',
        'university_id',
        'course_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'username_updated_at' => 'datetime',
            'onboarding_completed' => 'boolean',
            'is_verified' => 'boolean',
            'status' => UserStatus::class,
            'role' => UserRole::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if ($user->username === null || $user->username === '') {
                $user->username = UsernameGenerator::generateUnique($user->name);
            }

            if ($user->status === null) {
                $user->status = UserStatus::Active;
            }

            if ($user->role === null) {
                $user->role = UserRole::Student;
            }
        });
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function featuredProfile(): HasOne
    {
        return $this->hasOne(FeaturedProfile::class);
    }

    public function flags(): HasMany
    {
        return $this->hasMany(UserFlag::class);
    }

    public function raisedFlags(): HasMany
    {
        return $this->hasMany(UserFlag::class, 'flagged_by');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', UserStatus::Active->value);
    }

    public static function publicScope(): Builder
    {
        return static::query()->withGlobalScope(ScopeActive::class, new ScopeActive);
    }

    public function hasRole(string|UserRole $role): bool
    {
        $value = $role instanceof UserRole ? $role->value : $role;

        return $this->role?->value === $value;
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function getRoleNames()
    {
        return collect([$this->role?->value])->filter();
    }

    public function assignRole(string|UserRole $role): self
    {
        $this->forceFill([
            'role' => $role instanceof UserRole ? $role : UserRole::from($role),
        ])->save();

        return $this;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'image/avif',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(80)
            ->nonQueued();

        $this->addMediaConversion('full')
            ->width(1200)
            ->format('webp')
            ->quality(80)
            ->nonQueued();
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->getFirstMediaUrl('avatar', 'thumb') !== ''
            ? $this->getFirstMediaUrl('avatar', 'thumb')
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=random',
        );
    }

    protected function profileUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => '/@'.$this->username,
        );
    }
}
