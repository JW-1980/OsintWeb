<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates comprehensive event type definitions covering all aspects of
     * conflict monitoring: combat, equipment, territorial, infrastructure,
     * humanitarian, and cyber events.
     */
    public function run(): void
    {
        $now = now();
        $sortOrder = 0;

        $eventTypes = [
            // ===============================================
            // COMBAT EVENTS (15 types)
            // ===============================================
            [
                'name' => 'Airstrike',
                'slug' => 'airstrike',
                'icon' => 'plane-departure',
                'color' => '#dc2626',
                'description' => 'Attack conducted by manned or unmanned aircraft using bombs, missiles, or other air-delivered munitions.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'aircraft_type', 'type' => 'select', 'options' => ['fixed_wing', 'helicopter', 'uav', 'unknown']],
                        ['name' => 'munition_type', 'type' => 'text', 'required' => false],
                        ['name' => 'targets', 'type' => 'multiselect', 'options' => ['military', 'infrastructure', 'residential', 'industrial', 'unknown']],
                        ['name' => 'damage_assessment', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'Artillery Strike',
                'slug' => 'artillery-strike',
                'icon' => 'crosshairs',
                'color' => '#ea580c',
                'description' => 'Attack using artillery systems including howitzers, mortars, and self-propelled guns.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'artillery_type', 'type' => 'select', 'options' => ['howitzer', 'mortar', 'self_propelled', 'unknown']],
                        ['name' => 'caliber', 'type' => 'text'],
                        ['name' => 'estimated_rounds', 'type' => 'number'],
                        ['name' => 'crater_analysis', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'MLRS Strike',
                'slug' => 'mlrs-strike',
                'icon' => 'arrows-up-to-line',
                'color' => '#f97316',
                'description' => 'Attack using Multiple Launch Rocket Systems such as HIMARS, GRAD, or similar.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'system_type', 'type' => 'text'],
                        ['name' => 'rocket_type', 'type' => 'text'],
                        ['name' => 'estimated_rockets', 'type' => 'number'],
                        ['name' => 'impact_pattern', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'Missile Strike',
                'slug' => 'missile-strike',
                'icon' => 'rocket',
                'color' => '#b91c1c',
                'description' => 'Attack using guided missiles including cruise missiles, ballistic missiles, or tactical missiles.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'missile_type', 'type' => 'select', 'options' => ['cruise', 'ballistic', 'tactical', 'anti_ship', 'unknown']],
                        ['name' => 'missile_model', 'type' => 'text'],
                        ['name' => 'launch_platform', 'type' => 'select', 'options' => ['ground', 'air', 'naval', 'submarine', 'unknown']],
                        ['name' => 'intercept_attempted', 'type' => 'boolean'],
                        ['name' => 'intercept_success', 'type' => 'boolean'],
                    ],
                ]),
            ],
            [
                'name' => 'Naval Engagement',
                'slug' => 'naval-engagement',
                'icon' => 'anchor',
                'color' => '#0369a1',
                'description' => 'Combat action involving naval vessels including ship-to-ship, ship-to-shore, or anti-ship operations.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'engagement_type', 'type' => 'select', 'options' => ['ship_to_ship', 'ship_to_shore', 'anti_ship_missile', 'naval_drone', 'mine']],
                        ['name' => 'vessels_involved', 'type' => 'textarea'],
                        ['name' => 'sea_conditions', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'Drone Strike',
                'slug' => 'drone-strike',
                'icon' => 'plane',
                'color' => '#7c3aed',
                'description' => 'Attack conducted by unmanned aerial vehicles including kamikaze drones, reconnaissance drones with weapons, or loitering munitions.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'drone_type', 'type' => 'select', 'options' => ['kamikaze', 'armed_reconnaissance', 'loitering_munition', 'fpv', 'unknown']],
                        ['name' => 'drone_model', 'type' => 'text'],
                        ['name' => 'target_type', 'type' => 'text'],
                        ['name' => 'swarm_attack', 'type' => 'boolean'],
                        ['name' => 'drone_count', 'type' => 'number'],
                    ],
                ]),
            ],
            [
                'name' => 'Ground Combat',
                'slug' => 'ground-combat',
                'icon' => 'users',
                'color' => '#854d0e',
                'description' => 'Direct ground combat including infantry engagements, mechanized warfare, and combined arms operations.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'combat_type', 'type' => 'select', 'options' => ['infantry', 'mechanized', 'urban', 'trench', 'combined_arms']],
                        ['name' => 'forces_involved', 'type' => 'textarea'],
                        ['name' => 'duration_hours', 'type' => 'number'],
                        ['name' => 'outcome', 'type' => 'select', 'options' => ['ongoing', 'attacker_success', 'defender_success', 'stalemate', 'unknown']],
                    ],
                ]),
            ],
            [
                'name' => 'Ambush',
                'slug' => 'ambush',
                'icon' => 'eye-slash',
                'color' => '#4a5568',
                'description' => 'Surprise attack from a concealed position against moving or stationary targets.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'ambush_type', 'type' => 'select', 'options' => ['convoy', 'patrol', 'checkpoint', 'other']],
                        ['name' => 'target_description', 'type' => 'textarea'],
                        ['name' => 'weapons_used', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'Special Operations',
                'slug' => 'special-operations',
                'icon' => 'user-secret',
                'color' => '#1e293b',
                'description' => 'Operations conducted by special forces including raids, reconnaissance, sabotage, or hostage rescue.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'operation_type', 'type' => 'select', 'options' => ['raid', 'reconnaissance', 'sabotage', 'hostage_rescue', 'assassination', 'other']],
                        ['name' => 'unit_type', 'type' => 'text'],
                        ['name' => 'objective', 'type' => 'textarea'],
                        ['name' => 'outcome', 'type' => 'select', 'options' => ['success', 'partial_success', 'failure', 'unknown']],
                    ],
                ]),
            ],
            [
                'name' => 'Air Defense Engagement',
                'slug' => 'air-defense-engagement',
                'icon' => 'shield-alt',
                'color' => '#065f46',
                'description' => 'Engagement of air targets by ground-based or naval air defense systems.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'system_type', 'type' => 'text'],
                        ['name' => 'target_type', 'type' => 'select', 'options' => ['aircraft', 'missile', 'drone', 'rocket', 'unknown']],
                        ['name' => 'missiles_fired', 'type' => 'number'],
                        ['name' => 'intercept_success', 'type' => 'boolean'],
                        ['name' => 'targets_engaged', 'type' => 'number'],
                        ['name' => 'targets_destroyed', 'type' => 'number'],
                    ],
                ]),
            ],
            [
                'name' => 'IED/Mine Incident',
                'slug' => 'ied-mine',
                'icon' => 'bomb',
                'color' => '#92400e',
                'description' => 'Incident involving improvised explosive devices, landmines, or other explosive hazards.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'device_type', 'type' => 'select', 'options' => ['ied', 'anti_tank_mine', 'anti_personnel_mine', 'booby_trap', 'unknown']],
                        ['name' => 'trigger_method', 'type' => 'select', 'options' => ['pressure', 'remote', 'victim_activated', 'timed', 'unknown']],
                        ['name' => 'target', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'Chemical Attack',
                'slug' => 'chemical-attack',
                'icon' => 'biohazard',
                'color' => '#facc15',
                'description' => 'Attack using chemical agents including nerve agents, blister agents, or toxic industrial chemicals.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'agent_type', 'type' => 'select', 'options' => ['nerve', 'blister', 'choking', 'blood', 'tic', 'unknown']],
                        ['name' => 'delivery_method', 'type' => 'select', 'options' => ['artillery', 'airstrike', 'ground', 'unknown']],
                        ['name' => 'symptoms_reported', 'type' => 'textarea'],
                        ['name' => 'affected_area_km2', 'type' => 'number'],
                    ],
                ]),
            ],
            [
                'name' => 'Incendiary Attack',
                'slug' => 'incendiary-attack',
                'icon' => 'fire',
                'color' => '#f59e0b',
                'description' => 'Attack using incendiary weapons including thermite, white phosphorus, or napalm.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'incendiary_type', 'type' => 'select', 'options' => ['thermite', 'white_phosphorus', 'napalm', 'unknown']],
                        ['name' => 'delivery_method', 'type' => 'text'],
                        ['name' => 'area_affected', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'Cluster Munition Strike',
                'slug' => 'cluster-munition',
                'icon' => 'grip',
                'color' => '#e11d48',
                'description' => 'Attack using cluster munitions that disperse submunitions over a wide area.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'munition_type', 'type' => 'text'],
                        ['name' => 'delivery_system', 'type' => 'select', 'options' => ['aircraft', 'mlrs', 'artillery', 'unknown']],
                        ['name' => 'submunition_type', 'type' => 'text'],
                        ['name' => 'uxo_count', 'type' => 'number'],
                    ],
                ]),
            ],
            [
                'name' => 'Thermobaric Attack',
                'slug' => 'thermobaric-attack',
                'icon' => 'explosion',
                'color' => '#be123c',
                'description' => 'Attack using thermobaric or fuel-air explosive weapons.',
                'category' => 'combat',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'weapon_system', 'type' => 'text'],
                        ['name' => 'delivery_method', 'type' => 'text'],
                        ['name' => 'target_type', 'type' => 'text'],
                    ],
                ]),
            ],

            // ===============================================
            // EQUIPMENT EVENTS (8 types)
            // ===============================================
            [
                'name' => 'Equipment Destroyed',
                'slug' => 'equipment-destroyed',
                'icon' => 'times-circle',
                'color' => '#991b1b',
                'description' => 'Military equipment confirmed destroyed beyond repair.',
                'category' => 'equipment',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'destruction_method', 'type' => 'select', 'options' => ['combat', 'airstrike', 'artillery', 'atgm', 'mine', 'drone', 'sabotage', 'unknown']],
                        ['name' => 'crew_fate', 'type' => 'select', 'options' => ['unknown', 'survived', 'killed', 'captured']],
                    ],
                ]),
            ],
            [
                'name' => 'Equipment Damaged',
                'slug' => 'equipment-damaged',
                'icon' => 'exclamation-triangle',
                'color' => '#c2410c',
                'description' => 'Military equipment damaged but potentially repairable.',
                'category' => 'equipment',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'damage_type', 'type' => 'select', 'options' => ['mobility_kill', 'firepower_kill', 'minor', 'major', 'unknown']],
                        ['name' => 'damage_source', 'type' => 'text'],
                        ['name' => 'repair_likelihood', 'type' => 'select', 'options' => ['likely', 'possible', 'unlikely', 'unknown']],
                    ],
                ]),
            ],
            [
                'name' => 'Equipment Captured',
                'slug' => 'equipment-captured',
                'icon' => 'hand-rock',
                'color' => '#15803d',
                'description' => 'Military equipment captured by opposing force.',
                'category' => 'equipment',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'condition', 'type' => 'select', 'options' => ['operational', 'damaged', 'unknown']],
                        ['name' => 'capture_circumstances', 'type' => 'textarea'],
                        ['name' => 'reuse_potential', 'type' => 'select', 'options' => ['likely', 'possible', 'unlikely', 'unknown']],
                    ],
                ]),
            ],
            [
                'name' => 'Equipment Abandoned',
                'slug' => 'equipment-abandoned',
                'icon' => 'door-open',
                'color' => '#a16207',
                'description' => 'Military equipment abandoned by operating force.',
                'category' => 'equipment',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'condition', 'type' => 'select', 'options' => ['operational', 'damaged', 'stuck', 'unknown']],
                        ['name' => 'abandonment_reason', 'type' => 'select', 'options' => ['retreat', 'mechanical_failure', 'fuel', 'combat_damage', 'unknown']],
                    ],
                ]),
            ],
            [
                'name' => 'Equipment Spotted',
                'slug' => 'equipment-spotted',
                'icon' => 'eye',
                'color' => '#2563eb',
                'description' => 'Military equipment observed in imagery or video without status change.',
                'category' => 'equipment',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'context', 'type' => 'select', 'options' => ['convoy', 'position', 'transit', 'training', 'parade', 'storage', 'other']],
                        ['name' => 'quantity', 'type' => 'number'],
                        ['name' => 'unit_identification', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'Equipment Transfer',
                'slug' => 'equipment-transfer',
                'icon' => 'exchange-alt',
                'color' => '#0891b2',
                'description' => 'Transfer of military equipment between parties or delivery of new equipment.',
                'category' => 'equipment',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'transfer_type', 'type' => 'select', 'options' => ['delivery', 'inter_unit', 'international_aid', 'sale', 'unknown']],
                        ['name' => 'source', 'type' => 'text'],
                        ['name' => 'recipient', 'type' => 'text'],
                        ['name' => 'quantity', 'type' => 'number'],
                    ],
                ]),
            ],
            [
                'name' => 'Equipment Modification',
                'slug' => 'equipment-modification',
                'icon' => 'wrench',
                'color' => '#6366f1',
                'description' => 'Observation of modified or improvised military equipment.',
                'category' => 'equipment',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'modification_type', 'type' => 'textarea'],
                        ['name' => 'purpose', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'First Observed Equipment',
                'slug' => 'first-observed-equipment',
                'icon' => 'star',
                'color' => '#a855f7',
                'description' => 'First documented observation of a new equipment type in the conflict.',
                'category' => 'equipment',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'significance', 'type' => 'textarea'],
                        ['name' => 'source_country', 'type' => 'text'],
                        ['name' => 'quantity_observed', 'type' => 'number'],
                    ],
                ]),
            ],

            // ===============================================
            // TERRITORIAL EVENTS (8 types)
            // ===============================================
            [
                'name' => 'Territorial Advance',
                'slug' => 'territorial-advance',
                'icon' => 'arrow-right',
                'color' => '#16a34a',
                'description' => 'Territorial gains by an advancing force.',
                'category' => 'territorial',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'advancing_force', 'type' => 'text'],
                        ['name' => 'area_gained_km2', 'type' => 'number'],
                        ['name' => 'settlements_captured', 'type' => 'textarea'],
                        ['name' => 'front_line_shift_km', 'type' => 'number'],
                    ],
                ]),
            ],
            [
                'name' => 'Territorial Retreat',
                'slug' => 'territorial-retreat',
                'icon' => 'arrow-left',
                'color' => '#dc2626',
                'description' => 'Territorial losses due to retreat or withdrawal.',
                'category' => 'territorial',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'retreating_force', 'type' => 'text'],
                        ['name' => 'area_lost_km2', 'type' => 'number'],
                        ['name' => 'settlements_lost', 'type' => 'textarea'],
                        ['name' => 'retreat_type', 'type' => 'select', 'options' => ['orderly', 'fighting', 'rout', 'unknown']],
                    ],
                ]),
            ],
            [
                'name' => 'Encirclement',
                'slug' => 'encirclement',
                'icon' => 'circle-notch',
                'color' => '#9333ea',
                'description' => 'Military encirclement of enemy forces or territory.',
                'category' => 'territorial',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'encircling_force', 'type' => 'text'],
                        ['name' => 'encircled_force', 'type' => 'text'],
                        ['name' => 'area_km2', 'type' => 'number'],
                        ['name' => 'estimated_personnel', 'type' => 'number'],
                        ['name' => 'supply_status', 'type' => 'select', 'options' => ['cut_off', 'limited', 'unknown']],
                    ],
                ]),
            ],
            [
                'name' => 'Breakout',
                'slug' => 'breakout',
                'icon' => 'sign-out-alt',
                'color' => '#0d9488',
                'description' => 'Military breakout from encirclement.',
                'category' => 'territorial',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'breaking_force', 'type' => 'text'],
                        ['name' => 'success_level', 'type' => 'select', 'options' => ['full', 'partial', 'failed', 'ongoing']],
                        ['name' => 'personnel_escaped', 'type' => 'number'],
                        ['name' => 'equipment_lost', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'Settlement Captured',
                'slug' => 'settlement-captured',
                'icon' => 'city',
                'color' => '#22c55e',
                'description' => 'Capture of a town, city, or village.',
                'category' => 'territorial',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'settlement_name', 'type' => 'text', 'required' => true],
                        ['name' => 'settlement_type', 'type' => 'select', 'options' => ['city', 'town', 'village', 'hamlet', 'other']],
                        ['name' => 'pre_war_population', 'type' => 'number'],
                        ['name' => 'strategic_significance', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'Position Established',
                'slug' => 'position-established',
                'icon' => 'fort-awesome',
                'color' => '#3b82f6',
                'description' => 'Establishment of a new defensive position or fortification.',
                'category' => 'territorial',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'position_type', 'type' => 'select', 'options' => ['trench', 'bunker', 'checkpoint', 'observation_post', 'artillery_position', 'other']],
                        ['name' => 'fortification_level', 'type' => 'select', 'options' => ['hasty', 'prepared', 'fortified', 'unknown']],
                    ],
                ]),
            ],
            [
                'name' => 'River Crossing',
                'slug' => 'river-crossing',
                'icon' => 'water',
                'color' => '#0ea5e9',
                'description' => 'Military river crossing operation.',
                'category' => 'territorial',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'river_name', 'type' => 'text'],
                        ['name' => 'crossing_method', 'type' => 'select', 'options' => ['bridge', 'pontoon', 'amphibious', 'boats', 'unknown']],
                        ['name' => 'bridgehead_established', 'type' => 'boolean'],
                        ['name' => 'crossing_force', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'Contested Zone',
                'slug' => 'contested-zone',
                'icon' => 'arrows-alt-h',
                'color' => '#eab308',
                'description' => 'Area where control is actively contested between parties.',
                'category' => 'territorial',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'parties_contesting', 'type' => 'text'],
                        ['name' => 'area_km2', 'type' => 'number'],
                        ['name' => 'combat_intensity', 'type' => 'select', 'options' => ['low', 'medium', 'high', 'intense']],
                    ],
                ]),
            ],

            // ===============================================
            // INFRASTRUCTURE EVENTS (8 types)
            // ===============================================
            [
                'name' => 'Infrastructure Damaged',
                'slug' => 'infrastructure-damaged',
                'icon' => 'building',
                'color' => '#f97316',
                'description' => 'Damage to civilian or military infrastructure.',
                'category' => 'infrastructure',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'infrastructure_type', 'type' => 'select', 'options' => ['power_plant', 'power_grid', 'water_treatment', 'bridge', 'road', 'railway', 'airport', 'port', 'hospital', 'school', 'government', 'industrial', 'residential', 'other']],
                        ['name' => 'damage_level', 'type' => 'select', 'options' => ['minor', 'moderate', 'severe', 'destroyed']],
                        ['name' => 'damage_source', 'type' => 'text'],
                        ['name' => 'service_impact', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'Infrastructure Destroyed',
                'slug' => 'infrastructure-destroyed',
                'icon' => 'building-circle-xmark',
                'color' => '#b91c1c',
                'description' => 'Complete destruction of infrastructure.',
                'category' => 'infrastructure',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'infrastructure_type', 'type' => 'select', 'options' => ['power_plant', 'power_grid', 'water_treatment', 'bridge', 'road', 'railway', 'airport', 'port', 'hospital', 'school', 'government', 'industrial', 'residential', 'other']],
                        ['name' => 'destruction_cause', 'type' => 'text'],
                        ['name' => 'affected_population', 'type' => 'number'],
                    ],
                ]),
            ],
            [
                'name' => 'Infrastructure Repaired',
                'slug' => 'infrastructure-repaired',
                'icon' => 'hammer',
                'color' => '#16a34a',
                'description' => 'Repair or restoration of damaged infrastructure.',
                'category' => 'infrastructure',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'infrastructure_type', 'type' => 'text'],
                        ['name' => 'repair_level', 'type' => 'select', 'options' => ['temporary', 'partial', 'full']],
                        ['name' => 'service_restored', 'type' => 'boolean'],
                    ],
                ]),
            ],
            [
                'name' => 'Bridge Destroyed',
                'slug' => 'bridge-destroyed',
                'icon' => 'road-bridge',
                'color' => '#7c2d12',
                'description' => 'Destruction of a bridge or crossing point.',
                'category' => 'infrastructure',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'bridge_name', 'type' => 'text'],
                        ['name' => 'bridge_type', 'type' => 'select', 'options' => ['road', 'rail', 'pedestrian', 'combined']],
                        ['name' => 'spans_river', 'type' => 'text'],
                        ['name' => 'strategic_importance', 'type' => 'textarea'],
                        ['name' => 'demolition_method', 'type' => 'select', 'options' => ['airstrike', 'artillery', 'demolition_charges', 'unknown']],
                    ],
                ]),
            ],
            [
                'name' => 'Power Outage',
                'slug' => 'power-outage',
                'icon' => 'plug-circle-xmark',
                'color' => '#475569',
                'description' => 'Loss of electrical power to an area.',
                'category' => 'infrastructure',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'outage_cause', 'type' => 'select', 'options' => ['attack', 'overload', 'maintenance', 'unknown']],
                        ['name' => 'affected_area', 'type' => 'textarea'],
                        ['name' => 'affected_population', 'type' => 'number'],
                        ['name' => 'duration_hours', 'type' => 'number'],
                    ],
                ]),
            ],
            [
                'name' => 'Communications Disrupted',
                'slug' => 'communications-disrupted',
                'icon' => 'tower-cell',
                'color' => '#6366f1',
                'description' => 'Disruption to communication networks or internet.',
                'category' => 'infrastructure',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'disruption_type', 'type' => 'select', 'options' => ['mobile_network', 'internet', 'landline', 'broadcast', 'multiple']],
                        ['name' => 'cause', 'type' => 'select', 'options' => ['attack', 'jamming', 'infrastructure_damage', 'unknown']],
                        ['name' => 'affected_area', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'Fuel/Supply Depot Hit',
                'slug' => 'supply-depot-hit',
                'icon' => 'gas-pump',
                'color' => '#ea580c',
                'description' => 'Attack on fuel storage, ammunition depot, or supply facility.',
                'category' => 'infrastructure',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'depot_type', 'type' => 'select', 'options' => ['fuel', 'ammunition', 'food', 'equipment', 'mixed', 'unknown']],
                        ['name' => 'damage_level', 'type' => 'select', 'options' => ['minor', 'moderate', 'severe', 'destroyed']],
                        ['name' => 'secondary_explosions', 'type' => 'boolean'],
                    ],
                ]),
            ],
            [
                'name' => 'Environmental Damage',
                'slug' => 'environmental-damage',
                'icon' => 'leaf',
                'color' => '#65a30d',
                'description' => 'Environmental damage including oil spills, fires, or contamination.',
                'category' => 'infrastructure',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'damage_type', 'type' => 'select', 'options' => ['oil_spill', 'chemical_contamination', 'forest_fire', 'crop_destruction', 'water_contamination', 'other']],
                        ['name' => 'affected_area_km2', 'type' => 'number'],
                        ['name' => 'environmental_impact', 'type' => 'textarea'],
                    ],
                ]),
            ],

            // ===============================================
            // HUMANITARIAN EVENTS (8 types)
            // ===============================================
            [
                'name' => 'Civilian Casualties',
                'slug' => 'civilian-casualties',
                'icon' => 'user-injured',
                'color' => '#be123c',
                'description' => 'Civilian deaths or injuries as a result of conflict.',
                'category' => 'humanitarian',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'killed', 'type' => 'number'],
                        ['name' => 'wounded', 'type' => 'number'],
                        ['name' => 'cause', 'type' => 'select', 'options' => ['airstrike', 'shelling', 'small_arms', 'ied', 'mine', 'unknown']],
                        ['name' => 'location_type', 'type' => 'select', 'options' => ['residential', 'market', 'hospital', 'school', 'evacuation', 'other']],
                    ],
                ]),
            ],
            [
                'name' => 'Displacement',
                'slug' => 'displacement',
                'icon' => 'person-walking-luggage',
                'color' => '#0284c7',
                'description' => 'Forced displacement of civilian population.',
                'category' => 'humanitarian',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'displacement_type', 'type' => 'select', 'options' => ['internal', 'cross_border', 'both']],
                        ['name' => 'estimated_people', 'type' => 'number'],
                        ['name' => 'origin', 'type' => 'text'],
                        ['name' => 'destination', 'type' => 'text'],
                        ['name' => 'cause', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'Humanitarian Aid Delivery',
                'slug' => 'humanitarian-aid',
                'icon' => 'hand-holding-heart',
                'color' => '#059669',
                'description' => 'Delivery of humanitarian assistance to affected populations.',
                'category' => 'humanitarian',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'aid_type', 'type' => 'multiselect', 'options' => ['food', 'water', 'medical', 'shelter', 'winter', 'other']],
                        ['name' => 'provider', 'type' => 'text'],
                        ['name' => 'beneficiaries', 'type' => 'number'],
                        ['name' => 'delivery_method', 'type' => 'select', 'options' => ['convoy', 'airdrop', 'local_distribution']],
                    ],
                ]),
            ],
            [
                'name' => 'Evacuation',
                'slug' => 'evacuation',
                'icon' => 'truck-ramp-box',
                'color' => '#0891b2',
                'description' => 'Organized evacuation of civilians from conflict zone.',
                'category' => 'humanitarian',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'evacuation_type', 'type' => 'select', 'options' => ['humanitarian_corridor', 'organized_convoy', 'individual', 'military']],
                        ['name' => 'evacuees', 'type' => 'number'],
                        ['name' => 'origin', 'type' => 'text'],
                        ['name' => 'destination', 'type' => 'text'],
                        ['name' => 'success', 'type' => 'select', 'options' => ['successful', 'partial', 'disrupted', 'failed']],
                    ],
                ]),
            ],
            [
                'name' => 'Prisoner Exchange',
                'slug' => 'prisoner-exchange',
                'icon' => 'people-arrows',
                'color' => '#8b5cf6',
                'description' => 'Exchange of prisoners between conflicting parties.',
                'category' => 'humanitarian',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'exchange_type', 'type' => 'select', 'options' => ['equal', 'all_for_all', 'negotiated']],
                        ['name' => 'party_a_released', 'type' => 'number'],
                        ['name' => 'party_b_released', 'type' => 'number'],
                        ['name' => 'mediator', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'Hospital/Medical Facility Attack',
                'slug' => 'hospital-attack',
                'icon' => 'hospital',
                'color' => '#dc2626',
                'description' => 'Attack on or damage to medical facilities.',
                'category' => 'humanitarian',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'facility_type', 'type' => 'select', 'options' => ['hospital', 'clinic', 'field_hospital', 'pharmacy', 'ambulance']],
                        ['name' => 'facility_name', 'type' => 'text'],
                        ['name' => 'damage_level', 'type' => 'select', 'options' => ['minor', 'moderate', 'severe', 'destroyed']],
                        ['name' => 'casualties', 'type' => 'number'],
                        ['name' => 'still_operational', 'type' => 'boolean'],
                    ],
                ]),
            ],
            [
                'name' => 'School Attack',
                'slug' => 'school-attack',
                'icon' => 'school',
                'color' => '#dc2626',
                'description' => 'Attack on or damage to educational facilities.',
                'category' => 'humanitarian',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'facility_type', 'type' => 'select', 'options' => ['kindergarten', 'primary_school', 'secondary_school', 'university', 'vocational']],
                        ['name' => 'facility_name', 'type' => 'text'],
                        ['name' => 'damage_level', 'type' => 'select', 'options' => ['minor', 'moderate', 'severe', 'destroyed']],
                        ['name' => 'casualties', 'type' => 'number'],
                        ['name' => 'in_session', 'type' => 'boolean'],
                    ],
                ]),
            ],
            [
                'name' => 'Mass Grave Discovered',
                'slug' => 'mass-grave',
                'icon' => 'cross',
                'color' => '#1e293b',
                'description' => 'Discovery of mass burial site.',
                'category' => 'humanitarian',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'estimated_bodies', 'type' => 'number'],
                        ['name' => 'victim_type', 'type' => 'select', 'options' => ['civilian', 'military', 'mixed', 'unknown']],
                        ['name' => 'cause_of_death', 'type' => 'text'],
                        ['name' => 'investigation_status', 'type' => 'select', 'options' => ['discovered', 'under_investigation', 'exhumed', 'documented']],
                    ],
                ]),
            ],

            // ===============================================
            // CYBER EVENTS (6 types)
            // ===============================================
            [
                'name' => 'Cyberattack',
                'slug' => 'cyberattack',
                'icon' => 'laptop-code',
                'color' => '#7c3aed',
                'description' => 'Cyberattack against government, military, or civilian infrastructure.',
                'category' => 'cyber',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'attack_type', 'type' => 'select', 'options' => ['ddos', 'ransomware', 'wiper', 'phishing', 'supply_chain', 'other']],
                        ['name' => 'target_sector', 'type' => 'select', 'options' => ['government', 'military', 'energy', 'financial', 'media', 'telecom', 'healthcare', 'other']],
                        ['name' => 'target_name', 'type' => 'text'],
                        ['name' => 'attributed_to', 'type' => 'text'],
                        ['name' => 'impact', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'Data Breach',
                'slug' => 'data-breach',
                'icon' => 'database',
                'color' => '#be185d',
                'description' => 'Unauthorized access to or theft of data.',
                'category' => 'cyber',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'target_organization', 'type' => 'text'],
                        ['name' => 'data_type', 'type' => 'multiselect', 'options' => ['personal', 'financial', 'military', 'government', 'corporate']],
                        ['name' => 'records_affected', 'type' => 'number'],
                        ['name' => 'data_published', 'type' => 'boolean'],
                    ],
                ]),
            ],
            [
                'name' => 'Service Outage',
                'slug' => 'service-outage',
                'icon' => 'server',
                'color' => '#ef4444',
                'description' => 'Disruption to online services or systems.',
                'category' => 'cyber',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'service_affected', 'type' => 'text'],
                        ['name' => 'cause', 'type' => 'select', 'options' => ['ddos', 'infrastructure_attack', 'technical', 'unknown']],
                        ['name' => 'duration_hours', 'type' => 'number'],
                        ['name' => 'geographic_scope', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'GPS/Satellite Interference',
                'slug' => 'gps-interference',
                'icon' => 'satellite-dish',
                'color' => '#0ea5e9',
                'description' => 'Jamming or spoofing of GPS or satellite communications.',
                'category' => 'cyber',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'interference_type', 'type' => 'select', 'options' => ['jamming', 'spoofing', 'unknown']],
                        ['name' => 'affected_systems', 'type' => 'multiselect', 'options' => ['gps', 'glonass', 'galileo', 'starlink', 'other']],
                        ['name' => 'affected_area', 'type' => 'textarea'],
                        ['name' => 'impact_on_operations', 'type' => 'textarea'],
                    ],
                ]),
            ],
            [
                'name' => 'Information Operation',
                'slug' => 'information-operation',
                'icon' => 'bullhorn',
                'color' => '#f59e0b',
                'description' => 'Coordinated information or influence operation.',
                'category' => 'cyber',
                'supports_media' => true,
                'supports_equipment' => false,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'operation_type', 'type' => 'select', 'options' => ['disinformation', 'propaganda', 'hack_and_leak', 'impersonation', 'other']],
                        ['name' => 'platforms_used', 'type' => 'multiselect', 'options' => ['twitter', 'facebook', 'telegram', 'youtube', 'tiktok', 'traditional_media', 'other']],
                        ['name' => 'narrative', 'type' => 'textarea'],
                        ['name' => 'attributed_to', 'type' => 'text'],
                        ['name' => 'reach_estimate', 'type' => 'text'],
                    ],
                ]),
            ],
            [
                'name' => 'Electronic Warfare',
                'slug' => 'electronic-warfare',
                'icon' => 'wave-square',
                'color' => '#6366f1',
                'description' => 'Electronic warfare activities including radar jamming or communications disruption.',
                'category' => 'cyber',
                'supports_media' => true,
                'supports_equipment' => true,
                'supports_sources' => true,
                'schema' => json_encode([
                    'fields' => [
                        ['name' => 'ew_type', 'type' => 'select', 'options' => ['radar_jamming', 'communications_jamming', 'drone_suppression', 'decoy', 'other']],
                        ['name' => 'affected_systems', 'type' => 'textarea'],
                        ['name' => 'ew_system_identified', 'type' => 'text'],
                        ['name' => 'operational_impact', 'type' => 'textarea'],
                    ],
                ]),
            ],
        ];

        foreach ($eventTypes as $eventType) {
            $sortOrder++;

            // Extract description and category for separate handling
            $description = $eventType['description'] ?? null;
            $category = $eventType['category'] ?? null;
            unset($eventType['description'], $eventType['category']);

            // Add the description to the schema as a comment field
            if ($description) {
                $schema = json_decode($eventType['schema'], true);
                $schema['description'] = $description;
                $schema['category'] = $category;
                $eventType['schema'] = json_encode($schema);
            }

            DB::table('event_types')->insert([
                ...$eventType,
                'is_active' => true,
                'sort_order' => $sortOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
