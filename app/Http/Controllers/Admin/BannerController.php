<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controller para gerenciar Banners Hero da home
 */
class BannerController extends Controller
{
    /**
     * Lista todos os banners
     */
    public function index()
    {
        $banners = Banner::ordered()->get();

        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Salva novo banner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image_desktop' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'image_mobile' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'link' => 'nullable|url|max:500',
            'alt_text' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'order' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        // Valida dimensões desktop
        if ($request->hasFile('image_desktop')) {
            $img = getimagesize($request->file('image_desktop')->path());
            if ($img[0] != 1400 || $img[1] != 385) {
                return back()->withErrors(['image_desktop' => "Imagem desktop deve ter 1400x385px. Enviada: {$img[0]}x{$img[1]}px"])->withInput();
            }
        }

        // Valida dimensões mobile
        if ($request->hasFile('image_mobile')) {
            $img = getimagesize($request->file('image_mobile')->path());
            if ($img[0] != 766 || $img[1] != 981) {
                return back()->withErrors(['image_mobile' => "Imagem mobile deve ter 766x981px. Enviada: {$img[0]}x{$img[1]}px"])->withInput();
            }
        }

        // Upload das imagens
        $desktopPath = $request->file('image_desktop')->store('banners', 'public');
        $mobilePath = $request->file('image_mobile')->store('banners', 'public');

        Banner::create([
            'image_desktop' => $desktopPath,
            'image_mobile' => $mobilePath,
            'link' => $request->link,
            'alt_text' => $request->alt_text,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'order' => $request->order,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner criado com sucesso!');
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Atualiza banner
     */
    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'image_desktop' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'image_mobile' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            'link' => 'nullable|url|max:500',
            'alt_text' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'order' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        // Atualiza imagem desktop se enviada
        if ($request->hasFile('image_desktop')) {
            $img = getimagesize($request->file('image_desktop')->path());
            if ($img[0] != 1400 || $img[1] != 385) {
                return back()->withErrors(['image_desktop' => "Imagem desktop deve ter 1400x385px. Enviada: {$img[0]}x{$img[1]}px"])->withInput();
            }

            Storage::disk('public')->delete($banner->image_desktop);
            $banner->image_desktop = $request->file('image_desktop')->store('banners', 'public');
        }

        // Atualiza imagem mobile se enviada
        if ($request->hasFile('image_mobile')) {
            $img = getimagesize($request->file('image_mobile')->path());
            if ($img[0] != 766 || $img[1] != 981) {
                return back()->withErrors(['image_mobile' => "Imagem mobile deve ter 766x981px. Enviada: {$img[0]}x{$img[1]}px"])->withInput();
            }

            Storage::disk('public')->delete($banner->image_mobile);
            $banner->image_mobile = $request->file('image_mobile')->store('banners', 'public');
        }

        $banner->update([
            'link' => $request->link,
            'alt_text' => $request->alt_text,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'order' => $request->order,
            'active' => $request->boolean('active'),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner atualizado com sucesso!');
    }

    /**
     * Remove banner
     */
    public function destroy(Banner $banner)
    {
        Storage::disk('public')->delete([$banner->image_desktop, $banner->image_mobile]);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner removido com sucesso!');
    }
}
