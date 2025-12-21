<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpamPatternsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patterns = [
            // Keywords - Common spam phrases
            ['pattern' => 'buy now', 'type' => 'keyword', 'weight' => 0.8],
            ['pattern' => 'click here', 'type' => 'keyword', 'weight' => 0.6],
            ['pattern' => 'limited offer', 'type' => 'keyword', 'weight' => 0.7],
            ['pattern' => 'act now', 'type' => 'keyword', 'weight' => 0.6],
            ['pattern' => 'free money', 'type' => 'keyword', 'weight' => 1.0],
            ['pattern' => 'make money fast', 'type' => 'keyword', 'weight' => 1.0],
            ['pattern' => 'work from home', 'type' => 'keyword', 'weight' => 0.5],
            ['pattern' => 'earn extra cash', 'type' => 'keyword', 'weight' => 0.6],
            ['pattern' => 'casino online', 'type' => 'keyword', 'weight' => 0.9],
            ['pattern' => 'online gambling', 'type' => 'keyword', 'weight' => 0.8],
            ['pattern' => 'viagra', 'type' => 'keyword', 'weight' => 1.0],
            ['pattern' => 'cialis', 'type' => 'keyword', 'weight' => 1.0],
            ['pattern' => 'weight loss', 'type' => 'keyword', 'weight' => 0.5],
            ['pattern' => 'cryptocurrency investment', 'type' => 'keyword', 'weight' => 0.7],
            ['pattern' => 'bitcoin doubler', 'type' => 'keyword', 'weight' => 1.0],
            ['pattern' => 'guaranteed profit', 'type' => 'keyword', 'weight' => 0.8],
            ['pattern' => 'risk free', 'type' => 'keyword', 'weight' => 0.5],
            ['pattern' => 'no obligation', 'type' => 'keyword', 'weight' => 0.4],
            ['pattern' => 'winner selected', 'type' => 'keyword', 'weight' => 0.7],
            ['pattern' => 'congratulations you won', 'type' => 'keyword', 'weight' => 0.9],
            ['pattern' => 'claim your prize', 'type' => 'keyword', 'weight' => 0.8],
            ['pattern' => 'seo services', 'type' => 'keyword', 'weight' => 0.6],
            ['pattern' => 'website traffic', 'type' => 'keyword', 'weight' => 0.5],
            ['pattern' => 'social media followers', 'type' => 'keyword', 'weight' => 0.6],
            ['pattern' => 'cheap watches', 'type' => 'keyword', 'weight' => 0.7],
            ['pattern' => 'replica', 'type' => 'keyword', 'weight' => 0.5],

            // Domains - Known spam domains
            ['pattern' => 'bit.ly', 'type' => 'domain', 'weight' => 0.3],
            ['pattern' => 'tinyurl.com', 'type' => 'domain', 'weight' => 0.3],
            ['pattern' => 't.co', 'type' => 'domain', 'weight' => 0.2],

            // Regex patterns
            ['pattern' => '/\b\d{10,}\b/', 'type' => 'regex', 'weight' => 0.4], // Long numbers (phone numbers)
            ['pattern' => '/\$\d+[,\d]*\s*(USD|dollars?)/i', 'type' => 'regex', 'weight' => 0.5], // Money amounts
            ['pattern' => '/\b(whatsapp|telegram|signal)\s*[:\s]*\+?\d+/i', 'type' => 'regex', 'weight' => 0.6], // Messenger contacts
            ['pattern' => '/https?:\/\/[^\s]+\.ru\b/i', 'type' => 'regex', 'weight' => 0.4], // .ru domains
            ['pattern' => '/https?:\/\/[^\s]+\.cn\b/i', 'type' => 'regex', 'weight' => 0.4], // .cn domains
            ['pattern' => '/\b(DM|PM)\s+me\b/i', 'type' => 'regex', 'weight' => 0.3], // DM/PM solicitation

            // Email patterns
            ['pattern' => '@gmail.com', 'type' => 'email', 'weight' => 0.1],
            ['pattern' => '@yahoo.com', 'type' => 'email', 'weight' => 0.1],
            ['pattern' => '@hotmail.com', 'type' => 'email', 'weight' => 0.1],
        ];

        $now = now();

        foreach ($patterns as $pattern) {
            DB::table('spam_patterns')->insert([
                ...$pattern,
                'is_active' => true,
                'hit_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
