<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyValue;
use Illuminate\Http\Request;

class PropertyValueController extends Controller
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
    public function store(Request $request, Property $property)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);

        $property->values()->create($validated);

        return redirect()->route('admin.properties.show', $property)->with('success', 'Value created successfully.');
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
    public function edit(PropertyValue $value)
    {
        return view('admin.values.edit', compact('value'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PropertyValue $value)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $value->update($validated);
        return redirect()->route('admin.properties.show', $value->property_id)->with('success', 'Value updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PropertyValue $value)
    {
        $propertyId = $value->property_id;
        $value->delete();
        return redirect()->route('admin.properties.show', $propertyId)->with('success', 'Value deleted successfully.');
    }
}
