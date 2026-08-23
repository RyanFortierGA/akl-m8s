<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('venue_cost_cents')->default(0)->after('price_cents');
            $table->unsignedInteger('host_cost_cents')->default(0)->after('venue_cost_cents');
            $table->unsignedInteger('other_cost_cents')->default(0)->after('host_cost_cents');
            $table->text('cost_notes')->nullable()->after('other_cost_cents');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'venue_cost_cents',
                'host_cost_cents',
                'other_cost_cents',
                'cost_notes',
            ]);
        });
    }
};
