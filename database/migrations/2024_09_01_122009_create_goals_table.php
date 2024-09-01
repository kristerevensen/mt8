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
        // this table has a foreign key constraint to project_code in the projects table
        // this table creates goal name, goal type, goal value, goal description, and goal unique identifier
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->index();
            $table->string('goal_name');
            $table->string('goal_type');
            $table->decimal('goal_value', 12, 2)->nullable();
            $table->text('goal_description')->nullable();
            $table->string('goal_uuid')->unique();
            $table->timestamps();

            // Foreign key constraint if project_code relates to a projects table
            $table->foreign('project_code')->references('project_code')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
