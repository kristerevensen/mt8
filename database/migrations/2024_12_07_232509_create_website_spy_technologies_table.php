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
        Schema::create('website_spy_technologies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('project_code');
            $table->string('name');
            $table->string('version')->nullable();
            $table->string('icon')->nullable();
            $table->string('website')->nullable();
            $table->string('cpe')->nullable();
            $table->json('categories')->nullable();
            $table->foreignId('website_analysis_id')->constrained('website_analyses')->onDelete('cascade');
            $table->timestamps();

            $table->foreign('uuid')
                ->references('uuid')
                ->on('website_analyses')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_spy_technologies');
    }
};
