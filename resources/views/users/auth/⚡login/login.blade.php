@assets
    @vite(['resources/js/app.tsx'])
@endassets
<div>
    <div id="walletConnectBtn"></div>
    <button
        type="button"
        wire:click="connectWallet"
        wire:loading.attr="disabled"
        class="w-full bg-emerald-500/90 hover:bg-emerald-500 text-white font-semibold rounded-2xl py-3.5 text-sm transition-all duration-200 active:scale-95 shadow-[0_4px_20px_rgba(52,211,153,0.3)]"
    >
        <span wire:loading.remove>Connect Wallet</span>
        <span wire:loading class="w-4 h-4 border-2 border-white/30 border-t-transparent rounded-full animate-spin"></span>
    </button>
</div>