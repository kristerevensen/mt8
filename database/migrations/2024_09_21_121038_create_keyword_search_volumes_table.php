<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeywordSearchVolumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keyword_search_volumes', function (Blueprint $table) {
            $table->id();
            $table->uuid('keyword_uuid'); // Foreign key to keywords
            $table->string('project_code'); // Project code that owns the keyword
            $table->integer('year'); // Year for the search volume data
            $table->integer('month'); // Month for the search volume data
            $table->integer('search_volume')->nullable(); // Search volume for the month
            $table->timestamps(); // Created and updated timestamps

            // Foreign key constraints to ensure data integrity
            $table->foreign('keyword_uuid')->references('keyword_uuid')->on('keywords')->onDelete('cascade'); // Cascade on delete
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade'); // Cascade on delete

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keyword_search_volumes');
    }
}
