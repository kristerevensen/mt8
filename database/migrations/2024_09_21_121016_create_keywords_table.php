<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->uuid('keyword_uuid')->primary(); // Unique UUID for keyword
            $table->string('keyword'); // The actual keyword
            $table->uuid('list_uuid')->nullable(); // Foreign key to keyword lists, set as nullable for "on delete set null"
            $table->string('project_code'); // Project code that owns the keyword
            $table->string('spell')->nullable(); // Correct spelling if available
            $table->integer('location_code')->nullable(); // Location code from DataForSEO
            $table->string('language_code')->nullable(); // Language code
            $table->boolean('search_partners')->nullable(); // Search partners included
            $table->string('competition')->nullable(); // Competition level (High, Medium, Low)
            $table->integer('competition_index')->nullable(); // Competition index (0-100)
            $table->integer('search_volume')->nullable(); // Average search volume
            $table->decimal('low_top_of_page_bid', 10, 2)->nullable(); // Low bid CPC
            $table->decimal('high_top_of_page_bid', 10, 2)->nullable(); // High bid CPC
            $table->decimal('cpc', 10, 2)->nullable(); // Cost per click
            $table->timestamp('analyzed_at')->nullable(); // When the keyword was analyzed
            $table->timestamps(); // Created and updated timestamps

            // Foreign key constraint, with "on delete set null"
            $table->foreign('list_uuid')->references('list_uuid')->on('keyword_lists')->onDelete('set null');

            // Add a unique constraint for keyword and project_code combination
            $table->unique(['keyword', 'project_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keywords');
    }
}
