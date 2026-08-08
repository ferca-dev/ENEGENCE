<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estados', function (Blueprint $table) {
            $table->string('abbreviation', 10)->nullable()->after('name');
            $table->unsignedBigInteger('female_population')->nullable()->after('total_population');
            $table->unsignedBigInteger('male_population')->nullable()->after('female_population');
            $table->unsignedBigInteger('inhabited_dwellings')->nullable()->after('male_population');
        });
    }

    public function down(): void
    {
        Schema::table('estados', function (Blueprint $table) {
            $table->dropColumn([
                'abbreviation',
                'female_population',
                'male_population',
                'inhabited_dwellings',
            ]);
        });
    }
};
