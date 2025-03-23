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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('transNo'); // Use unsignedInteger if it's a reference
            $table->string('caption')->nullable(); // Changed from integer to string
            $table->text('post')->nullable(); // Changed to text for larger content
            $table->tinyInteger('status')->default(1); // Changed from char to tinyInteger
            $table->integer('code');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
