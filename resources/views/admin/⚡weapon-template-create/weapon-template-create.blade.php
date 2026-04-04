
<div>
<div class="p-6 bg-white rounded-2xl shadow space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Create Weapon Template</h2>
        <p class="text-sm text-gray-500">Upload gambar, buat metadata, lalu simpan ke IPFS.</p>
    </div>

    @if ($successMessage)
        <div class="p-4 rounded-xl bg-green-100 text-green-800">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="p-4 rounded-xl bg-red-100 text-red-800">
            {{ $errorMessage }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Weapon Name</label>
                <input type="text" wire:model.defer="name"
                    class="w-full border focus:outline-none px-4 py-2 rounded-xl border-gray-300 border focus:outline-none focus:ring focus:ring-indigo-200">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rarity</label>
                <select wire:model.defer="rarity"
                    class="w-full border focus:outline-none px-4 py-2 rounded-xl border-gray-300 focus:ring focus:ring-indigo-200">
                    <option value="Common">Common</option>
                    <option value="Rare">Rare</option>
                    <option value="Epic">Epic</option>
                    <option value="Legendary">Legendary</option>
                </select>
                @error('rarity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Weapon Type</label>
                <input type="text" wire:model.defer="weapon_type"
                    class="w-full border focus:outline-none px-4 py-2 rounded-xl border-gray-300 focus:ring focus:ring-indigo-200">
                @error('weapon_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Base Attack</label>
                <input type="number" wire:model.defer="base_attack"
                    class="w-full border focus:outline-none px-4 py-2 rounded-xl border-gray-300 focus:ring focus:ring-indigo-200">
                @error('base_attack') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Base Defense</label>
                <input type="number" wire:model.defer="base_defense"
                    class="w-full border focus:outline-none px-4 py-2 rounded-xl border-gray-300 focus:ring focus:ring-indigo-200">
                @error('base_defense') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Weapon Image</label>
                <input type="file" wire:model="image"
                    class="w-full border focus:outline-none px-4 py-2 rounded-xl border-gray-300 focus:ring focus:ring-indigo-200">
                @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div wire:loading wire:target="image" class="text-sm text-blue-500 mt-2">
                    Uploading image preview...
                </div>

                @if ($image)
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-700">Image Preview</p>
                        <img src="{{ $image->temporaryUrl() }}"
                            class="w-40 h-40 object-cover rounded-xl border shadow">
                    </div>
                @endif
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea wire:model.defer="description" rows="4"
                class="w-full border focus:outline-none px-4 py-2 rounded-xl border-gray-300 focus:ring focus:ring-indigo-200"></textarea>
            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                class="px-5 py-3 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition"
                wire:loading.attr="disabled"
                wire:target="save">
                <span wire:loading.remove wire:target="save">Save Template</span>
                <span wire:loading wire:target="save">Saving to IPFS...</span>
            </button>

            <button type="button" wire:click="resetForm"
                class="px-5 py-3 rounded-xl bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition">
                Reset
            </button>
        </div>
    </form>
</div>
</div>