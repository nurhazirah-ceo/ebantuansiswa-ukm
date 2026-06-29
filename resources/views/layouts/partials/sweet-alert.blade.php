@once
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
@endonce

@php
    $sweetAlert = null;
    $sweetAlertRedirectUrl = session('sweet_alert_redirect_url');
    $ignoredStatusAlerts = ['profile-updated', 'password-updated'];

    foreach ([
        'error' => ['icon' => 'error', 'title' => 'Ralat'],
        'warning' => ['icon' => 'warning', 'title' => 'Perhatian'],
        'success' => ['icon' => 'success', 'title' => 'Berjaya'],
        'status' => ['icon' => 'success', 'title' => 'Berjaya'],
        'info' => ['icon' => 'info', 'title' => 'Makluman'],
    ] as $key => $meta) {
        if (! session()->has($key)) {
            continue;
        }

        $message = session($key);

        if ($key === 'status') {
            if ($message === 'verification-link-sent') {
                $meta = ['icon' => 'success', 'title' => 'Emel dihantar'];
                $message = 'Pautan pengesahan baharu telah dihantar ke emel anda.';
            } elseif (in_array($message, $ignoredStatusAlerts, true)) {
                continue;
            }
        }

        $sweetAlert = [
            'icon' => $meta['icon'],
            'title' => $meta['title'],
            'text' => $message,
            'confirmButtonText' => 'OK',
            'confirmButtonColor' => '#2B4570',
        ];
        break;
    }

    if (! $sweetAlert && isset($errors) && $errors->any()) {
        $sweetAlert = [
            'icon' => 'error',
            'title' => 'Maklumat tidak lengkap',
            'text' => $errors->first().($errors->count() > 1 ? ' Sila semak semua mesej ralat pada borang.' : ''),
            'confirmButtonText' => 'OK',
            'confirmButtonColor' => '#2B4570',
        ];
    }
@endphp

@if ($sweetAlert)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (! window.Swal) {
                return;
            }

            Swal.fire(@json($sweetAlert)).then(function (result) {
                const redirectUrl = @json($sweetAlertRedirectUrl);

                if (result.isConfirmed && redirectUrl) {
                    window.location.href = redirectUrl;
                }
            });
        });
    </script>
@endif
