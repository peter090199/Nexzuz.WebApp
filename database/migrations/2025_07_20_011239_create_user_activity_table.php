<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activity', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('viewer_code');
            $table->string('viewed_code')->nullable(); // Allow null if no viewed_code
            $table->string('activity_type');
            $table->timestamp('timestamp')->useCurrent(); // Defaults to CURRENT_TIMESTAMP
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity');
    }
};
