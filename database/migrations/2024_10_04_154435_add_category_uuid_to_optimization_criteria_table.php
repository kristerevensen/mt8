<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryUuidToOptimizationCriteriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('optimization_criteria', function (Blueprint $table) {
            $table->string('category_uuid')->index()->after('criteria_uuid'); // Legg til category_uuid-kolonnen

            // Legg til foreign key-referanse til categories-tabellen
            $table->foreign('category_uuid')->references('category_uuid')->on('optimization_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('optimization_criteria', function (Blueprint $table) {
            $table->dropForeign(['category_uuid']); // Fjern foreign key
            $table->dropColumn('category_uuid'); // Fjern kolonnen
        });
    }
}
