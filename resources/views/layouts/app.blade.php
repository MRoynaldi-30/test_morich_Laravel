<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Production Schedule')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="min-h-screen md:flex">
        <aside class="w-full bg-slate-900 text-white md:min-h-screen md:w-64">
            <div class="border-b border-slate-700 px-6 py-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-300">
                    Production System
                </p>
                <h1 class="mt-2 text-xl font-bold">Schedule Manager</h1>
            </div>

            <nav class="space-y-1 p-4">
                <a href="{{ route('dashboard') }}" class="block rounded-lg px-4 py-3 hover:bg-slate-800">
                    Dashboard
                </a>
                <a href="{{ route('sewing-lines.page') }}" class="block rounded-lg px-4 py-3 hover:bg-slate-800">
                    Sewing Lines
                </a>
                <a href="{{ route('orders.page') }}" class="block rounded-lg px-4 py-3 hover:bg-slate-800">
                    Master Orders
                </a>
                <a href="{{ route('schedules.page') }}" class="block rounded-lg px-4 py-3 hover:bg-slate-800">
                    Production Schedules
                </a>
            </nav>

            <div class="px-6 py-4 text-xs text-slate-400">
                Laravel 11 · Schedule Produksi
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            <header class="border-b border-slate-200 bg-white px-6 py-5">
                <h2 class="text-xl font-bold">@yield('page-title')</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Production planning and daily output monitoring
                </p>
            </header>

            <div class="p-4 md:p-8">
                <div id="notification" class="mb-5 hidden rounded-lg border px-4 py-3 text-sm" role="status"
                    aria-live="polite"></div>

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        window.notify = function(message, success = true) {
            const box = document.getElementById('notification');
            if (!box) return;

            box.textContent = message;
            box.className = 'mb-5 rounded-lg border px-4 py-3 text-sm ' +
                (success ?
                    'border-green-200 bg-green-50 text-green-800' :
                    'border-red-200 bg-red-50 text-red-800');

            box.classList.remove('hidden');
        };
    </script>
    @stack('scripts')
</body>

</html>
