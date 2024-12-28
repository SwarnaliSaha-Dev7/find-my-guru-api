<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('country_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('city_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->timestamps();
        });

        Schema::create('currency', function (Blueprint $table) {
            $table->id();
            $table->char('code', 5)->nullable();
            $table->string('symbol', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('duration', function (Blueprint $table) {
            $table->id();
            $table->enum('unit', ['days', 'weeks', 'months', 'years'])->default('days');
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('time_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_name', 50);
            $table->string('unit_abbr', 50);
            $table->string('unit_plural', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('qualifications');
        Schema::dropIfExists('currency');
        Schema::dropIfExists('duration');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('time_units');
    }
};
