<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mlbb_skins', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('hero');
            $table->string('skin');
            $table->string('role_slug')->index();
            $table->string('hero_slug')->index();
            $table->string('skin_slug')->index();
            $table->timestamps();

            $table->unique(['hero_slug', 'skin_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mlbb_skins');
    }
};
