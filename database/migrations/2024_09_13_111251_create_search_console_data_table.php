<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchConsoleDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_console_data', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->integer('clicks');
            $table->integer('impressions');
            $table->date('date');
            $table->string('project_code');  // Bruker project_code i stedet for project_id
            $table->timestamps();

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
        Schema::dropIfExists('search_console_data');
    }
}
