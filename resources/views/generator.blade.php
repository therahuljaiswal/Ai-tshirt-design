<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('AI Generator') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="generator()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                <!-- History Sidebar -->
                <div class="md:col-span-1 bg-gray-800 rounded-lg p-4 h-fit">
                    <h3 class="text-lg font-bold mb-4 text-white">History</h3>
                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach(auth()->user()->generatedImages()->latest()->take(10)->get() as $image)
                            <div class="bg-gray-900 rounded p-2 border border-gray-700 hover:border-neon-purple transition cursor-pointer" @click="imageUrl = '{{ Storage::url($image->image_path) }}'">
                                <div class="aspect-square bg-black mb-2 rounded overflow-hidden">
                                    <img src="{{ Storage::url($image->image_path) }}" alt="History" class="w-full h-full object-cover">
                                </div>
                                <p class="text-xs text-gray-400 truncate">{{ $image->prompt }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Generator Area -->
                <div class="md:col-span-3 bg-gray-800 rounded-lg p-6">
                    <div class="mb-6">
                        <label class="block text-gray-300 mb-2">Describe your image</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="prompt" @keydown.enter="generate" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-3 text-white focus:ring-neon-purple focus:border-neon-purple" placeholder="A cyberpunk city with neon lights...">
                            <button @click="generate" :disabled="loading" class="px-6 py-3 bg-gradient-to-r from-neon-purple to-electric-blue text-black font-bold rounded-lg hover:opacity-90 transition disabled:opacity-50 flex items-center gap-2">
                                <span x-show="!loading">Generate</span>
                                <span x-show="loading" class="animate-spin h-5 w-5 border-2 border-black border-t-transparent rounded-full"></span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">
                            Cost:
                            <span x-show="credits >= 15" class="text-electric-blue">10 Credits (Premium)</span>
                            <span x-show="credits < 15" class="text-gray-500">Free (Watermarked)</span>
                            | Available: <span x-text="credits"></span> Credits
                        </p>
                    </div>

                    <!-- Display Area -->
                    <div class="bg-gray-900 rounded-xl min-h-[500px] flex items-center justify-center border border-gray-700 relative overflow-hidden">

                        <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-gray-900/80 z-10">
                            <!-- Glowing Circle Loader -->
                            <div class="w-20 h-20 border-4 border-neon-purple border-t-transparent rounded-full animate-spin shadow-[0_0_20px_rgba(176,38,255,0.6)]"></div>
                        </div>

                        <template x-if="imageUrl">
                            <div class="relative w-full h-full flex items-center justify-center bg-black">
                                <img :src="imageUrl" class="max-w-full max-h-[600px] object-contain shadow-2xl">
                                <a :href="imageUrl" download class="absolute top-4 right-4 bg-gray-800/80 text-white px-4 py-2 rounded-full shadow hover:bg-gray-700 backdrop-blur-sm transition">Download</a>
                            </div>
                        </template>

                        <template x-if="!imageUrl && !loading">
                            <div class="text-gray-600 flex flex-col items-center">
                                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-lg">Enter a prompt to create magic</span>
                            </div>
                        </template>

                        <!-- Error Toast -->
                        <div x-show="error" class="absolute bottom-4 left-4 right-4 bg-red-500/90 text-white p-4 rounded-lg shadow-lg backdrop-blur-sm text-center" x-transition x-init="setTimeout(() => error = null, 5000)" style="display: none;">
                            <span x-text="error"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function generator() {
            return {
                prompt: '',
                imageUrl: null,
                loading: false,
                error: null,
                credits: {{ auth()->user()->credits }},

                async generate() {
                    if (!this.prompt) return;

                    this.loading = true;
                    this.error = null;

                    try {
                        const response = await fetch('{{ route('api.generate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ prompt: this.prompt })
                        });

                        const data = await response.json();

                        if (!response.ok || !data.success) throw new Error(data.error || 'Generation failed');

                        this.imageUrl = data.image_url;
                        this.credits = data.remaining_credits;

                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>
