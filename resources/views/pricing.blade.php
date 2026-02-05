<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Buy Credits') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Pack 1 -->
                <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 flex flex-col relative overflow-hidden">
                    <h3 class="text-xl font-bold text-gray-200">Starter Pack</h3>
                    <div class="my-4">
                        <span class="text-4xl font-bold text-white">₹99</span>
                    </div>
                    <p class="text-gray-400 mb-6">300 Credits</p>
                    <button onclick="buyCredits(99, 300)" class="mt-auto w-full py-3 bg-gray-700 hover:bg-gray-600 rounded-lg text-white font-bold transition">Buy Now</button>
                </div>

                <!-- Pack 2 (Popular) -->
                <div class="bg-gray-800 rounded-2xl p-8 border border-electric-blue flex flex-col relative overflow-hidden shadow-[0_0_20px_rgba(0,243,255,0.1)]">
                    <div class="absolute top-0 right-0 bg-electric-blue text-black text-xs font-bold px-3 py-1">POPULAR</div>
                    <h3 class="text-xl font-bold text-white">Pro Pack</h3>
                    <div class="my-4">
                        <span class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-neon-purple to-electric-blue">₹499</span>
                    </div>
                    <p class="text-gray-300 mb-6">1800 Credits</p>
                    <button onclick="buyCredits(499, 1800)" class="mt-auto w-full py-3 bg-gradient-to-r from-neon-purple to-electric-blue text-black font-bold rounded-lg hover:opacity-90 transition">Buy Now</button>
                </div>

                <!-- Pack 3 -->
                <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 flex flex-col relative overflow-hidden">
                    <h3 class="text-xl font-bold text-gray-200">Ultimate Pack</h3>
                    <div class="my-4">
                        <span class="text-4xl font-bold text-white">₹999</span>
                    </div>
                    <p class="text-gray-400 mb-6">4000 Credits</p>
                    <button onclick="buyCredits(999, 4000)" class="mt-auto w-full py-3 bg-gray-700 hover:bg-gray-600 rounded-lg text-white font-bold transition">Buy Now</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        async function buyCredits(amount, credits) {
            try {
                // 1. Create Order
                const response = await fetch('{{ route('payment.create-order') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ amount, credits })
                });

                const data = await response.json();
                if (data.error) throw new Error(data.error);

                // 2. Open Razorpay
                const options = {
                    key: data.key,
                    amount: data.amount,
                    currency: "INR",
                    name: "ToolBaz AI",
                    description: credits + " Credits Pack",
                    order_id: data.order_id,
                    handler: async function (response) {
                        // 3. Verify Payment
                        const verifyResponse = await fetch('{{ route('payment.verify') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_signature: response.razorpay_signature,
                                amount: amount,
                                credits: credits
                            })
                        });

                        const verifyData = await verifyResponse.json();
                        if (verifyData.success) {
                            alert('Payment Successful! Credits added.');
                            window.location.href = '{{ route('generator') }}';
                        } else {
                            alert('Payment Verification Failed');
                        }
                    },
                    prefill: {
                        name: "{{ auth()->user()->name }}",
                        email: "{{ auth()->user()->email }}",
                    },
                    theme: {
                        color: "#b026ff"
                    }
                };

                const rzp1 = new Razorpay(options);
                rzp1.open();

            } catch (e) {
                alert('Error: ' + e.message);
            }
        }
    </script>
</x-app-layout>
