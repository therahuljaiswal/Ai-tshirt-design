<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Typography\FontFactory;

class DeepInfraService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.deepinfra.com/v1/inference/black-forest-labs/FLUX.1-schnell';

    public function __construct()
    {
        $this->apiKey = config('services.deepinfra.key');
    }

    public function generateImage($prompt, $width = 1024, $height = 1024)
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->apiUrl, [
                'prompt' => $prompt,
                'width' => $width,
                'height' => $height,
                'num_inference_steps' => 4,
            ]);

        if ($response->failed()) {
            throw new \Exception('DeepInfra API Error: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['images'][0])) {
             // Sometimes error is in different format
             if (isset($data['error'])) {
                 throw new \Exception('API Error: ' . json_encode($data['error']));
             }
             throw new \Exception('No image returned from API');
        }

        $imageData = $data['images'][0];

        // Decode base64
        if (str_starts_with($imageData, 'http')) {
             $imageContent = file_get_contents($imageData);
        } else {
             $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
             $imageContent = base64_decode($base64);
        }

        // Ensure directory exists
        if (!Storage::disk('public')->exists('generated')) {
            Storage::disk('public')->makeDirectory('generated');
        }

        $filename = 'generated/' . uniqid() . '.png';
        Storage::disk('public')->put($filename, $imageContent);

        return $filename;
    }

    public function applyWatermark($imagePath)
    {
        $fullPath = Storage::disk('public')->path($imagePath);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($fullPath);

        $logoPath = public_path('logo.png');
        if (file_exists($logoPath)) {
            $image->place($logoPath, 'center', 0, 0, 50);
        } else {
            // Fallback text
            $image->text('ToolBaz', 512, 512, function ($font) {
                $font->size(100);
                $font->color('rgba(255, 255, 255, 0.5)');
                $font->align('center');
                $font->valign('middle');
            });
        }

        $image->save($fullPath);
    }
}
