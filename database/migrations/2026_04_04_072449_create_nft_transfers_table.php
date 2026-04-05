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
        Schema::create('nft_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('wallet_from')->nullable();
            $table->string('wallet_to')->nullable();
            $table->string('contract_address')->nullable();
            $table->string('token_id')->nullable();
            $table->string('amount')->default('1');
            $table->string('tx_hash')->nullable();
            $table->string('chain')->nullable();
            $table->string('status')->default('success');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nft_transfers');
    }
};
