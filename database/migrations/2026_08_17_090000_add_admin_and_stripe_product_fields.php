<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('remember_token');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('price_cents');
            $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
            $table->string('stripe_product_name')->nullable()->after('stripe_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['stripe_product_id', 'stripe_price_id', 'stripe_product_name']);
        });
    }
};
