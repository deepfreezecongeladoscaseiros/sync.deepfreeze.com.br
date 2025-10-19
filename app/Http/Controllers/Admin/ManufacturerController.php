<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManufacturerController extends Controller
{
    public function index(Request $request)
    {
        $query = Manufacturer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            
            $query->where(function($q) use ($search) {
                $q->where('trade_name', 'like', "%{$search}%")
                  ->orWhere('legal_name', 'like', "%{$search}%")
                  ->orWhere('legacy_id', 'like', "%{$search}%");
            });
        }

        $manufacturers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        
        return view('admin.manufacturers.index', compact('manufacturers'));
    }

    public function create()
    {
        return view('admin.manufacturers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'trade_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'cnpj' => 'nullable|string|max:18',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:9',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'active' => 'nullable|boolean',
        ]);

        Manufacturer::create($validated);

        return redirect()->route('admin.manufacturers.index')->with('success', 'Manufacturer created successfully.');
    }

    public function show(Manufacturer $manufacturer)
    {
        return view('admin.manufacturers.show', compact('manufacturer'));
    }

    public function edit(Manufacturer $manufacturer)
    {
        return view('admin.manufacturers.edit', compact('manufacturer'));
    }

    public function update(Request $request, Manufacturer $manufacturer)
    {
        $validated = $request->validate([
            'trade_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'cnpj' => 'nullable|string|max:18',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:9',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'active' => 'nullable|boolean',
        ]);

        $manufacturer->update($validated);

        return redirect()->route('admin.manufacturers.index')->with('success', 'Manufacturer updated successfully.');
    }

    public function destroy(Manufacturer $manufacturer)
    {
        $manufacturer->delete();
        return redirect()->route('admin.manufacturers.index')->with('success', 'Manufacturer deleted successfully.');
    }
}
