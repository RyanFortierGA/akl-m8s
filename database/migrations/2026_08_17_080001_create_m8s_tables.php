<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('emoji', 16);
            $table->timestamps();
        });

        Schema::create('interest_user', function (Blueprint $table) {
            $table->foreignId('interest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['interest_id', 'user_id']);
        });

        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('primary_color')->default('#0B1520');
            $table->string('accent_color')->default('#D8FF2E');
            $table->unsignedTinyInteger('platform_fee_percent')->default(10);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('community_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->unique(['community_id', 'user_id']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('emoji', 16)->nullable();
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('venue_name');
            $table->string('venue_address')->nullable();
            $table->string('suburb')->nullable();
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('status')->default('published');
            $table->timestamps();

            $table->unique(['community_id', 'slug']);
        });

        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending_payment');
            $table->unsignedTinyInteger('party_size')->default(1);
            $table->unsignedInteger('amount_paid_cents')->default(0);
            $table->unsignedInteger('platform_fee_cents')->default(0);
            $table->string('stripe_checkout_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });

        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('contact_shared_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'mate_id']);
        });

        Schema::create('event_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('event_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->boolean('would_hang_again')->default(true);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reviews');
        Schema::dropIfExists('event_messages');
        Schema::dropIfExists('connections');
        Schema::dropIfExists('rsvps');
        Schema::dropIfExists('events');
        Schema::dropIfExists('community_user');
        Schema::dropIfExists('communities');
        Schema::dropIfExists('interest_user');
        Schema::dropIfExists('interests');
    }
};
