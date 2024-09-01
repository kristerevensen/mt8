<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('goal_uuid')->index();
            $table->string('page_url');
            $table->string('referrer')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();

            // Foreign key constraint if goal_uuid relates to a goals table
            $table->foreign('goal_uuid')->references('goal_uuid')->on('goals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversions');
    }
}
