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
            $table->uuid('keyword_uuid')->primary(); // Unique ID for keyword
            $table->string('keyword')->unique(); // The actual keyword
            // legge til keyword list id, som også er foreign key til keyword_lists
            $table->uuid('list_uuid'); // Foreign key to keyword lists
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

            //lag foreign key men ikke constraint til keyword_lists, slik at keyword ikke blir slettet, men bare fjerner id, hvis listen fjernes
            $table->foreign('list_uuid')->references('list_uuid')->on('keyword_lists')->onDelete('set null');
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
