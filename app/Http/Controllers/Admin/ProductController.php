<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\TrayCommerceService;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('legacy_id', 'like', "%{$search}%")
                  ->orWhereHas('brand', function($q) use ($search) {
                      $q->where('brand', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::all();
        $categories = Category::all();
        $properties = Property::with('values')->get();
        return view('admin.products.create', compact('brands', 'categories', 'properties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'property_values' => 'nullable|array',
            'property_values.*' => 'exists:property_values,id',
            // Adicionar outras regras de validação conforme necessário
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);
        $product->propertyValues()->sync($request->input('property_values', []));

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $brands = Brand::all();
        $categories = Category::all();
        $properties = Property::with('values')->get();
        $product->load('propertyValues', 'manufacturer', 'variations');
        return view('admin.products.edit', compact('product', 'brands', 'categories', 'properties'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'property_values' => 'nullable|array',
            'property_values.*' => 'exists:property_values,id',
            // Adicionar outras regras de validação conforme necessário
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);
        $product->propertyValues()->sync($request->input('property_values', []));

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function syncToTray(Product $product, TrayCommerceService $trayCommerceService)
    {
        try {
            $trayCommerceService->sendProduct($product);
            return redirect()->route('admin.products.edit', $product->id)->with('success', 'Product synced to Tray successfully. Tray ID: ' . $product->fresh()->tray_id);
        } catch (\Exception $e) {
            return redirect()->route('admin.products.edit', $product->id)->with('error', 'Error syncing product to Tray: ' . $e->getMessage());
        }
    }

    public function syncImage(Product $product, TrayCommerceService $trayCommerceService)
    {
        try {
            $trayCommerceService->sendImage($product);
            return redirect()->route('admin.products.edit', $product->id)->with('success', 'Product image synced to Tray successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.products.edit', $product->id)->with('error', 'Error syncing product image to Tray: ' . $e->getMessage());
        }
    }

    public function syncProperties(Product $product, TrayCommerceService $trayCommerceService)
    {
        try {
            $trayCommerceService->assignPropertiesToProduct($product);
            return redirect()->route('admin.products.edit', $product->id)->with('success', 'Product properties synced to Tray successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.products.edit', $product->id)->with('error', 'Error syncing product properties to Tray: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
