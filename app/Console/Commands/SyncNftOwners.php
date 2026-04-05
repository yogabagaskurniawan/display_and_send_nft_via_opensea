<?php

namespace App\Console\Commands;

use App\Models\Nft;
use App\Models\NftOwner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncNftOwners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-nft-owners {--fresh : Scan ulang dari block deploy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    const TRANSFER_SINGLE_TOPIC = '0xc3d58168c5ae7397731d063d5bbf3d657854427343f4c083240f7aacaa2d0f62';
    const ZERO_ADDRESS          = '0x0000000000000000000000000000000000000000';
    const BLOCK_CHUNK           = 2000;

    private string $rpcUrl;
    private string $contractAddress;

    public function handle()
    {
        $this->contractAddress = strtolower(config('app.collection_opensea_smartcontract'));
        $this->rpcUrl          = config('app.tatum_rpc_url');
        $apiKey                = config('app.opensea_api_key');

        // Reset cache kalau pakai --fresh
        if ($this->option('fresh')) {
            cache()->forget('nft_last_synced_block');
            cache()->forget('nft_candidate_wallets');
            $this->info("🔄 Cache direset");
        }

        $this->info("🚀 Sync NFT owners...");

        // ================================================
        // STEP 1: Ambil NFT list dari OpenSea (cache 1 jam)
        // ================================================
        $nfts = cache()->remember('nft_opensea_list', now()->addHour(), function () use ($apiKey) {
            $response = Http::withHeaders(['x-api-key' => $apiKey])
                ->get("https://api.opensea.io/api/v2/chain/matic/contract/{$this->contractAddress}/nfts");

            if (!$response->successful()) {
                $this->error("❌ Gagal ambil NFT list");
                return [];
            }

            return $response->json('nfts') ?? [];
        });

        if (empty($nfts)) return Command::FAILURE;
        $this->info("📦 " . count($nfts) . " NFT ditemukan");

        // ================================================
        // STEP 2: Simpan/update metadata NFT ke DB dulu
        // ================================================
        $nftModels = [];
        foreach ($nfts as $item) {
            $tokenId = (string) ($item['identifier'] ?? null);
            if (!$tokenId) continue;

            $nftModels[$tokenId] = Nft::updateOrCreate(
                ['contract_address' => $this->contractAddress, 'token_id' => $tokenId],
                [
                    'name'           => $item['name'] ?? null,
                    'description'    => $item['description'] ?? null,
                    'image'          => $item['display_image_url'] ?? $item['image_url'] ?? null,
                    'token_standard' => $item['token_standard'] ?? 'erc1155',
                    'supply'         => (int) ($item['supply'] ?? 0),
                ]
            );
        }

        // ================================================
        // STEP 3: Scan event log — hanya block baru
        // ================================================
        $latestBlock  = $this->getLatestBlock();
        $deployBlock  = (int) config('app.contract_deploy_block', 0);
        $fromBlock    = (int) cache('nft_last_synced_block', $deployBlock);
        $toBlock      = $latestBlock;

        if ($fromBlock >= $toBlock) {
            $this->info("✅ Tidak ada block baru, skip scan");
            return Command::SUCCESS;
        }

        $this->info("🔍 Scan block {$fromBlock} → {$toBlock} (" . ($toBlock - $fromBlock) . " block)");

        // Ambil delta transfer dari block baru saja
        // Format: $newTransfers[tokenId][wallet] = delta balance
        $newTransfers = $this->scanTransferEvents($fromBlock, $toBlock);
        $this->info("Debug transfers: " . json_encode($newTransfers));
        $this->info("Debug nftModels keys: " . json_encode(array_keys($nftModels)));
        if (empty($newTransfers)) {
            $this->info("  ℹ️  Tidak ada transfer baru");
            cache(['nft_last_synced_block' => $toBlock], now()->addDays(30));
            return Command::SUCCESS;
        }

        // ================================================
        // STEP 4: Update balance — hanya wallet yang berubah
        // Tidak perlu balanceOf semua wallet!
        // ================================================
        // STEP 4: Update balance
        foreach ($newTransfers as $tokenId => $walletDeltas) {
            $nft = $nftModels[$tokenId] ?? null;
            if (!$nft) continue;

            $this->info("💾 Update token #{$tokenId} — " . count($walletDeltas) . " wallet");

            foreach ($walletDeltas as $wallet => $delta) {
                // ✅ HAPUS: if ($delta === 0) continue;
                // Tetap cek balanceOf meskipun delta = 0
                // karena wallet mungkin masih pegang NFT

                $actualBalance = $this->getBalance($wallet, $tokenId);

                if ($actualBalance <= 0) {
                    NftOwner::where('nft_id', $nft->id)
                        ->where('wallet_address', $wallet)
                        ->delete();
                    $this->line("    🗑️  {$wallet} → 0 (dihapus)");
                } else {
                    NftOwner::updateOrCreate(
                        ['nft_id' => $nft->id, 'wallet_address' => $wallet],
                        ['balance' => $actualBalance]
                    );
                    $this->line("    ✅ {$wallet} → {$actualBalance} NFT");
                }

                usleep(100000);
            }
        }

        // ================================================
        // STEP 5: Simpan posisi block terakhir
        // ================================================
        cache(['nft_last_synced_block' => $toBlock], now()->addDays(30));

        $this->info("✅ Sync selesai sampai block {$toBlock}");
        return Command::SUCCESS;
    }

    /**
     * Scan TransferSingle events dan hitung delta balance per wallet per token
     * Return: [tokenId => [walletAddress => deltaBalance]]
     */
    private function scanTransferEvents(int $fromBlock, int $toBlock): array
    {
        $transfers = [];
        $chunk     = 250; // mulai dari 500, aman untuk Infura

        $block = $fromBlock;
        while ($block <= $toBlock) {
            $chunkTo = min($block + $chunk - 1, $toBlock);

            $response = Http::timeout(15)->post($this->rpcUrl, [
                "jsonrpc" => "2.0",
                "method"  => "eth_getLogs",
                "params"  => [[
                    "fromBlock" => '0x' . dechex($block),
                    "toBlock"   => '0x' . dechex($chunkTo),
                    "address"   => $this->contractAddress,
                    "topics"    => [self::TRANSFER_SINGLE_TOPIC],
                ]],
                "id" => 1,
            ]);

            $error = $response->json('error');

            // Kalau block range terlalu besar, kecilkan chunk dan retry
            if ($error && $error['code'] === -32005) {
                $chunk = (int) ($chunk / 2);
                $this->warn("  ⚠️ Range terlalu besar, kecilkan chunk ke {$chunk} dan retry...");

                if ($chunk < 10) {
                    $this->error("  ❌ Chunk terlalu kecil, skip block {$block}");
                    $block = $chunkTo + 1;
                    $chunk = 250; // reset
                }

                usleep(300000);
                continue; // retry dengan chunk lebih kecil
            }

            if ($error) {
                $this->warn("  ⚠️ RPC error: " . json_encode($error));
                $block = $chunkTo + 1;
                usleep(500000);
                continue;
            }

            $logs = $response->json('result') ?? [];

            if (count($logs) > 0) {
                $this->line("  📡 Block {$block}–{$chunkTo}: " . count($logs) . " event");
            }

            foreach ($logs as $log) {
                $topics = $log['topics'] ?? [];
                $raw    = $log['data'] ?? '0x';

                if (count($topics) < 4) continue;

                $data = substr($raw, 2); // hapus "0x"

                if (strlen($data) < 128) continue;

                $from = strtolower('0x' . substr($topics[2], 26));
                $to   = strtolower('0x' . substr($topics[3], 26));

                // ✅ BENAR: ambil 64 char pertama, strip leading zero, baru hexdec
                $tokenIdHex = ltrim(substr($data, 0, 64), '0') ?: '0';
                $tokenId    = (string) hexdec($tokenIdHex);

                if (!$tokenId || $tokenId === '0') continue;

                $this->line("    🔍 tokenId={$tokenId} from={$from} to={$to}");

                if ($from !== self::ZERO_ADDRESS) {
                    $transfers[$tokenId][$from] = ($transfers[$tokenId][$from] ?? 0) - 1;
                }

                if ($to !== self::ZERO_ADDRESS) {
                    $transfers[$tokenId][$to] = ($transfers[$tokenId][$to] ?? 0) + 1;
                }
            }

            $block = $chunkTo + 1;

            // Kalau chunk berhasil, coba naikkan lagi pelan-pelan (max 500)
            if ($chunk < 250) {
                $chunk = min($chunk * 2, 250);
            }

            usleep(150000);
        }

        return $transfers;
    }

    private function getLatestBlock(): int
    {
        $hex = Http::post($this->rpcUrl, [
            "jsonrpc" => "2.0", "method" => "eth_blockNumber", "params" => [], "id" => 1
        ])->json('result');

        return (int) hexdec(substr($hex, 2));
    }

    private function getBalance(string $wallet, string $tokenId): int
    {
        $selector      = '00fdd58e';
        $paddedWallet  = str_pad(substr($wallet, 2), 64, '0', STR_PAD_LEFT); // ganti ltrim → substr
        $paddedTokenId = str_pad(dechex((int) $tokenId), 64, '0', STR_PAD_LEFT);
        $data          = '0x' . $selector . $paddedWallet . $paddedTokenId;

        $response = Http::timeout(10)->post($this->rpcUrl, [
            "jsonrpc" => "2.0",
            "method"  => "eth_call",
            "params"  => [["to" => $this->contractAddress, "data" => $data], "latest"],
            "id"      => 1,
        ]);

        $hex = $response->json('result') ?? '0x0';
        return (int) hexdec(substr($hex, 2)); // ganti ltrim → substr
    }
}
