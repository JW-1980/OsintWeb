<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Application settings
            [
                'key' => 'app.name',
                'value' => 'OsintWeb',
                'type' => 'string',
                'group' => 'application',
                'description' => 'Application name displayed in the UI',
                'is_public' => true,
            ],
            [
                'key' => 'app.tagline',
                'value' => 'OSINT Military Conflict Tracking Platform',
                'type' => 'string',
                'group' => 'application',
                'description' => 'Application tagline/description',
                'is_public' => true,
            ],
            [
                'key' => 'app.logo_url',
                'value' => '',
                'type' => 'string',
                'group' => 'application',
                'description' => 'URL to the application logo',
                'is_public' => true,
            ],
            [
                'key' => 'app.favicon_url',
                'value' => '',
                'type' => 'string',
                'group' => 'application',
                'description' => 'URL to the application favicon',
                'is_public' => true,
            ],
            [
                'key' => 'app.default_locale',
                'value' => 'en',
                'type' => 'string',
                'group' => 'application',
                'description' => 'Default application language',
                'is_public' => true,
            ],
            [
                'key' => 'app.timezone',
                'value' => 'UTC',
                'type' => 'string',
                'group' => 'application',
                'description' => 'Default timezone for displaying dates',
                'is_public' => true,
            ],
            [
                'key' => 'app.date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'group' => 'application',
                'description' => 'Date format for display',
                'is_public' => true,
            ],
            [
                'key' => 'app.datetime_format',
                'value' => 'Y-m-d H:i:s',
                'type' => 'string',
                'group' => 'application',
                'description' => 'DateTime format for display',
                'is_public' => true,
            ],

            // Map settings
            [
                'key' => 'map.default_center_lat',
                'value' => '48.3794',
                'type' => 'string',
                'group' => 'map',
                'description' => 'Default map center latitude',
                'is_public' => true,
            ],
            [
                'key' => 'map.default_center_lng',
                'value' => '31.1656',
                'type' => 'string',
                'group' => 'map',
                'description' => 'Default map center longitude',
                'is_public' => true,
            ],
            [
                'key' => 'map.default_zoom',
                'value' => '6',
                'type' => 'integer',
                'group' => 'map',
                'description' => 'Default map zoom level',
                'is_public' => true,
            ],
            [
                'key' => 'map.tile_provider',
                'value' => 'openstreetmap',
                'type' => 'string',
                'group' => 'map',
                'description' => 'Map tile provider (openstreetmap, mapbox, custom)',
                'is_public' => true,
            ],
            [
                'key' => 'map.cluster_events',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'map',
                'description' => 'Enable event clustering on the map',
                'is_public' => true,
            ],

            // Events settings
            [
                'key' => 'events.require_approval',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'events',
                'description' => 'Require moderator approval before publishing events',
                'is_public' => false,
            ],
            [
                'key' => 'events.default_visibility',
                'value' => 'public',
                'type' => 'string',
                'group' => 'events',
                'description' => 'Default visibility for new events',
                'is_public' => false,
            ],
            [
                'key' => 'events.allow_anonymous_viewing',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'events',
                'description' => 'Allow non-authenticated users to view public events',
                'is_public' => true,
            ],
            [
                'key' => 'events.min_confidence_score',
                'value' => '0.5',
                'type' => 'string',
                'group' => 'events',
                'description' => 'Minimum confidence score to display events',
                'is_public' => false,
            ],

            // Registration settings
            [
                'key' => 'registration.enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'registration',
                'description' => 'Enable user registration',
                'is_public' => true,
            ],
            [
                'key' => 'registration.require_email_verification',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'registration',
                'description' => 'Require email verification after registration',
                'is_public' => false,
            ],
            [
                'key' => 'registration.default_role',
                'value' => 'viewer',
                'type' => 'string',
                'group' => 'registration',
                'description' => 'Default role for new users',
                'is_public' => false,
            ],
            [
                'key' => 'registration.require_approval',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'registration',
                'description' => 'Require admin approval for new accounts',
                'is_public' => false,
            ],

            // Security settings
            [
                'key' => 'security.two_factor_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Enable two-factor authentication option',
                'is_public' => false,
            ],
            [
                'key' => 'security.session_lifetime',
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Session lifetime in minutes',
                'is_public' => false,
            ],
            [
                'key' => 'security.api_rate_limit',
                'value' => '60',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'API rate limit per minute',
                'is_public' => false,
            ],

            // Export settings
            [
                'key' => 'export.max_records',
                'value' => '10000',
                'type' => 'integer',
                'group' => 'export',
                'description' => 'Maximum number of records per export',
                'is_public' => false,
            ],
            [
                'key' => 'export.allowed_formats',
                'value' => '["csv","geojson","kml"]',
                'type' => 'json',
                'group' => 'export',
                'description' => 'Allowed export formats',
                'is_public' => true,
            ],

            // Audit settings
            [
                'key' => 'audit.retention_days',
                'value' => '365',
                'type' => 'integer',
                'group' => 'audit',
                'description' => 'Days to retain audit logs',
                'is_public' => false,
            ],
            [
                'key' => 'audit.log_api_requests',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'audit',
                'description' => 'Log all API requests',
                'is_public' => false,
            ],

            // Installation tracking
            [
                'key' => 'installation.completed',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'installation',
                'description' => 'Whether the installation wizard has been completed',
                'is_public' => false,
            ],
            [
                'key' => 'installation.completed_at',
                'value' => '',
                'type' => 'string',
                'group' => 'installation',
                'description' => 'When the installation was completed',
                'is_public' => false,
            ],

            // Actor type emoji settings (customizable via admin panel)
            [
                'key' => 'actors.emoji.country',
                'value' => '🏳️',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for country actors (fallback if no flag)',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.terrorist',
                'value' => '🏴‍☠️',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for terrorist organizations',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.militia',
                'value' => '⚔️',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for militia groups',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.pmc',
                'value' => '🏴',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for Private Military Companies (PMCs)',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.separatist',
                'value' => '🏳️',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for separatist movements',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.rebel',
                'value' => '🚩',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for rebel groups',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.cartel',
                'value' => '💊',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for cartels and criminal organizations',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.insurgent',
                'value' => '💣',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for insurgent groups',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.political',
                'value' => '🏛️',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for political organizations',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.international',
                'value' => '🌐',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for international organizations',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.coalition',
                'value' => '🤝',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for military coalitions',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.proxy',
                'value' => '🎭',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for proxy forces',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.paramilitary',
                'value' => '🛡️',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for paramilitary forces',
                'is_public' => true,
            ],
            [
                'key' => 'actors.emoji.unknown',
                'value' => '❓',
                'type' => 'string',
                'group' => 'actors',
                'description' => 'Emoji for unknown/unidentified actors',
                'is_public' => true,
            ],
        ];

        $now = now();

        foreach ($settings as $setting) {
            DB::table('settings')->insert([
                ...$setting,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
