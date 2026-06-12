<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Warkop Kayu</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center"
        style="background: #f1f5f9;">

        <div class="row w-100 justify-content-center">

            <div class="col-md-5 col-lg-4">

                <div class="card shadow border-0 rounded-4">

                    <div class="card-body guest-card-body">

                        <div class="text-center mb-4">

                            <h2 class="fw-bold mb-2">
                                Sistem Manajemen Warkop Kayu
                            </h2>

                            <p class="text-muted mb-0">
                                Masuk ke akun Anda
                            </p>

                        </div>

                        {{ $slot }}

                    </div>

                </div>

            </div>

        </div>

    </div>

    @include('partials.toast-container')
    @include('partials.flash-error-modal')
    @include('partials.flash-messages')

</body>

</html>