<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('website_spy_metadata', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('project_code');
            $table->foreignId('website_analysis_id')->constrained('website_analyses')->onDelete('cascade');
            
            // SEO Data
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            // Contact Information
            $table->json('phone_numbers')->nullable();
            $table->json('email_addresses')->nullable();
            
            // Social Media
            $table->json('social_media_urls')->nullable();
            
            // Domain Info
            $table->string('country_iso_code')->nullable();
            $table->string('language_code')->nullable();
            
            $table->timestamps();

            $table->foreign('uuid')
                ->references('uuid')
                ->on('website_analyses')
                ->onDelete('cascade');
            
            $table->foreign('project_code')
                ->references('project_code')
                ->on('projects')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_spy_metadata');
    }
};
