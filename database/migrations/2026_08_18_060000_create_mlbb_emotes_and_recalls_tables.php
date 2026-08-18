<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mlbb_emotes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group')->nullable()->index();
            $table->text('description')->nullable();
            $table->json('heroes')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->text('image_url')->nullable();
            $table->timestamps();
        });

        Schema::create('mlbb_recalls', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group')->nullable()->index();
            $table->text('description')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->text('image_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mlbb_emotes');
        Schema::dropIfExists('mlbb_recalls');
    }
};
