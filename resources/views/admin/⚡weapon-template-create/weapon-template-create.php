<?php

use Livewire\Component;

use App\Models\WeaponTemplate;
use App\Services\IpfsService;
use App\Services\NftMetadataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $name;
    public $description;
    public $rarity = 'Common';
    public $weapon_type = 'Sword';
    public $base_attack = 0;
    public $base_defense = 0;
    public $image;

    public $successMessage = null;
    public $errorMessage = null;

    // public function mount()
    // {
    //     dd(config('services.ipfs'));
    // }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'rarity' => ['required', 'string', 'max:100'],
            'weapon_type' => ['required', 'string', 'max:100'],
            'base_attack' => ['required', 'integer', 'min:0'],
            'base_defense' => ['required', 'integer', 'min:0'],
            'image' => [
                'required',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(5 * 1024), // 5MB
            ],
        ];
    }

    public function save(IpfsService $ipfsService, NftMetadataService $metadataService)
    {
        $this->resetMessages();

        $validated = $this->validate();

        DB::beginTransaction();

        try {
            // 1. upload gambar ke IPFS
            $tempPath = $this->image->getRealPath();
            $imageFileName = Str::slug($validated['name']) . '.' . $this->image->getClientOriginalExtension();

            $imageUpload = $ipfsService->uploadFile($tempPath, $imageFileName);

            // 2. simpan template dulu ke DB
            $template = WeaponTemplate::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'rarity' => $validated['rarity'],
                'weapon_type' => $validated['weapon_type'],
                'base_attack' => $validated['base_attack'],
                'base_defense' => $validated['base_defense'],
                'image_ipfs_hash' => $imageUpload['hash'],
                'image_uri' => $imageUpload['uri'],
                'image_url' => $imageUpload['url'],
                'is_active' => true,
            ]);

            // 3. generate metadata JSON
            $metadata = $metadataService->buildTemplateMetadata($template);

            // 4. upload metadata ke IPFS
            $metadataUpload = $ipfsService->uploadJson(
                $metadata,
                $template->slug . '.json'
            );

            // 5. update template dengan metadata uri
            $template->update([
                'metadata_ipfs_hash' => $metadataUpload['hash'],
                'metadata_uri' => $metadataUpload['uri'],
                'metadata_url' => $metadataUpload['url'],
            ]);

            DB::commit();

            $this->successMessage = 'Weapon template berhasil dibuat dan diupload ke IPFS.';
            $this->resetForm();

            $this->dispatch('weapon-template-created');

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->errorMessage = 'Gagal membuat template: ' . $e->getMessage();
        }
    }

    public function resetForm()
    {
        $this->reset([
            'name',
            'description',
            'rarity',
            'weapon_type',
            'base_attack',
            'base_defense',
            'image',
        ]);

        $this->rarity = 'Common';
        $this->weapon_type = 'Sword';
        $this->base_attack = 0;
        $this->base_defense = 0;
    }

    public function resetMessages()
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }
};