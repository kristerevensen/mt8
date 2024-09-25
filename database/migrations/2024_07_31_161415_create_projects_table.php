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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('project_name');
            $table->string('project_domain');
            $table->string('project_language');
            $table->string('project_country');
            $table->string('project_category');
            $table->string('project_location_code');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('team_id');
            $table->timestamps();

            // Define foreign keys
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            //set foreign key for location_code,slik at prosjektet IKKE blir slettet eller settes til null, hvis lokasjonen fjernes
            $table->foreign('project_location_code')->references('location_code')->on('locations')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
