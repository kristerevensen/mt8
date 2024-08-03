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
        Schema::create('data_pages', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('url'); // URL of the data page
            $table->string('url_code')->index(); // Unique code for the URL, indexed for faster search
            $table->string('event_type')->index(); // Event type, indexed for faster search
            $table->string('title')->nullable(); // Title of the data page, nullable
            $table->string('referrer')->nullable(); // Referrer of the data page, nullable
            $table->string('device_type')->nullable(); // Device type, nullable
            $table->integer('session_start')->nullable(); // Session start time, nullable
            $table->string('project_code'); // Project code, foreign key to projects table
            $table->string('session_id')->nullable(); // Session ID, nullable
            $table->string('hostname')->nullable(); // Hostname, nullable
            $table->string('protocol')->nullable(); // Protocol, nullable
            $table->string('pathname')->nullable(); // Pathname, nullable
            $table->string('language')->nullable(); // Language, nullable
            $table->integer('bounce')->default(0); // Bounce count, default to 0
            $table->integer('entrance')->default(0); // Entrance count, default to 0
            $table->integer('exits')->default(0); // Exit count, default to 0
            $table->text('meta_description')->nullable(); // Meta description, nullable
            $table->boolean('cookie_enabled')->nullable(); // Whether cookies are enabled, nullable
            $table->integer('screen_width')->nullable(); // Screen width, nullable
            $table->integer('screen_height')->nullable(); // Screen height, nullable
            $table->integer('history_length')->nullable(); // History length, nullable
            $table->integer('word_count')->nullable(); // Word count, nullable
            $table->integer('form_count')->nullable(); // Form count, nullable
            $table->text('inbound_links')->nullable(); // Inbound links, nullable
            $table->text('outbound_links')->nullable(); // Outbound links, nullable
            $table->text('content_hash')->nullable(); // Content hash, nullable
            $table->text('page_content')->nullable(); // Page content, nullable
            $table->boolean('analyzed')->default(0); // Whether the page has been analyzed, default to 0
            $table->timestamp('analyzed_at')->nullable(); // Time when the page was analyzed, nullable
            $table->timestamps(); // Created at and updated at timestamps

            // Foreign key constraint referencing projects table
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_pages');
    }
};
