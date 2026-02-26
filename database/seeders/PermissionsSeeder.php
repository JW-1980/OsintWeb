<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Events permissions
            ['name' => 'View Events', 'slug' => 'events.view', 'group' => 'events', 'description' => 'Can view published events', 'is_system' => true],
            ['name' => 'View Draft Events', 'slug' => 'events.view-drafts', 'group' => 'events', 'description' => 'Can view draft/unpublished events', 'is_system' => true],
            ['name' => 'Create Events', 'slug' => 'events.create', 'group' => 'events', 'description' => 'Can create new events', 'is_system' => true],
            ['name' => 'Edit Events', 'slug' => 'events.edit', 'group' => 'events', 'description' => 'Can edit own events', 'is_system' => true],
            ['name' => 'Edit All Events', 'slug' => 'events.edit-all', 'group' => 'events', 'description' => 'Can edit any event', 'is_system' => true],
            ['name' => 'Delete Events', 'slug' => 'events.delete', 'group' => 'events', 'description' => 'Can delete own events', 'is_system' => true],
            ['name' => 'Delete All Events', 'slug' => 'events.delete-all', 'group' => 'events', 'description' => 'Can delete any event', 'is_system' => true],
            ['name' => 'Verify Events', 'slug' => 'events.verify', 'group' => 'events', 'description' => 'Can verify events', 'is_system' => true],
            ['name' => 'Approve Events', 'slug' => 'events.approve', 'group' => 'events', 'description' => 'Can approve pending events for publication', 'is_system' => true],
            ['name' => 'Publish Events', 'slug' => 'events.publish', 'group' => 'events', 'description' => 'Can publish events directly', 'is_system' => true],
            ['name' => 'Schedule Events', 'slug' => 'events.schedule', 'group' => 'events', 'description' => 'Can schedule events for future publication', 'is_system' => true],
            ['name' => 'Manage Event Access', 'slug' => 'events.manage-access', 'group' => 'events', 'description' => 'Can manage who has access to events', 'is_system' => true],

            // Equipment permissions
            ['name' => 'View Equipment', 'slug' => 'equipment.view', 'group' => 'equipment', 'description' => 'Can view military equipment', 'is_system' => true],
            ['name' => 'Create Equipment', 'slug' => 'equipment.create', 'group' => 'equipment', 'description' => 'Can add new military equipment', 'is_system' => true],
            ['name' => 'Edit Equipment', 'slug' => 'equipment.edit', 'group' => 'equipment', 'description' => 'Can edit military equipment', 'is_system' => true],
            ['name' => 'Delete Equipment', 'slug' => 'equipment.delete', 'group' => 'equipment', 'description' => 'Can delete military equipment', 'is_system' => true],
            ['name' => 'Verify Equipment', 'slug' => 'equipment.verify', 'group' => 'equipment', 'description' => 'Can verify equipment entries', 'is_system' => true],
            ['name' => 'Manage Equipment Properties', 'slug' => 'equipment.manage-properties', 'group' => 'equipment', 'description' => 'Can manage equipment properties', 'is_system' => true],
            ['name' => 'Manage Equipment Media', 'slug' => 'equipment.manage-media', 'group' => 'equipment', 'description' => 'Can add/remove images, videos, links', 'is_system' => true],
            ['name' => 'Rollback Equipment Versions', 'slug' => 'equipment.rollback', 'group' => 'equipment', 'description' => 'Can rollback equipment properties to previous versions', 'is_system' => true],

            // Actors permissions
            ['name' => 'View Actors', 'slug' => 'actors.view', 'group' => 'actors', 'description' => 'Can view actors/groups', 'is_system' => true],
            ['name' => 'Create Actors', 'slug' => 'actors.create', 'group' => 'actors', 'description' => 'Can create new actors', 'is_system' => true],
            ['name' => 'Edit Actors', 'slug' => 'actors.edit', 'group' => 'actors', 'description' => 'Can edit actors', 'is_system' => true],
            ['name' => 'Delete Actors', 'slug' => 'actors.delete', 'group' => 'actors', 'description' => 'Can delete actors', 'is_system' => true],

            // Control Zones permissions
            ['name' => 'View Zones', 'slug' => 'zones.view', 'group' => 'zones', 'description' => 'Can view control zones', 'is_system' => true],
            ['name' => 'Create Zones', 'slug' => 'zones.create', 'group' => 'zones', 'description' => 'Can create control zones', 'is_system' => true],
            ['name' => 'Edit Zones', 'slug' => 'zones.edit', 'group' => 'zones', 'description' => 'Can edit control zones', 'is_system' => true],
            ['name' => 'Delete Zones', 'slug' => 'zones.delete', 'group' => 'zones', 'description' => 'Can delete control zones', 'is_system' => true],

            // Users permissions
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users', 'description' => 'Can view user profiles', 'is_system' => true],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group' => 'users', 'description' => 'Can create/edit/delete users', 'is_system' => true],
            ['name' => 'Assign Roles', 'slug' => 'users.assign-roles', 'group' => 'users', 'description' => 'Can assign roles to users', 'is_system' => true],
            ['name' => 'Manage User Groups', 'slug' => 'users.manage-groups', 'group' => 'users', 'description' => 'Can manage user groups', 'is_system' => true],

            // Roles and Permissions
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'roles', 'description' => 'Can view roles', 'is_system' => true],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'group' => 'roles', 'description' => 'Can create/edit/delete roles', 'is_system' => true],
            ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'group' => 'roles', 'description' => 'Can assign permissions to roles', 'is_system' => true],

            // Export permissions
            ['name' => 'Export Data', 'slug' => 'export.data', 'group' => 'export', 'description' => 'Can export data (CSV, GeoJSON, KML)', 'is_system' => true],
            ['name' => 'Export Bulk', 'slug' => 'export.bulk', 'group' => 'export', 'description' => 'Can perform bulk exports', 'is_system' => true],

            // Statistics permissions
            ['name' => 'View Statistics', 'slug' => 'stats.view', 'group' => 'stats', 'description' => 'Can view statistics and analytics', 'is_system' => true],
            ['name' => 'View Advanced Analytics', 'slug' => 'stats.advanced', 'group' => 'stats', 'description' => 'Can view advanced analytics', 'is_system' => true],

            // Audit permissions
            ['name' => 'View Audit Logs', 'slug' => 'audit.view', 'group' => 'audit', 'description' => 'Can view audit logs', 'is_system' => true],
            ['name' => 'Export Audit Logs', 'slug' => 'audit.export', 'group' => 'audit', 'description' => 'Can export audit logs', 'is_system' => true],

            // Settings permissions
            ['name' => 'View Settings', 'slug' => 'settings.view', 'group' => 'settings', 'description' => 'Can view application settings', 'is_system' => true],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'group' => 'settings', 'description' => 'Can modify application settings', 'is_system' => true],

            // API permissions
            ['name' => 'API Access', 'slug' => 'api.access', 'group' => 'api', 'description' => 'Can access the API', 'is_system' => true],
            ['name' => 'API Full Access', 'slug' => 'api.full-access', 'group' => 'api', 'description' => 'Can access all API endpoints without restrictions', 'is_system' => true],
        ];

        $now = now();

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                ...$permission,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Create roles
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full system access with all permissions',
                'color' => '#DC2626',
                'priority' => 100,
                'is_system' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Moderator',
                'slug' => 'moderator',
                'description' => 'Can moderate content, verify events, and manage users',
                'color' => '#2563EB',
                'priority' => 80,
                'is_system' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Can create, edit, and publish events and equipment',
                'color' => '#059669',
                'priority' => 60,
                'is_system' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Analyst',
                'slug' => 'analyst',
                'description' => 'Can create and edit own content, view analytics',
                'color' => '#7C3AED',
                'priority' => 40,
                'is_system' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Basic read access to published content',
                'color' => '#6B7280',
                'priority' => 10,
                'is_system' => true,
                'is_default' => true,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insertOrIgnore([
                ...$role,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Assign permissions to roles
        $rolePermissions = [
            'admin' => [], // Admin has all permissions through bypass, no need to assign
            'moderator' => [
                'events.view', 'events.view-drafts', 'events.create', 'events.edit', 'events.edit-all',
                'events.delete', 'events.verify', 'events.approve', 'events.publish', 'events.schedule',
                'events.manage-access',
                'equipment.view', 'equipment.create', 'equipment.edit', 'equipment.verify',
                'equipment.manage-properties', 'equipment.manage-media',
                'actors.view', 'actors.create', 'actors.edit',
                'zones.view', 'zones.create', 'zones.edit',
                'users.view', 'users.manage', 'users.manage-groups',
                'export.data', 'stats.view', 'stats.advanced', 'audit.view',
                'api.access',
            ],
            'editor' => [
                'events.view', 'events.view-drafts', 'events.create', 'events.edit',
                'events.delete', 'events.publish', 'events.schedule',
                'equipment.view', 'equipment.create', 'equipment.edit',
                'equipment.manage-properties', 'equipment.manage-media',
                'actors.view', 'actors.create', 'actors.edit',
                'zones.view', 'zones.create', 'zones.edit',
                'export.data', 'stats.view',
                'api.access',
            ],
            'analyst' => [
                'events.view', 'events.view-drafts', 'events.create', 'events.edit', 'events.delete',
                'equipment.view', 'equipment.manage-properties',
                'actors.view',
                'zones.view',
                'export.data', 'stats.view', 'stats.advanced',
                'api.access',
            ],
            'viewer' => [
                'events.view',
                'equipment.view',
                'actors.view',
                'zones.view',
                'stats.view',
                'api.access',
            ],
        ];

        foreach ($rolePermissions as $roleSlug => $permissionSlugs) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');

            foreach ($permissionSlugs as $permissionSlug) {
                $permissionId = DB::table('permissions')->where('slug', $permissionSlug)->value('id');

                if ($roleId && $permissionId) {
                    DB::table('role_permission')->insertOrIgnore([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
