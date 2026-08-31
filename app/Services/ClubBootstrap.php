<?php

namespace App\Services;

use App\Models\Community;
use App\Models\Connection;
use App\Models\Event;
use App\Models\Interest;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class ClubBootstrap
{
    /**
     * @return array{admin: User, created: bool, password_set: bool, community: Community, events: Collection<int, Event>}
     */
    public function run(string $name, string $email, ?string $password, bool $withDemoMates = true): array
    {
        $this->interests();

        $admin = User::query()->firstOrNew(['email' => $email]);
        $created = ! $admin->exists;
        $passwordSet = false;

        $admin->fill([
            'name' => $name,
            'suburb' => $admin->suburb ?: 'Ponsonby',
            'bio' => $admin->bio ?: 'Building Auckland M8s. Usually down for football or a pint.',
        ]);
        $admin->email_verified_at ??= now();
        $admin->is_admin = true;

        if ($created && ! filled($password)) {
            throw new \InvalidArgumentException('A password is required to create the admin account.');
        }

        if ($created || filled($password)) {
            $admin->password = Hash::make((string) $password);
            $passwordSet = true;
        }

        $admin->save();

        $community = Community::query()->firstOrCreate(
            ['slug' => 'auckland-m8s'],
            [
                'owner_id' => $admin->id,
                'name' => 'Auckland M8s',
                'city' => 'Auckland',
                'tagline' => 'Meet people. Do stuff. Make mates.',
                'description' => 'A club for guys in Auckland. Curated nights, venue booked, teams mixed. Solo or with mates.',
                'primary_color' => '#1E2C2A',
                'accent_color' => '#C45C26',
            ],
        );

        $community->addMember($admin, 'organizer');

        $mates = $withDemoMates ? $this->demoMates($community) : collect();
        $all = collect([$admin, ...$mates]);

        $events = $this->events($community, $admin);

        if ($withDemoMates) {
            $this->rsvps($events, $all, $admin, $mates);
        }

        return [
            'admin' => $admin,
            'created' => $created,
            'password_set' => $passwordSet,
            'community' => $community,
            'events' => $events,
        ];
    }

    private function interests(): void
    {
        collect([
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
        ])->each(fn (array $interest) => Interest::query()->firstOrCreate(
            ['slug' => $interest[1]],
            ['name' => $interest[0], 'emoji' => $interest[2]],
        ));
    }

    /**
     * @return Collection<int, User>
     */
    private function demoMates(Community $community): Collection
    {
        $interests = Interest::query()->pluck('id');

        return collect([
            ['James', 'james@aklm8s.nz', 27, 'Grey Lynn', 'jamesakl'],
            ['Michael', 'michael@aklm8s.nz', 29, 'Mt Eden', 'mikeclimbs'],
            ['Chris', 'chris@aklm8s.nz', 26, 'Newmarket', 'chrisgolf'],
            ['Tyler', 'tyler@aklm8s.nz', 24, 'CBD', 'tylr'],
            ['Sam', 'sam@aklm8s.nz', 32, 'Takapuna', 'samruns'],
            ['Noah', 'noah@aklm8s.nz', 28, 'Kingsland', 'noahfood'],
            ['Luca', 'luca@aklm8s.nz', 25, 'Parnell', 'lucaboards'],
            ['Matt', 'matt@aklm8s.nz', 30, 'Epsom', 'mattfooty'],
            ['Alex', 'alex@aklm8s.nz', 31, 'Ponsonby', 'alexwalks'],
            ['Ben', 'ben@aklm8s.nz', 28, 'Kingsland', 'benhoops'],
            ['Jordan', 'jordan@aklm8s.nz', 26, 'Mt Eden', 'jordakl'],
        ])->map(function (array $mate) use ($community, $interests) {
            $user = User::query()->firstOrCreate(
                ['email' => $mate[1]],
                [
                    'name' => $mate[0],
                    'password' => Hash::make('password'),
                    'age' => $mate[2],
                    'suburb' => $mate[3],
                    'instagram' => $mate[4],
                    'email_verified_at' => now(),
                    'is_admin' => false,
                ],
            );

            if ($user->interests()->doesntExist() && $interests->isNotEmpty()) {
                $user->interests()->sync($interests->shuffle()->take(rand(3, 6))->all());
            }

            $community->addMember($user);

            return $user;
        });
    }

    /**
     * @return Collection<int, Event>
     */
    private function events(Community $community, User $admin): Collection
    {
        $now = Carbon::now('Pacific/Auckland');

        $catalog = [
            [
                'slug' => 'friday-football',
                'series' => 'football',
                'title' => 'Friday Football',
                'emoji' => '⚽',
                'description' => 'Twenty guys, one pitch, teams mixed. Most come solo. Mates welcome too.',
                'starts_at' => $now->copy()->next('Friday')->setTime(18, 30),
                'ends_at' => $now->copy()->next('Friday')->setTime(20, 0),
                'venue_name' => 'Mt Eden 5-a-side',
                'venue_address' => '32 Normanby Rd, Mt Eden',
                'suburb' => 'Mt Eden',
                'capacity' => 20,
                'price_cents' => 1500,
                'venue_cost_cents' => 18000,
                'host_cost_cents' => 2000,
                'other_cost_cents' => 0,
                'cost_notes' => 'Pitch hire + balls',
            ],
            [
                'slug' => 'bowling-night',
                'series' => 'bowling',
                'title' => 'Bowling Night',
                'emoji' => '🎳',
                'description' => 'Lanes booked, shoes included. Easy first night if sport is not your thing.',
                'starts_at' => $now->copy()->next('Wednesday')->setTime(19, 0),
                'ends_at' => $now->copy()->next('Wednesday')->setTime(21, 0),
                'venue_name' => 'Woodrow Studios',
                'venue_address' => '17 Putiki St, Grey Lynn',
                'suburb' => 'Grey Lynn',
                'capacity' => 16,
                'price_cents' => 2500,
                'venue_cost_cents' => 28000,
                'host_cost_cents' => 0,
                'other_cost_cents' => 4000,
                'cost_notes' => 'Lanes + shoes',
            ],
            [
                'slug' => 'sunday-hoops',
                'series' => 'hoops',
                'title' => 'Sunday Hoops',
                'emoji' => '🏀',
                'description' => 'Casual run at Victoria Park. Mixed ability, short games, names on the sideline so nobody is left standing around.',
                'starts_at' => $now->copy()->next('Sunday')->setTime(10, 0),
                'ends_at' => $now->copy()->next('Sunday')->setTime(11, 30),
                'venue_name' => 'Victoria Park courts',
                'venue_address' => 'Victoria St West',
                'suburb' => 'Freemans Bay',
                'capacity' => 16,
                'price_cents' => 0,
                'venue_cost_cents' => 0,
                'host_cost_cents' => 0,
                'other_cost_cents' => 0,
                'cost_notes' => 'Free public courts',
            ],
            [
                'slug' => 'pints-and-pool',
                'series' => 'pool',
                'title' => 'Pints & Pool',
                'emoji' => '🎱',
                'description' => 'A table booked in Ponsonby. Arrive, grab a pint, get put on a table with two people you do not know yet.',
                'starts_at' => $now->copy()->addDays(8)->next('Tuesday')->setTime(19, 30),
                'ends_at' => $now->copy()->addDays(8)->next('Tuesday')->setTime(22, 0),
                'venue_name' => 'The Bedford',
                'venue_address' => 'Ponsonby Rd',
                'suburb' => 'Ponsonby',
                'capacity' => 18,
                'price_cents' => 1000,
                'venue_cost_cents' => 0,
                'host_cost_cents' => 5000,
                'other_cost_cents' => 3000,
                'cost_notes' => 'Table hold + first round float',
            ],
            [
                'slug' => 'friday-bar-night',
                'series' => 'bar',
                'title' => 'Friday Bar Night',
                'emoji' => '🍻',
                'description' => 'Corner booked on K Road, ice-breakers, then pub quiz. Mixed tables on purpose.',
                'starts_at' => $now->copy()->addWeek()->next('Friday')->setTime(19, 30),
                'ends_at' => $now->copy()->addWeek()->next('Friday')->setTime(22, 0),
                'venue_name' => 'The Whiskey',
                'venue_address' => '210 Karangahape Rd',
                'suburb' => 'K Road',
                'capacity' => 24,
                'price_cents' => 1000,
                'venue_cost_cents' => 0,
                'host_cost_cents' => 6000,
                'other_cost_cents' => 4000,
                'cost_notes' => 'Quiz + welcome drinks',
            ],
            [
                'slug' => 'mtg-night',
                'series' => 'mtg',
                'title' => 'MTG Night',
                'emoji' => '🃏',
                'description' => 'Commander and casual drafts at a central table. Borrow a deck if you need one.',
                'starts_at' => $now->copy()->next('Thursday')->setTime(19, 0),
                'ends_at' => $now->copy()->next('Thursday')->setTime(22, 0),
                'venue_name' => 'Counter Culture',
                'venue_address' => 'Victoria St West',
                'suburb' => 'Auckland CBD',
                'capacity' => 14,
                'price_cents' => 1000,
                'venue_cost_cents' => 0,
                'host_cost_cents' => 3000,
                'other_cost_cents' => 2000,
                'cost_notes' => 'Table hire + prize cards',
            ],
            [
                'slug' => 'trivia-night',
                'series' => 'trivia',
                'title' => 'Trivia Night',
                'emoji' => '🧠',
                'description' => 'Pub quiz teams assigned on the night. Good if you want a low-key first one.',
                'starts_at' => $now->copy()->next('Tuesday')->setTime(19, 30),
                'ends_at' => $now->copy()->next('Tuesday')->setTime(22, 0),
                'venue_name' => 'The Birdcage',
                'venue_address' => 'Fort St',
                'suburb' => 'Auckland CBD',
                'capacity' => 24,
                'price_cents' => 1000,
                'venue_cost_cents' => 0,
                'host_cost_cents' => 4000,
                'other_cost_cents' => 2000,
                'cost_notes' => 'Table + quiz host',
            ],
            [
                'slug' => 'first-football-night',
                'series' => 'football',
                'title' => 'First Football Night',
                'emoji' => '⚽',
                'description' => 'The night Auckland M8s started. Most of the room came solo.',
                'starts_at' => $now->copy()->subDays(10)->setTime(18, 30),
                'ends_at' => $now->copy()->subDays(10)->setTime(20, 0),
                'venue_name' => 'Mt Eden 5-a-side',
                'venue_address' => '32 Normanby Rd, Mt Eden',
                'suburb' => 'Mt Eden',
                'capacity' => 20,
                'price_cents' => 1500,
                'venue_cost_cents' => 18000,
                'host_cost_cents' => 2000,
                'other_cost_cents' => 0,
                'cost_notes' => 'Pitch hire + balls',
            ],
        ];

        return collect($catalog)->map(function (array $event) use ($community, $admin) {
            return Event::query()->updateOrCreate(
                [
                    'community_id' => $community->id,
                    'slug' => $event['slug'],
                ],
                [
                    'organizer_id' => $admin->id,
                    'series' => $event['series'] ?? null,
                    'title' => $event['title'],
                    'emoji' => $event['emoji'],
                    'description' => $event['description'],
                    'starts_at' => $event['starts_at'],
                    'ends_at' => $event['ends_at'],
                    'venue_name' => $event['venue_name'],
                    'venue_address' => $event['venue_address'],
                    'suburb' => $event['suburb'],
                    'capacity' => $event['capacity'],
                    'price_cents' => $event['price_cents'],
                    'venue_cost_cents' => $event['venue_cost_cents'],
                    'host_cost_cents' => $event['host_cost_cents'],
                    'other_cost_cents' => $event['other_cost_cents'],
                    'cost_notes' => $event['cost_notes'],
                    'status' => Event::STATUS_PUBLISHED,
                ],
            );
        });
    }

    /**
     * @param  Collection<int, Event>  $events
     * @param  Collection<int, User>  $all
     * @param  Collection<int, User>  $mates
     */
    private function rsvps(Collection $events, Collection $all, User $admin, Collection $mates): void
    {
        $bySlug = $events->keyBy('slug');

        $this->fillRsvps($bySlug->get('friday-football'), $all->take(10), Rsvp::STATUS_CONFIRMED, 1500);
        $this->fillRsvps($bySlug->get('friday-football'), $all->slice(10, 2), Rsvp::STATUS_WAITLISTED, 0);
        $this->fillRsvps($bySlug->get('bowling-night'), $all->take(6), Rsvp::STATUS_CONFIRMED, 2500);
        $this->fillRsvps($bySlug->get('sunday-hoops'), $all->take(9), Rsvp::STATUS_CONFIRMED, 0);
        $this->fillRsvps($bySlug->get('pints-and-pool'), $all->take(5), Rsvp::STATUS_CONFIRMED, 1000);
        $this->fillRsvps($bySlug->get('friday-bar-night'), $all->take(8), Rsvp::STATUS_CONFIRMED, 1000);
        $this->fillRsvps($bySlug->get('mtg-night'), $all->take(5), Rsvp::STATUS_CONFIRMED, 1000);
        $this->fillRsvps($bySlug->get('trivia-night'), $all->take(7), Rsvp::STATUS_CONFIRMED, 1000);
        $this->fillRsvps($bySlug->get('first-football-night'), $all, Rsvp::STATUS_ATTENDED, 1500);

        $past = $bySlug->get('first-football-night');
        $mate = $mates->first();

        if ($past && $mate && $admin->isNot($mate)) {
            Connection::query()->firstOrCreate(
                ['user_id' => $admin->id, 'mate_id' => $mate->id],
                ['event_id' => $past->id, 'contact_shared_at' => now()->subDays(9)],
            );
            Connection::query()->firstOrCreate(
                ['user_id' => $mate->id, 'mate_id' => $admin->id],
                ['event_id' => $past->id, 'contact_shared_at' => now()->subDays(9)],
            );
        }
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function fillRsvps(?Event $event, Collection $users, string $status, int $amount): void
    {
        if (! $event || $users->isEmpty()) {
            return;
        }

        foreach ($users->values() as $index => $user) {
            Rsvp::query()->firstOrCreate(
                [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                ],
                [
                    'status' => $status,
                    'party_size' => $index % 5 === 0 ? 2 : 1,
                    'amount_paid_cents' => $amount,
                    'platform_fee_cents' => (int) round($amount * 0.10),
                    'paid_at' => $status === Rsvp::STATUS_WAITLISTED ? null : now()->subDays(2),
                ],
            );
        }
    }
}
