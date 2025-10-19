<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $integrations = Integration::paginate();
        return view('admin.integrations.index', compact('integrations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.integrations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $integration = Integration::create($request->all());

        return redirect()->route('admin.integrations.show', $integration)
            ->with('success', 'Integration created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Integration $integration)
    {
        return view('admin.integrations.show', compact('integration'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Integration $integration)
    {
        return view('admin.integrations.edit', compact('integration'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Integration $integration)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $integration->update($request->all());

        return redirect()->route('admin.integrations.index')
            ->with('success', 'Integration updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Integration $integration)
    {
        $integration->delete();

        return redirect()->route('admin.integrations.index')
            ->with('success', 'Integration deleted successfully');
    }
}
