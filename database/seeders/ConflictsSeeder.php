<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConflictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Populates the conflicts table with current active conflicts (2024-2025)
     * and links actors to conflicts via conflict_parties table.
     */
    public function run(): void
    {
        $conflicts = [
            [
                'name' => 'Russia-Ukraine War',
                'short_name' => 'Ukraine War',
                'alias_names' => json_encode(['Russo-Ukrainian War', 'War in Ukraine', 'Ukrainian Conflict']),
                'conflict_type' => 'INTERSTATE',
                'intensity_level' => 'high',
                'region' => 'Eastern Europe',
                'start_date' => '2022-02-24',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 200000,
                    'civilian' => 30000,
                    'total' => 230000,
                    'last_updated' => '2024-12-01'
                ]),
                'description' => 'Large-scale military conflict between Russia and Ukraine that began with Russia\'s invasion of Ukraine in February 2022.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/Russian_invasion_of_Ukraine',
            ],
            [
                'name' => 'Israel-Gaza Conflict',
                'short_name' => 'Gaza War 2023',
                'alias_names' => json_encode(['Israel-Hamas War', 'Operation Iron Swords', '2023 Gaza War']),
                'conflict_type' => 'INTERSTATE',
                'intensity_level' => 'high',
                'region' => 'Middle East',
                'start_date' => '2023-10-07',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 15000,
                    'civilian' => 35000,
                    'total' => 50000,
                    'last_updated' => '2024-12-01'
                ]),
                'description' => 'Armed conflict between Israel and Hamas following Hamas\' attack on October 7, 2023.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/2023_Israel%E2%80%93Hamas_war',
            ],
            [
                'name' => 'Sudan Civil War',
                'short_name' => 'Sudan War',
                'alias_names' => json_encode(['Sudanese Conflict 2023', 'SAF-RSF War']),
                'conflict_type' => 'CIVIL_WAR',
                'intensity_level' => 'high',
                'region' => 'Northeast Africa',
                'start_date' => '2023-04-15',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 20000,
                    'civilian' => 15000,
                    'total' => 35000,
                    'last_updated' => '2024-11-01'
                ]),
                'description' => 'Armed conflict in Sudan between the Sudanese Armed Forces and the Rapid Support Forces.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/War_in_Sudan_(2023%E2%80%93present)',
            ],
            [
                'name' => 'Myanmar Civil War',
                'short_name' => 'Myanmar Conflict',
                'alias_names' => json_encode(['Myanmar Crisis', 'Burma Civil War']),
                'conflict_type' => 'CIVIL_WAR',
                'intensity_level' => 'high',
                'region' => 'Southeast Asia',
                'start_date' => '2021-02-01',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 30000,
                    'civilian' => 20000,
                    'total' => 50000,
                    'last_updated' => '2024-10-01'
                ]),
                'description' => 'Ongoing civil war in Myanmar following the military coup of February 2021.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/Myanmar_civil_war_(2021%E2%80%93present)',
            ],
            [
                'name' => 'Yemeni Civil War',
                'short_name' => 'Yemen Conflict',
                'alias_names' => json_encode(['Yemen War', 'Yemeni Crisis']),
                'conflict_type' => 'CIVIL_WAR',
                'intensity_level' => 'medium',
                'region' => 'Middle East',
                'start_date' => '2014-09-21',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 150000,
                    'civilian' => 250000,
                    'total' => 400000,
                    'last_updated' => '2024-09-01'
                ]),
                'description' => 'Ongoing multi-sided civil war in Yemen between Houthi forces and the internationally recognized government.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/Yemeni_civil_war_(2014%E2%80%93present)',
            ],
            [
                'name' => 'Syrian Civil War',
                'short_name' => 'Syria Conflict',
                'alias_names' => json_encode(['Syrian Conflict', 'War in Syria']),
                'conflict_type' => 'CIVIL_WAR',
                'intensity_level' => 'low',
                'region' => 'Middle East',
                'start_date' => '2011-03-15',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 200000,
                    'civilian' => 400000,
                    'total' => 600000,
                    'last_updated' => '2024-08-01'
                ]),
                'description' => 'Ongoing multi-sided conflict in Syria involving government forces, rebel groups, and foreign powers.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/Syrian_civil_war',
            ],
            [
                'name' => 'Sahel Insurgency',
                'short_name' => 'Sahel Crisis',
                'alias_names' => json_encode(['Sahel Conflict', 'West Africa Insurgency']),
                'conflict_type' => 'TERRORISM',
                'intensity_level' => 'high',
                'region' => 'Sahel',
                'start_date' => '2011-01-17',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 50000,
                    'civilian' => 100000,
                    'total' => 150000,
                    'last_updated' => '2024-11-01'
                ]),
                'description' => 'Ongoing Islamist insurgency across the Sahel region involving multiple terrorist groups.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/Insurgency_in_the_Maghreb',
            ],
            [
                'name' => 'Somali Civil War',
                'short_name' => 'Somalia Conflict',
                'alias_names' => json_encode(['War in Somalia', 'Al-Shabaab Insurgency']),
                'conflict_type' => 'CIVIL_WAR',
                'intensity_level' => 'medium',
                'region' => 'East Africa',
                'start_date' => '2009-01-01',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 30000,
                    'civilian' => 50000,
                    'total' => 80000,
                    'last_updated' => '2024-10-01'
                ]),
                'description' => 'Ongoing conflict in Somalia between the government and Al-Shabaab militants.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/Somali_Civil_War_(2009%E2%80%93present)',
            ],
            [
                'name' => 'War in Afghanistan',
                'short_name' => 'Afghanistan Conflict',
                'alias_names' => json_encode(['Afghan War', 'Taliban Insurgency']),
                'conflict_type' => 'CIVIL_WAR',
                'intensity_level' => 'medium',
                'region' => 'South Asia',
                'start_date' => '2001-10-07',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 150000,
                    'civilian' => 200000,
                    'total' => 350000,
                    'last_updated' => '2024-09-01'
                ]),
                'description' => 'Ongoing conflict in Afghanistan involving Taliban government forces and ISIS-K.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/War_in_Afghanistan_(2001%E2%80%93present)',
            ],
            [
                'name' => 'Mexican Drug War',
                'short_name' => 'Mexico Cartel War',
                'alias_names' => json_encode(['Mexican Cartel Violence', 'War on Drugs Mexico']),
                'conflict_type' => 'OTHER',
                'intensity_level' => 'high',
                'region' => 'North America',
                'start_date' => '2006-12-11',
                'is_active' => true,
                'estimated_casualties' => json_encode([
                    'military' => 50000,
                    'civilian' => 350000,
                    'total' => 400000,
                    'last_updated' => '2024-11-01'
                ]),
                'description' => 'Ongoing armed conflict between Mexican drug cartels and the Mexican government.',
                'wikipedia_url' => 'https://en.wikipedia.org/wiki/Mexican_drug_war',
            ],
        ];

        foreach ($conflicts as $conflict) {
            DB::table('conflicts')->insertOrIgnore(array_merge($conflict, [
                'uuid' => (string) Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Conflicts seeded successfully.');

        // Seed conflict parties (link actors to conflicts)
        $this->seedConflictParties();
    }

    /**
     * Seed conflict_parties table to link actors with conflicts.
     */
    private function seedConflictParties(): void
    {
        // Helper to find actor and conflict IDs
        $getActorId = fn($name) => DB::table('actors')->where('name', $name)->value('id');
        $getConflictId = fn($name) => DB::table('conflicts')->where('name', $name)->value('id');

        $conflictParties = [
            // Russia-Ukraine War
            [
                'conflict_id' => $getConflictId('Russia-Ukraine War'),
                'actor_id' => $getActorId('Russian Federation'),
                'side' => 'Aggressor',
                'role' => 'Primary Combatant',
                'joined_date' => '2022-02-24',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Russia-Ukraine War'),
                'actor_id' => $getActorId('Ukraine'),
                'side' => 'Defender',
                'role' => 'Primary Combatant',
                'joined_date' => '2022-02-24',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Russia-Ukraine War'),
                'actor_id' => $getActorId('Wagner Group'),
                'side' => 'Aggressor',
                'role' => 'Support',
                'joined_date' => '2022-02-24',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Russia-Ukraine War'),
                'actor_id' => $getActorId('Donetsk People\'s Republic'),
                'side' => 'Aggressor',
                'role' => 'Support',
                'joined_date' => '2022-02-24',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Russia-Ukraine War'),
                'actor_id' => $getActorId('Luhansk People\'s Republic'),
                'side' => 'Aggressor',
                'role' => 'Support',
                'joined_date' => '2022-02-24',
                'is_currently_active' => true,
            ],

            // Israel-Gaza Conflict
            [
                'conflict_id' => $getConflictId('Israel-Gaza Conflict'),
                'actor_id' => $getActorId('Israel'),
                'side' => 'Government',
                'role' => 'Primary Combatant',
                'joined_date' => '2023-10-07',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Israel-Gaza Conflict'),
                'actor_id' => $getActorId('Hamas'),
                'side' => 'Opposition',
                'role' => 'Primary Combatant',
                'joined_date' => '2023-10-07',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Israel-Gaza Conflict'),
                'actor_id' => $getActorId('Palestinian Islamic Jihad'),
                'side' => 'Opposition',
                'role' => 'Support',
                'joined_date' => '2023-10-07',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Israel-Gaza Conflict'),
                'actor_id' => $getActorId('Hezbollah'),
                'side' => 'Opposition',
                'role' => 'Support',
                'joined_date' => '2023-10-08',
                'is_currently_active' => true,
            ],

            // Sudan Civil War
            [
                'conflict_id' => $getConflictId('Sudan Civil War'),
                'actor_id' => $getActorId('Sudanese Armed Forces'),
                'side' => 'Government',
                'role' => 'Primary Combatant',
                'joined_date' => '2023-04-15',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Sudan Civil War'),
                'actor_id' => $getActorId('Rapid Support Forces'),
                'side' => 'Opposition',
                'role' => 'Primary Combatant',
                'joined_date' => '2023-04-15',
                'is_currently_active' => true,
            ],

            // Myanmar Civil War
            [
                'conflict_id' => $getConflictId('Myanmar Civil War'),
                'actor_id' => $getActorId('Tatmadaw'),
                'side' => 'Government',
                'role' => 'Primary Combatant',
                'joined_date' => '2021-02-01',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Myanmar Civil War'),
                'actor_id' => $getActorId('People\'s Defense Force'),
                'side' => 'Opposition',
                'role' => 'Primary Combatant',
                'joined_date' => '2021-05-05',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Myanmar Civil War'),
                'actor_id' => $getActorId('Arakan Army'),
                'side' => 'Opposition',
                'role' => 'Support',
                'joined_date' => '2021-03-01',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Myanmar Civil War'),
                'actor_id' => $getActorId('Kachin Independence Army'),
                'side' => 'Opposition',
                'role' => 'Support',
                'joined_date' => '2021-03-01',
                'is_currently_active' => true,
            ],

            // Syrian Civil War
            [
                'conflict_id' => $getConflictId('Syrian Civil War'),
                'actor_id' => $getActorId('Syrian Arab Army'),
                'side' => 'Government',
                'role' => 'Primary Combatant',
                'joined_date' => '2011-03-15',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Syrian Civil War'),
                'actor_id' => $getActorId('Syrian Democratic Forces'),
                'side' => 'Opposition',
                'role' => 'Support',
                'joined_date' => '2015-10-10',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Syrian Civil War'),
                'actor_id' => $getActorId('Hayat Tahrir al-Sham'),
                'side' => 'Opposition',
                'role' => 'Primary Combatant',
                'joined_date' => '2017-01-28',
                'is_currently_active' => true,
            ],

            // Sahel Insurgency
            [
                'conflict_id' => $getConflictId('Sahel Insurgency'),
                'actor_id' => $getActorId('Jama\'at Nasr al-Islam wal Muslimin'),
                'side' => 'Opposition',
                'role' => 'Primary Combatant',
                'joined_date' => '2017-03-02',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Sahel Insurgency'),
                'actor_id' => $getActorId('Islamic State in the Greater Sahara'),
                'side' => 'Opposition',
                'role' => 'Primary Combatant',
                'joined_date' => '2015-05-01',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Sahel Insurgency'),
                'actor_id' => $getActorId('Boko Haram'),
                'side' => 'Opposition',
                'role' => 'Support',
                'joined_date' => '2015-01-01',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Sahel Insurgency'),
                'actor_id' => $getActorId('Islamic State West Africa Province'),
                'side' => 'Opposition',
                'role' => 'Support',
                'joined_date' => '2016-03-12',
                'is_currently_active' => true,
            ],

            // Somali Civil War
            [
                'conflict_id' => $getConflictId('Somali Civil War'),
                'actor_id' => $getActorId('Al-Shabaab'),
                'side' => 'Opposition',
                'role' => 'Primary Combatant',
                'joined_date' => '2009-01-01',
                'is_currently_active' => true,
            ],

            // War in Afghanistan
            [
                'conflict_id' => $getConflictId('War in Afghanistan'),
                'actor_id' => $getActorId('Taliban'),
                'side' => 'Government',
                'role' => 'Primary Combatant',
                'joined_date' => '2021-08-15',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('War in Afghanistan'),
                'actor_id' => $getActorId('Islamic State - Khorasan Province'),
                'side' => 'Opposition',
                'role' => 'Primary Combatant',
                'joined_date' => '2015-01-10',
                'is_currently_active' => true,
            ],

            // Mexican Drug War
            [
                'conflict_id' => $getConflictId('Mexican Drug War'),
                'actor_id' => $getActorId('Sinaloa Cartel'),
                'side' => 'Criminal',
                'role' => 'Primary Combatant',
                'joined_date' => '2006-12-11',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Mexican Drug War'),
                'actor_id' => $getActorId('Jalisco New Generation Cartel'),
                'side' => 'Criminal',
                'role' => 'Primary Combatant',
                'joined_date' => '2010-01-01',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Mexican Drug War'),
                'actor_id' => $getActorId('Los Zetas'),
                'side' => 'Criminal',
                'role' => 'Support',
                'joined_date' => '2010-01-01',
                'is_currently_active' => true,
            ],
            [
                'conflict_id' => $getConflictId('Mexican Drug War'),
                'actor_id' => $getActorId('Gulf Cartel'),
                'side' => 'Criminal',
                'role' => 'Support',
                'joined_date' => '2006-12-11',
                'is_currently_active' => true,
            ],
        ];

        foreach ($conflictParties as $party) {
            if ($party['conflict_id'] && $party['actor_id']) {
                DB::table('conflict_parties')->insertOrIgnore(array_merge($party, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $this->command->info('Conflict parties seeded successfully.');
    }
}
