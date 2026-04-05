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
        Schema::create('nft_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nft_id')->constrained('nfts')->cascadeOnDelete();
            $table->string('wallet_address')->index();
            $table->unsignedBigInteger('balance')->default(0);
            $table->timestamps();

            $table->unique(['nft_id', 'wallet_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nft_owners');
    }
};
