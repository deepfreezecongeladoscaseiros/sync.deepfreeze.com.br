<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\TrayCommerceService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $properties = Property::paginate(10);
        return view('admin.properties.index', compact('properties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.properties.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:properties']);
        Property::create($validated);
        return redirect()->route('admin.properties.index')->with('success', 'Property created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        return view('admin.properties.show', compact('property'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property)
    {
        return view('admin.properties.edit', compact('property'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:properties,name,' . $property->id]);
        $property->update($validated);
        return redirect()->route('admin.properties.index')->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function syncToTray(Property $property, TrayCommerceService $trayCommerceService)
    {
        try {
            $trayCommerceService->sendProperty($property);
            return redirect()->route('admin.properties.edit', $property->id)->with('success', 'Property synced to Tray successfully. Tray ID: ' . $property->fresh()->tray_id);
        } catch (\Exception $e) {
            return redirect()->route('admin.properties.edit', $property->id)->with('error', 'Error syncing property to Tray: ' . $e->getMessage());
        }
    }

    public function destroy(Property $property)
    {
        $property->delete();
        return redirect()->route('admin.properties.index')->with('success', 'Property deleted successfully.');
    }
}
