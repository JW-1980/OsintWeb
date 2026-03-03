<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConflictSeeder extends Seeder
{
    /**
     * Map seeder type values to database conflict_type enum values.
     */
    private function mapConflictType(string $type): string
    {
        return match ($type) {
            'interstate' => 'INTERSTATE',
            'civil_war' => 'CIVIL_WAR',
            'insurgency' => 'INSURGENCY',
            'territorial' => 'BORDER_DISPUTE',
            'asymmetric' => 'INSURGENCY',
            default => 'OTHER',
        };
    }

    /**
     * Run the database seeds.
     * Seeds realistic active and historical conflicts.
     */
    public function run(): void
    {
        $conflicts = [
            // Active Major Conflicts
            [
                'name' => 'Russo-Ukrainian War',
                'description' => 'Full-scale invasion of Ukraine by the Russian Federation beginning February 24, 2022. Largest conventional military conflict in Europe since World War II. Involves extensive use of drones, precision missiles, and combined arms warfare. Major territorial changes in eastern and southern Ukraine.',
                'start_date' => '2022-02-24',
                'end_date' => null,
                'is_active' => true,
                'region' => 'Europe',
                'intensity_level' => 'high',
                'conflict_type' => 'INTERSTATE',
                'estimated_casualties' => json_encode(['estimate' => '500000+', 'displaced' => '14000000']),
            ],
            [
                'name' => 'Israel-Hamas War',
                'description' => 'Conflict triggered by Hamas attack on October 7, 2023 resulting in Israeli ground invasion of Gaza Strip. Characterized by urban warfare, tunnel combat, and significant civilian casualties. Involves rocket attacks, precision strikes, and hostage situations.',
                'start_date' => '2023-10-07',
                'end_date' => null,
                'is_active' => true,
                'region' => 'Middle East',
                'intensity_level' => 'high',
                'conflict_type' => 'INSURGENCY',
                'estimated_casualties' => json_encode(['estimate' => '40000+', 'displaced' => '2000000']),
            ],
            [
                'name' => 'Syrian Civil War',
                'description' => 'Multi-sided civil war in Syria ongoing since 2011. Involves Syrian government, various rebel factions, Kurdish forces, ISIS remnants, and foreign interventions by Russia, Iran, Turkey, and the US-led coalition. Characterized by siege warfare and mass displacement.',
                'start_date' => '2011-03-15',
                'end_date' => null,
                'is_active' => true,
                'region' => 'Middle East',
                'intensity_level' => 'medium',
                'conflict_type' => 'CIVIL_WAR',
                'estimated_casualties' => json_encode(['estimate' => '500000+', 'displaced' => '13000000']),
            ],
            [
                'name' => 'Yemeni Civil War',
                'description' => 'Civil war between Houthi rebels and internationally recognized government backed by Saudi-led coalition. Features extensive drone and missile attacks on Saudi Arabia and UAE. Severe humanitarian crisis with famine conditions.',
                'start_date' => '2014-09-21',
                'end_date' => null,
                'is_active' => true,
                'region' => 'Middle East',
                'intensity_level' => 'medium',
                'conflict_type' => 'CIVIL_WAR',
                'estimated_casualties' => json_encode(['estimate' => '150000+', 'displaced' => '4000000']),
            ],
            [
                'name' => 'Myanmar Civil War',
                'description' => 'Armed conflict following 2021 military coup. Involves military junta (Tatmadaw) against People\'s Defence Forces and ethnic armed organizations. Widespread resistance across the country with significant territorial losses for the military.',
                'start_date' => '2021-02-01',
                'end_date' => null,
                'is_active' => true,
                'region' => 'Southeast Asia',
                'intensity_level' => 'medium',
                'conflict_type' => 'CIVIL_WAR',
                'estimated_casualties' => json_encode(['estimate' => '50000+', 'displaced' => '2500000']),
            ],
            [
                'name' => 'Sudan Civil War',
                'description' => 'Armed conflict between Sudanese Armed Forces and Rapid Support Forces beginning April 2023. Urban warfare in Khartoum, ethnic violence in Darfur. Massive humanitarian crisis and displacement.',
                'start_date' => '2023-04-15',
                'end_date' => null,
                'is_active' => true,
                'region' => 'Africa',
                'intensity_level' => 'high',
                'conflict_type' => 'CIVIL_WAR',
                'estimated_casualties' => json_encode(['estimate' => '15000+', 'displaced' => '10000000']),
            ],
            [
                'name' => 'Ethiopian Civil Conflict',
                'description' => 'Multi-front conflict involving federal government, Tigray forces, Amhara militias, and Oromia insurgents. 2020-2022 Tigray War caused massive casualties. Ongoing tensions despite ceasefire agreements.',
                'start_date' => '2020-11-04',
                'end_date' => null,
                'is_active' => true,
                'region' => 'Africa',
                'intensity_level' => 'medium',
                'conflict_type' => 'CIVIL_WAR',
                'estimated_casualties' => json_encode(['estimate' => '600000+', 'displaced' => '3000000']),
            ],
            [
                'name' => 'Sahel Insurgency',
                'description' => 'Jihadist insurgency across Mali, Burkina Faso, and Niger by groups linked to Al-Qaeda and ISIS. French withdrawal and military coups have complicated counter-terrorism efforts.',
                'start_date' => '2012-01-16',
                'end_date' => null,
                'is_active' => true,
                'region' => 'Africa',
                'intensity_level' => 'medium',
                'conflict_type' => 'INSURGENCY',
                'estimated_casualties' => json_encode(['estimate' => '50000+', 'displaced' => '4000000']),
            ],
            // Lower Intensity / Concluded Conflicts
            [
                'name' => 'Nagorno-Karabakh Conflict',
                'description' => 'Dispute over Nagorno-Karabakh region between Armenia and Azerbaijan. 2020 war resulted in Azerbaijani victory. September 2023 offensive led to complete Armenian exodus from the region.',
                'start_date' => '1988-02-20',
                'end_date' => '2023-09-20',
                'is_active' => false,
                'region' => 'Caucasus',
                'intensity_level' => 'low',
                'conflict_type' => 'BORDER_DISPUTE',
                'estimated_casualties' => json_encode(['estimate' => '40000+', 'displaced' => '120000']),
            ],
            [
                'name' => 'Kashmir Conflict',
                'description' => 'Territorial dispute between India and Pakistan over Kashmir region. Recurring military clashes along Line of Control. Insurgency in Indian-administered Kashmir with periodic escalations.',
                'start_date' => '1947-10-22',
                'end_date' => null,
                'is_active' => true,
                'region' => 'South Asia',
                'intensity_level' => 'low',
                'conflict_type' => 'BORDER_DISPUTE',
                'estimated_casualties' => json_encode(['estimate' => '100000+', 'displaced' => '1000000']),
            ],
            [
                'name' => 'Taiwan Strait Tensions',
                'description' => 'Strategic competition between China and Taiwan with increasing military activity. PLA air and naval incursions into Taiwan\'s ADIZ. Risk of potential military conflict over Taiwan\'s status.',
                'start_date' => '1949-10-01',
                'end_date' => null,
                'is_active' => true,
                'region' => 'East Asia',
                'intensity_level' => 'low',
                'conflict_type' => 'BORDER_DISPUTE',
                'estimated_casualties' => null,
            ],
            [
                'name' => 'Korean Peninsula Tensions',
                'description' => 'Ongoing military standoff between North Korea and South Korea since 1953 armistice. Periodic provocations including missile tests and border incidents. Nuclear weapons development by DPRK.',
                'start_date' => '1950-06-25',
                'end_date' => null,
                'is_active' => true,
                'region' => 'East Asia',
                'intensity_level' => 'low',
                'conflict_type' => 'INTERSTATE',
                'estimated_casualties' => null,
            ],
            [
                'name' => 'Gulf War',
                'description' => 'Coalition military campaign to liberate Kuwait from Iraqi occupation. Featured extensive air campaign and rapid ground offensive. First large-scale deployment of precision-guided munitions.',
                'start_date' => '1990-08-02',
                'end_date' => '1991-02-28',
                'is_active' => false,
                'region' => 'Middle East',
                'intensity_level' => 'high',
                'conflict_type' => 'INTERSTATE',
                'estimated_casualties' => json_encode(['estimate' => '50000', 'displaced' => '5000000']),
            ],
            [
                'name' => 'Iraq War',
                'description' => 'US-led invasion and subsequent occupation of Iraq. Overthrow of Saddam Hussein followed by insurgency and sectarian conflict. Withdrawal completed 2011 with ongoing instability.',
                'start_date' => '2003-03-20',
                'end_date' => '2011-12-18',
                'is_active' => false,
                'region' => 'Middle East',
                'intensity_level' => 'high',
                'conflict_type' => 'INTERSTATE',
                'estimated_casualties' => json_encode(['estimate' => '500000+', 'displaced' => '4000000']),
            ],
            [
                'name' => 'War in Afghanistan',
                'description' => 'US-led intervention following 9/11 attacks. Twenty-year conflict ending with Taliban takeover in August 2021. Longest war in US history involving counter-terrorism and nation-building efforts.',
                'start_date' => '2001-10-07',
                'end_date' => '2021-08-30',
                'is_active' => false,
                'region' => 'Central Asia',
                'intensity_level' => 'medium',
                'conflict_type' => 'INSURGENCY',
                'estimated_casualties' => json_encode(['estimate' => '200000+', 'displaced' => '6000000']),
            ],
            [
                'name' => 'Libyan Civil War',
                'description' => 'Two civil wars (2011 and 2014-2020) following NATO intervention and overthrow of Gaddafi. Ongoing political instability with rival governments. Foreign intervention by Turkey, UAE, Russia.',
                'start_date' => '2011-02-15',
                'end_date' => null,
                'is_active' => true,
                'region' => 'North Africa',
                'intensity_level' => 'frozen',
                'conflict_type' => 'CIVIL_WAR',
                'estimated_casualties' => json_encode(['estimate' => '50000+', 'displaced' => '500000']),
            ],
        ];

        $typeMapping = [
            'interstate' => 'INTERSTATE',
            'civil_war' => 'CIVIL_WAR',
            'insurgency' => 'INSURGENCY',
            'asymmetric' => 'OTHER',
            'territorial' => 'BORDER_DISPUTE',
        ];

        $statusMapping = [
            'active' => true,
            'tension' => true,
            'concluded' => false,
            'frozen' => false,
        ];

        foreach ($conflicts as $conflict) {
            DB::table('conflicts')->updateOrInsert(
                ['name' => $conflict['name']],
                array_merge($conflict, [
                    'uuid' => Str::uuid(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Conflicts seeded: ' . count($conflicts) . ' conflicts');
    }
}
