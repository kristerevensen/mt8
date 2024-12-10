<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('website_historical_ranks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('project_code');
            $table->foreignId('website_analysis_id')->constrained('website_analyses')->onDelete('cascade');
            
            // Time period
            $table->integer('year');
            $table->integer('month');
            
            // Organic metrics
            $table->integer('organic_pos_1')->nullable();
            $table->integer('organic_pos_2_3')->nullable();
            $table->integer('organic_pos_4_10')->nullable();
            $table->integer('organic_pos_11_20')->nullable();
            $table->integer('organic_pos_21_30')->nullable();
            $table->integer('organic_pos_31_40')->nullable();
            $table->integer('organic_pos_41_50')->nullable();
            $table->integer('organic_pos_51_60')->nullable();
            $table->integer('organic_pos_61_70')->nullable();
            $table->integer('organic_pos_71_80')->nullable();
            $table->integer('organic_pos_81_90')->nullable();
            $table->integer('organic_pos_91_100')->nullable();
            $table->decimal('organic_etv', 20, 2)->nullable();
            $table->decimal('organic_impressions_etv', 20, 2)->nullable();
            $table->integer('organic_count')->nullable();
            $table->decimal('organic_estimated_paid_traffic_cost', 20, 2)->nullable();
            
            // Change metrics
            $table->integer('organic_new')->nullable();
            $table->integer('organic_up')->nullable();
            $table->integer('organic_down')->nullable();
            $table->integer('organic_lost')->nullable();
            
            // Paid metrics
            $table->integer('paid_pos_1')->nullable();
            $table->integer('paid_pos_2_3')->nullable();
            $table->integer('paid_pos_4_10')->nullable();
            $table->decimal('paid_etv', 20, 2)->nullable();
            $table->decimal('paid_impressions_etv', 20, 2)->nullable();
            $table->integer('paid_count')->nullable();
            $table->decimal('paid_estimated_traffic_cost', 20, 2)->nullable();
            
            // Change metrics for paid
            $table->integer('paid_new')->nullable();
            $table->integer('paid_up')->nullable();
            $table->integer('paid_down')->nullable();
            $table->integer('paid_lost')->nullable();

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

    public function down()
    {
        Schema::dropIfExists('website_historical_ranks');
    }
};
