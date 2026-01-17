<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SpamPattern Model
 *
 * Patterns for detecting spam in comments and submissions.
 *
 * Database Schema:
 * @property int $id Primary key
 * @property string $pattern The pattern to match (keyword, regex, domain, etc.)
 * @property string $type Pattern type (keyword, regex, domain, email, ip)
 * @property float $weight Score weight when pattern matches (0.00-1.00)
 * @property bool $is_active Whether pattern is currently active
 * @property int $hit_count Number of times pattern has matched
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SpamPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'pattern',
        'type',
        'weight',
        'is_active',
        'hit_count',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'hit_count' => 'integer',
    ];

    public const TYPES = [
        'keyword' => 'Keyword (exact match)',
        'regex' => 'Regular Expression',
        'domain' => 'Domain/URL',
        'email' => 'Email pattern',
        'ip' => 'IP address',
    ];

    /**
     * Scope for active patterns.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if content matches this pattern.
     */
    public function matches(string $content): bool
    {
        $content = strtolower($content);
        $pattern = strtolower($this->pattern);

        switch ($this->type) {
            case 'keyword':
                return str_contains($content, $pattern);

            case 'regex':
                return (bool) preg_match($this->pattern, $content);

            case 'domain':
                return str_contains($content, $pattern);

            case 'email':
                // Extract emails and check pattern
                preg_match_all('/[\w.+-]+@[\w.-]+\.[a-zA-Z]{2,}/', $content, $matches);
                foreach ($matches[0] as $email) {
                    if (str_contains(strtolower($email), $pattern)) {
                        return true;
                    }
                }
                return false;

            default:
                return false;
        }
    }
}
