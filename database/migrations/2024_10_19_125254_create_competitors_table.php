<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->string('uuid'); // UUID
            $table->string('target_domain'); // The domain we are analyzing competitors for
            $table->string('competitor_domain'); // The competitor's domain
            $table->string('se_type'); // Search engine type, e.g., 'google'
            $table->integer('location_code'); // Location code
            $table->string('language_code'); // Language code
            $table->decimal('avg_position', 8, 2); // Average position in the SERP
            $table->integer('sum_position'); // Sum of positions
            $table->integer('intersections'); // Number of intersections
            $table->json('full_domain_metrics')->nullable(); // Full domain metrics as JSON
            $table->json('metrics')->nullable(); // Metrics as JSON
            $table->json('competitor_metrics')->nullable(); // Competitor-specific metrics as JSON
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
        Schema::dropIfExists('competitors');
    }
}
