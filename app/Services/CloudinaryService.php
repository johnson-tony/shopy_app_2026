<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected ?string $cloudName;
    protected ?string $apiKey;
    protected ?string $apiSecret;
    protected string $categoryFolder;
    protected string $modeFolder;

    public function __construct()
    {
        $this->cloudName = trim((string) config('services.cloudinary.cloud_name'));
        $this->apiKey = trim((string) config('services.cloudinary.api_key'));
        $this->apiSecret = trim((string) config('services.cloudinary.api_secret'));
        $this->categoryFolder = trim((string) config('services.cloudinary.category_folder', 'category'));
        $this->modeFolder = trim((string) config('services.cloudinary.mode_folder', 'mode'));
    }

    /**
     * Check if Cloudinary credentials are fully configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->cloudName) && !empty($this->apiKey) && !empty($this->apiSecret);
    }

    /**
     * Upload an image for a category into the category folder.
     *
     * @param UploadedFile|string $file
     * @return string Secure URL of uploaded image
     * @throws Exception
     */
    public function uploadCategoryImage(UploadedFile|string $file): string
    {
        return $this->uploadImage($file, $this->categoryFolder);
    }

    /**
     * Upload an image for a mode into the separate mode folder.
     *
     * @param UploadedFile|string $file
     * @return string Secure URL of uploaded image
     * @throws Exception
     */
    public function uploadModeImage(UploadedFile|string $file): string
    {
        return $this->uploadImage($file, $this->modeFolder);
    }

    /**
     * Upload an image to a specific Cloudinary folder.
     *
     * @param UploadedFile|string $file
     * @param string $folder
     * @return string Secure URL of uploaded image
     * @throws Exception
     */
    public function uploadImage(UploadedFile|string $file, string $folder): string
    {
        if (!$this->isConfigured()) {
            throw new Exception('Cloudinary credentials are not properly configured.');
        }

        $timestamp = time();

        // Cloudinary requires signed parameters in alphabetical order
        // Signature string: folder={folder}&timestamp={timestamp}{api_secret}
        $paramsToSign = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];
        ksort($paramsToSign);

        $signatureString = collect($paramsToSign)
            ->map(fn($val, $key) => "{$key}={$val}")
            ->implode('&') . $this->apiSecret;

        $signature = sha1($signatureString);

        $uploadUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

        $httpRequest = Http::timeout(30);

        if ($file instanceof UploadedFile) {
            $response = $httpRequest->attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $file->getClientOriginalName()
            )->post($uploadUrl, [
                        'api_key' => $this->apiKey,
                        'timestamp' => $timestamp,
                        'folder' => $folder,
                        'signature' => $signature,
                    ]);
        } else {
            // Local file path or base64
            $response = $httpRequest->attach(
                'file',
                fopen($file, 'r'),
                basename($file)
            )->post($uploadUrl, [
                        'api_key' => $this->apiKey,
                        'timestamp' => $timestamp,
                        'folder' => $folder,
                        'signature' => $signature,
                    ]);
        }

        if ($response->failed()) {
            $errorMessage = $response->json('error.message', 'Unknown Cloudinary upload error');
            Log::error('Cloudinary Category Upload Failed', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'body' => $response->body(),
            ]);
            throw new Exception("Cloudinary Image Upload Failed: {$errorMessage}");
        }

        $secureUrl = $response->json('secure_url');

        if (empty($secureUrl)) {
            throw new Exception('Cloudinary did not return a valid secure URL.');
        }

        return $secureUrl;
    }

    /**
     * Delete an image from Cloudinary by its URL or public ID.
     */
    public function deleteImage(?string $imageUrlOrPublicId): bool
    {
        if (empty($imageUrlOrPublicId) || !$this->isConfigured()) {
            return false;
        }

        $publicId = $this->extractPublicId($imageUrlOrPublicId);

        if (empty($publicId)) {
            return false;
        }

        $timestamp = time();

        // Signature string: public_id={publicId}&timestamp={timestamp}{api_secret}
        $paramsToSign = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        ksort($paramsToSign);

        $signatureString = collect($paramsToSign)
            ->map(fn($val, $key) => "{$key}={$val}")
            ->implode('&') . $this->apiSecret;

        $signature = sha1($signatureString);

        $destroyUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy";

        try {
            $response = Http::timeout(15)->post($destroyUrl, [
                'public_id' => $publicId,
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ]);

            if ($response->successful()) {
                $result = $response->json('result');
                return $result === 'ok' || $result === 'not found';
            }

            Log::warning('Cloudinary Category Delete Warning', [
                'public_id' => $publicId,
                'response' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('Cloudinary Image Delete Exception', [
                'public_id' => $publicId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }


    public function extractPublicId(string $urlOrPublicId): ?string
    {
        if (!str_starts_with($urlOrPublicId, 'http://') && !str_starts_with($urlOrPublicId, 'https://')) {
            // Strip file extension if present
            return preg_replace('/\.[^.\/]+$/', '', $urlOrPublicId);
        }

        // Match Cloudinary URL format: .../upload/(v[0-9]+/)?(path/to/image)(.ext)?
        if (preg_match('/\/upload\/(?:v\d+\/)?([^\.]+)/', $urlOrPublicId, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
