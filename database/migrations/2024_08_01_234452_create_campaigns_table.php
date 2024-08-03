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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->string('campaign_name'); // Campaign name (required)
            $table->string('project_code'); // Foreign key to projects table
            $table->string('campaign_token')->unique(); // Unique 8-character token
            $table->foreignId('created_by')->constrained('users'); // Foreign key to users table
            $table->dateTime('start')->nullable(); // Start date (nullable)
            $table->dateTime('end')->nullable(); // End date (nullable)
            $table->boolean('status')->default(0); // Status (boolean, default inactive)
            $table->boolean('reporting')->default(0); // Reporting (boolean, default inactive)
            $table->boolean('force_lowercase')->default(0); // Force lowercase (boolean, default inactive)
            $table->boolean('utm_activated')->default(0); // UTM activated (boolean, default inactive)
            $table->boolean('monitor_urls')->default(0); // Monitor URLs (boolean, default inactive)
            $table->text('description')->nullable(); // Description (nullable)
            $table->timestamps(); // Timestamps for created_at and updated_at

            // Foreign key constraint for project_code referencing projects.project_code
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
