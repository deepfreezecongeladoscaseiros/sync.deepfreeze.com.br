<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use App\Services\TrayCommerceService;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
        ]);

        $product->variants()->create($validated);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Variant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Variant $variant)
    {
        return view('admin.variants.edit', compact('variant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Variant $variant)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
        ]);

        $variant->update($validated);

        return redirect()->route('admin.products.edit', $variant->product_id)->with('success', 'Variant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function syncToTray(Variant $variant, TrayCommerceService $trayCommerceService)
    {
        try {
            $trayCommerceService->sendVariant($variant);
            return redirect()->route('admin.products.edit', $variant->product_id)->with('success', 'Variant synced to Tray successfully. Tray ID: ' . $variant->fresh()->tray_id);
        } catch (\Exception $e) {
            return redirect()->route('admin.products.edit', $variant->product_id)->with('error', 'Error syncing variant to Tray: ' . $e->getMessage());
        }
    }

    public function destroy(Variant $variant)
    {
        $productId = $variant->product_id;
        $variant->delete();
        return redirect()->route('admin.products.edit', $productId)->with('success', 'Variant deleted successfully.');
    }
}
