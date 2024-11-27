<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('language_location', function (Blueprint $table) {
            $table->id();
            $table->string('location_code');
            $table->string('location_name');
            $table->string('location_code_parent')->nullable();
            $table->string('country_iso_code');
            $table->string('location_type');
            $table->json('available_sources')->nullable(); // Dette er et JSON-felt for kilder som er tilgjengelige
            $table->string('language_name');
            $table->string('language_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language_location');
    }
}
