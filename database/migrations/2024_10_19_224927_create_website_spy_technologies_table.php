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

            // UUID som string og unik
            $table->string('uuid');
            // legg til domain
            $table->string('domain');
            // legg til domain rank
            $table->integer('domain_rank');

            // Foreign key til projects
            $table->string('project_code');
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade');

            // Metadata felter
            $table->string('status_message');
            $table->integer('status_code');
            $table->timestamp('time')->nullable();

            // Kategori og underkategori
            $table->string('category');  // Hovedkategori, f.eks. "Technologies"
            $table->string('subcategory');  // Underkategori, f.eks. "Ecommerce"
            $table->string('item_title');  // Teknologinavn, f.eks. "Shopify"
            $table->text('description')->nullable();  // Eventuell beskrivelse av teknologi

            $table->timestamps();
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
