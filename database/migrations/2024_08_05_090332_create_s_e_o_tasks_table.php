<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeoTasksTable extends Migration
{
    public function up()
    {
        Schema::create('seo_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('project_code'); // Bruker project_code som utenlandsk nøkkel
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade');
            $table->string('location_name');
            $table->string('task_id')->nullable();
            $table->string('target');
            $table->string('tag')->nullable();
            $table->string('pingback_url')->nullable();
            $table->string('postback_url')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->json('result')->nullable(); // For storing API results
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('seo_tasks');
    }
}
