<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfoBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InfoBlockController extends Controller
{
    public function index()
    {
        $infoBlocks = InfoBlock::ordered()->get();
        return view('admin.info-blocks.index', compact('infoBlocks'));
    }

    public function create()
    {
        $nextOrder = InfoBlock::max('order') + 1;
        return view('admin.info-blocks.create', compact('nextOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|integer|min:1|unique:info_blocks,order',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'image_alt' => 'nullable|string|max:255',
            'title' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:255',
            'background_color' => 'nullable|string|max:20',
            'active' => 'boolean',
        ]);

        $imagePath = $request->file('image')->store('info-blocks', 'public');
        $validated['image_path'] = $imagePath;
        unset($validated['image']);

        InfoBlock::create($validated);

        return redirect()->route('admin.info-blocks.index')
            ->with('success', 'Bloco de informação criado com sucesso!');
    }

    public function edit(InfoBlock $infoBlock)
    {
        return view('admin.info-blocks.edit', compact('infoBlock'));
    }

    public function update(Request $request, InfoBlock $infoBlock)
    {
        $validated = $request->validate([
            'order' => 'required|integer|min:1|unique:info_blocks,order,' . $infoBlock->id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'image_alt' => 'nullable|string|max:255',
            'title' => 'required|string|max:100',
            'subtitle' => 'nullable|string|max:255',
            'background_color' => 'nullable|string|max:20',
            'active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($infoBlock->image_path);
            $imagePath = $request->file('image')->store('info-blocks', 'public');
            $validated['image_path'] = $imagePath;
        }
        
        unset($validated['image']);
        $infoBlock->update($validated);

        return redirect()->route('admin.info-blocks.index')
            ->with('success', 'Bloco de informação atualizado com sucesso!');
    }

    public function destroy(InfoBlock $infoBlock)
    {
        Storage::disk('public')->delete($infoBlock->image_path);
        $infoBlock->delete();

        return redirect()->route('admin.info-blocks.index')
            ->with('success', 'Bloco de informação excluído com sucesso!');
    }
}
