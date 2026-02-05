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
        $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        $user = auth()->user();

        // Logic:
        // If credits >= 15: Premium (No Watermark), Deduct 10.
        // Else: Free (Watermarked), Deduct 0.
        $isPremium = $user->credits >= 15;

        try {
            $path = $this->deepInfraService->generateImage($request->prompt);

            if (!$isPremium) {
                $this->deepInfraService->applyWatermark($path);
            } else {
                $user->decrement('credits', 10);
            }

            // Record generation
            $user->generatedImages()->create([
                'prompt' => $request->prompt,
                'image_path' => $path,
                'is_watermarked' => !$isPremium,
            ]);

            return response()->json([
                'success' => true,
                'image_url' => Storage::url($path),
                'remaining_credits' => $user->fresh()->credits,
                'is_watermarked' => !$isPremium
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
