<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'plan',
        'status',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'cancellation_reason',
        'features',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'features' => 'array',
    ];

    public const PLANS = [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'features' => [
                'view_free_articles' => true,
                'comment' => true,
                'bookmark' => true,
            ],
        ],
        'basic' => [
            'name' => 'Basic',
            'price' => 9.99,
            'features' => [
                'view_free_articles' => true,
                'view_premium_articles' => false,
                'comment' => true,
                'bookmark' => true,
                'no_ads' => true,
            ],
        ],
        'premium' => [
            'name' => 'Premium',
            'price' => 19.99,
            'features' => [
                'view_free_articles' => true,
                'view_premium_articles' => true,
                'comment' => true,
                'bookmark' => true,
                'no_ads' => true,
                'early_access' => true,
                'exclusive_content' => true,
            ],
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 99.99,
            'features' => [
                'view_free_articles' => true,
                'view_premium_articles' => true,
                'comment' => true,
                'bookmark' => true,
                'no_ads' => true,
                'early_access' => true,
                'exclusive_content' => true,
                'api_access' => true,
                'team_accounts' => true,
            ],
        ],
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (UserSubscription $subscription) {
            if (empty($subscription->uuid)) {
                $subscription->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    /**
     * Check if subscription has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        // Check custom features first
        if ($this->features && isset($this->features[$feature])) {
            return (bool) $this->features[$feature];
        }

        // Check plan features
        $planFeatures = self::PLANS[$this->plan]['features'] ?? [];
        return $planFeatures[$feature] ?? false;
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Scope for active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Scope by plan.
     */
    public function scopeOfPlan($query, string|array $plans)
    {
        return $query->whereIn('plan', (array) $plans);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get plan details.
     */
    public function getPlanDetailsAttribute(): array
    {
        return self::PLANS[$this->plan] ?? [];
    }

    /**
     * Get plan name.
     */
    public function getPlanNameAttribute(): string
    {
        return self::PLANS[$this->plan]['name'] ?? ucfirst($this->plan);
    }
}
