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
        Schema::create('events', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->unsignedBigInteger('user_id');  // Adding user_id as foreign key
            $table->string('title'); // Event title
            $table->text('description'); // Event description
            $table->dateTime('start_time'); // Event start time (date and time)
            $table->dateTime('end_time'); // Event end time (date and time)
            $table->string('location'); // Event location
            $table->softDeletes();  // This adds the 'deleted_at' column
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
