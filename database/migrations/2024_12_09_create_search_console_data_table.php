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
        Schema::create('search_console_data', function (Blueprint $table) {
            $table->id();
            $table->string('project_code');
            $table->string('url');
            $table->string('type')->default('page'); // page, query, device
            $table->string('dimension_value'); // URL for pages, keyword for queries, device type for devices
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->float('ctr', 8, 4)->default(0);
            $table->float('position', 8, 2)->default(0);
            $table->date('date');
            $table->timestamps();

            $table->foreign('project_code')
                  ->references('project_code')
                  ->on('projects')
                  ->onDelete('cascade');

            $table->index(['project_code', 'date', 'type']);
            $table->index(['url', 'date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_console_data');
    }
};
