<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Connection;
use App\Models\Event;
use App\Models\Interest;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $interests = collect([
            ['Football', 'football', '⚽'],
            ['Basketball', 'basketball', '🏀'],
            ['Golf', 'golf', '⛳'],
            ['Gaming', 'gaming', '🎮'],
            ['Cars', 'cars', '🏎️'],
            ['Fitness', 'fitness', '🏋️'],
            ['Bars', 'bars', '🍻'],
            ['Food', 'food', '🍔'],
            ['Board games', 'board-games', '🎲'],
            ['Movies', 'movies', '🎬'],
            ['Hiking', 'hiking', '🥾'],
            ['Pool', 'pool', '🎱'],
            ['Bowling', 'bowling', '🎳'],
            ['Climbing', 'climbing', '🧗'],
        ])->map(fn (array $interest) => Interest::query()->create([
            'name' => $interest[0],
            'slug' => $interest[1],
            'emoji' => $interest[2],
        ]));

        $ryan = User::query()->create([
            'name' => 'Ryan',
            'email' => 'ryan@aklm8s.nz',
            'password' => Hash::make('password'),
            'age' => 31,
            'suburb' => 'Ponsonby',
            'instagram' => 'ryanm8s',
            'phone' => '021 000 0001',
            'bio' => 'Building Auckland M8s. Usually down for football or a pint.',
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $mates = collect([
            ['James', 'james@aklm8s.nz', 27, 'Grey Lynn', 'jamesakl'],
            ['Michael', 'michael@aklm8s.nz', 29, 'Mt Eden', 'mikeclimbs'],
            ['Chris', 'chris@aklm8s.nz', 26, 'Newmarket', 'chrisgolf'],
            ['Tyler', 'tyler@aklm8s.nz', 24, 'CBD', 'tylr'],
            ['Sam', 'sam@aklm8s.nz', 32, 'Takapuna', 'samruns'],
            ['Noah', 'noah@aklm8s.nz', 28, 'Kingsland', 'noahfood'],
            ['Luca', 'luca@aklm8s.nz', 25, 'Parnell', 'lucaboards'],
            ['Matt', 'matt@aklm8s.nz', 30, 'Epsom', 'mattfooty'],
        ])->map(fn (array $mate) => User::query()->create([
            'name' => $mate[0],
            'email' => $mate[1],
            'password' => Hash::make('password'),
            'age' => $mate[2],
            'suburb' => $mate[3],
            'instagram' => $mate[4],
            'email_verified_at' => now(),
        ]));

        $all = collect([$ryan, ...$mates]);

        $all->each(fn (User $user) => $user->interests()->sync(
            $interests->random(rand(3, 6))->pluck('id')->all()
        ));

        $community = Community::query()->create([
            'owner_id' => $ryan->id,
            'name' => 'Auckland M8s',
            'slug' => 'auckland-m8s',
            'city' => 'Auckland',
            'tagline' => 'Meet people. Do stuff. Make mates.',
            'description' => 'A club for guys in Auckland who want a reliable way to make new friends. Structured nights, no existing group required, and you will not be the only person coming alone.',
        ]);

        $all->each(fn (User $user) => $community->addMember($user, $user->is($ryan) ? 'organizer' : 'member'));

        $football = Event::query()->create([
            'community_id' => $community->id,
            'organizer_id' => $ryan->id,
            'title' => 'Friday Football',
            'slug' => 'friday-football',
            'emoji' => '⚽',
            'description' => '20 guys. One 5-a-side pitch. Zero existing friend groups. Come alone — most people are.',
            'starts_at' => now()->next('Friday')->setTime(18, 30),
            'ends_at' => now()->next('Friday')->setTime(20, 0),
            'venue_name' => 'Mt Eden 5-a-side',
            'venue_address' => '32 Normanby Rd, Mt Eden',
            'suburb' => 'Mt Eden',
            'capacity' => 20,
            'price_cents' => 1500,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $bowling = Event::query()->create([
            'community_id' => $community->id,
            'organizer_id' => $ryan->id,
            'title' => 'Bowling Night',
            'slug' => 'bowling-night',
            'emoji' => '🎳',
            'description' => 'Lanes booked. Shoes included. Good for anyone who is not into sport but still wants an easy first night.',
            'starts_at' => now()->next('Wednesday')->setTime(19, 0),
            'ends_at' => now()->next('Wednesday')->setTime(21, 0),
            'venue_name' => 'Woodrow Studios',
            'venue_address' => '17 Putiki St, Grey Lynn',
            'suburb' => 'Grey Lynn',
            'capacity' => 16,
            'price_cents' => 2500,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $bar = Event::query()->create([
            'community_id' => $community->id,
            'organizer_id' => $ryan->id,
            'title' => 'Friday Bar Night',
            'slug' => 'friday-bar-night',
            'emoji' => '🍻',
            'description' => 'A table booked, a few ice-breakers, and a pub quiz round. The whole point is that nobody already knows each other.',
            'starts_at' => now()->addWeek()->next('Friday')->setTime(19, 30),
            'ends_at' => now()->addWeek()->next('Friday')->setTime(22, 0),
            'venue_name' => 'The Whiskey',
            'venue_address' => '210 Karangahape Rd',
            'suburb' => 'K Road',
            'capacity' => 30,
            'price_cents' => 1000,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $past = Event::query()->create([
            'community_id' => $community->id,
            'organizer_id' => $ryan->id,
            'title' => 'First Football Night',
            'slug' => 'first-football-night',
            'emoji' => '⚽',
            'description' => 'The night Auckland M8s started. Most of these guys came alone.',
            'starts_at' => now()->subDays(10)->setTime(18, 30),
            'ends_at' => now()->subDays(10)->setTime(20, 0),
            'venue_name' => 'Mt Eden 5-a-side',
            'venue_address' => '32 Normanby Rd, Mt Eden',
            'suburb' => 'Mt Eden',
            'capacity' => 20,
            'price_cents' => 1500,
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $this->rsvp($football, $all->take(14), Rsvp::STATUS_CONFIRMED, 1500);
        $this->rsvp($bowling, $all->take(7), Rsvp::STATUS_CONFIRMED, 2500);
        $this->rsvp($bar, $all->take(11), Rsvp::STATUS_CONFIRMED, 1000);
        $this->rsvp($past, $all, Rsvp::STATUS_ATTENDED, 1500);

        Connection::query()->create([
            'user_id' => $ryan->id,
            'mate_id' => $mates[0]->id,
            'event_id' => $past->id,
            'contact_shared_at' => now()->subDays(9),
        ]);

        Connection::query()->create([
            'user_id' => $mates[0]->id,
            'mate_id' => $ryan->id,
            'event_id' => $past->id,
            'contact_shared_at' => now()->subDays(9),
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function rsvp(Event $event, $users, string $status, int $amount): void
    {
        foreach ($users as $index => $user) {
            Rsvp::query()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => $status,
                'party_size' => $index % 5 === 0 ? 2 : 1,
                'amount_paid_cents' => $amount,
                'platform_fee_cents' => (int) round($amount * 0.10),
                'paid_at' => now()->subDays(2),
            ]);
        }
    }
}
