@assets
@vite(['resources/js/app.tsx'])
@endassets
<div>
    <div id="disconnectBtn"></div>
    {{-- menampikan nft --}}
    @if($loading)
        <p class="text-gray-500">Loading NFT...</p>
    @elseif(empty($nfts))
        <p class="text-red-500">Tidak ada NFT ditemukan.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($nfts as $item)
                <div class="m-4 border rounded-xl overflow-hidden shadow hover:shadow-lg transition">
                    
                    {{-- Gambar --}}
                    <img 
                        src="{{ $item['display_image_url'] ?? $item['image_url'] }}" 
                        alt="{{ $item['name'] }}"
                        class="w-full h-56 object-cover"
                    >

                    {{-- Info --}}
                    <div class="p-4 space-y-1">
                        <h2 class="font-bold text-lg">{{ $item['name'] }}</h2>
                        <p class="text-gray-500 text-sm line-clamp-2">{{ $item['description'] }}</p>

                        <p class="text-xs text-gray-400">
                            Token ID: <span class="font-mono">{{ $item['identifier'] }}</span>
                        </p>

                        <p class="text-xs text-gray-400">
                            Standard: <span class="uppercase">{{ $item['token_standard'] }}</span>
                        </p>

                        {{-- Traits --}}
                        @if(!empty($item['traits']))
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($item['traits'] as $trait)
                                    <span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full">
                                        {{ $trait['trait_type'] }}: {{ $trait['value'] }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Link OpenSea --}}
                        <a 
                            href="{{ $item['opensea_url'] }}" 
                            target="_blank"
                            class="block mt-3 text-center text-sm bg-blue-600 text-white py-1.5 rounded-lg hover:bg-blue-700"
                        >
                            Lihat di OpenSea
                        </a>

                        {{-- React Send Widget per NFT --}}
                        <div 
                            class="nft-send-widget mt-2"
                            data-contract="{{ $item['contract'] }}"
                            data-token-id="{{ $item['identifier'] }}"
                        ></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if (session('success-message'))
        <div class="mt-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success-message') }}
        </div>
    @endif
    {{-- tombol disconnect --}}
    <button wire:click='disconnectWallet' wire:loading.attr="disabled"
        class="w-full bg-red-500/15 text-red-400 border border-red-500/20 rounded-2xl py-3.5 font-medium hover:bg-red-500/25 transition duration-200 active:scale-95 text-sm fade-up"
        style="animation-delay: 200ms">
        <span wire:loading.remove>Disconnect Wallet</span>
        <span wire:loading class="w-4 h-4 border-2 border-white/30 border-t-transparent rounded-full animate-spin"></span>
    </button>
</div>