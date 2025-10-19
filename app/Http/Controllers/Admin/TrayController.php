<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrayCredential;
use App\Services\TrayCommerceService;
use Illuminate\Http\Request;

class TrayController extends Controller
{
    public function index()
    {
        $credentials = TrayCredential::first();
        return view('admin.tray.index', compact('credentials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|string|max:255',
            'api_host' => 'required|url',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'code' => 'required|string',
        ]);

        TrayCredential::updateOrCreate(
            ['store_id' => $validated['store_id']],
            $validated
        );

        return redirect()->route('admin.tray.index')->with('success', 'Tray credentials saved successfully.');
    }

    public function generateTokens(TrayCommerceService $trayCommerceService)
    {
        try {
            $trayCommerceService->generateTokens();
            return redirect()->route('admin.tray.index')->with('success', 'Tray tokens generated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.tray.index')->with('error', 'Error generating tokens: ' . $e->getMessage());
        }
    }
}
