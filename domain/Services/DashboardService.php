<?php

namespace domain\Services;

use App\Models\Products;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DashboardService
{
    protected Products $product;

    public function __construct()
    {
        $this->product = new Products();
    }

    public function get(int $productId): Products
    {
        return $this->product->findOrFail($productId);
    }

    public function all()
    {
        return $this->product->newQuery()->latest()->get();
    }

    public function store(array $data, UploadedFile $image): Products
    {
        $path = $image->store('images', 'public');

        return Products::create([
            'name' => $data['name'],
            'price' => $data['price'],
            'image' => '/storage/' . $path,
            'status' => 'inactive',
        ]);
    }

    public function delete(int $productId): void
    {
        $product = $this->product->findOrFail($productId);
        $this->deleteImage($product->image);
        $product->delete();
    }

    public function status(int $productId): Products
    {
        $product = $this->product->findOrFail($productId);
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return $product;
    }

    public function update(array $data, int $productId): Products
    {
        $product = $this->product->findOrFail($productId);
        $product->update([
            'name' => $data['name'],
            'price' => $data['price'],
        ]);

        return $product;
    }

    protected function deleteImage(?string $image): void
    {
        if (!$image || !str_starts_with($image, '/storage/')) {
            return;
        }

        $path = substr($image, strlen('/storage/'));
        Storage::disk('public')->delete($path);
    }
}
