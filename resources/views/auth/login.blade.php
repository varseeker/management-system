<x-guest-layout>
    @if (session('status'))

    <div class="alert alert-success">
        {{ session('status') }}
    </div>

    @endif

    @if ($errors->any())

    <div class="alert alert-danger">

        <ul class="mb-0">

            @foreach ($errors->all() as $error)

            <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">
                Email
            </label>

            <input type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Kata Sandi
            </label>

            <input type="password"
                name="password"
                class="form-control"
                required>
        </div>

        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input"
                    type="checkbox"
                    name="remember"
                    id="remember">

                <label class="form-check-label" for="remember">
                    Ingat saya
                </label>
            </div>
        </div>

        <button class="btn btn-primary w-100 py-2">
            Masuk
        </button>

    </form>

</x-guest-layout>
