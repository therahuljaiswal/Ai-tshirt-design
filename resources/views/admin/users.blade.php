<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Admin Dashboard - Users') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, selectedUser: null, amount: 0 }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-700">
                <div class="p-6 text-gray-100">

                    @if (session('status'))
                        <div class="mb-4 bg-green-900 text-green-200 p-3 rounded">
                            {{ session('status') }}
                        </div>
                    @endif

                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-700">
                                <th class="p-3">ID</th>
                                <th class="p-3">Name</th>
                                <th class="p-3">Email</th>
                                <th class="p-3">Credits</th>
                                <th class="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="border-b border-gray-700 hover:bg-gray-750">
                                    <td class="p-3">{{ $user->id }}</td>
                                    <td class="p-3">{{ $user->name }}</td>
                                    <td class="p-3">{{ $user->email }}</td>
                                    <td class="p-3 font-bold text-electric-blue">{{ $user->credits }}</td>
                                    <td class="p-3">
                                        <button @click="showModal = true; selectedUser = {{ $user->id }}; amount = 100" class="px-3 py-1 bg-neon-purple text-black font-bold rounded text-sm hover:opacity-90">
                                            Gift Credits
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Gift Credits Modal -->
        <div x-show="showModal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50" style="display: none;">
            <div class="bg-gray-800 p-6 rounded-xl border border-gray-600 w-96 max-w-full shadow-2xl">
                <h3 class="text-xl font-bold text-white mb-4">Add Credits</h3>
                <form method="POST" action="{{ route('admin.add-credits') }}">
                    @csrf
                    <input type="hidden" name="user_id" :value="selectedUser">

                    <div class="mb-4">
                        <label class="block text-gray-300 mb-1">Amount</label>
                        <input type="number" name="credits" x-model="amount" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-white">
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-700 text-gray-300 rounded hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-neon-purple to-electric-blue text-black font-bold rounded hover:opacity-90">Add Credits</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
