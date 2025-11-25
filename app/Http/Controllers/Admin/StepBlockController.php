<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\StepBlock;
use Illuminate\Http\Request;
use Storage;

class StepBlockController extends Controller
{
    public function index() {
        return view('admin.step-blocks.index', ['blocks' => StepBlock::ordered()->get()]);
    }

    public function create() {
        return view('admin.step-blocks.create', ['nextOrder' => StepBlock::max('order') + 1]);
    }

    public function store(Request $request) {
        // Validação individual de cada campo (wildcards não funcionam corretamente)
        $data = $request->validate([
            'order' => 'required|integer|unique:step_blocks',
            'active' => 'boolean',
            // Item 1
            'item_1_icon' => 'required|image|max:2048',
            'item_1_title' => 'required|max:100',
            'item_1_description' => 'required',
            'item_1_alt' => 'nullable|max:255',
            // Item 2
            'item_2_icon' => 'required|image|max:2048',
            'item_2_title' => 'required|max:100',
            'item_2_description' => 'required',
            'item_2_alt' => 'nullable|max:255',
            // Item 3
            'item_3_icon' => 'required|image|max:2048',
            'item_3_title' => 'required|max:100',
            'item_3_description' => 'required',
            'item_3_alt' => 'nullable|max:255',
            // Item 4
            'item_4_icon' => 'required|image|max:2048',
            'item_4_title' => 'required|max:100',
            'item_4_description' => 'required',
            'item_4_alt' => 'nullable|max:255',
        ]);

        // Processa upload dos 4 ícones
        foreach([1,2,3,4] as $i) {
            $data["item_{$i}_icon_path"] = $request->file("item_{$i}_icon")->store('step-icons', 'public');
        }

        // Define active como false se não foi enviado (checkbox desmarcado)
        $data['active'] = $request->has('active') ? true : false;

        StepBlock::create($data);
        return redirect()->route('admin.step-blocks.index')->with('success', 'Criado!');
    }

    public function edit(StepBlock $stepBlock) {
        return view('admin.step-blocks.edit', compact('stepBlock'));
    }

    public function update(Request $request, StepBlock $stepBlock) {
        // Validação individual de cada campo (wildcards não funcionam corretamente)
        $data = $request->validate([
            'order' => 'required|integer|unique:step_blocks,order,'.$stepBlock->id,
            'active' => 'boolean',
            // Item 1
            'item_1_icon' => 'nullable|image|max:2048',
            'item_1_title' => 'required|max:100',
            'item_1_description' => 'required',
            'item_1_alt' => 'nullable|max:255',
            // Item 2
            'item_2_icon' => 'nullable|image|max:2048',
            'item_2_title' => 'required|max:100',
            'item_2_description' => 'required',
            'item_2_alt' => 'nullable|max:255',
            // Item 3
            'item_3_icon' => 'nullable|image|max:2048',
            'item_3_title' => 'required|max:100',
            'item_3_description' => 'required',
            'item_3_alt' => 'nullable|max:255',
            // Item 4
            'item_4_icon' => 'nullable|image|max:2048',
            'item_4_title' => 'required|max:100',
            'item_4_description' => 'required',
            'item_4_alt' => 'nullable|max:255',
        ]);

        // Processa upload dos 4 ícones (se houver novos)
        foreach([1,2,3,4] as $i) {
            if($request->hasFile("item_{$i}_icon")) {
                // Remove ícone antigo
                Storage::disk('public')->delete($stepBlock->{"item_{$i}_icon_path"});
                // Salva novo ícone
                $data["item_{$i}_icon_path"] = $request->file("item_{$i}_icon")->store('step-icons', 'public');
            }
        }

        // Define active como false se não foi enviado (checkbox desmarcado)
        $data['active'] = $request->has('active') ? true : false;

        $stepBlock->update($data);
        return redirect()->route('admin.step-blocks.index')->with('success', 'Atualizado!');
    }

    public function destroy(StepBlock $stepBlock) {
        foreach([1,2,3,4] as $i) Storage::disk('public')->delete($stepBlock->{"item_{$i}_icon_path"});
        $stepBlock->delete();
        return redirect()->route('admin.step-blocks.index')->with('success', 'Excluído!');
    }
}
