<?php

namespace App\Http\Controllers;

use domain\Facades\DashboardFacade;
use Illuminate\Http\Request;

class DashboardController extends ParentController
{
    public function index()
    {
        return view('pages.dashboard.index', [
            'products' => DashboardFacade::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'image' => ['required', 'image', 'max:4096'],
        ]);

        DashboardFacade::store($validated, $request->file('image'));

        return redirect()->route('dashboard')->with('success', 'Product added.');
    }

    public function delete(int $product_id)
    {
        DashboardFacade::delete($product_id);

        return redirect()->route('dashboard')->with('success', 'Product deleted.');
    }

    public function status(int $product_id)
    {
        DashboardFacade::status($product_id);

        return redirect()->route('dashboard')->with('success', 'Product status updated.');
    }

    public function edit(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        return view('pages.dashboard.edit', [
            'product' => DashboardFacade::get((int) $validated['product_id']),
        ]);
    }

    public function update(Request $request, int $product_id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
        ]);

        DashboardFacade::update($validated, $product_id);

        return redirect()->route('dashboard')->with('success', 'Product updated.');
    }
}
