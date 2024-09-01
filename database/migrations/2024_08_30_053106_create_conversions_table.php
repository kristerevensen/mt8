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
            $table->string('project_code')->index();
            $table->string('conversion_type');
            $table->decimal('conversion_value', 10, 2)->nullable();
            $table->string('page_url');
            $table->string('referrer')->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->timestamps();

            // Foreign key constraint if project_code relates to a projects table
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade');
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
