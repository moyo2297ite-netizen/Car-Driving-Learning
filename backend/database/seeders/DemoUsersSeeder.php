<?php

namespace Database\Seeders;

use App\Enums\TransmissionType;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@drivingschool.test'],
            [
                'name' => 'المدير العام',
                'phone' => '0999000001',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'supervisor@drivingschool.test'],
            [
                'name' => 'المشرف',
                'phone' => '0999000002',
                'password' => Hash::make('password'),
                'role' => UserRole::Supervisor,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'instructor@drivingschool.test'],
            [
                'name' => 'المدرب',
                'phone' => '0999000003',
                'password' => Hash::make('password'),
                'role' => UserRole::Instructor,
                'teaches_manual' => true,
                'teaches_automatic' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'student@drivingschool.test'],
            [
                'name' => 'الطالب',
                'phone' => '0999000004',
                'password' => Hash::make('password'),
                'role' => UserRole::Student,
                'transmission_type' => TransmissionType::Automatic,
            ],
        );
    }
}
