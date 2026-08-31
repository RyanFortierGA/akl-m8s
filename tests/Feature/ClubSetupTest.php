<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\AucklandClubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_command_creates_admin_and_sample_nights(): void
    {
        $this->artisan('m8s:setup', [
            '--email' => 'ryan@aklm8s.nz',
            '--password' => 'secret-pass',
            '--name' => 'Ryan',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'ryan@aklm8s.nz',
            'is_admin' => true,
        ]);

        $this->assertGreaterThanOrEqual(5, Event::query()->count());

        $this->post(route('login.store'), [
            'email' => 'ryan@aklm8s.nz',
            'password' => 'secret-pass',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_seeder_can_run_twice_without_duplicating_events(): void
    {
        $this->seed(AucklandClubSeeder::class);
        $this->seed(AucklandClubSeeder::class);

        $this->assertSame(1, User::query()->where('email', 'ryan@aklm8s.nz')->count());
        $this->assertSame(9, Event::query()->count());
    }
}
