<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptimizationCriteriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('optimization_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('criteria_name');
            $table->text('criteria_description')->nullable();
            $table->string('criteria_uuid')->unique();
            $table->string('category_uuid')->index(); // Legg til category_uuid
            $table->string('project_code')->index();
            $table->timestamps();

            // Foreign keys
            $table->foreign('category_uuid')->references('category_uuid')->on('optimization_categories')->onDelete('cascade');
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('optimization_criteria');
    }
}
