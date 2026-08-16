# Auckland M8s

A club for making mates through real-life events. Run Auckland first. White-label for other cities comes later.

## Stack

Laravel 13, Vue 3, Inertia, Tailwind, SQLite, Stripe Checkout.

## Run it

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
composer dev
```

Then open [http://localhost:8000](http://localhost:8000)

## Admin login

Password for both: `password`

- Admin: `ryan@aklm8s.nz` — logs in at `/login`, then lands on `/admin`
- Member: `james@aklm8s.nz`

From `/admin` you can create events, attach a Stripe product, and see signup / waitlist counts.

## Stripe products

1. In Stripe Dashboard, create a Product and a one-time Price (e.g. Friday Football $15 NZD).
2. Add `STRIPE_SECRET` to `.env`.
3. Edit the event in admin and pick that product from the dropdown, or paste a `price_...` id.

Checkout charges that Price. Without Stripe keys, events still work with a NZD price and local checkout.

## First nights

The seed already has Auckland M8s plus football, bowling, and a bar night.

## What is in this version

- Curated events with capacity, waitlists, and “coming alone” social proof
- Admin desk: create events, plug in Stripe products, count signups and waitlists
- Ticket checkout (Stripe catalog Price or local)
- Event chat for confirmed attendees
- After the night: who did you meet, mates list, QR/contact card

## What to do this week

Sell 20 spots at $15. If those guys ask when the next night is, keep going.
