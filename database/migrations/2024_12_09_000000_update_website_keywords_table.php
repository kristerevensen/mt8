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
        Schema::table('website_keywords', function (Blueprint $table) {
            $table->string('uuid')->after('id');
            $table->string('project_code')->after('uuid');
            $table->unsignedBigInteger('website_analysis_id')->after('project_code');
            $table->integer('location_code')->after('website_analysis_id');
            $table->string('language_code')->after('location_code');
            $table->string('keyword')->after('language_code');
            $table->string('se_type')->default('google')->after('keyword');
            $table->integer('rank_group')->nullable()->after('se_type');
            $table->integer('rank_absolute')->nullable()->after('rank_group');
            $table->string('position')->nullable()->after('rank_absolute');
            $table->string('type')->nullable()->after('position');
            $table->boolean('is_featured_snippet')->default(false)->after('type');
            $table->integer('search_volume')->nullable()->after('is_featured_snippet');
            $table->decimal('competition', 5, 4)->nullable()->after('search_volume');
            $table->string('competition_level')->nullable()->after('competition');
            $table->decimal('cpc', 10, 2)->nullable()->after('competition_level');
            $table->json('monthly_searches')->nullable()->after('cpc');
            $table->decimal('traffic_cost', 10, 2)->nullable()->after('monthly_searches');
            
            // Add foreign key constraints
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade');
            $table->foreign('website_analysis_id')->references('id')->on('website_analyses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website_keywords', function (Blueprint $table) {
            $table->dropForeign(['project_code']);
            $table->dropForeign(['website_analysis_id']);
            
            $table->dropColumn([
                'uuid',
                'project_code',
                'website_analysis_id',
                'location_code',
                'language_code',
                'keyword',
                'se_type',
                'rank_group',
                'rank_absolute',
                'position',
                'type',
                'is_featured_snippet',
                'search_volume',
                'competition',
                'competition_level',
                'cpc',
                'monthly_searches',
                'traffic_cost'
            ]);
        });
    }
};
