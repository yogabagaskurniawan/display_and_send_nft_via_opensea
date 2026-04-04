<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class IpfsService
{
    protected string $projectId;
    protected string $projectSecret;
    protected string $apiUrl;
    protected string $gateway;

    public function __construct()
    {
        $this->projectId = config('services.ipfs.project_id');
        $this->projectSecret = config('services.ipfs.project_secret');
        $this->apiUrl = config('services.ipfs.api');
        $this->gateway = rtrim(config('services.ipfs.gateway'), '/') . '/';
    }

    public function uploadFile(string $filePath, ?string $fileName = null): array
    {
        if (!File::exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $fileName = $fileName ?: basename($filePath);

        $response = Http::withBasicAuth($this->projectId, $this->projectSecret)
            ->attach(
                'file',
                fopen($filePath, 'r'),
                $fileName
            )
            ->post($this->apiUrl);

        if (!$response->successful()) {
            throw new \Exception('IPFS file upload failed: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['Hash'])) {
            throw new \Exception('Invalid IPFS response: missing Hash');
        }

        return [
            'hash' => $data['Hash'],
            'uri' => 'ipfs://' . $data['Hash'],
            'url' => $this->gateway . $data['Hash'],
            'raw' => $data,
        ];
    }

    public function uploadJson(array $jsonData, string $fileName = 'metadata.json'): array
    {
        $tempPath = storage_path('app/temp_' . uniqid() . '.json');

        file_put_contents(
            $tempPath,
            json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        try {
            return $this->uploadFile($tempPath, $fileName);
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}