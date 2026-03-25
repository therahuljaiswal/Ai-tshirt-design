<?php

namespace App\Http\Controllers;

use App\Services\DeepInfraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    protected $deepInfraService;

    public function __construct(DeepInfraService $deepInfraService)
    {
        $this->deepInfraService = $deepInfraService;
    }

    public function index()
    {
        return view('generator');
    }

    public function generate(Request $request)
    {
        // 1. Validation including new fields (Size, Image, Strength)
        $request->validate([
            'prompt'     => 'required|string|max:1000',
            'size'       => 'nullable|string|in:1024x1024,768x1024,1280x768', // Restrict sizes for safety
            'init_image' => 'nullable|image|max:10240', // Max 10MB upload
            'strength'   => 'nullable|numeric|min:0.1|max:0.9', // Creativity slider
        ]);

        $user = auth()->user();

        // 2. Logic: Check if user qualifies for Premium features
        // Must have at least 15 credits to trigger the "Premium" pipeline
        $isPremium = $user->credits >= 15;

        // 3. Parse Size (Default to 1024x1024 if empty)
        $sizeString = $request->input('size', '1024x1024');
        [$width, $height] = explode('x', $sizeString);

        try {
            // --- PREMIUM FLOW (The "T-Shirt" Pipeline) ---
            if ($isPremium) {
                
                // This runs: Generate -> Remove BG -> Upscale to 4K
                $result = $this->deepInfraService->generateTShirtDesign(
                    $request->prompt,
                    (int)$width,
                    (int)$height,
                    $request->file('init_image'), // Pass uploaded file (if any)
                    (float)$request->input('strength', 0.7)
                );

                // Premium users get the 'final' 4K image
                // The service returns an array: ['base' => ..., 'clean' => ..., 'final' => ...]
                $path = $result['final'];

                // Deduct 10 Credits
                $user->decrement('credits', 10);

            } 
            // --- FREE FLOW (Standard) ---
            else {
                
                // Just generate the standard image
                $path = $this->deepInfraService->generateBaseImage(
                    $request->prompt,
                    (int)$width,
                    (int)$height,
                    $request->file('init_image'),
                    (float)$request->input('strength', 0.7)
                );

                // Apply Watermark
                $this->deepInfraService->applyWatermark($path);
            }

            // 4. Save to Database
            $user->generatedImages()->create([
                'prompt' => $request->prompt,
                'image_path' => $path, // Saves the path (e.g., "generated/xyz.png")
                'is_watermarked' => !$isPremium,
            ]);

            return response()->json([
                'success' => true,
                'image_url' => Storage::url($path), // Returns full URL for frontend
                'remaining_credits' => $user->fresh()->credits,
                'is_watermarked' => !$isPremium
            ]);

        } catch (\Exception $e) {
            // Log error if needed: \Log::error($e->getMessage());
            return response()->json([
                'success' => false, 
                'error' => 'Generation Failed: ' . $e->getMessage()
            ], 500);
        }
    }
}