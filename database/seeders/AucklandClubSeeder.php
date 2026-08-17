<?php

namespace Database\Seeders;

use App\Services\ClubBootstrap;
use Illuminate\Database\Seeder;

class AucklandClubSeeder extends Seeder
{
    public function run(ClubBootstrap $club): void
    {
        $club->run(
            name: (string) env('ADMIN_NAME', 'Ryan'),
            email: (string) env('ADMIN_EMAIL', 'ryan@aklm8s.nz'),
            password: env('ADMIN_PASSWORD') ?: 'password',
            withDemoMates: true,
        );
    }
}
