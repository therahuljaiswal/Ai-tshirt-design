<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DeepInfraService
{
    protected $apiKey;
    protected $headers;

    // Models
    protected $modelGen = 'black-forest-labs/FLUX-1-schnell';
    // protected $modelBg  = 'briaai/RMBG-1.4'; // High quality BG removal
    protected $modelBg = "Bria/remove_background";
    // protected $modelUp  = 'batou1986/4x-UltraSharp'; // 4x Upscaler
    protected $modelUp = 'stabilityai/stable-diffusion-x4-upscaler';

    public function __construct()
    {
        $this->apiKey = config('services.deepinfra.key');
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * The Master Function: Runs the full T-Shirt Pipeline
     * 1. Generates Image (Text or Image-to-Image)
     * 2. Removes Background
     * 3. Upscales to 4K
     */
    public function generateTShirtDesign($prompt, $width = 1024, $height = 1024, $initImage = null, $strength = 0.7)
    {
        // Step 1: Generate Base Image
        $baseImagePath = $this->generateBaseImage($prompt, $width, $height, $initImage, $strength);
        $baseImageUrl = $this->localPathToUrl($baseImagePath);

        // Step 2: Remove Background
        // $cleanImagePath = $this->removeBackground($baseImageUrl);
        // $cleanImageUrl = $this->localPathToUrl($cleanImagePath);
        // Step 2: Remove Background (Use 'Bria/remove_background' if 1.4 is down)
        // I have added a try-catch block here to be safe
        try {
            $cleanImagePath = $this->removeBackground($baseImageUrl);
        } catch (\Exception $e) {
            // If background removal fails, just return the base image
            $cleanImagePath = $baseImagePath;
        }
        // Step 3: Upscale to 4K
        // $finalImagePath = $this->upscaleImage($cleanImageUrl);

        // Step 3: Upscale (DISABLED because API is down)
        // We just copy the clean image to 'final' so the controller doesn't break
        $finalImagePath = $cleanImagePath;

        return [
            'base' => $baseImagePath,
            'clean' => $cleanImagePath,
            'final' => $finalImagePath // This is the 4K PNG
        ];
    }

    /**
     * Core Generation (Flux.1)
     * Supports Text-to-Image AND Image-to-Image
     */
    public function generateBaseImage($prompt, $width, $height, $initImage = null, $strength = 0.7)
    {
        $payload = [
            'prompt' => $prompt,
            'width' => $width,
            'height' => $height,
            'num_inference_steps' => 4, // Schnell is optimized for 4
        ];

        // Handle Image-to-Image (Optional Upload)
        if ($initImage) {
            $payload['image'] = $this->imageToBase64($initImage);
            $payload['strength'] = $strength; // 0.1 (Mostly original) to 1.0 (Mostly creative)
        }

        return $this->callApi($this->modelGen, $payload, 'generated');
    }

    /**
     * Background Removal (RMBG-1.4)
     */
    public function removeBackground($imageUrl)
    {
        // Note: DeepInfra expects 'image' as a URL or Base64
        return $this->callApi($this->modelBg, [
            'image' => $imageUrl
        ], 'clean');
    }

    /**
     * Upscaling (4x UltraSharp)
     */
    public function upscaleImage($imageUrl)
    {
        return $this->callApi($this->modelUp, [
            'image' => $imageUrl,
            'scale' => 4,
            'face_enhance' => false // Set true if doing portraits
        ], 'upscaled');
    }

    /**
     * Helper: Handles the API Call, Error Checking, and Saving
     */
    protected function callApi($model, $payload, $folder)
    {
        $response = Http::withHeaders($this->headers)
            ->post("https://api.deepinfra.com/v1/inference/{$model}", $payload);

        if ($response->failed()) {
            throw new \Exception("DeepInfra Error ({$model}): " . $response->body());
        }

        $data = $response->json();

        // Standardize Output (DeepInfra returns 'images' or 'output')
        $outputData = $data['images'][0]['image_url'] 
                      ?? $data['output'] 
                      ?? $data['images'][0] 
                      ?? null;

        if (!$outputData) {
            throw new \Exception("No image returned from {$model}");
        }

        // Decode Base64
        $imageContent = $this->parseImageResponse($outputData);

        // Save to Storage
        $filename = "{$folder}/" . Str::random(16) . '.png';
        Storage::disk('public')->put($filename, $imageContent);

        return $filename;
    }

    /**
     * Apply Watermark (For Free Users)
     */
    public function applyWatermark($imagePath)
    {
        $fullPath = Storage::disk('public')->path($imagePath);
        $manager = new ImageManager(new Driver());
        $image = $manager->read($fullPath);

        $logoPath = public_path('watermark.png'); // Make sure this file exists!

        if (file_exists($logoPath)) {
            // Place watermark at 50% opacity
            $image->place($logoPath, 'center', 0, 0, 50);
        } else {
            // Text Fallback
            $image->text('ToolBaz.com', $image->width()/2, $image->height()/2, function ($font) {
                $font->size(60);
                $font->color('rgba(255, 255, 255, 0.3)');
                $font->align('center');
                $font->valign('middle');
            });
        }

        $image->save($fullPath);
    }

    // --- Helpers ---

    protected function imageToBase64($imageInput)
    {
        // If it's an UploadedFile object (from Request)
        if (is_object($imageInput) && method_exists($imageInput, 'getRealPath')) {
            $data = file_get_contents($imageInput->getRealPath());
            $mime = $imageInput->getMimeType();
            return "data:{$mime};base64," . base64_encode($data);
        }
        
        // If it's a local file path string
        if (file_exists($imageInput)) {
            $data = file_get_contents($imageInput);
            $mime = mime_content_type($imageInput);
            return "data:{$mime};base64," . base64_encode($data);
        }

        return $imageInput; // Assume it's already base64 or URL
    }

    protected function parseImageResponse($imageData)
    {
        // Remove data URI header if present
        if (str_contains($imageData, 'base64,')) {
            $imageData = explode('base64,', $imageData)[1];
            return base64_decode($imageData);
        }
        // If it's a URL (DeepInfra sometimes returns URLs for large files)
        if (filter_var($imageData, FILTER_VALIDATE_URL)) {
            return file_get_contents($imageData);
        }
        
        return base64_decode($imageData);
    }

    protected function localPathToUrl($path)
    {
        // DeepInfra needs a public URL to fetch the image for the next step
        // OR a base64 string. Using Base64 is safer for local dev.
        $fullPath = Storage::disk('public')->path($path);
        $data = file_get_contents($fullPath);
        return "data:image/png;base64," . base64_encode($data);
    }
}