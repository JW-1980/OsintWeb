<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SitrepTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates default SITREP (Situation Report) templates for various
     * reporting needs including daily briefs, weekly summaries, and
     * incident-specific reports.
     */
    public function run(): void
    {
        $now = now();

        $templates = [
            // ===============================================
            // DAILY BRIEF TEMPLATE
            // ===============================================
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Daily Intelligence Brief',
                'description' => 'Comprehensive daily situation report covering all monitored conflicts and significant events from the past 24 hours.',
                'template_type' => 'daily_brief',
                'sections' => json_encode([
                    [
                        'title' => 'Executive Summary',
                        'type' => 'text',
                        'order' => 1,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Brief overview of the most significant developments in 2-3 paragraphs. Highlight any critical changes to the operational environment.',
                        'word_limit' => 300,
                    ],
                    [
                        'title' => 'Key Events (Last 24 Hours)',
                        'type' => 'event_list',
                        'order' => 2,
                        'required' => true,
                        'query_config' => [
                            'time_range' => '24h',
                            'sort_by' => 'significance',
                            'limit' => 10,
                            'include_media' => true,
                            'verification_status' => ['verified', 'likely'],
                        ],
                        'instructions' => 'List of verified events with timestamps, locations, and brief descriptions.',
                    ],
                    [
                        'title' => 'Territorial Changes',
                        'type' => 'map_snapshot',
                        'order' => 3,
                        'required' => false,
                        'query_config' => [
                            'time_range' => '24h',
                            'show_control_zones' => true,
                            'show_front_lines' => true,
                            'highlight_changes' => true,
                        ],
                        'instructions' => 'Map showing any territorial changes with before/after comparison if applicable.',
                    ],
                    [
                        'title' => 'Equipment Losses',
                        'type' => 'equipment_table',
                        'order' => 4,
                        'required' => false,
                        'query_config' => [
                            'time_range' => '24h',
                            'group_by' => 'actor',
                            'include_images' => true,
                            'verification_status' => ['verified'],
                        ],
                        'instructions' => 'Tabulated summary of confirmed equipment losses by party.',
                    ],
                    [
                        'title' => 'Casualty Estimates',
                        'type' => 'statistics',
                        'order' => 5,
                        'required' => false,
                        'query_config' => [
                            'time_range' => '24h',
                            'categories' => ['military', 'civilian'],
                            'source_requirements' => 'multiple_sources',
                        ],
                        'instructions' => 'Estimated casualties with confidence levels and source attribution.',
                    ],
                    [
                        'title' => 'Information Environment',
                        'type' => 'text',
                        'order' => 6,
                        'required' => false,
                        'query_config' => null,
                        'instructions' => 'Notable propaganda, disinformation, or significant social media narratives observed.',
                        'word_limit' => 200,
                    ],
                    [
                        'title' => 'Forecast & Indicators',
                        'type' => 'text',
                        'order' => 7,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Assessment of likely developments in next 24-72 hours. Key indicators to watch.',
                        'word_limit' => 250,
                    ],
                    [
                        'title' => 'Sources',
                        'type' => 'source_list',
                        'order' => 8,
                        'required' => true,
                        'query_config' => [
                            'auto_generate' => true,
                            'include_reliability_ratings' => true,
                        ],
                        'instructions' => 'Automatically generated list of all sources cited in this report.',
                    ],
                ]),
                'header_config' => json_encode([
                    'show_logo' => true,
                    'logo_position' => 'left',
                    'classification' => 'UNCLASSIFIED // OSINT',
                    'distribution' => 'For authorized personnel only',
                    'show_date' => true,
                    'show_report_number' => true,
                    'report_number_format' => 'SITREP-{YYYY}{MM}{DD}-{SEQ}',
                ]),
                'footer_config' => json_encode([
                    'show_page_numbers' => true,
                    'show_disclaimer' => true,
                    'disclaimer_text' => 'This report is compiled from open sources and may contain unverified information. Assessments represent analyst judgment based on available evidence.',
                    'show_classification' => true,
                ]),
                'styling' => json_encode([
                    'font_family' => 'Inter, Arial, sans-serif',
                    'heading_font' => 'Inter, Arial, sans-serif',
                    'base_font_size' => '11pt',
                    'heading_color' => '#1a365d',
                    'accent_color' => '#2563eb',
                    'border_style' => 'solid 1px #e5e7eb',
                    'page_margins' => '1in',
                ]),
                'is_default' => true,
                'is_public' => true,
            ],

            // ===============================================
            // WEEKLY SUMMARY TEMPLATE
            // ===============================================
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Weekly Intelligence Summary',
                'description' => 'Comprehensive weekly analysis covering trends, patterns, and significant developments over the past 7 days.',
                'template_type' => 'weekly_summary',
                'sections' => json_encode([
                    [
                        'title' => 'Week in Review',
                        'type' => 'text',
                        'order' => 1,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'High-level summary of the week\'s most significant developments and their implications.',
                        'word_limit' => 500,
                    ],
                    [
                        'title' => 'Key Statistics',
                        'type' => 'statistics_dashboard',
                        'order' => 2,
                        'required' => true,
                        'query_config' => [
                            'time_range' => '7d',
                            'metrics' => [
                                'total_events',
                                'events_by_type',
                                'equipment_losses',
                                'territorial_changes_km2',
                                'source_reliability_breakdown',
                            ],
                            'compare_to_previous' => true,
                        ],
                        'instructions' => 'Statistical dashboard with week-over-week comparisons.',
                    ],
                    [
                        'title' => 'Operational Overview',
                        'type' => 'text',
                        'order' => 3,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Analysis of military operations, offensive/defensive actions, and strategic movements.',
                        'word_limit' => 600,
                    ],
                    [
                        'title' => 'Notable Events Timeline',
                        'type' => 'event_timeline',
                        'order' => 4,
                        'required' => true,
                        'query_config' => [
                            'time_range' => '7d',
                            'sort_by' => 'date',
                            'significance_threshold' => 7,
                            'include_images' => true,
                        ],
                        'instructions' => 'Chronological timeline of significant events with visual markers.',
                    ],
                    [
                        'title' => 'Territorial Analysis',
                        'type' => 'map_comparison',
                        'order' => 5,
                        'required' => true,
                        'query_config' => [
                            'compare_start' => '-7d',
                            'compare_end' => 'now',
                            'show_gains_losses' => true,
                            'calculate_area' => true,
                        ],
                        'instructions' => 'Side-by-side map comparison showing territorial changes over the week.',
                    ],
                    [
                        'title' => 'Equipment Loss Assessment',
                        'type' => 'equipment_analysis',
                        'order' => 6,
                        'required' => true,
                        'query_config' => [
                            'time_range' => '7d',
                            'group_by' => ['actor', 'equipment_category'],
                            'include_valuations' => true,
                            'show_trends' => true,
                        ],
                        'instructions' => 'Detailed analysis of equipment losses with categorization and trend analysis.',
                    ],
                    [
                        'title' => 'Information Warfare Summary',
                        'type' => 'text',
                        'order' => 7,
                        'required' => false,
                        'query_config' => null,
                        'instructions' => 'Analysis of propaganda themes, disinformation campaigns, and narrative trends.',
                        'word_limit' => 400,
                    ],
                    [
                        'title' => 'Emerging Patterns',
                        'type' => 'text',
                        'order' => 8,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Identification of emerging patterns, trends, or anomalies requiring attention.',
                        'word_limit' => 300,
                    ],
                    [
                        'title' => 'Outlook & Predictions',
                        'type' => 'text',
                        'order' => 9,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Assessment of likely developments in the coming week with confidence levels.',
                        'word_limit' => 400,
                    ],
                    [
                        'title' => 'Appendix: Data Sources',
                        'type' => 'source_analysis',
                        'order' => 10,
                        'required' => true,
                        'query_config' => [
                            'auto_generate' => true,
                            'show_reliability_breakdown' => true,
                            'show_source_diversity' => true,
                        ],
                        'instructions' => 'Analysis of source reliability and diversity for this report.',
                    ],
                ]),
                'header_config' => json_encode([
                    'show_logo' => true,
                    'logo_position' => 'center',
                    'classification' => 'UNCLASSIFIED // OSINT',
                    'distribution' => 'For authorized personnel only',
                    'show_date' => true,
                    'show_report_number' => true,
                    'report_number_format' => 'WEEKLY-{YYYY}-W{WW}',
                    'show_week_range' => true,
                ]),
                'footer_config' => json_encode([
                    'show_page_numbers' => true,
                    'show_disclaimer' => true,
                    'disclaimer_text' => 'This report is compiled from open sources. Assessments and predictions represent analyst judgment based on available evidence and may be subject to revision.',
                    'show_classification' => true,
                ]),
                'styling' => json_encode([
                    'font_family' => 'Inter, Arial, sans-serif',
                    'heading_font' => 'Inter, Arial, sans-serif',
                    'base_font_size' => '11pt',
                    'heading_color' => '#1e3a5f',
                    'accent_color' => '#3b82f6',
                    'border_style' => 'solid 1px #d1d5db',
                    'page_margins' => '0.75in',
                ]),
                'is_default' => true,
                'is_public' => true,
            ],

            // ===============================================
            // INCIDENT REPORT TEMPLATE
            // ===============================================
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Incident Report',
                'description' => 'Detailed report template for documenting specific incidents with comprehensive evidence collection.',
                'template_type' => 'incident_report',
                'sections' => json_encode([
                    [
                        'title' => 'Incident Overview',
                        'type' => 'incident_header',
                        'order' => 1,
                        'required' => true,
                        'query_config' => null,
                        'fields' => [
                            'incident_type',
                            'date_time',
                            'location_coordinates',
                            'location_name',
                            'actors_involved',
                            'verification_status',
                        ],
                        'instructions' => 'Basic incident identification information.',
                    ],
                    [
                        'title' => 'Executive Summary',
                        'type' => 'text',
                        'order' => 2,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Brief summary of what occurred, who was involved, and the outcome.',
                        'word_limit' => 200,
                    ],
                    [
                        'title' => 'Detailed Narrative',
                        'type' => 'text',
                        'order' => 3,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Comprehensive chronological account of the incident based on all available sources.',
                        'word_limit' => 1000,
                    ],
                    [
                        'title' => 'Location Analysis',
                        'type' => 'location_detail',
                        'order' => 4,
                        'required' => true,
                        'query_config' => [
                            'show_satellite_imagery' => true,
                            'show_street_view' => true,
                            'show_surrounding_context' => true,
                            'geolocation_methodology' => true,
                        ],
                        'instructions' => 'Geolocation analysis with supporting imagery and methodology explanation.',
                    ],
                    [
                        'title' => 'Visual Evidence',
                        'type' => 'media_gallery',
                        'order' => 5,
                        'required' => true,
                        'query_config' => [
                            'include_metadata' => true,
                            'include_verification_notes' => true,
                            'show_original_source' => true,
                        ],
                        'instructions' => 'All visual evidence with annotations and verification status.',
                    ],
                    [
                        'title' => 'Casualty Assessment',
                        'type' => 'casualty_table',
                        'order' => 6,
                        'required' => false,
                        'query_config' => [
                            'categories' => ['killed', 'wounded', 'missing'],
                            'distinguish_military_civilian' => true,
                            'source_attribution' => true,
                        ],
                        'instructions' => 'Estimated casualties with source attribution and confidence levels.',
                    ],
                    [
                        'title' => 'Damage Assessment',
                        'type' => 'damage_assessment',
                        'order' => 7,
                        'required' => false,
                        'query_config' => [
                            'categories' => ['infrastructure', 'residential', 'military', 'environmental'],
                            'include_imagery' => true,
                        ],
                        'instructions' => 'Assessment of physical damage with categorization.',
                    ],
                    [
                        'title' => 'Equipment Involved',
                        'type' => 'equipment_detail',
                        'order' => 8,
                        'required' => false,
                        'query_config' => [
                            'include_identification_methodology' => true,
                            'show_reference_images' => true,
                        ],
                        'instructions' => 'Identification of weapons, vehicles, or equipment involved with methodology.',
                    ],
                    [
                        'title' => 'Source Analysis',
                        'type' => 'source_credibility',
                        'order' => 9,
                        'required' => true,
                        'query_config' => [
                            'show_reliability_ratings' => true,
                            'show_corroboration_matrix' => true,
                            'identify_contradictions' => true,
                        ],
                        'instructions' => 'Analysis of all sources with reliability ratings and corroboration assessment.',
                    ],
                    [
                        'title' => 'Assessment & Conclusions',
                        'type' => 'text',
                        'order' => 10,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Analyst assessment of what occurred with confidence level and any remaining uncertainties.',
                        'word_limit' => 400,
                    ],
                    [
                        'title' => 'Potential Legal Implications',
                        'type' => 'text',
                        'order' => 11,
                        'required' => false,
                        'query_config' => null,
                        'instructions' => 'If applicable, assessment of potential violations of international humanitarian law.',
                        'word_limit' => 300,
                    ],
                ]),
                'header_config' => json_encode([
                    'show_logo' => true,
                    'logo_position' => 'left',
                    'classification' => 'UNCLASSIFIED // OSINT',
                    'distribution' => 'For authorized personnel only',
                    'show_date' => true,
                    'show_report_number' => true,
                    'report_number_format' => 'INC-{YYYY}{MM}{DD}-{SEQ}',
                    'show_incident_type' => true,
                ]),
                'footer_config' => json_encode([
                    'show_page_numbers' => true,
                    'show_disclaimer' => true,
                    'disclaimer_text' => 'This incident report is based on open source information and represents the analyst\'s assessment at time of publication. Information may be updated as additional evidence becomes available.',
                    'show_classification' => true,
                    'show_analyst_contact' => true,
                ]),
                'styling' => json_encode([
                    'font_family' => 'Inter, Arial, sans-serif',
                    'heading_font' => 'Inter, Arial, sans-serif',
                    'base_font_size' => '10pt',
                    'heading_color' => '#7c2d12',
                    'accent_color' => '#dc2626',
                    'border_style' => 'solid 1px #fecaca',
                    'page_margins' => '1in',
                ]),
                'is_default' => true,
                'is_public' => true,
            ],

            // ===============================================
            // EQUIPMENT LOSS REPORT TEMPLATE
            // ===============================================
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Equipment Loss Report',
                'description' => 'Specialized template for documenting and analyzing military equipment losses.',
                'template_type' => 'equipment_loss',
                'sections' => json_encode([
                    [
                        'title' => 'Summary Statistics',
                        'type' => 'equipment_stats',
                        'order' => 1,
                        'required' => true,
                        'query_config' => [
                            'time_range' => 'report_period',
                            'group_by' => 'actor',
                            'show_totals' => true,
                            'show_categories' => true,
                        ],
                        'instructions' => 'High-level summary of equipment losses by party and category.',
                    ],
                    [
                        'title' => 'Detailed Loss Registry',
                        'type' => 'equipment_registry',
                        'order' => 2,
                        'required' => true,
                        'query_config' => [
                            'time_range' => 'report_period',
                            'columns' => ['date', 'type', 'model', 'actor', 'location', 'status', 'verification', 'source'],
                            'include_thumbnails' => true,
                            'sortable' => true,
                        ],
                        'instructions' => 'Complete registry of all documented equipment losses.',
                    ],
                    [
                        'title' => 'Category Breakdown',
                        'type' => 'category_analysis',
                        'order' => 3,
                        'required' => true,
                        'query_config' => [
                            'categories' => [
                                'main_battle_tanks',
                                'infantry_fighting_vehicles',
                                'armored_personnel_carriers',
                                'artillery_systems',
                                'multiple_rocket_launchers',
                                'air_defense_systems',
                                'aircraft',
                                'helicopters',
                                'unmanned_aerial_vehicles',
                                'naval_vessels',
                                'support_vehicles',
                            ],
                            'show_subcategories' => true,
                        ],
                        'instructions' => 'Breakdown by equipment category with visual charts.',
                    ],
                    [
                        'title' => 'Loss Type Analysis',
                        'type' => 'loss_type_breakdown',
                        'order' => 4,
                        'required' => true,
                        'query_config' => [
                            'loss_types' => ['destroyed', 'damaged', 'abandoned', 'captured'],
                            'show_percentages' => true,
                        ],
                        'instructions' => 'Analysis of how equipment was lost (destroyed, captured, abandoned, damaged).',
                    ],
                    [
                        'title' => 'Geographic Distribution',
                        'type' => 'loss_map',
                        'order' => 5,
                        'required' => true,
                        'query_config' => [
                            'show_heatmap' => true,
                            'show_individual_markers' => true,
                            'cluster_threshold' => 50,
                        ],
                        'instructions' => 'Map showing geographic distribution of equipment losses.',
                    ],
                    [
                        'title' => 'Notable Losses',
                        'type' => 'featured_losses',
                        'order' => 6,
                        'required' => false,
                        'query_config' => [
                            'criteria' => ['high_value', 'rare', 'strategic_significance'],
                            'include_detailed_analysis' => true,
                            'max_items' => 5,
                        ],
                        'instructions' => 'Detailed analysis of particularly significant equipment losses.',
                    ],
                    [
                        'title' => 'Trend Analysis',
                        'type' => 'trend_charts',
                        'order' => 7,
                        'required' => true,
                        'query_config' => [
                            'show_daily_losses' => true,
                            'show_cumulative' => true,
                            'show_moving_average' => true,
                            'compare_to_previous_period' => true,
                        ],
                        'instructions' => 'Charts showing loss trends over time.',
                    ],
                    [
                        'title' => 'Verification Methodology',
                        'type' => 'methodology',
                        'order' => 8,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Explanation of how losses are verified and counted.',
                        'default_content' => 'Equipment losses are documented based on visual evidence from open sources. Each loss is verified through analysis of imagery, geolocation, and cross-referencing with multiple sources where possible. Losses are categorized as: Destroyed (confirmed destroyed beyond repair), Damaged (confirmed damage, repair status unknown), Abandoned (confirmed abandoned by operating force), Captured (confirmed in possession of opposing force).',
                    ],
                    [
                        'title' => 'Source Attribution',
                        'type' => 'source_list',
                        'order' => 9,
                        'required' => true,
                        'query_config' => [
                            'auto_generate' => true,
                            'group_by' => 'source_type',
                        ],
                        'instructions' => 'Complete list of sources for documented losses.',
                    ],
                ]),
                'header_config' => json_encode([
                    'show_logo' => true,
                    'logo_position' => 'left',
                    'classification' => 'UNCLASSIFIED // OSINT',
                    'distribution' => 'For authorized personnel only',
                    'show_date' => true,
                    'show_report_number' => true,
                    'report_number_format' => 'EQUIP-{YYYY}{MM}{DD}-{SEQ}',
                    'show_period_covered' => true,
                ]),
                'footer_config' => json_encode([
                    'show_page_numbers' => true,
                    'show_disclaimer' => true,
                    'disclaimer_text' => 'Equipment losses documented in this report are based on visually confirmed evidence from open sources. Actual losses may be higher than documented figures. This data should be considered a minimum confirmed count.',
                    'show_classification' => true,
                ]),
                'styling' => json_encode([
                    'font_family' => 'Inter, Arial, sans-serif',
                    'heading_font' => 'Inter, Arial, sans-serif',
                    'base_font_size' => '10pt',
                    'heading_color' => '#065f46',
                    'accent_color' => '#059669',
                    'border_style' => 'solid 1px #a7f3d0',
                    'page_margins' => '0.75in',
                ]),
                'is_default' => true,
                'is_public' => true,
            ],

            // ===============================================
            // TERRITORIAL CHANGE REPORT TEMPLATE
            // ===============================================
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Territorial Change Report',
                'description' => 'Specialized template for documenting and analyzing changes in territorial control.',
                'template_type' => 'territorial_change',
                'sections' => json_encode([
                    [
                        'title' => 'Change Summary',
                        'type' => 'territorial_summary',
                        'order' => 1,
                        'required' => true,
                        'query_config' => [
                            'time_range' => 'report_period',
                            'show_net_change' => true,
                            'show_by_actor' => true,
                            'unit' => 'km2',
                        ],
                        'instructions' => 'Summary of territorial changes by party with area calculations.',
                    ],
                    [
                        'title' => 'Overview Map',
                        'type' => 'comparison_map',
                        'order' => 2,
                        'required' => true,
                        'query_config' => [
                            'show_before_after' => true,
                            'highlight_changes' => true,
                            'show_front_line_movement' => true,
                            'show_settlement_changes' => true,
                        ],
                        'instructions' => 'Before/after map comparison showing all territorial changes.',
                    ],
                    [
                        'title' => 'Regional Analysis',
                        'type' => 'regional_breakdown',
                        'order' => 3,
                        'required' => true,
                        'query_config' => [
                            'divide_by' => 'administrative_region',
                            'show_individual_maps' => true,
                            'show_statistics' => true,
                        ],
                        'instructions' => 'Detailed analysis of changes by geographic region.',
                    ],
                    [
                        'title' => 'Significant Locations',
                        'type' => 'location_list',
                        'order' => 4,
                        'required' => true,
                        'query_config' => [
                            'types' => ['cities', 'towns', 'strategic_points', 'infrastructure'],
                            'show_control_changes' => true,
                            'include_population' => true,
                        ],
                        'instructions' => 'List of significant locations that changed control.',
                    ],
                    [
                        'title' => 'Front Line Analysis',
                        'type' => 'front_line_detail',
                        'order' => 5,
                        'required' => true,
                        'query_config' => [
                            'show_movement_vectors' => true,
                            'calculate_distances' => true,
                            'identify_salients' => true,
                        ],
                        'instructions' => 'Detailed analysis of front line changes with direction and distance.',
                    ],
                    [
                        'title' => 'Operational Context',
                        'type' => 'text',
                        'order' => 6,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Analysis of military operations that led to territorial changes.',
                        'word_limit' => 600,
                    ],
                    [
                        'title' => 'Strategic Implications',
                        'type' => 'text',
                        'order' => 7,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Assessment of strategic significance of territorial changes.',
                        'word_limit' => 400,
                    ],
                    [
                        'title' => 'Historical Comparison',
                        'type' => 'historical_chart',
                        'order' => 8,
                        'required' => false,
                        'query_config' => [
                            'show_control_over_time' => true,
                            'time_range' => 'conflict_start',
                            'interval' => 'weekly',
                        ],
                        'instructions' => 'Chart showing territorial control changes over the course of the conflict.',
                    ],
                    [
                        'title' => 'Data Sources & Methodology',
                        'type' => 'methodology',
                        'order' => 9,
                        'required' => true,
                        'query_config' => null,
                        'instructions' => 'Explanation of how territorial control is assessed and mapped.',
                        'default_content' => 'Territorial control is assessed based on multiple factors including: presence of military forces, functioning of administrative structures, control of key infrastructure, and reports from local sources. Control zones are categorized as: Full Control (administrative and military presence), Contested (active fighting, unclear control), and Influence (limited presence without full control). Mapping relies on geolocated footage, satellite imagery analysis, and corroborated reports from multiple sources.',
                    ],
                    [
                        'title' => 'Source Attribution',
                        'type' => 'source_list',
                        'order' => 10,
                        'required' => true,
                        'query_config' => [
                            'auto_generate' => true,
                            'include_satellite_imagery_dates' => true,
                        ],
                        'instructions' => 'Complete list of sources including satellite imagery dates.',
                    ],
                ]),
                'header_config' => json_encode([
                    'show_logo' => true,
                    'logo_position' => 'left',
                    'classification' => 'UNCLASSIFIED // OSINT',
                    'distribution' => 'For authorized personnel only',
                    'show_date' => true,
                    'show_report_number' => true,
                    'report_number_format' => 'TERR-{YYYY}{MM}{DD}-{SEQ}',
                    'show_period_covered' => true,
                    'show_conflict_name' => true,
                ]),
                'footer_config' => json_encode([
                    'show_page_numbers' => true,
                    'show_disclaimer' => true,
                    'disclaimer_text' => 'Territorial assessments are based on open source analysis and may not reflect ground truth. Control zones should be considered approximate. Actual front lines may differ from mapped representations.',
                    'show_classification' => true,
                ]),
                'styling' => json_encode([
                    'font_family' => 'Inter, Arial, sans-serif',
                    'heading_font' => 'Inter, Arial, sans-serif',
                    'base_font_size' => '10pt',
                    'heading_color' => '#4338ca',
                    'accent_color' => '#6366f1',
                    'border_style' => 'solid 1px #c7d2fe',
                    'page_margins' => '0.75in',
                    'map_border' => 'solid 2px #4338ca',
                ]),
                'is_default' => true,
                'is_public' => true,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('sitrep_templates')->insert([
                ...$template,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
