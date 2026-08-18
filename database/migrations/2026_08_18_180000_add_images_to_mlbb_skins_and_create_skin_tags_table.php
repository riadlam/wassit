<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mlbb_skins', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('skin_slug');
            $table->string('rarity')->nullable()->after('sort_order');
            $table->boolean('painted')->default(false)->after('rarity');
            $table->string('image_path')->nullable()->after('painted');
            $table->string('thumbnail_path')->nullable()->after('image_path');
            $table->text('source_image_url')->nullable()->after('thumbnail_path');
            $table->json('tags')->nullable()->after('source_image_url');
            $table->timestamp('synced_at')->nullable()->after('tags');
        });

        Schema::create('mlbb_skin_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image_path')->nullable();
            $table->text('source_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mlbb_skin_tags');

        Schema::table('mlbb_skins', function (Blueprint $table) {
            $table->dropColumn([
                'sort_order',
                'rarity',
                'painted',
                'image_path',
                'thumbnail_path',
                'source_image_url',
                'tags',
                'synced_at',
            ]);
        });
    }
};
