<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds non-state actors (militias, terrorist groups, PMCs, etc.)
     */
    public function run(): void
    {
        $this->seedNonStateActors();
        $this->command->info('Non-state actors seeded successfully.');
    }

    private function seedNonStateActors(): void
    {
        $actors = [
            // Russia-aligned groups
            [
                'name' => 'Wagner Group',
                'actor_type' => 'PMC',
                'description' => 'Russian private military company founded by Dmitry Utkin and funded by Yevgeny Prigozhin. Operated in Syria, Libya, Mali, CAR, and Ukraine. Known for brutal tactics. Prigozhin killed August 2023 after failed mutiny. Assets largely absorbed by Russian MoD.',
                'alias_names' => json_encode(['PMC Wagner', 'Vagner', 'Africa Corps']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 90,
                'founded_date' => '2014-01-01',
                'operational_areas' => json_encode(['Ukraine', 'Syria', 'Libya', 'Mali', 'CAR', 'Sudan']),
            ],
            [
                'name' => 'Donetsk People\'s Republic Forces',
                'actor_type' => 'MILITIA',
                'description' => 'Armed forces of the self-proclaimed DPR in eastern Ukraine. Integrated into Russian military structure after 2022 invasion. Originally formed 2014 during Donbas war.',
                'alias_names' => json_encode(['DPR Army', 'DNR Forces', '1st Army Corps']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 70,
                'founded_date' => '2014-01-01',
                'operational_areas' => json_encode(['Donetsk Oblast']),
            ],
            [
                'name' => 'Luhansk People\'s Republic Forces',
                'actor_type' => 'MILITIA',
                'description' => 'Armed forces of the self-proclaimed LPR in eastern Ukraine. Integrated into Russian military structure. Part of 2nd Army Corps under Russian Southern Military District.',
                'alias_names' => json_encode(['LPR Army', 'LNR Forces', '2nd Army Corps']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 65,
                'founded_date' => '2014-01-01',
                'operational_areas' => json_encode(['Luhansk Oblast']),
            ],

            // Palestinian groups
            [
                'name' => 'Hamas',
                'actor_type' => 'INSURGENT',
                'description' => 'Palestinian Sunni Islamist organization governing Gaza Strip since 2007. Military wing Al-Qassam Brigades. Responsible for October 7, 2023 attack on Israel. Designated terrorist organization by US, EU, and others.',
                'alias_names' => json_encode(['Islamic Resistance Movement', 'Al-Qassam Brigades', 'Izz ad-Din al-Qassam Brigades']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 95,
                'founded_date' => '1987-01-01',
                'operational_areas' => json_encode(['Gaza Strip', 'West Bank']),
            ],
            [
                'name' => 'Palestinian Islamic Jihad',
                'actor_type' => 'INSURGENT',
                'description' => 'Palestinian Islamist militant organization. Second largest armed group in Gaza after Hamas. Iranian-backed. Al-Quds Brigades is military wing.',
                'alias_names' => json_encode(['PIJ', 'Islamic Jihad', 'Al-Quds Brigades']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 70,
                'founded_date' => '1981-01-01',
                'operational_areas' => json_encode(['Gaza Strip', 'West Bank']),
            ],

            // Lebanese/Iranian-backed groups
            [
                'name' => 'Hezbollah',
                'actor_type' => 'INSURGENT',
                'description' => 'Lebanese Shia Islamist political party and militant group. Iranian-backed with significant military capability. Fought 2006 war with Israel. Currently engaged in cross-border attacks supporting Hamas.',
                'alias_names' => json_encode(['Party of God', 'Islamic Resistance in Lebanon']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 95,
                'founded_date' => '1982-01-01',
                'operational_areas' => json_encode(['Lebanon', 'Syria']),
            ],
            [
                'name' => 'Houthis',
                'actor_type' => 'INSURGENT',
                'description' => 'Yemeni Shia Islamist movement officially known as Ansar Allah. Controls northern Yemen including Sanaa. Iranian-backed. Attacking Red Sea shipping in support of Gaza.',
                'alias_names' => json_encode(['Ansar Allah', 'Ansarullah']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 85,
                'founded_date' => '1992-01-01',
                'operational_areas' => json_encode(['Yemen', 'Red Sea', 'Saudi Arabia']),
            ],
            [
                'name' => 'Iraqi Popular Mobilization Forces',
                'actor_type' => 'MILITIA',
                'description' => 'Iraqi state-sponsored umbrella organization of predominantly Shia militias. Formed 2014 to fight ISIS. Many components are Iranian-backed. Significant political influence in Iraq.',
                'alias_names' => json_encode(['PMF', 'PMU', 'Hashd al-Shaabi', 'Popular Mobilization Units']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'medium',
                'autocomplete_priority' => 65,
                'founded_date' => '2014-01-01',
                'operational_areas' => json_encode(['Iraq', 'Syria']),
            ],

            // Syrian groups
            [
                'name' => 'Syrian Democratic Forces',
                'actor_type' => 'MILITIA',
                'description' => 'Multi-ethnic alliance in northern Syria dominated by Kurdish YPG. US-backed force that defeated ISIS territorial caliphate. Controls northeast Syria including oil fields.',
                'alias_names' => json_encode(['SDF', 'QSD', "People's Defense Units", 'YPG']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 75,
                'founded_date' => '2015-01-01',
                'operational_areas' => json_encode(['Northeast Syria', 'Rojava']),
            ],
            [
                'name' => 'Hayat Tahrir al-Sham',
                'actor_type' => 'TERRORIST',
                'description' => 'Syrian Sunni Islamist militant group controlling Idlib province. Formerly Jabhat al-Nusra (Al-Qaeda affiliate). Led by Abu Mohammad al-Julani. Designated terrorist organization.',
                'alias_names' => json_encode(['HTS', 'Jabhat al-Nusra', 'Al-Nusra Front', 'Jabhat Fateh al-Sham']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 75,
                'founded_date' => '2017-01-01',
                'operational_areas' => json_encode(['Idlib', 'Northwest Syria']),
            ],
            [
                'name' => 'Syrian National Army',
                'actor_type' => 'MILITIA',
                'description' => 'Turkish-backed Syrian opposition coalition operating in northern Syria. Formed from various FSA factions. Participates in Turkish military operations against SDF.',
                'alias_names' => json_encode(['TFSA', 'Turkish-backed FSA', 'National Army']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'medium',
                'autocomplete_priority' => 55,
                'founded_date' => '2017-01-01',
                'operational_areas' => json_encode(['Northern Syria', 'Turkish border zone']),
            ],

            // African groups
            [
                'name' => 'JNIM',
                'actor_type' => 'TERRORIST',
                'description' => 'Jamaat Nusrat al-Islam wal-Muslimin - Al-Qaeda affiliate in the Sahel. Merger of multiple jihadist groups. Primary insurgent threat in Mali, Burkina Faso, and Niger.',
                'alias_names' => json_encode(['Group for Support of Islam and Muslims', 'GSIM']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 60,
                'founded_date' => '2017-01-01',
                'operational_areas' => json_encode(['Mali', 'Burkina Faso', 'Niger']),
            ],
            [
                'name' => 'Islamic State Greater Sahara',
                'actor_type' => 'TERRORIST',
                'description' => 'ISIS affiliate in the Sahel region. Operates in Mali-Niger-Burkina Faso border area. Rival to JNIM with periodic clashes between the groups.',
                'alias_names' => json_encode(['ISGS', 'ISIS Sahel', 'Islamic State in the Greater Sahara']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 55,
                'founded_date' => '2015-01-01',
                'operational_areas' => json_encode(['Mali', 'Niger', 'Burkina Faso']),
            ],
            [
                'name' => 'Al-Shabaab',
                'actor_type' => 'TERRORIST',
                'description' => 'Somali Al-Qaeda affiliate controlling parts of southern Somalia. Carries out attacks in Somalia and Kenya. One of Al-Qaeda\'s most effective affiliates.',
                'alias_names' => json_encode(['Harakat al-Shabaab al-Mujahideen', 'HSM', 'The Youth']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 75,
                'founded_date' => '2006-01-01',
                'operational_areas' => json_encode(['Somalia', 'Kenya']),
            ],
            [
                'name' => 'Rapid Support Forces',
                'actor_type' => 'MILITIA',
                'description' => 'Sudanese paramilitary force led by Mohamed Hamdan Dagalo (Hemedti). Evolved from Janjaweed militia. Currently fighting SAF for control of Sudan.',
                'alias_names' => json_encode(['RSF', 'Janjaweed']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 80,
                'founded_date' => '2013-01-01',
                'operational_areas' => json_encode(['Sudan', 'Darfur', 'Khartoum']),
            ],

            // Myanmar groups
            [
                'name' => 'People\'s Defence Force',
                'actor_type' => 'MILITIA',
                'description' => 'Armed wing of Myanmar\'s National Unity Government opposing military junta. Formed after 2021 coup. Operating nationwide with varying capabilities.',
                'alias_names' => json_encode(['PDF', 'NUG Armed Forces']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 65,
                'founded_date' => '2021-01-01',
                'operational_areas' => json_encode(['Myanmar']),
            ],
            [
                'name' => 'Arakan Army',
                'actor_type' => 'ETHNIC_MILITIA',
                'description' => 'Ethnic Rakhine armed group seeking autonomy in Rakhine State. Part of Three Brotherhood Alliance. Major territorial gains against Myanmar military in 2023-2024.',
                'alias_names' => json_encode(['AA', 'ULA/AA']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 60,
                'founded_date' => '2009-01-01',
                'operational_areas' => json_encode(['Rakhine State', 'Myanmar']),
            ],
            [
                'name' => 'Kachin Independence Army',
                'actor_type' => 'ETHNIC_MILITIA',
                'description' => 'Ethnic Kachin armed organization in northern Myanmar. One of largest and best-equipped ethnic armed groups. Long history of conflict with Myanmar military.',
                'alias_names' => json_encode(['KIA', 'KIO']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'medium',
                'autocomplete_priority' => 55,
                'founded_date' => '1961-01-01',
                'operational_areas' => json_encode(['Kachin State', 'Northern Shan State']),
            ],

            // Ethiopian groups
            [
                'name' => 'Tigray Defense Forces',
                'actor_type' => 'MILITIA',
                'description' => 'Armed forces of the Tigray regional government. Fought 2020-2022 Tigray War against Ethiopian federal forces and Eritrea. Ceased hostilities under November 2022 agreement.',
                'alias_names' => json_encode(['TDF', 'TPLF Forces']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => false,
                'activity_level' => 'low',
                'autocomplete_priority' => 50,
                'founded_date' => '2020-01-01',
                'operational_areas' => json_encode(['Tigray', 'Ethiopia']),
            ],
            [
                'name' => 'Fano Militia',
                'actor_type' => 'MILITIA',
                'description' => 'Amhara irregular militia forces. Originally allied with Ethiopian government against Tigray but now in conflict with federal government over regional militias.',
                'alias_names' => json_encode(['Amhara Fano', 'Fano']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'medium',
                'autocomplete_priority' => 45,
                'founded_date' => '2018-01-01',
                'operational_areas' => json_encode(['Amhara Region', 'Ethiopia']),
            ],

            // Ukrainian volunteer units
            [
                'name' => 'Azov Brigade',
                'actor_type' => 'MILITIA',
                'description' => 'Ukrainian National Guard unit originally formed as volunteer battalion in 2014. Defended Mariupol in 2022. Controversial far-right origins but reformed as professional military unit.',
                'alias_names' => json_encode(['Azov Regiment', 'Azov Battalion', '3rd Assault Brigade']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 70,
                'founded_date' => '2014-01-01',
                'operational_areas' => json_encode(['Ukraine']),
            ],
            [
                'name' => 'International Legion of Ukraine',
                'actor_type' => 'MILITIA',
                'description' => 'Foreign volunteer military unit fighting for Ukraine. Composed of volunteers from 50+ countries. Formally part of Ukrainian Armed Forces.',
                'alias_names' => json_encode(['Foreign Legion of Ukraine']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'medium',
                'autocomplete_priority' => 50,
                'founded_date' => '2022-01-01',
                'operational_areas' => json_encode(['Ukraine']),
            ],

            // Other significant groups
            [
                'name' => 'ISIS Remnants',
                'actor_type' => 'TERRORIST',
                'description' => 'Remaining Islamic State fighters in Iraq and Syria conducting insurgent operations. Lost territorial caliphate in 2019 but maintains clandestine presence.',
                'alias_names' => json_encode(['Islamic State', 'ISIL', 'Daesh', 'ISIS']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'medium',
                'autocomplete_priority' => 80,
                'founded_date' => '2006-01-01',
                'operational_areas' => json_encode(['Iraq', 'Syria']),
            ],
            [
                'name' => 'ISIS-Khorasan',
                'actor_type' => 'TERRORIST',
                'description' => 'ISIS affiliate in Afghanistan and Pakistan. Opposes both Taliban and Western targets. Responsible for Kabul airport bombing and Moscow concert hall attack.',
                'alias_names' => json_encode(['ISIS-K', 'ISKP', 'Islamic State Khorasan Province']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'high',
                'autocomplete_priority' => 75,
                'founded_date' => '2015-01-01',
                'operational_areas' => json_encode(['Afghanistan', 'Pakistan', 'Central Asia']),
            ],
            [
                'name' => 'Al-Qaeda',
                'actor_type' => 'TERRORIST',
                'description' => 'Global Salafi-jihadist organization founded by Osama bin Laden. Weakened leadership but maintains affiliates worldwide. Currently led by unknown successor after Zawahiri death.',
                'alias_names' => json_encode(['AQ', 'The Base', 'Al-Qaida']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => true,
                'activity_level' => 'medium',
                'autocomplete_priority' => 85,
                'founded_date' => '1988-01-01',
                'operational_areas' => json_encode(['Global', 'Afghanistan', 'Yemen', 'Sahel']),
            ],
            [
                'name' => 'Taliban',
                'actor_type' => 'INSURGENT',
                'description' => 'Islamic fundamentalist movement now governing Afghanistan. Seized power August 2021 after US withdrawal. Imposes strict Islamic law and restrictions on women.',
                'alias_names' => json_encode(['Islamic Emirate of Afghanistan', 'IEA']),
                'country_id' => null,
                'is_state_actor' => false,
                'is_active_in_conflict' => false,
                'activity_level' => 'medium',
                'autocomplete_priority' => 80,
                'founded_date' => '1994-01-01',
                'operational_areas' => json_encode(['Afghanistan']),
            ],
        ];

        foreach ($actors as $actor) {
            DB::table('actors')->updateOrInsert(
                ['name' => $actor['name']],
                array_merge($actor, [
                    'uuid' => Str::uuid(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Non-state actors seeded: ' . count($actors) . ' actors');
    }
}
