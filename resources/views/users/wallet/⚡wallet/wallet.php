<?php

use App\Models\NftTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Livewire\Component;
new class extends Component
{
    public $nfts = [];  // pakai array, bukan null
    public $loading = true;

    public function mount()
    {
        $contractAddress = config('app.collection_opensea_smartcontract');

        $response = Http::withHeaders([
            'x-api-key' => config('app.opensea_api_key'),
        ])->get(
            "https://api.opensea.io/api/v2/chain/matic/contract/{$contractAddress}/nfts"
        );
        // dd($response->json());  // Debug: lihat struktur data yang diterima
        $this->nfts = $response->json('nfts') ?? [];  // ambil array 'nfts' langsung
        $this->loading = false;
    }

    public function disconnectWallet()
    {
        $this->dispatch('disconnect-wallet');
    }

    #[On('nftTransferSuccess')]
    public function saveNftTransfer($txHash, $from, $to, $contract, $tokenId, $amount, $chain)
    {
        NftTransfer::create([
            'user_id' => Auth::id(),
            'wallet_from' => $from,
            'wallet_to' => $to,
            'contract_address' => $contract,
            'token_id' => $tokenId,
            'amount' => $amount,
            'tx_hash' => $txHash,
            'chain' => $chain,
            'status' => 'success',
        ]);

        session()->flash('success-message', 'Histori transfer NFT berhasil disimpan.');
    }
};