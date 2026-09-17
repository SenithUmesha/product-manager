<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">Product Manager</a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-white-50 small">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" class="mb-0">
                @csrf
                <button class="btn btn-sm btn-outline-light" type="submit">Log out</button>
            </form>
        </div>
    </div>
</nav>
