<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Site administrators with full access to all features'
            ],
            [
                'name' => 'Creator',
                'slug' => 'creator',
                'description' => 'Crossword creators with access to the dashboard'
            ],
            [
                'name' => 'Solver',
                'slug' => 'solver',
                'description' => 'Regular users who can solve crosswords and participate in competitions'
            ]
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        // Create permissions
        $permissions = [
            // User management permissions
            [
                'name' => 'View Users',
                'slug' => 'users.view',
                'description' => 'Can view all users'
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users.create',
                'description' => 'Can create new users'
            ],
            [
                'name' => 'Edit Users',
                'slug' => 'users.edit',
                'description' => 'Can edit user details'
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users.delete',
                'description' => 'Can delete users'
            ],
            [
                'name' => 'Manage Roles',
                'slug' => 'roles.manage',
                'description' => 'Can manage user roles'
            ],

            // Crossword management permissions
            [
                'name' => 'View All Crosswords',
                'slug' => 'crosswords.view.all',
                'description' => 'Can view all crosswords including unpublished ones'
            ],
            [
                'name' => 'Create Crosswords',
                'slug' => 'crosswords.create',
                'description' => 'Can create new crosswords'
            ],
            [
                'name' => 'Edit Any Crossword',
                'slug' => 'crosswords.edit.any',
                'description' => 'Can edit any crossword'
            ],
            [
                'name' => 'Edit Own Crosswords',
                'slug' => 'crosswords.edit.own',
                'description' => 'Can edit own crosswords'
            ],
            [
                'name' => 'Delete Any Crossword',
                'slug' => 'crosswords.delete.any',
                'description' => 'Can delete any crossword'
            ],
            [
                'name' => 'Delete Own Crosswords',
                'slug' => 'crosswords.delete.own',
                'description' => 'Can delete own crosswords'
            ],
            [
                'name' => 'Publish Crosswords',
                'slug' => 'crosswords.publish',
                'description' => 'Can publish crosswords'
            ],

            // Competition management permissions
            [
                'name' => 'View All Competitions',
                'slug' => 'competitions.view.all',
                'description' => 'Can view all competitions including inactive ones'
            ],
            [
                'name' => 'Create Competitions',
                'slug' => 'competitions.create',
                'description' => 'Can create new competitions'
            ],
            [
                'name' => 'Edit Any Competition',
                'slug' => 'competitions.edit.any',
                'description' => 'Can edit any competition'
            ],
            [
                'name' => 'Edit Own Competitions',
                'slug' => 'competitions.edit.own',
                'description' => 'Can edit own competitions'
            ],
            [
                'name' => 'Delete Any Competition',
                'slug' => 'competitions.delete.any',
                'description' => 'Can delete any competition'
            ],
            [
                'name' => 'Delete Own Competitions',
                'slug' => 'competitions.delete.own',
                'description' => 'Can delete own competitions'
            ],
            [
                'name' => 'Terminate Competitions',
                'slug' => 'competitions.terminate',
                'description' => 'Can terminate competitions early'
            ],

            // Dashboard access
            [
                'name' => 'Access Dashboard',
                'slug' => 'dashboard.access',
                'description' => 'Can access the dashboard'
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        $adminRole = Role::where('slug', 'administrator')->first();
        $creatorRole = Role::where('slug', 'creator')->first();
        $solverRole = Role::where('slug', 'solver')->first();

        $adminRole->permissions()->attach(Permission::all());

        $creatorPermissions = [
            'dashboard.access',
            'crosswords.view.all',
            'crosswords.create',
            'crosswords.edit.own',
            'crosswords.delete.own',
            'crosswords.publish',
            'competitions.view.all',
            'competitions.create',
            'competitions.edit.own',
            'competitions.delete.own',
            'competitions.terminate',
        ];

        $creatorRole->permissions()->attach(
            Permission::whereIn('slug', $creatorPermissions)->get()
        );
    }
}
