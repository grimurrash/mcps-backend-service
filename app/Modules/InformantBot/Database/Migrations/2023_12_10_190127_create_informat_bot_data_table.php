<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('informant_bot_data', static function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->unique();
            $table->integer('step')->default(0);
            $table->integer('test_points')->nullable();
            $table->text('review')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informant_bot_data');
    }
};
