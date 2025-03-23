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
            $table->integer('transNo');
            $table->uuid('posts_uuid')->unique(); // Defining UUID column and making it unique
            $table->string('caption')->nullable();
            $table->text('post')->nullable();
            $table->tinyInteger('status')->default(1);
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
