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
        Schema::create('weapon_templates', function (Blueprint $table) {
            $table->id();

            // relasi ke blockchain
            $table->unsignedBigInteger('template_id_onchain')->nullable()->index();

            // data item
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('rarity')->default('Common');
            $table->string('weapon_type')->default('Sword');

            // stat dasar
            $table->unsignedInteger('base_attack')->default(0);
            $table->unsignedInteger('base_defense')->default(0);

            // IPFS
            $table->string('image_ipfs_hash')->nullable();
            $table->string('image_uri')->nullable();      // ipfs://...
            $table->string('image_url')->nullable();      // gateway URL

            $table->string('metadata_ipfs_hash')->nullable();
            $table->string('metadata_uri')->nullable();   // ipfs://...
            $table->string('metadata_url')->nullable();   // gateway URL

            // admin utility
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weapon_templates');
    }
};
