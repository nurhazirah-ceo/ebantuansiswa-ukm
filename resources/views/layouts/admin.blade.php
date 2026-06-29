<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('page-title', config('app.name', 'eBantuanSiswa UKM')) | {{ config('app.name', 'eBantuanSiswa UKM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.sweet-alert')
</head>

<body class="bg-slate-50 font-sans antialiased text-slate-900">
    <div class="min-h-screen">

        {{-- Navbar sedia ada --}}
        @include('layouts.navigation')

        {{-- Page Content --}}
        <main class="main-content admin-main-content">
            @yield('content')
        </main>

    </div>

    <script>
        document.addEventListener('submit', function (event) {
            const form = event.target.closest('form[data-confirm]');

            if (!form || form.dataset.confirmed === 'true') {
                if (form) {
                    delete form.dataset.confirmed;
                }
                return;
            }

            event.preventDefault();

            Swal.fire({
                icon: form.dataset.confirmIcon || 'question',
                title: form.dataset.confirmTitle || 'Sahkan tindakan?',
                text: form.dataset.confirmText || 'Tindakan ini akan direkodkan dalam sistem.',
                showCancelButton: true,
                confirmButtonText: form.dataset.confirmButton || 'Ya, teruskan',
                cancelButtonText: form.dataset.cancelButton || 'Batal',
                confirmButtonColor: form.dataset.confirmColor || '#071633',
                cancelButtonColor: '#64748b'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.requestSubmit(event.submitter || undefined);
                }
            });
        });
    </script>
</body>
</html>
