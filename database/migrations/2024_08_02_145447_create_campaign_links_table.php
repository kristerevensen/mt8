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
        Schema::create('campaign_links', function (Blueprint $table) {
            $table->bigIncrements('id'); // Auto-incrementing ID
            $table->timestamps(); // Timestamps for created_at and updated_at
            $table->text('landing_page'); // Landing page URL
            $table->string('link_token')->unique(); // Unique link token
            $table->string('project_code'); // Foreign key to projects table
            $table->string('source')->nullable(); // Source for UTM tracking
            $table->string('medium')->nullable(); // Medium for UTM tracking
            $table->string('term')->nullable(); // Term for UTM tracking
            $table->string('content')->nullable(); // Content for UTM tracking
            $table->string('target')->nullable(); // Target URL
            $table->text('tagged_url')->nullable(); // Tagged URL with UTM parameters
            $table->unsignedBigInteger('campaign_id'); // Foreign key to campaigns table
            $table->text('custom_parameters')->nullable(); // Custom parameters for tracking
            $table->text('description')->nullable(); // Description of the link

            // Foreign key constraints
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_links');
    }
};
