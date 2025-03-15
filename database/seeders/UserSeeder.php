<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                "name"=> "Alisher",
                "email"=> "admin@gmail.com",
                "password"=> Hash::make("Admin123"),
                "active" => true
            ],
            [
                "name" => "Creator",
                "email" => "creator@gmail.com",
                "password"=> Hash::make("Creator123"),
                "active"=> true
            ],
            [
                "name" => "Solver",
                "email" => "solver@gmail.com",
                "password"=> Hash::make("Solver123"),
                "active"=> true
            ]
        ];

        DB::beginTransaction();
        try {
            foreach ($users as $key => $user) {
                $user = User::query()->create($user);
                UserProfile::query()->create([
                    'user_id' => $user->id
                ]);

                if ($key === 0) {
                    $adminRole = Role::where('slug', 'administrator')->first();

                    if ($adminRole) {
                        $user->roles()->attach($adminRole->id);
                    }
                }

                if ($key === 1) {
                    $creatorRole = Role::where('slug', 'creator')->first();

                    if ($creatorRole) {
                        $user->roles()->attach($creatorRole->id);
                    }
                }

                if ($key === 2) {
                    $solverRole = Role::where('slug', 'solver')->first();

                    if ($solverRole) {
                        $user->roles()->attach($solverRole->id);
                    }
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
