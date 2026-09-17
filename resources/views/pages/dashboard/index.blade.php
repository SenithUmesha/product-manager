@extends('layouts.app')

@section('content')
    @include('layouts.nav')

    <main class="container py-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Product Manager</h1>
                <p class="text-muted mb-0">A tiny Laravel CRUD dashboard from 2022, cleaned up without turning it into a different project.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Couldn’t save that product.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Add product</h2>

                <form action="{{ route('dashboard.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="productName" class="form-label">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" id="productName"
                                maxlength="255" required>
                        </div>
                        <div class="col-md-3">
                            <label for="productPrice" class="form-label">Price</label>
                            <input type="number" name="price" value="{{ old('price') }}" min="0" max="9999999.99"
                                step="0.01" class="form-control" id="productPrice" required>
                        </div>
                        <div class="col-md-4">
                            <label for="productImage" class="form-label">Image</label>
                            <input class="form-control" name="image" type="file" id="productImage" accept="image/*" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Add product</button>
                </form>
            </div>
        </section>

        <section class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h5 mb-0">Products</h2>
                    <span class="badge text-bg-secondary">{{ $products->count() }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Product</th>
                                <th scope="col">Price</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <th scope="row">{{ $product->id }}</th>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ asset($product->image) }}" width="72" height="52"
                                                class="rounded border object-fit-cover" alt="{{ $product->name }}">
                                            <strong>{{ $product->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ number_format((float) $product->price, 2) }}</td>
                                    <td>
                                        @if ($product->status === 'active')
                                            <span class="badge text-bg-success">Active</span>
                                        @else
                                            <span class="badge text-bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary"
                                                onclick="openProductEdit({{ $product->id }})" aria-label="Edit {{ $product->name }}">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>

                                            <form action="{{ route('dashboard.status', $product->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                    aria-label="Toggle status for {{ $product->name }}">
                                                    <i class="fa-solid fa-arrows-rotate"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('dashboard.delete', $product->id) }}" method="POST"
                                                onsubmit="return confirm('Delete this product?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    aria-label="Delete {{ $product->name }}">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">No products yet. Add the first one above.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="productEdit" tabindex="-1" aria-labelledby="productEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productEditLabel">Edit product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="productEditContent"></div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        function openProductEdit(productId) {
            $.ajax({
                url: "{{ route('dashboard.edit') }}",
                type: 'GET',
                data: { product_id: productId },
                success: function(response) {
                    $('#productEditContent').html(response);
                    $('#productEdit').modal('show');
                },
                error: function() {
                    alert('Could not load this product.');
                }
            });
        }
    </script>
@endpush
