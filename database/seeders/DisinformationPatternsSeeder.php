<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DisinformationPatternsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates comprehensive disinformation pattern definitions for detecting
     * various forms of information manipulation, propaganda, and coordinated
     * inauthentic behavior.
     */
    public function run(): void
    {
        $now = now();

        $patterns = [
            // ===============================================
            // LINGUISTIC PATTERNS (Pattern Type: linguistic)
            // ===============================================
            [
                'name' => 'Emotionally Charged Language',
                'slug' => 'emotionally-charged-language',
                'description' => 'Use of highly emotional, inflammatory, or sensationalist language designed to provoke strong reactions rather than inform.',
                'pattern_type' => 'linguistic',
                'detection_rules' => json_encode([
                    'min_emotional_words' => 3,
                    'emotional_density_threshold' => 0.15,
                    'check_capitalization' => true,
                    'check_exclamation_marks' => true,
                ]),
                'keywords' => json_encode([
                    'shocking', 'outrageous', 'horrifying', 'devastating', 'explosive',
                    'bombshell', 'scandal', 'crisis', 'catastrophe', 'unprecedented',
                    'urgent', 'breaking', 'exclusive', 'must see', 'you won\'t believe',
                ]),
                'regex_patterns' => json_encode([
                    '/[A-Z]{4,}/',  // All caps words
                    '/!{2,}/',      // Multiple exclamation marks
                    '/\?\!+/',      // Interrobang patterns
                ]),
                'severity' => 2,
                'base_threat_weight' => 15,
                'examples' => json_encode([
                    'SHOCKING: You Won\'t BELIEVE What They\'re Hiding!!!',
                    'This EXPLOSIVE revelation will DESTROY everything you thought you knew!',
                    'URGENT: The catastrophic truth they don\'t want you to see!',
                ]),
                'indicators' => json_encode([
                    'Excessive use of capital letters',
                    'Multiple exclamation marks',
                    'Superlatives without evidence',
                    'Fear-inducing language',
                    'Urgency without justification',
                ]),
                'counter_indicators' => json_encode([
                    'Academic or scientific language',
                    'Citations and references',
                    'Balanced presentation of viewpoints',
                    'Measured tone with evidence',
                ]),
            ],
            [
                'name' => 'Whataboutism',
                'slug' => 'whataboutism',
                'description' => 'Deflection technique that responds to criticism by pointing to alleged wrongdoing by others rather than addressing the original issue.',
                'pattern_type' => 'linguistic',
                'detection_rules' => json_encode([
                    'check_deflection_phrases' => true,
                    'check_comparative_accusations' => true,
                    'context_required' => true,
                ]),
                'keywords' => json_encode([
                    'what about', 'but what about', 'yeah but', 'however',
                    'on the other hand', 'but they also', 'don\'t forget',
                    'remember when', 'why don\'t you talk about', 'but America',
                    'but the West', 'double standards', 'hypocrisy',
                ]),
                'regex_patterns' => json_encode([
                    '/what about .+\?/i',
                    '/but (they|you|the west|america|nato) (also|did|does)/i',
                    '/why (don\'t|doesn\'t) (anyone|the media|they) talk about/i',
                ]),
                'severity' => 3,
                'base_threat_weight' => 20,
                'examples' => json_encode([
                    'Why are you criticizing X when Y did the same thing?',
                    'What about when the West invaded Iraq?',
                    'But America also has human rights problems, so...',
                ]),
                'indicators' => json_encode([
                    'Deflection from original topic',
                    'Tu quoque fallacy',
                    'False equivalence',
                    'Lack of direct response to criticism',
                ]),
                'counter_indicators' => json_encode([
                    'Relevant comparison with context',
                    'Acknowledgment of original point before comparison',
                    'Good faith engagement with criticism',
                ]),
            ],
            [
                'name' => 'False Attribution',
                'slug' => 'false-attribution',
                'description' => 'Attributing statements, quotes, or actions to individuals or organizations that never made them.',
                'pattern_type' => 'linguistic',
                'detection_rules' => json_encode([
                    'check_quote_verification' => true,
                    'check_source_validity' => true,
                    'flag_unverified_quotes' => true,
                ]),
                'keywords' => json_encode([
                    'reportedly said', 'allegedly stated', 'is rumored to have',
                    'sources say', 'insiders claim', 'anonymous source',
                    'leaked document shows', 'secret recording reveals',
                ]),
                'regex_patterns' => json_encode([
                    '/"[^"]+"\s*-\s*[A-Z][a-z]+/i',  // Quote attribution pattern
                    '/according to (unverified|anonymous|unnamed) sources/i',
                ]),
                'severity' => 4,
                'base_threat_weight' => 35,
                'examples' => json_encode([
                    'Famous Person: "Quote they never actually said"',
                    'According to leaked documents, the president admitted...',
                    'Anonymous insider reveals shocking confession...',
                ]),
                'indicators' => json_encode([
                    'No verifiable source for quote',
                    'Quote contradicts known statements',
                    'Anonymous or unnamed sources for significant claims',
                    'Quote appears in no reputable outlets',
                ]),
                'counter_indicators' => json_encode([
                    'Quote verifiable in original context',
                    'Video/audio evidence of statement',
                    'Multiple independent sources confirm',
                ]),
            ],
            [
                'name' => 'Loaded Language and Framing',
                'slug' => 'loaded-language-framing',
                'description' => 'Strategic use of terminology to frame events in a particular ideological light, often using euphemisms or dysphemisms.',
                'pattern_type' => 'linguistic',
                'detection_rules' => json_encode([
                    'check_euphemism_dysphemism' => true,
                    'analyze_framing_bias' => true,
                    'compare_neutral_alternatives' => true,
                ]),
                'keywords' => json_encode([
                    'regime', 'junta', 'freedom fighters', 'terrorists',
                    'liberation', 'occupation', 'special operation', 'invasion',
                    'collateral damage', 'ethnic cleansing', 'denazification',
                    'peacekeeping', 'intervention', 'aggression',
                ]),
                'regex_patterns' => json_encode([
                    '/so-called\s+["\']?\w+["\']?/i',
                    '/the\s+(illegitimate|puppet|corrupt)\s+\w+/i',
                ]),
                'severity' => 3,
                'base_threat_weight' => 20,
                'examples' => json_encode([
                    'Using "special military operation" instead of "invasion"',
                    'Calling defenders "nationalists" or "Nazis"',
                    'Referring to "regime" vs "government" based on alignment',
                ]),
                'indicators' => json_encode([
                    'Consistent use of charged terminology',
                    'Avoidance of neutral descriptors',
                    'Framing that matches state narratives',
                    'Dehumanizing language',
                ]),
                'counter_indicators' => json_encode([
                    'Use of neutral, descriptive language',
                    'Acknowledgment of terminology choices',
                    'Multiple perspectives presented',
                ]),
            ],
            [
                'name' => 'Appeal to Fear',
                'slug' => 'appeal-to-fear',
                'description' => 'Using fear, threats, or predictions of catastrophe to manipulate opinion rather than presenting evidence-based arguments.',
                'pattern_type' => 'linguistic',
                'detection_rules' => json_encode([
                    'check_threat_language' => true,
                    'check_catastrophizing' => true,
                    'check_urgency_without_evidence' => true,
                ]),
                'keywords' => json_encode([
                    'threat', 'danger', 'risk', 'catastrophe', 'disaster',
                    'collapse', 'destruction', 'annihilation', 'war', 'death',
                    'survival', 'extinction', 'nuclear', 'apocalypse',
                    'your family', 'your children', 'your country',
                ]),
                'regex_patterns' => json_encode([
                    '/if (we|you|they) don\'t .+, .+ will (happen|occur|be destroyed)/i',
                    '/before it\'s too late/i',
                    '/the end of .+ as we know it/i',
                ]),
                'severity' => 3,
                'base_threat_weight' => 25,
                'examples' => json_encode([
                    'If we don\'t act now, our civilization will be destroyed!',
                    'They are coming for your children - wake up!',
                    'This is the end of freedom as we know it!',
                ]),
                'indicators' => json_encode([
                    'Predictions of doom without evidence',
                    'Threats to personal safety',
                    'Calls to action based on fear',
                    'Vague but ominous warnings',
                ]),
                'counter_indicators' => json_encode([
                    'Risk assessment with evidence',
                    'Proportionate response suggestions',
                    'Historical context for threats',
                ]),
            ],

            // ===============================================
            // BEHAVIORAL PATTERNS (Pattern Type: behavioral)
            // ===============================================
            [
                'name' => 'Coordinated Amplification',
                'slug' => 'coordinated-amplification',
                'description' => 'Multiple accounts amplifying the same content in a coordinated manner, often within a short time window.',
                'pattern_type' => 'behavioral',
                'detection_rules' => json_encode([
                    'min_accounts' => 5,
                    'time_window_minutes' => 30,
                    'content_similarity_threshold' => 0.85,
                    'check_account_creation_dates' => true,
                ]),
                'keywords' => json_encode([]),
                'regex_patterns' => json_encode([]),
                'severity' => 4,
                'base_threat_weight' => 40,
                'examples' => json_encode([
                    '20 accounts sharing identical content within 10 minutes',
                    'New accounts all created same week posting same hashtags',
                    'Coordinated replies to high-profile posts',
                ]),
                'indicators' => json_encode([
                    'Near-identical content from multiple accounts',
                    'Posting within narrow time window',
                    'Similar account characteristics',
                    'Synchronized hashtag usage',
                    'Bot-like posting patterns',
                ]),
                'counter_indicators' => json_encode([
                    'Organic viral spread over hours/days',
                    'Diverse account histories',
                    'Natural variation in shared content',
                ]),
            ],
            [
                'name' => 'Sockpuppet Behavior',
                'slug' => 'sockpuppet-behavior',
                'description' => 'Use of fake accounts or multiple accounts by the same operator to create false impression of grassroots support.',
                'pattern_type' => 'behavioral',
                'detection_rules' => json_encode([
                    'check_account_age' => true,
                    'check_posting_patterns' => true,
                    'check_follower_following_ratio' => true,
                    'check_content_originality' => true,
                ]),
                'keywords' => json_encode([]),
                'regex_patterns' => json_encode([]),
                'severity' => 4,
                'base_threat_weight' => 35,
                'examples' => json_encode([
                    'Multiple accounts with similar usernames (e.g., PatriotEagle1, PatriotEagle2)',
                    'Accounts that only engage with specific topics or accounts',
                    'Profile pictures from stock photos or AI-generated',
                ]),
                'indicators' => json_encode([
                    'Recent account creation',
                    'Generic or AI-generated profile pictures',
                    'Limited engagement outside target topics',
                    'Unusual follower/following ratios',
                    'Copy-paste style responses',
                ]),
                'counter_indicators' => json_encode([
                    'Long account history',
                    'Diverse interests and engagements',
                    'Personal photos and content',
                    'Genuine social network',
                ]),
            ],
            [
                'name' => 'Astroturfing',
                'slug' => 'astroturfing',
                'description' => 'Organized activity designed to create the appearance of spontaneous grassroots movement while being coordinated from a central source.',
                'pattern_type' => 'behavioral',
                'detection_rules' => json_encode([
                    'check_message_templating' => true,
                    'check_timing_coordination' => true,
                    'check_source_diversity' => false,
                    'check_organic_engagement' => true,
                ]),
                'keywords' => json_encode([
                    'grassroots', 'movement', 'people are saying', 'everyone knows',
                    'the silent majority', 'real patriots', 'true believers',
                ]),
                'regex_patterns' => json_encode([
                    '/as a (concerned citizen|regular person|mother|father|veteran)/i',
                    '/speaking for (the people|real americans|ordinary folks)/i',
                ]),
                'severity' => 4,
                'base_threat_weight' => 40,
                'examples' => json_encode([
                    'Fake reviews or testimonials',
                    'Manufactured hashtag campaigns',
                    'Paid commenters posing as organic supporters',
                    'Fake petition signatures',
                ]),
                'indicators' => json_encode([
                    'Identical or templated messages',
                    'Coordinated timing of posts',
                    'Accounts with similar characteristics',
                    'Unnatural engagement patterns',
                    'Professional-quality content from "ordinary" accounts',
                ]),
                'counter_indicators' => json_encode([
                    'Genuine grassroots organizing history',
                    'Diverse participant backgrounds',
                    'Organic growth over time',
                    'Local community ties',
                ]),
            ],
            [
                'name' => 'Rage Farming',
                'slug' => 'rage-farming',
                'description' => 'Deliberately posting provocative content to generate angry responses and increase engagement, amplifying divisive narratives.',
                'pattern_type' => 'behavioral',
                'detection_rules' => json_encode([
                    'check_engagement_ratio' => true,
                    'check_negative_sentiment_responses' => true,
                    'check_provocative_framing' => true,
                ]),
                'keywords' => json_encode([
                    'triggered', 'snowflake', 'cope', 'seethe', 'stay mad',
                    'watch them lose it', 'this will make them crazy',
                ]),
                'regex_patterns' => json_encode([
                    '/wait (for|til) (the|they) .+ (meltdown|reaction|response)/i',
                    '/this is going to break the internet/i',
                ]),
                'severity' => 3,
                'base_threat_weight' => 25,
                'examples' => json_encode([
                    'Posting intentionally misleading statistics to provoke corrections',
                    'Sharing out-of-context clips designed to anger',
                    'Making inflammatory statements to generate engagement',
                ]),
                'indicators' => json_encode([
                    'High ratio of angry responses',
                    'Content designed to provoke rather than inform',
                    'Account benefits from controversy',
                    'Pattern of inflammatory posts',
                ]),
                'counter_indicators' => json_encode([
                    'Good faith engagement with criticism',
                    'Corrections when errors identified',
                    'Balanced discussion encouraged',
                ]),
            ],

            // ===============================================
            // TEMPORAL PATTERNS (Pattern Type: temporal)
            // ===============================================
            [
                'name' => 'Breaking News Exploitation',
                'slug' => 'breaking-news-exploitation',
                'description' => 'Rapid injection of disinformation during breaking news events when verification is difficult and attention is high.',
                'pattern_type' => 'temporal',
                'detection_rules' => json_encode([
                    'check_posting_speed' => true,
                    'check_source_verification_time' => true,
                    'check_claim_confidence_vs_evidence' => true,
                ]),
                'keywords' => json_encode([
                    'breaking', 'just in', 'developing', 'confirmed',
                    'exclusive', 'first to report', 'you heard it here',
                ]),
                'regex_patterns' => json_encode([
                    '/breaking:?\s+.+confirmed/i',
                    '/just (happened|confirmed|announced)/i',
                ]),
                'severity' => 4,
                'base_threat_weight' => 35,
                'examples' => json_encode([
                    'False casualty numbers posted minutes after an event',
                    'Fake responsibility claims during attacks',
                    'Manipulated images shared before verification possible',
                ]),
                'indicators' => json_encode([
                    'Claims made faster than verification allows',
                    'High confidence without sources',
                    'Contradicts official information',
                    'Benefits specific narrative',
                ],),
                'counter_indicators' => json_encode([
                    'Hedged language acknowledging uncertainty',
                    'Updates as information develops',
                    'Links to official sources',
                ]),
            ],
            [
                'name' => 'Anniversary Disinformation',
                'slug' => 'anniversary-disinformation',
                'description' => 'Coordinated campaigns timed to anniversaries of significant events to reshape historical narratives.',
                'pattern_type' => 'temporal',
                'detection_rules' => json_encode([
                    'check_date_significance' => true,
                    'check_historical_revisionism' => true,
                    'check_coordinated_timing' => true,
                ]),
                'keywords' => json_encode([
                    'anniversary', 'years ago', 'on this day', 'remember when',
                    'the truth about', 'what really happened', 'hidden history',
                ]),
                'regex_patterns' => json_encode([
                    '/\d+ years ago (today|on this day)/i',
                    '/the (real|true|hidden) (story|history) of/i',
                ]),
                'severity' => 3,
                'base_threat_weight' => 20,
                'examples' => json_encode([
                    'Holocaust denial content on January 27 (Holocaust Remembrance Day)',
                    'Historical revisionism on national holidays',
                    'Coordinated reframing of past conflicts on key dates',
                ]),
                'indicators' => json_encode([
                    'Content timed to specific dates',
                    'Historical claims contradicting established record',
                    'Coordinated posting by multiple accounts',
                    'Appeals to "hidden" or "suppressed" history',
                ]),
                'counter_indicators' => json_encode([
                    'Educational content from established sources',
                    'Primary source documentation',
                    'Academic consensus cited',
                ]),
            ],
            [
                'name' => 'Election Cycle Manipulation',
                'slug' => 'election-cycle-manipulation',
                'description' => 'Disinformation campaigns strategically timed to influence elections, often intensifying in the final weeks before voting.',
                'pattern_type' => 'temporal',
                'detection_rules' => json_encode([
                    'check_election_proximity' => true,
                    'check_candidate_targeting' => true,
                    'check_voting_process_claims' => true,
                ]),
                'keywords' => json_encode([
                    'vote', 'election', 'ballot', 'fraud', 'rigged',
                    'stolen', 'candidate', 'campaign', 'polling',
                    'voter suppression', 'election integrity',
                ]),
                'regex_patterns' => json_encode([
                    '/election (fraud|rigging|stealing)/i',
                    '/(vote|voting) (fraud|manipulation)/i',
                    '/ballot (harvesting|stuffing|dump)/i',
                ]),
                'severity' => 5,
                'base_threat_weight' => 50,
                'examples' => json_encode([
                    'False claims about voting procedures',
                    'Fabricated scandals about candidates',
                    'Misleading polling information',
                    'Voter suppression through wrong date/location info',
                ]),
                'indicators' => json_encode([
                    'Timing correlates with election calendar',
                    'Targets specific candidates or parties',
                    'Claims about voting process without evidence',
                    'Coordinated amplification near election day',
                ]),
                'counter_indicators' => json_encode([
                    'Official election authority sources',
                    'Nonpartisan voter information',
                    'Verifiable polling methodology',
                ]),
            ],
            [
                'name' => 'Burst Activity Pattern',
                'slug' => 'burst-activity-pattern',
                'description' => 'Sudden spikes in posting activity from accounts that are normally dormant, suggesting coordinated activation.',
                'pattern_type' => 'temporal',
                'detection_rules' => json_encode([
                    'dormancy_threshold_days' => 30,
                    'activation_spike_multiplier' => 10,
                    'check_topic_correlation' => true,
                ]),
                'keywords' => json_encode([]),
                'regex_patterns' => json_encode([]),
                'severity' => 4,
                'base_threat_weight' => 30,
                'examples' => json_encode([
                    'Account dormant for 6 months suddenly posts 50 times in one day',
                    'Multiple sleeper accounts activated simultaneously on same topic',
                    'Sudden activity coordinated with external events',
                ]),
                'indicators' => json_encode([
                    'Long periods of account inactivity',
                    'Sudden high-volume posting',
                    'Activity correlates with specific events',
                    'Similar activation pattern across accounts',
                ]),
                'counter_indicators' => json_encode([
                    'Account owner explains absence',
                    'Gradual return to activity',
                    'Diverse topics in resumed posting',
                ]),
            ],

            // ===============================================
            // NETWORK PATTERNS (Pattern Type: network)
            // ===============================================
            [
                'name' => 'Hub and Spoke Network',
                'slug' => 'hub-spoke-network',
                'description' => 'Central accounts that create content which is then amplified by a network of dependent accounts with little organic engagement.',
                'pattern_type' => 'network',
                'detection_rules' => json_encode([
                    'check_amplification_network' => true,
                    'check_hub_account_characteristics' => true,
                    'check_spoke_account_diversity' => false,
                ]),
                'keywords' => json_encode([]),
                'regex_patterns' => json_encode([]),
                'severity' => 4,
                'base_threat_weight' => 40,
                'examples' => json_encode([
                    'One account creates content, 50 accounts immediately repost',
                    'Spoke accounts have minimal original content',
                    'Network only engages with hub account',
                ]),
                'indicators' => json_encode([
                    'Clear hierarchical amplification structure',
                    'Spoke accounts lack original content',
                    'Rapid reposting of hub content',
                    'Limited engagement outside network',
                ]),
                'counter_indicators' => json_encode([
                    'Organic follower relationships',
                    'Diverse content sharing patterns',
                    'Engagement with multiple communities',
                ]),
            ],
            [
                'name' => 'Cross-Platform Coordination',
                'slug' => 'cross-platform-coordination',
                'description' => 'Coordinated disinformation campaigns that operate across multiple social media platforms simultaneously.',
                'pattern_type' => 'network',
                'detection_rules' => json_encode([
                    'check_cross_platform_timing' => true,
                    'check_content_mirroring' => true,
                    'check_account_linkages' => true,
                ]),
                'keywords' => json_encode([]),
                'regex_patterns' => json_encode([]),
                'severity' => 5,
                'base_threat_weight' => 45,
                'examples' => json_encode([
                    'Same disinformation posted on Twitter, Facebook, Telegram within minutes',
                    'YouTube video promoted by coordinated accounts across platforms',
                    'Hashtag campaigns synchronized across platforms',
                ]),
                'indicators' => json_encode([
                    'Identical content across platforms',
                    'Synchronized posting times',
                    'Similar account names/handles across platforms',
                    'Cross-platform promotion patterns',
                ]),
                'counter_indicators' => json_encode([
                    'Organic cross-posting by individuals',
                    'Time delays between platform posts',
                    'Platform-specific content adaptations',
                ]),
            ],
            [
                'name' => 'Bot Network Signatures',
                'slug' => 'bot-network-signatures',
                'description' => 'Automated accounts operating in coordinated networks with characteristic behavioral patterns.',
                'pattern_type' => 'network',
                'detection_rules' => json_encode([
                    'check_posting_regularity' => true,
                    'check_response_timing' => true,
                    'check_content_generation_patterns' => true,
                ]),
                'keywords' => json_encode([]),
                'regex_patterns' => json_encode([]),
                'severity' => 4,
                'base_threat_weight' => 35,
                'examples' => json_encode([
                    'Accounts posting exactly every 15 minutes',
                    'Immediate automated responses to specific keywords',
                    'Identical engagement patterns across network',
                ]),
                'indicators' => json_encode([
                    'Machine-like posting regularity',
                    'Instant responses to triggers',
                    'No sleep/activity patterns',
                    'Generic or templated responses',
                    'High volume with low engagement received',
                ]),
                'counter_indicators' => json_encode([
                    'Human activity patterns',
                    'Varied response times',
                    'Original and contextual replies',
                ]),
            ],

            // ===============================================
            // MEDIA MANIPULATION PATTERNS (Pattern Type: media)
            // ===============================================
            [
                'name' => 'Decontextualized Media',
                'slug' => 'decontextualized-media',
                'description' => 'Authentic media presented with false context - wrong date, location, or event attribution.',
                'pattern_type' => 'media',
                'detection_rules' => json_encode([
                    'check_reverse_image_search' => true,
                    'check_metadata_consistency' => true,
                    'check_context_claims' => true,
                ]),
                'keywords' => json_encode([
                    'just happened', 'breaking', 'exclusive footage', 'leaked',
                    'they don\'t want you to see', 'censored', 'banned video',
                ]),
                'regex_patterns' => json_encode([
                    '/this (just|recently) (happened|occurred) in/i',
                    '/(footage|video|photo) from .+ (today|yesterday)/i',
                ]),
                'severity' => 4,
                'base_threat_weight' => 40,
                'examples' => json_encode([
                    'Old explosion video presented as current event',
                    'Photo from one conflict used to represent another',
                    'Video game footage presented as real combat',
                ]),
                'indicators' => json_encode([
                    'Media appears in earlier reverse image searches',
                    'Metadata inconsistent with claimed date',
                    'Visual clues contradict claimed location/time',
                    'Original context differs from claim',
                ]),
                'counter_indicators' => json_encode([
                    'First appearance matches claimed date',
                    'Metadata supports claimed context',
                    'Multiple independent sources confirm',
                ]),
            ],
            [
                'name' => 'Cheap Fakes',
                'slug' => 'cheap-fakes',
                'description' => 'Simple manipulations using basic editing techniques - cropping, speed changes, selective editing, or misleading captions.',
                'pattern_type' => 'media',
                'detection_rules' => json_encode([
                    'check_video_speed' => true,
                    'check_selective_editing' => true,
                    'check_caption_accuracy' => true,
                ]),
                'keywords' => json_encode([
                    'watch carefully', 'look at this', 'proof', 'evidence',
                    'caught on camera', 'exposed',
                ]),
                'regex_patterns' => json_encode([]),
                'severity' => 3,
                'base_threat_weight' => 30,
                'examples' => json_encode([
                    'Video slowed down to make someone appear intoxicated',
                    'Cropped image removing important context',
                    'Selectively edited quote changing meaning',
                    'Misleading subtitles on foreign language video',
                ]),
                'indicators' => json_encode([
                    'Audio/video sync issues',
                    'Abrupt cuts or jumps',
                    'Visible cropping artifacts',
                    'Speed inconsistencies',
                    'Caption doesn\'t match audio',
                ]),
                'counter_indicators' => json_encode([
                    'Full unedited version available',
                    'Context provided with media',
                    'Source video confirms editing',
                ]),
            ],
            [
                'name' => 'Deepfakes and AI-Generated Media',
                'slug' => 'deepfakes-ai-media',
                'description' => 'AI-generated or manipulated media including synthetic faces, voice cloning, and video manipulation.',
                'pattern_type' => 'media',
                'detection_rules' => json_encode([
                    'check_facial_consistency' => true,
                    'check_audio_artifacts' => true,
                    'check_ai_detection_tools' => true,
                ]),
                'keywords' => json_encode([
                    'leaked video', 'secret recording', 'confession',
                    'caught saying', 'admits to',
                ]),
                'regex_patterns' => json_encode([]),
                'severity' => 5,
                'base_threat_weight' => 50,
                'examples' => json_encode([
                    'Synthetic video of politician making false statements',
                    'Cloned voice used in fake audio recording',
                    'AI-generated face in fabricated scenario',
                ]),
                'indicators' => json_encode([
                    'Unnatural facial movements or expressions',
                    'Inconsistent lighting or shadows on face',
                    'Audio doesn\'t match lip movements',
                    'Blurring around face edges',
                    'Unusual blinking patterns',
                    'Detection tools flag as synthetic',
                ]),
                'counter_indicators' => json_encode([
                    'Multiple camera angles available',
                    'Verified by original source',
                    'Passes deepfake detection tools',
                    'Consistent with known statements/behavior',
                ]),
            ],
            [
                'name' => 'Staged or Fabricated Events',
                'slug' => 'staged-fabricated-events',
                'description' => 'Completely fabricated scenes or crisis actors used to create false evidence of events that never occurred.',
                'pattern_type' => 'media',
                'detection_rules' => json_encode([
                    'check_location_verification' => true,
                    'check_participant_identification' => true,
                    'check_independent_corroboration' => true,
                ]),
                'keywords' => json_encode([
                    'crisis actors', 'staged', 'fake', 'false flag',
                    'hoax', 'setup', 'manufactured',
                ]),
                'regex_patterns' => json_encode([]),
                'severity' => 5,
                'base_threat_weight' => 50,
                'examples' => json_encode([
                    'Staged rescue scenes for propaganda',
                    'Fabricated atrocity evidence',
                    'Actors portraying victims or witnesses',
                ]),
                'indicators' => json_encode([
                    'Scene doesn\'t match claimed location',
                    'Same individuals appear in multiple "incidents"',
                    'No independent corroboration',
                    'Inconsistencies in victim/witness accounts',
                    'Professional production quality unexpected for context',
                ]),
                'counter_indicators' => json_encode([
                    'Multiple independent sources document event',
                    'Location and timing verified',
                    'Consistent witness accounts',
                    'Physical evidence supports claims',
                ]),
            ],
            [
                'name' => 'Misleading Data Visualization',
                'slug' => 'misleading-data-viz',
                'description' => 'Charts, graphs, and infographics that misrepresent data through manipulated scales, cherry-picked data, or misleading comparisons.',
                'pattern_type' => 'media',
                'detection_rules' => json_encode([
                    'check_axis_scales' => true,
                    'check_data_source' => true,
                    'check_comparison_validity' => true,
                ]),
                'keywords' => json_encode([
                    'statistics show', 'data proves', 'the numbers',
                    'research confirms', 'studies show',
                ]),
                'regex_patterns' => json_encode([]),
                'severity' => 3,
                'base_threat_weight' => 25,
                'examples' => json_encode([
                    'Y-axis not starting at zero to exaggerate changes',
                    'Cherry-picked time periods',
                    'Comparing incompatible data sets',
                    'Misleading map projections or color scales',
                ]),
                'indicators' => json_encode([
                    'Truncated or manipulated axes',
                    'Missing data source citations',
                    'Selective time period without justification',
                    'Visual emphasis contradicts actual data',
                    'Apples-to-oranges comparisons',
                ]),
                'counter_indicators' => json_encode([
                    'Clear data sources cited',
                    'Appropriate scales and baselines',
                    'Methodology explained',
                    'Full dataset available',
                ]),
            ],
        ];

        foreach ($patterns as $pattern) {
            DB::table('disinformation_patterns')->insert([
                ...$pattern,
                'is_active' => true,
                'match_count' => 0,
                'version' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
