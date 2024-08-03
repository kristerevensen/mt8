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
        Schema::create('campaign_link_clicks', function (Blueprint $table) {
            $table->bigIncrements('id'); // Auto-incrementing ID
            $table->string('user_agent')->nullable(); // User agent string
            $table->string('referrer')->nullable(); // Referrer URL
            $table->string('ip')->nullable(); // IP address
            $table->string('platform')->nullable(); // Platform or OS
            $table->string('browser')->nullable(); // Browser name and version
            $table->string('device_type')->nullable(); // Device type (desktop, mobile, tablet)
            $table->string('screen_resolution')->nullable(); // Screen resolution
            $table->string('language')->nullable(); // Preferred language
            $table->string('session_id')->nullable(); // Session ID
            $table->string('link_token'); // Link token from campaign_links
            $table->timestamps(); // Timestamps for created_at and updated_at

            // Foreign key constraint for link_token referencing campaign_links.link_token
            $table->foreign('link_token')->references('link_token')->on('campaign_links')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_link_clicks');
    }
};
