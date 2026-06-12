<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">
                Name
            </label>

            <input type="text"
                name="name"
                value="{{ old('name') }}"
                class="form-control"
                required>
        </div>

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
                Password
            </label>

            <input type="password"
                name="password"
                class="form-control"
                required>
        </div>

        <div class="mb-4">
            <label class="form-label">
                Confirm Password
            </label>

            <input type="password"
                name="password_confirmation"
                class="form-control"
                required>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">

            <a href="{{ route('login') }}"
                class="text-decoration-none">
                Already registered?
            </a>

        </div>

        <button class="btn btn-primary w-100 py-2">
            Register
        </button>

    </form>

</x-guest-layout>