<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\TrayCommerceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            
            $query->where(function($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('legacy_id', 'like', "%{$search}%");
            });
        }

        $brands = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        
        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255|unique:brands',
        ]);

        Brand::create([
            'brand' => $validated['brand'],
            'slug' => Str::slug($validated['brand']),
        ]);

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return view('admin.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255|unique:brands,brand,' . $brand->id,
        ]);

        $brand->update([
            'brand' => $validated['brand'],
            'slug' => Str::slug($validated['brand']),
        ]);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function syncToTray(Brand $brand, TrayCommerceService $trayCommerceService)
    {
        try {
            $trayCommerceService->sendBrand($brand);
            return redirect()->route('admin.brands.edit', $brand->id)->with('success', 'Brand synced to Tray successfully. Tray ID: ' . $brand->fresh()->tray_id);
        } catch (\Exception $e) {
            return redirect()->route('admin.brands.edit', $brand->id)->with('error', 'Error syncing brand to Tray: ' . $e->getMessage());
        }
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }
}
