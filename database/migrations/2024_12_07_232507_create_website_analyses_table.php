<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('website_analyses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('project_code');
            $table->string('url');
            $table->integer('location_code')->nullable();
            $table->string('language_code', 10)->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('mobile_screenshot_path')->nullable();
            $table->json('analysis_data')->nullable();
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->integer('domain_rank')->nullable();
            $table->timestamp('last_visited')->nullable();
            $table->string('content_language')->nullable();
            $table->timestamps();

            $table->foreign('project_code')
                  ->references('project_code')
                  ->on('projects')
                  ->onDelete('cascade');
        });

        // Create analysis_tasks table to track individual tasks
        Schema::create('analysis_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_analysis_id')->constrained('website_analyses')->onDelete('cascade');
            $table->string('task_type'); // screenshot, technology, competitor, seo, etc.
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Add index for faster lookups
            $table->index(['website_analysis_id', 'task_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('analysis_tasks');
        Schema::dropIfExists('website_analyses');
    }
};
