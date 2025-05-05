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
        Schema::create('commentposts', function (Blueprint $table) {
            $table->id();
            $table->uuid('comment_uuid')->unique();
            $table->string('post_uuidOrUind');
            $table->integer('status')->default(0); // 0 - public, 1 - private
            $table->integer('code'); 
            $table->text('comment');
            $table->date('date_comment');
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
        Schema::dropIfExists('comments');
    }
};
