<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminWaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_real_waitlist_excluding_demo_accounts(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'ryan@aklm8s.nz']);
        User::factory()->create(['email' => 'james@aklm8s.nz', 'name' => 'James']);
        User::factory()->create(['email' => 'real@gmail.com', 'name' => 'Real Guy', 'suburb' => 'Ponsonby']);
        User::factory()->create(['email' => 'another@outlook.com', 'name' => 'Another']);

        $this->actingAs($admin)
            ->get(route('admin.waitlist'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/Waitlist')
                ->where('stats.total', 2)
                ->has('entries', 2)
                ->where('entries', fn ($entries) => collect($entries)->pluck('email')->sort()->values()->all() === [
                    'another@outlook.com',
                    'real@gmail.com',
                ]));
    }

    public function test_members_cannot_view_admin_waitlist(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('admin.waitlist'))
            ->assertForbidden();
    }
}
