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
        Schema::create('commentreplies', function (Blueprint $table) {
            $table->id();
            $table->uuid('comment_uuid');
            // $table->uuid('replies_uuid')->unique();
            $table->integer('status')->default(0); // 0 - public, 1 - private
            $table->integer('code'); // Assuming this references a User or another model
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
        Schema::dropIfExists('commentreplies');
    }
};
