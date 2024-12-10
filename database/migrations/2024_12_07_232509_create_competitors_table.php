<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('project_code');
            $table->string('target_domain');
            $table->string('competitor_domain');
            $table->string('se_type')->default('google');
            $table->string('location_code');
            $table->string('language_code');
            $table->decimal('avg_position', 10, 2);
            $table->integer('sum_position');
            $table->integer('intersections');
            $table->json('full_domain_metrics')->nullable();
            $table->json('metrics')->nullable();
            $table->json('competitor_metrics')->nullable();
            $table->foreignId('website_analysis_id')->constrained('website_analyses')->onDelete('cascade');
            $table->timestamps();

            // SEO metrics
            $table->decimal('metrics_organic_etv', 10, 2)->nullable();
            $table->integer('metrics_organic_count')->nullable();
            $table->integer('metrics_organic_pos_1')->nullable();
            $table->integer('metrics_organic_pos_2_3')->nullable();
            $table->integer('metrics_organic_pos_4_10')->nullable();
            $table->decimal('metrics_organic_impressions_etv', 10, 2)->nullable();
            $table->decimal('metrics_organic_estimated_cost', 10, 2)->nullable();
            $table->decimal('metrics_paid_etv', 10, 2)->nullable();
            $table->integer('metrics_paid_count')->nullable();
            $table->decimal('metrics_paid_impressions_etv', 10, 2)->nullable();
            $table->decimal('metrics_paid_estimated_cost', 10, 2)->nullable();

            $table->foreign('uuid')
                ->references('uuid')
                ->on('website_analyses')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('competitors');
    }
};
