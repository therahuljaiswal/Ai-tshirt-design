<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ToolBaz - AI Image Generator</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-cyber-black text-white font-sans selection:bg-neon-purple selection:text-white">
    <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-neon-purple/30 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-electric-blue/30 rounded-full blur-[100px]"></div>
        </div>

        @if (Route::has('login'))
            <div class="fixed top-0 right-0 px-6 py-4 sm:block text-right z-10">
                @auth
                    <a href="{{ url('/generator') }}" class="text-sm text-gray-300 hover:text-white underline">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-300 hover:text-white underline">Log in</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-300 hover:text-white underline">Register</a>
                    @endif
                @endauth
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col items-center text-center pt-20">
            <h1 class="text-6xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-neon-purple to-electric-blue mb-6">
                ToolBaz AI
            </h1>
            <p class="mt-4 text-xl text-gray-400 max-w-2xl">
                Generate professional-grade images with the power of FLUX-1 Schnell.
                <span class="text-neon-purple">Cyberpunk</span>. <span class="text-electric-blue">Realistic</span>. <span class="text-pink-500">Artistic</span>.
            </p>

            <div class="mt-10 flex gap-4">
                <a href="{{ route('register') }}" class="px-8 py-3 rounded-full bg-gradient-to-r from-neon-purple to-electric-blue text-black font-bold hover:opacity-90 transition transform hover:scale-105">
                    Try Now &rarr;
                </a>
                <a href="#pricing" class="px-8 py-3 rounded-full border border-gray-600 text-gray-300 hover:bg-gray-800 transition">
                    View Pricing
                </a>
            </div>

            <!-- Scrolling Examples -->
            <div class="mt-16 w-full overflow-hidden relative">
                <div class="flex gap-4 overflow-x-auto pb-4">
                    @for ($i = 0; $i < 5; $i++)
                    <div class="w-64 h-64 bg-gray-800 rounded-xl border border-gray-700 flex items-center justify-center shrink-0">
                        <span class="text-gray-500">AI Image {{ $i+1 }}</span>
                    </div>
                    @endfor
                </div>
            </div>

            <!-- Pricing Grid -->
            <div id="pricing" class="mt-24 w-full mb-20">
                <h2 class="text-3xl font-bold mb-10">Simple Pricing</h2>
                <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <!-- Free -->
                    <div class="p-8 rounded-2xl bg-gray-900 border border-gray-700 hover:border-neon-purple transition relative overflow-hidden group">
                        <h3 class="text-2xl font-bold text-gray-200">Free Tier</h3>
                        <p class="text-4xl font-bold mt-4">₹0</p>
                        <ul class="mt-6 space-y-3 text-left text-gray-400">
                            <li>✓ Unlimited Generations</li>
                            <li>✗ Watermarked Images</li>
                            <li>✓ Standard Speed</li>
                        </ul>
                        <a href="{{ route('register') }}" class="mt-8 block w-full py-3 rounded bg-gray-800 hover:bg-gray-700 text-white font-bold transition">Start Free</a>
                    </div>

                    <!-- Paid -->
                    <div class="p-8 rounded-2xl bg-gray-900 border border-electric-blue relative overflow-hidden group shadow-lg shadow-electric-blue/20">
                        <div class="absolute top-0 right-0 bg-electric-blue text-black text-xs font-bold px-3 py-1">POPULAR</div>
                        <h3 class="text-2xl font-bold text-white">Credit Packs</h3>
                        <p class="text-4xl font-bold mt-4 text-transparent bg-clip-text bg-gradient-to-r from-neon-purple to-electric-blue">₹99+</p>
                        <ul class="mt-6 space-y-3 text-left text-gray-300">
                            <li>✓ No Watermark</li>
                            <li>✓ High Speed Generation</li>
                            <li>✓ 10 Credits / Image</li>
                        </ul>
                        <a href="{{ route('pricing') }}" class="mt-8 block w-full py-3 rounded bg-gradient-to-r from-neon-purple to-electric-blue text-black font-bold hover:opacity-90 transition">Buy Credits</a>
                    </div>
                </div>
            </div>

            <footer class="pb-8 text-gray-500 text-sm">
                &copy; {{ date('Y') }} ToolBaz. All rights reserved.
            </footer>
        </div>
    </div>
</body>
</html>
