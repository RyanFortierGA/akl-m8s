<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('email');
            $table->string('suburb')->nullable()->after('age');
            $table->string('instagram')->nullable()->after('suburb');
            $table->string('phone')->nullable()->after('instagram');
            $table->text('bio')->nullable()->after('phone');
            $table->string('contact_token', 32)->nullable()->unique()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['age', 'suburb', 'instagram', 'phone', 'bio', 'contact_token']);
        });
    }
};
