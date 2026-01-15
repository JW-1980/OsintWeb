<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Populates the countries table with countries used by other seeders
     * and common countries for military/conflict analysis.
     */
    public function run(): void
    {
        $countries = [
            // Major military powers and conflict participants
            ['name' => 'Russia', 'iso_code' => 'RU', 'iso_code3' => 'RUS'],
            ['name' => 'Ukraine', 'iso_code' => 'UA', 'iso_code3' => 'UKR'],
            ['name' => 'United States', 'iso_code' => 'US', 'iso_code3' => 'USA'],
            ['name' => 'United Kingdom', 'iso_code' => 'GB', 'iso_code3' => 'GBR'],
            ['name' => 'Germany', 'iso_code' => 'DE', 'iso_code3' => 'DEU'],
            ['name' => 'France', 'iso_code' => 'FR', 'iso_code3' => 'FRA'],
            ['name' => 'China', 'iso_code' => 'CN', 'iso_code3' => 'CHN'],
            ['name' => 'Israel', 'iso_code' => 'IL', 'iso_code3' => 'ISR'],
            ['name' => 'Turkey', 'iso_code' => 'TR', 'iso_code3' => 'TUR'],
            ['name' => 'India', 'iso_code' => 'IN', 'iso_code3' => 'IND'],
            ['name' => 'Japan', 'iso_code' => 'JP', 'iso_code3' => 'JPN'],
            ['name' => 'South Korea', 'iso_code' => 'KR', 'iso_code3' => 'KOR'],
            ['name' => 'Sweden', 'iso_code' => 'SE', 'iso_code3' => 'SWE'],

            // NATO members
            ['name' => 'Poland', 'iso_code' => 'PL', 'iso_code3' => 'POL'],
            ['name' => 'Italy', 'iso_code' => 'IT', 'iso_code3' => 'ITA'],
            ['name' => 'Spain', 'iso_code' => 'ES', 'iso_code3' => 'ESP'],
            ['name' => 'Canada', 'iso_code' => 'CA', 'iso_code3' => 'CAN'],
            ['name' => 'Netherlands', 'iso_code' => 'NL', 'iso_code3' => 'NLD'],
            ['name' => 'Belgium', 'iso_code' => 'BE', 'iso_code3' => 'BEL'],
            ['name' => 'Norway', 'iso_code' => 'NO', 'iso_code3' => 'NOR'],
            ['name' => 'Denmark', 'iso_code' => 'DK', 'iso_code3' => 'DNK'],
            ['name' => 'Finland', 'iso_code' => 'FI', 'iso_code3' => 'FIN'],
            ['name' => 'Greece', 'iso_code' => 'GR', 'iso_code3' => 'GRC'],
            ['name' => 'Portugal', 'iso_code' => 'PT', 'iso_code3' => 'PRT'],
            ['name' => 'Czech Republic', 'iso_code' => 'CZ', 'iso_code3' => 'CZE'],
            ['name' => 'Romania', 'iso_code' => 'RO', 'iso_code3' => 'ROU'],
            ['name' => 'Bulgaria', 'iso_code' => 'BG', 'iso_code3' => 'BGR'],
            ['name' => 'Hungary', 'iso_code' => 'HU', 'iso_code3' => 'HUN'],
            ['name' => 'Slovakia', 'iso_code' => 'SK', 'iso_code3' => 'SVK'],
            ['name' => 'Croatia', 'iso_code' => 'HR', 'iso_code3' => 'HRV'],
            ['name' => 'Slovenia', 'iso_code' => 'SI', 'iso_code3' => 'SVN'],
            ['name' => 'Estonia', 'iso_code' => 'EE', 'iso_code3' => 'EST'],
            ['name' => 'Latvia', 'iso_code' => 'LV', 'iso_code3' => 'LVA'],
            ['name' => 'Lithuania', 'iso_code' => 'LT', 'iso_code3' => 'LTU'],

            // Middle East
            ['name' => 'Iran', 'iso_code' => 'IR', 'iso_code3' => 'IRN'],
            ['name' => 'Saudi Arabia', 'iso_code' => 'SA', 'iso_code3' => 'SAU'],
            ['name' => 'United Arab Emirates', 'iso_code' => 'AE', 'iso_code3' => 'ARE'],
            ['name' => 'Syria', 'iso_code' => 'SY', 'iso_code3' => 'SYR'],
            ['name' => 'Iraq', 'iso_code' => 'IQ', 'iso_code3' => 'IRQ'],
            ['name' => 'Lebanon', 'iso_code' => 'LB', 'iso_code3' => 'LBN'],
            ['name' => 'Jordan', 'iso_code' => 'JO', 'iso_code3' => 'JOR'],
            ['name' => 'Egypt', 'iso_code' => 'EG', 'iso_code3' => 'EGY'],
            ['name' => 'Qatar', 'iso_code' => 'QA', 'iso_code3' => 'QAT'],
            ['name' => 'Kuwait', 'iso_code' => 'KW', 'iso_code3' => 'KWT'],
            ['name' => 'Bahrain', 'iso_code' => 'BH', 'iso_code3' => 'BHR'],
            ['name' => 'Oman', 'iso_code' => 'OM', 'iso_code3' => 'OMN'],
            ['name' => 'Yemen', 'iso_code' => 'YE', 'iso_code3' => 'YEM'],
            ['name' => 'Palestine', 'iso_code' => 'PS', 'iso_code3' => 'PSE'],

            // Africa
            ['name' => 'Libya', 'iso_code' => 'LY', 'iso_code3' => 'LBY'],
            ['name' => 'Sudan', 'iso_code' => 'SD', 'iso_code3' => 'SDN'],
            ['name' => 'Ethiopia', 'iso_code' => 'ET', 'iso_code3' => 'ETH'],
            ['name' => 'Somalia', 'iso_code' => 'SO', 'iso_code3' => 'SOM'],
            ['name' => 'Nigeria', 'iso_code' => 'NG', 'iso_code3' => 'NGA'],
            ['name' => 'Mali', 'iso_code' => 'ML', 'iso_code3' => 'MLI'],
            ['name' => 'Niger', 'iso_code' => 'NE', 'iso_code3' => 'NER'],
            ['name' => 'Central African Republic', 'iso_code' => 'CF', 'iso_code3' => 'CAF'],
            ['name' => 'South Africa', 'iso_code' => 'ZA', 'iso_code3' => 'ZAF'],
            ['name' => 'Morocco', 'iso_code' => 'MA', 'iso_code3' => 'MAR'],
            ['name' => 'Algeria', 'iso_code' => 'DZ', 'iso_code3' => 'DZA'],
            ['name' => 'Tunisia', 'iso_code' => 'TN', 'iso_code3' => 'TUN'],
            ['name' => 'Eritrea', 'iso_code' => 'ER', 'iso_code3' => 'ERI'],

            // Asia
            ['name' => 'Pakistan', 'iso_code' => 'PK', 'iso_code3' => 'PAK'],
            ['name' => 'Afghanistan', 'iso_code' => 'AF', 'iso_code3' => 'AFG'],
            ['name' => 'North Korea', 'iso_code' => 'KP', 'iso_code3' => 'PRK'],
            ['name' => 'Taiwan', 'iso_code' => 'TW', 'iso_code3' => 'TWN'],
            ['name' => 'Vietnam', 'iso_code' => 'VN', 'iso_code3' => 'VNM'],
            ['name' => 'Thailand', 'iso_code' => 'TH', 'iso_code3' => 'THA'],
            ['name' => 'Myanmar', 'iso_code' => 'MM', 'iso_code3' => 'MMR'],
            ['name' => 'Indonesia', 'iso_code' => 'ID', 'iso_code3' => 'IDN'],
            ['name' => 'Philippines', 'iso_code' => 'PH', 'iso_code3' => 'PHL'],
            ['name' => 'Malaysia', 'iso_code' => 'MY', 'iso_code3' => 'MYS'],
            ['name' => 'Singapore', 'iso_code' => 'SG', 'iso_code3' => 'SGP'],

            // Other CIS / Former Soviet
            ['name' => 'Belarus', 'iso_code' => 'BY', 'iso_code3' => 'BLR'],
            ['name' => 'Kazakhstan', 'iso_code' => 'KZ', 'iso_code3' => 'KAZ'],
            ['name' => 'Georgia', 'iso_code' => 'GE', 'iso_code3' => 'GEO'],
            ['name' => 'Armenia', 'iso_code' => 'AM', 'iso_code3' => 'ARM'],
            ['name' => 'Azerbaijan', 'iso_code' => 'AZ', 'iso_code3' => 'AZE'],
            ['name' => 'Moldova', 'iso_code' => 'MD', 'iso_code3' => 'MDA'],
            ['name' => 'Uzbekistan', 'iso_code' => 'UZ', 'iso_code3' => 'UZB'],

            // Americas
            ['name' => 'Mexico', 'iso_code' => 'MX', 'iso_code3' => 'MEX'],
            ['name' => 'Brazil', 'iso_code' => 'BR', 'iso_code3' => 'BRA'],
            ['name' => 'Argentina', 'iso_code' => 'AR', 'iso_code3' => 'ARG'],
            ['name' => 'Colombia', 'iso_code' => 'CO', 'iso_code3' => 'COL'],
            ['name' => 'Venezuela', 'iso_code' => 'VE', 'iso_code3' => 'VEN'],
            ['name' => 'Chile', 'iso_code' => 'CL', 'iso_code3' => 'CHL'],

            // Oceania
            ['name' => 'Australia', 'iso_code' => 'AU', 'iso_code3' => 'AUS'],
            ['name' => 'New Zealand', 'iso_code' => 'NZ', 'iso_code3' => 'NZL'],

            // Other European
            ['name' => 'Austria', 'iso_code' => 'AT', 'iso_code3' => 'AUT'],
            ['name' => 'Switzerland', 'iso_code' => 'CH', 'iso_code3' => 'CHE'],
            ['name' => 'Ireland', 'iso_code' => 'IE', 'iso_code3' => 'IRL'],
            ['name' => 'Serbia', 'iso_code' => 'RS', 'iso_code3' => 'SRB'],
            ['name' => 'Kosovo', 'iso_code' => 'XK', 'iso_code3' => 'XKX'],
            ['name' => 'Bosnia and Herzegovina', 'iso_code' => 'BA', 'iso_code3' => 'BIH'],
            ['name' => 'Montenegro', 'iso_code' => 'ME', 'iso_code3' => 'MNE'],
            ['name' => 'North Macedonia', 'iso_code' => 'MK', 'iso_code3' => 'MKD'],
            ['name' => 'Albania', 'iso_code' => 'AL', 'iso_code3' => 'ALB'],
            ['name' => 'Cyprus', 'iso_code' => 'CY', 'iso_code3' => 'CYP'],
            ['name' => 'Iceland', 'iso_code' => 'IS', 'iso_code3' => 'ISL'],
            ['name' => 'Luxembourg', 'iso_code' => 'LU', 'iso_code3' => 'LUX'],
            ['name' => 'Malta', 'iso_code' => 'MT', 'iso_code3' => 'MLT'],
        ];

        $now = now();

        foreach ($countries as $country) {
            DB::table('countries')->updateOrInsert(
                ['iso_code' => $country['iso_code']],
                array_merge($country, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        $this->command->info('Countries seeded successfully: ' . count($countries) . ' countries.');
    }
}
