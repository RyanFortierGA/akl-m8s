# Auckland M8s

A club for making mates through real-life events. Run Auckland first. White-label for other cities comes later.

## Stack

Laravel 13, Vue 3, Inertia, Tailwind, SQLite, Stripe Checkout.

## Run it locally

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

## Seed the admin account on Forge

SSH into the site (Forge → the site → Commands, or SSH as `forge` into the site folder), then:

```bash
php artisan m8s:setup --email=you@aklm8s.nz --password='pick-a-strong-one'
```

That creates your admin user, the Auckland M8s club, and sample nights you can edit in `/admin`. It is safe to run again — it will not wipe nights you have already edited.

If you already ran migrations and just want the demo data:

```bash
php artisan db:seed --force
```

Default seed login is `ryan@aklm8s.nz` / `password` unless you set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in the Forge environment.

Then go to `https://your-domain/login`. Admins land on `/admin`.

## Admin login (local)

Password for both: `password`

- Admin: `ryan@aklm8s.nz` — logs in at `/login`, then lands on `/admin`
- Member: `james@aklm8s.nz`

From `/admin` you can create events, attach a Stripe product, and see signup / waitlist counts.

## Stripe products

1. In Stripe Dashboard, create a Product and a one-time Price (e.g. Friday Football $15 NZD).
2. Add `STRIPE_SECRET` to `.env`.
3. Edit the event in admin and pick that product from the dropdown, or paste a `price_...` id.

Checkout charges that Price. Without Stripe keys, events still work with a NZD price and local checkout.

## What is in this version

- Curated events with capacity, waitlists, and “coming alone” social proof
- Admin desk: create events, plug in Stripe products, count signups and waitlists
- Ticket checkout (Stripe catalog Price or local)
- Event chat for confirmed attendees
- After the night: who did you meet, mates list, QR/contact card

## What to do this week

Sell 20 spots at $15. If those guys ask when the next night is, keep going.
