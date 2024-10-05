<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCriteriaSolutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('criteria_solutions', function (Blueprint $table) {
            $table->id();
            $table->string('solution_name');
            $table->text('solution_description')->nullable();
            $table->string('criteria_uuid');
            $table->string('project_code')->index();
            $table->timestamps();

            // Foreign key to the optimization_criteria table
            $table->foreign('criteria_uuid')->references('criteria_uuid')->on('optimization_criteria')->onDelete('cascade');
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
        Schema::dropIfExists('criteria_solutions');
    }
}
