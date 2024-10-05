<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptimizationCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('optimization_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('category_uuid')->unique();
            $table->string('project_code')->index();
            $table->timestamps();

            // Foreign key to the projects table
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
        Schema::dropIfExists('optimization_categories');
    }
}
