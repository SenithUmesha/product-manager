<form action="{{ route('dashboard.update', $product->id) }}" method="POST">
    @csrf
    @method('PATCH')

    <div class="mb-3">
        <label for="editProductName" class="form-label">Name</label>
        <input type="text" name="name" value="{{ $product->name }}" class="form-control" id="editProductName"
            maxlength="255" required>
    </div>

    <div class="mb-4">
        <label for="editProductPrice" class="form-label">Price</label>
        <input type="number" name="price" value="{{ $product->price }}" min="0" max="9999999.99" step="0.01"
            class="form-control" id="editProductPrice" required>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
</form>
