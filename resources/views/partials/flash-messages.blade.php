@php
    $toastMessages = [];

    if (session('success')) {
        $toastMessages[] = [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => session('success'),
        ];
    }

    if (session('error')) {
        $toastMessages[] = [
            'type' => 'danger',
            'title' => 'Gagal',
            'message' => session('error'),
        ];
    }

    if (session('warning')) {
        $toastMessages[] = [
            'type' => 'warning',
            'title' => 'Perhatian',
            'message' => session('warning'),
        ];
    }

    if (session('info')) {
        $toastMessages[] = [
            'type' => 'info',
            'title' => 'Informasi',
            'message' => session('info'),
        ];
    }

    if (session('status') && ! session('success')) {
        $statusKey = session('status');
        $statusLabels = [
            'profile-updated' => 'Profil berhasil diperbarui.',
            'password-updated' => 'Kata sandi berhasil diperbarui.',
            'verification-link-sent' => 'Tautan verifikasi baru telah dikirim ke email Anda.',
        ];

        $toastMessages[] = [
            'type' => array_key_exists($statusKey, $statusLabels) ? 'success' : 'info',
            'title' => array_key_exists($statusKey, $statusLabels) ? 'Berhasil' : 'Informasi',
            'message' => $statusLabels[$statusKey] ?? $statusKey,
        ];
    }

    $validationErrors = $errors->any() ? $errors->all() : [];
@endphp

<script type="application/json" id="flash-messages-data">
{!! json_encode([
    'toasts' => $toastMessages,
    'errors' => $validationErrors,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>

@stack('flash-toasts')
