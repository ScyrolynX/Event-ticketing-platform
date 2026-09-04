<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $event->title }} - ScyrolynX</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-950 min-h-screen text-slate-100">

    <header class="bg-slate-950/80 backdrop-blur border-b border-violet-500/20 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-blue-500 rounded-md flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <span class="text-white font-bold text-sm">S</span>
                </div>
                <span class="text-lg font-bold text-white">ScyrolynX</span>
            </div>
            <a href="/events" class="text-sm text-violet-400 hover:text-violet-300">← Back to Events</a>
        </div>
    </header>

    <!-- Toast stack: fixed to the top-right, newest notification appears above older ones -->
    <div id="toast-stack" class="fixed top-20 right-6 z-50 flex flex-col-reverse gap-3 w-80"></div>

    <main class="max-w-4xl mx-auto px-6 py-12">
        <div class="h-48 bg-gradient-to-br from-violet-600 via-purple-600 to-blue-600 rounded-2xl relative flex items-end p-8 mb-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.15),_transparent_60%)] rounded-2xl"></div>
            <h1 class="relative text-4xl font-bold text-white">{{ $event->title }}</h1>
        </div>

        <div class="flex items-center gap-4 text-slate-400 mb-8">
            <span>📍 {{ $event->venue }}</span>
            <span class="text-slate-700">·</span>
            <span>📅 {{ $event->event_date->format('F j, Y') }}</span>
            <span class="text-slate-700">·</span>
            <span>🕐 {{ $event->event_date->format('g:i A') }}</span>
        </div>

        @if($event->description)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 mb-8">
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wide mb-2">About This Event</h2>
                <p class="text-slate-300 leading-relaxed">{{ $event->description }}</p>
            </div>
        @endif

        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wide mb-4">Available Tickets</h2>
            <div class="space-y-3">
                @foreach ($event->ticketTypes as $ticketType)
                    <div class="flex justify-between items-center bg-slate-800/50 rounded-lg px-5 py-4">
                        <div>
                            <div class="font-semibold text-white">{{ $ticketType->name }}</div>
                            <div class="text-xs text-slate-400">{{ $ticketType->quantity_available - $ticketType->quantity_sold }} remaining</div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-lg font-bold text-violet-300">GHS {{ number_format($ticketType->price, 2) }}</span>
                            <button
                                class="buy-btn bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-4 py-2 rounded-md transition-all duration-300 active:scale-95"
                                data-ticket-type-id="{{ $ticketType->id }}">
                                Buy
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    <script>
        const toastStack = document.getElementById('toast-stack');

        function showToast(text, isError) {
            const toast = document.createElement('div');
            toast.className = 'px-4 py-3 rounded-lg text-sm border shadow-lg transition-all duration-300 opacity-0 -translate-y-2 ' +
                (isError
                    ? 'bg-red-500/10 text-red-400 border-red-500/30'
                    : 'bg-green-500/10 text-green-400 border-green-500/30');
            toast.textContent = text;

            toastStack.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', '-translate-y-2');
            });

            setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        document.querySelectorAll('.buy-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                btn.classList.add('shadow-lg', 'shadow-violet-500/60', 'ring-2', 'ring-violet-400');
                setTimeout(() => btn.classList.remove('shadow-lg', 'shadow-violet-500/60', 'ring-2', 'ring-violet-400'), 600);

                const token = localStorage.getItem('token');

                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                const ticketTypeId = btn.dataset.ticketTypeId;

                const res = await fetch('/api/v1/orders', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ticket_type_id: ticketTypeId, quantity: 1 }),
                });

                if (res.status === 401) {
                    localStorage.removeItem('token');
                    window.location.href = '/login';
                    return;
                }

                const data = await res.json();

                if (!res.ok) {
                    showToast(data.message || 'Purchase failed.', true);
                    return;
                }

                showToast('Order placed! Total: GHS ' + data.order.total_amount, false);
            });
        });
    </script>
</body>
</html>
