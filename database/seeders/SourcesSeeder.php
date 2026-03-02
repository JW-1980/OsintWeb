<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Populates the sources table with major news outlets, wire services,
     * conflict-specific reporters, official government sources, and OSINT organizations.
     */
    public function run(): void
    {
        $sources = [
            // -------------------------------------------------------------------------
            // Major Wire Services (Highest Reliability)
            // -------------------------------------------------------------------------
            [
                'name' => 'Reuters',
                'url' => 'https://www.reuters.com',
                'description' => 'International news organization providing comprehensive, independent coverage worldwide.',
                'source_type' => 'news_outlet',
                'reliability_score' => 92,
                'verification_status' => 'trusted',
                'country_code' => 'GB',
                'language' => 'en',
            ],
            [
                'name' => 'Associated Press (AP)',
                'url' => 'https://apnews.com',
                'description' => 'Independent, not-for-profit news cooperative providing factual coverage.',
                'source_type' => 'news_outlet',
                'reliability_score' => 91,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'Agence France-Presse (AFP)',
                'url' => 'https://www.afp.com',
                'description' => 'Global news agency delivering fast, verified coverage from around the world.',
                'source_type' => 'news_outlet',
                'reliability_score' => 90,
                'verification_status' => 'trusted',
                'country_code' => 'FR',
                'language' => 'fr',
            ],

            // -------------------------------------------------------------------------
            // Major International Broadcasters
            // -------------------------------------------------------------------------
            [
                'name' => 'BBC News',
                'url' => 'https://www.bbc.com/news',
                'description' => 'British Broadcasting Corporation news service with global coverage.',
                'source_type' => 'news_outlet',
                'reliability_score' => 88,
                'verification_status' => 'trusted',
                'country_code' => 'GB',
                'language' => 'en',
            ],
            [
                'name' => 'Al Jazeera English',
                'url' => 'https://www.aljazeera.com',
                'description' => 'Qatar-based international news network with Middle East focus.',
                'source_type' => 'news_outlet',
                'reliability_score' => 78,
                'verification_status' => 'verified',
                'country_code' => 'QA',
                'language' => 'en',
                'known_biases' => ['Qatar government funding', 'Middle East regional perspective'],
            ],
            [
                'name' => 'Deutsche Welle (DW)',
                'url' => 'https://www.dw.com',
                'description' => 'German international public broadcaster providing world news.',
                'source_type' => 'news_outlet',
                'reliability_score' => 85,
                'verification_status' => 'trusted',
                'country_code' => 'DE',
                'language' => 'en',
            ],
            [
                'name' => 'France 24',
                'url' => 'https://www.france24.com',
                'description' => 'French international news channel covering global events.',
                'source_type' => 'news_outlet',
                'reliability_score' => 83,
                'verification_status' => 'verified',
                'country_code' => 'FR',
                'language' => 'en',
            ],
            [
                'name' => 'CNN International',
                'url' => 'https://edition.cnn.com',
                'description' => 'American news-based cable television channel with international reach.',
                'source_type' => 'news_outlet',
                'reliability_score' => 75,
                'verification_status' => 'verified',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'Sky News',
                'url' => 'https://news.sky.com',
                'description' => 'British free-to-air television news channel.',
                'source_type' => 'news_outlet',
                'reliability_score' => 80,
                'verification_status' => 'verified',
                'country_code' => 'GB',
                'language' => 'en',
            ],
            [
                'name' => 'NHK World Japan',
                'url' => 'https://www3.nhk.or.jp/nhkworld/',
                'description' => 'Japanese public broadcaster international service.',
                'source_type' => 'news_outlet',
                'reliability_score' => 84,
                'verification_status' => 'trusted',
                'country_code' => 'JP',
                'language' => 'en',
            ],

            // -------------------------------------------------------------------------
            // Quality Print Media
            // -------------------------------------------------------------------------
            [
                'name' => 'The New York Times',
                'url' => 'https://www.nytimes.com',
                'description' => 'American daily newspaper with in-depth investigative journalism.',
                'source_type' => 'news_outlet',
                'reliability_score' => 85,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'The Washington Post',
                'url' => 'https://www.washingtonpost.com',
                'description' => 'American daily newspaper known for political coverage.',
                'source_type' => 'news_outlet',
                'reliability_score' => 84,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'The Guardian',
                'url' => 'https://www.theguardian.com',
                'description' => 'British daily newspaper with global investigative journalism.',
                'source_type' => 'news_outlet',
                'reliability_score' => 82,
                'verification_status' => 'verified',
                'country_code' => 'GB',
                'language' => 'en',
            ],
            [
                'name' => 'Der Spiegel',
                'url' => 'https://www.spiegel.de',
                'description' => 'German weekly news magazine known for investigative journalism.',
                'source_type' => 'news_outlet',
                'reliability_score' => 83,
                'verification_status' => 'verified',
                'country_code' => 'DE',
                'language' => 'de',
            ],
            [
                'name' => 'Le Monde',
                'url' => 'https://www.lemonde.fr',
                'description' => 'French daily newspaper of record.',
                'source_type' => 'news_outlet',
                'reliability_score' => 82,
                'verification_status' => 'verified',
                'country_code' => 'FR',
                'language' => 'fr',
            ],
            [
                'name' => 'Financial Times',
                'url' => 'https://www.ft.com',
                'description' => 'British international daily newspaper focused on business news.',
                'source_type' => 'news_outlet',
                'reliability_score' => 86,
                'verification_status' => 'trusted',
                'country_code' => 'GB',
                'language' => 'en',
            ],
            [
                'name' => 'The Economist',
                'url' => 'https://www.economist.com',
                'description' => 'British weekly international news and business publication.',
                'source_type' => 'news_outlet',
                'reliability_score' => 85,
                'verification_status' => 'trusted',
                'country_code' => 'GB',
                'language' => 'en',
            ],

            // -------------------------------------------------------------------------
            // Ukraine/Russia Conflict Specific
            // -------------------------------------------------------------------------
            [
                'name' => 'Kyiv Independent',
                'url' => 'https://kyivindependent.com',
                'description' => 'English-language Ukrainian news outlet covering Ukraine and the region.',
                'source_type' => 'news_outlet',
                'reliability_score' => 75,
                'verification_status' => 'verified',
                'country_code' => 'UA',
                'language' => 'en',
                'known_biases' => ['Pro-Ukrainian perspective'],
            ],
            [
                'name' => 'Ukrainska Pravda',
                'url' => 'https://www.pravda.com.ua',
                'description' => 'Major Ukrainian online newspaper providing news and analysis.',
                'source_type' => 'news_outlet',
                'reliability_score' => 72,
                'verification_status' => 'verified',
                'country_code' => 'UA',
                'language' => 'uk',
                'known_biases' => ['Pro-Ukrainian perspective'],
            ],
            [
                'name' => 'Meduza',
                'url' => 'https://meduza.io',
                'description' => 'Russian independent news website based in Latvia, critical of Kremlin.',
                'source_type' => 'news_outlet',
                'reliability_score' => 78,
                'verification_status' => 'verified',
                'country_code' => 'LV',
                'language' => 'ru',
                'known_biases' => ['Opposition to Russian government'],
            ],
            [
                'name' => 'TASS',
                'url' => 'https://tass.com',
                'description' => 'Russian state news agency.',
                'source_type' => 'official',
                'reliability_score' => 35,
                'verification_status' => 'verified',
                'country_code' => 'RU',
                'language' => 'ru',
                'known_biases' => ['Russian state controlled', 'Pro-Kremlin'],
            ],
            [
                'name' => 'RIA Novosti',
                'url' => 'https://ria.ru',
                'description' => 'Russian state-owned news agency.',
                'source_type' => 'official',
                'reliability_score' => 32,
                'verification_status' => 'verified',
                'country_code' => 'RU',
                'language' => 'ru',
                'known_biases' => ['Russian state controlled', 'Pro-Kremlin'],
            ],
            [
                'name' => 'Ukrinform',
                'url' => 'https://www.ukrinform.net',
                'description' => 'Ukrainian national news agency.',
                'source_type' => 'official',
                'reliability_score' => 65,
                'verification_status' => 'verified',
                'country_code' => 'UA',
                'language' => 'uk',
                'known_biases' => ['Ukrainian government affiliated'],
            ],

            // -------------------------------------------------------------------------
            // Middle East Specific
            // -------------------------------------------------------------------------
            [
                'name' => 'Haaretz',
                'url' => 'https://www.haaretz.com',
                'description' => 'Israeli daily newspaper, oldest in Israel.',
                'source_type' => 'news_outlet',
                'reliability_score' => 78,
                'verification_status' => 'verified',
                'country_code' => 'IL',
                'language' => 'he',
            ],
            [
                'name' => 'The Times of Israel',
                'url' => 'https://www.timesofisrael.com',
                'description' => 'Online newspaper covering Israel and Middle East.',
                'source_type' => 'news_outlet',
                'reliability_score' => 75,
                'verification_status' => 'verified',
                'country_code' => 'IL',
                'language' => 'en',
            ],
            [
                'name' => 'Middle East Eye',
                'url' => 'https://www.middleeasteye.net',
                'description' => 'London-based online news publication covering Middle East.',
                'source_type' => 'news_outlet',
                'reliability_score' => 68,
                'verification_status' => 'verified',
                'country_code' => 'GB',
                'language' => 'en',
            ],
            [
                'name' => 'Al Arabiya',
                'url' => 'https://english.alarabiya.net',
                'description' => 'Saudi-owned pan-Arab television news channel.',
                'source_type' => 'news_outlet',
                'reliability_score' => 65,
                'verification_status' => 'verified',
                'country_code' => 'SA',
                'language' => 'ar',
                'known_biases' => ['Saudi government perspective'],
            ],
            [
                'name' => 'Syrian Observatory for Human Rights',
                'url' => 'https://www.syriahr.com',
                'description' => 'UK-based monitoring group tracking Syrian conflict casualties.',
                'source_type' => 'eyewitness',
                'reliability_score' => 62,
                'verification_status' => 'verified',
                'country_code' => 'GB',
                'language' => 'en',
                'known_biases' => ['Opposition-leaning in Syrian conflict'],
            ],

            // -------------------------------------------------------------------------
            // OSINT Organizations
            // -------------------------------------------------------------------------
            [
                'name' => 'Bellingcat',
                'url' => 'https://www.bellingcat.com',
                'description' => 'Netherlands-based investigative journalism group using open-source intelligence.',
                'source_type' => 'news_outlet',
                'reliability_score' => 88,
                'verification_status' => 'trusted',
                'country_code' => 'NL',
                'language' => 'en',
            ],
            [
                'name' => 'Oryx',
                'url' => 'https://www.oryxspioenkop.com',
                'description' => 'Military analysis blog tracking visually confirmed equipment losses.',
                'source_type' => 'document',
                'reliability_score' => 85,
                'verification_status' => 'trusted',
                'country_code' => 'NL',
                'language' => 'en',
            ],
            [
                'name' => 'Institute for the Study of War',
                'url' => 'https://www.understandingwar.org',
                'description' => 'Think tank providing military analysis and conflict assessments.',
                'source_type' => 'document',
                'reliability_score' => 82,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'Conflict Intelligence Team',
                'url' => 'https://citeam.org',
                'description' => 'OSINT group investigating conflicts using open sources.',
                'source_type' => 'news_outlet',
                'reliability_score' => 80,
                'verification_status' => 'verified',
                'country_code' => 'RU',
                'language' => 'ru',
            ],
            [
                'name' => 'GeoConfirmed',
                'url' => 'https://geoconfirmed.org',
                'description' => 'Volunteer OSINT organization geolocating conflict footage.',
                'source_type' => 'eyewitness',
                'reliability_score' => 78,
                'verification_status' => 'verified',
                'country_code' => 'NL',
                'language' => 'en',
            ],

            // -------------------------------------------------------------------------
            // Satellite Imagery Sources
            // -------------------------------------------------------------------------
            [
                'name' => 'Maxar Technologies',
                'url' => 'https://www.maxar.com',
                'description' => 'Commercial satellite imagery provider with high-resolution capabilities.',
                'source_type' => 'satellite',
                'reliability_score' => 92,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'Planet Labs',
                'url' => 'https://www.planet.com',
                'description' => 'Earth imaging company providing daily satellite coverage.',
                'source_type' => 'satellite',
                'reliability_score' => 90,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'Copernicus Sentinel',
                'url' => 'https://sentinel.esa.int',
                'description' => 'European Space Agency satellite program providing free imagery.',
                'source_type' => 'satellite',
                'reliability_score' => 88,
                'verification_status' => 'trusted',
                'country_code' => 'NL',
                'language' => 'en',
            ],
            [
                'name' => 'NASA Worldview',
                'url' => 'https://worldview.earthdata.nasa.gov',
                'description' => 'NASA interactive tool for browsing satellite imagery.',
                'source_type' => 'satellite',
                'reliability_score' => 90,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],

            // -------------------------------------------------------------------------
            // Official Government Sources
            // -------------------------------------------------------------------------
            [
                'name' => 'US Department of Defense',
                'url' => 'https://www.defense.gov',
                'description' => 'Official US military news and information.',
                'source_type' => 'official',
                'reliability_score' => 70,
                'verification_status' => 'verified',
                'country_code' => 'US',
                'language' => 'en',
                'known_biases' => ['US government perspective'],
            ],
            [
                'name' => 'UK Ministry of Defence',
                'url' => 'https://www.gov.uk/government/organisations/ministry-of-defence',
                'description' => 'Official British military news and intelligence updates.',
                'source_type' => 'official',
                'reliability_score' => 75,
                'verification_status' => 'verified',
                'country_code' => 'GB',
                'language' => 'en',
                'known_biases' => ['UK government perspective'],
            ],
            [
                'name' => 'NATO',
                'url' => 'https://www.nato.int',
                'description' => 'North Atlantic Treaty Organization official communications.',
                'source_type' => 'official',
                'reliability_score' => 72,
                'verification_status' => 'verified',
                'country_code' => 'BE',
                'language' => 'en',
                'known_biases' => ['Western alliance perspective'],
            ],
            [
                'name' => 'European External Action Service',
                'url' => 'https://www.eeas.europa.eu',
                'description' => 'EU diplomatic service and foreign policy communications.',
                'source_type' => 'official',
                'reliability_score' => 73,
                'verification_status' => 'verified',
                'country_code' => 'BE',
                'language' => 'en',
            ],
            [
                'name' => 'United Nations',
                'url' => 'https://news.un.org',
                'description' => 'United Nations official news service.',
                'source_type' => 'official',
                'reliability_score' => 80,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'OSCE Special Monitoring Mission',
                'url' => 'https://www.osce.org/special-monitoring-mission-to-ukraine',
                'description' => 'OSCE monitoring reports (historical, mission ended).',
                'source_type' => 'official',
                'reliability_score' => 82,
                'verification_status' => 'trusted',
                'country_code' => 'AT',
                'language' => 'en',
            ],

            // -------------------------------------------------------------------------
            // Human Rights Organizations
            // -------------------------------------------------------------------------
            [
                'name' => 'Human Rights Watch',
                'url' => 'https://www.hrw.org',
                'description' => 'International NGO documenting human rights abuses.',
                'source_type' => 'document',
                'reliability_score' => 85,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'Amnesty International',
                'url' => 'https://www.amnesty.org',
                'description' => 'Global human rights organization.',
                'source_type' => 'document',
                'reliability_score' => 84,
                'verification_status' => 'trusted',
                'country_code' => 'GB',
                'language' => 'en',
            ],
            [
                'name' => 'International Criminal Court',
                'url' => 'https://www.icc-cpi.int',
                'description' => 'International court prosecuting war crimes and crimes against humanity.',
                'source_type' => 'official',
                'reliability_score' => 88,
                'verification_status' => 'trusted',
                'country_code' => 'NL',
                'language' => 'en',
            ],
            [
                'name' => 'ICRC (International Committee of the Red Cross)',
                'url' => 'https://www.icrc.org',
                'description' => 'Humanitarian organization providing protection in armed conflicts.',
                'source_type' => 'official',
                'reliability_score' => 87,
                'verification_status' => 'trusted',
                'country_code' => 'CH',
                'language' => 'en',
            ],

            // -------------------------------------------------------------------------
            // Social Media / Aggregators
            // -------------------------------------------------------------------------
            [
                'name' => 'Twitter/X',
                'url' => 'https://twitter.com',
                'description' => 'Social media platform - requires individual account verification.',
                'source_type' => 'social_media',
                'reliability_score' => 30,
                'verification_status' => 'unverified',
                'country_code' => 'US',
                'language' => 'en',
                'known_biases' => ['Varies by account', 'Unverified content'],
            ],
            [
                'name' => 'Telegram',
                'url' => 'https://telegram.org',
                'description' => 'Messaging platform with public channels - requires verification.',
                'source_type' => 'social_media',
                'reliability_score' => 25,
                'verification_status' => 'unverified',
                'country_code' => 'AE',
                'language' => 'en',
                'known_biases' => ['Varies by channel', 'Unverified content'],
            ],
            [
                'name' => 'LiveUA Map',
                'url' => 'https://liveuamap.com',
                'description' => 'Real-time conflict map aggregating reports from various sources.',
                'source_type' => 'news_outlet',
                'reliability_score' => 65,
                'verification_status' => 'verified',
                'country_code' => 'UA',
                'language' => 'en',
            ],

            // -------------------------------------------------------------------------
            // Think Tanks and Research
            // -------------------------------------------------------------------------
            [
                'name' => 'RAND Corporation',
                'url' => 'https://www.rand.org',
                'description' => 'American nonprofit research organization.',
                'source_type' => 'document',
                'reliability_score' => 83,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'International Institute for Strategic Studies',
                'url' => 'https://www.iiss.org',
                'description' => 'British think tank providing defense and security analysis.',
                'source_type' => 'document',
                'reliability_score' => 84,
                'verification_status' => 'trusted',
                'country_code' => 'GB',
                'language' => 'en',
            ],
            [
                'name' => 'Stockholm International Peace Research Institute',
                'url' => 'https://www.sipri.org',
                'description' => 'Swedish institute researching conflict, armaments, and disarmament.',
                'source_type' => 'document',
                'reliability_score' => 86,
                'verification_status' => 'trusted',
                'country_code' => 'SE',
                'language' => 'en',
            ],
            [
                'name' => 'Carnegie Endowment for International Peace',
                'url' => 'https://carnegieendowment.org',
                'description' => 'Global think tank on international affairs.',
                'source_type' => 'document',
                'reliability_score' => 82,
                'verification_status' => 'trusted',
                'country_code' => 'US',
                'language' => 'en',
            ],
            [
                'name' => 'Center for Strategic and International Studies',
                'url' => 'https://www.csis.org',
                'description' => 'American think tank providing policy analysis.',
                'source_type' => 'document',
                'reliability_score' => 81,
                'verification_status' => 'verified',
                'country_code' => 'US',
                'language' => 'en',
            ],

            // -------------------------------------------------------------------------
            // Regional/Local Sources
            // -------------------------------------------------------------------------
            [
                'name' => 'Radio Free Europe/Radio Liberty',
                'url' => 'https://www.rferl.org',
                'description' => 'US-funded broadcaster covering Eastern Europe and Central Asia.',
                'source_type' => 'news_outlet',
                'reliability_score' => 72,
                'verification_status' => 'verified',
                'country_code' => 'CZ',
                'language' => 'en',
                'known_biases' => ['US government funded', 'Pro-Western'],
            ],
            [
                'name' => 'Novaya Gazeta',
                'url' => 'https://novayagazeta.eu',
                'description' => 'Russian independent newspaper (now in exile).',
                'source_type' => 'news_outlet',
                'reliability_score' => 76,
                'verification_status' => 'verified',
                'country_code' => 'LV',
                'language' => 'ru',
                'known_biases' => ['Opposition to Russian government'],
            ],
            [
                'name' => 'Bild',
                'url' => 'https://www.bild.de',
                'description' => 'German tabloid newspaper with significant conflict coverage.',
                'source_type' => 'news_outlet',
                'reliability_score' => 58,
                'verification_status' => 'verified',
                'country_code' => 'DE',
                'language' => 'de',
                'known_biases' => ['Tabloid sensationalism'],
            ],
            [
                'name' => 'Polish Press Agency (PAP)',
                'url' => 'https://www.pap.pl',
                'description' => 'Polish national news agency.',
                'source_type' => 'news_outlet',
                'reliability_score' => 75,
                'verification_status' => 'verified',
                'country_code' => 'PL',
                'language' => 'pl',
            ],
            [
                'name' => 'Anadolu Agency',
                'url' => 'https://www.aa.com.tr',
                'description' => 'Turkish state news agency.',
                'source_type' => 'official',
                'reliability_score' => 55,
                'verification_status' => 'verified',
                'country_code' => 'TR',
                'language' => 'tr',
                'known_biases' => ['Turkish government perspective'],
            ],
        ];

        // Get country ID mapping
        $countryMap = DB::table('countries')
            ->pluck('id', 'iso_code')
            ->toArray();

        $now = now();
        $seededCount = 0;

        foreach ($sources as $sourceData) {
            $countryId = null;
            if (!empty($sourceData['country_code']) && isset($countryMap[$sourceData['country_code']])) {
                $countryId = $countryMap[$sourceData['country_code']];
            }

            // Check if source already exists
            $exists = DB::table('sources')->where('name', $sourceData['name'])->exists();
            if ($exists) {
                continue;
            }

            DB::table('sources')->insertOrIgnore([
                'uuid' => (string) Str::uuid(),
                'name' => $sourceData['name'],
                'url' => $sourceData['url'] ?? null,
                'description' => $sourceData['description'] ?? null,
                'source_type' => $sourceData['source_type'],
                'reliability_score' => $sourceData['reliability_score'],
                'verification_status' => $sourceData['verification_status'],
                'country_id' => $countryId,
                'language' => $sourceData['language'] ?? 'en',
                'known_biases' => !empty($sourceData['known_biases']) ? json_encode($sourceData['known_biases']) : null,
                'total_reports' => 0,
                'accurate_reports' => 0,
                'disputed_reports' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $seededCount++;
        }

        $this->command->info("Sources seeded successfully: {$seededCount} sources added.");
    }
}
