<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SingleBanner;
use Illuminate\Http\Request;
use Storage;

class SingleBannerController extends Controller
{
    /**
     * Exibe listagem de todos os banners únicos
     * Ordenados por 'order'
     */
    public function index()
    {
        $banners = SingleBanner::ordered()->get();
        return view('admin.single-banners.index', compact('banners'));
    }

    /**
     * Exibe formulário de criação
     * Calcula próximo número de ordem
     */
    public function create()
    {
        $nextOrder = SingleBanner::max('order') + 1;
        return view('admin.single-banners.create', compact('nextOrder'));
    }

    /**
     * Armazena novo banner no banco
     * Faz upload das 2 imagens (desktop e mobile)
     */
    public function store(Request $request)
    {
        // Validação de todos os campos
        $data = $request->validate([
            'order' => 'required|integer|unique:single_banners',
            'desktop_image' => 'required|image|max:2048',
            'mobile_image' => 'required|image|max:2048',
            'link' => 'nullable|url|max:255',
            'alt_text' => 'nullable|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'active' => 'boolean',
        ]);

        // Upload da imagem desktop
        $data['desktop_image_path'] = $request->file('desktop_image')->store('single-banners', 'public');

        // Upload da imagem mobile
        $data['mobile_image_path'] = $request->file('mobile_image')->store('single-banners', 'public');

        // Define active como false se não foi enviado (checkbox desmarcado)
        $data['active'] = $request->has('active') ? true : false;

        SingleBanner::create($data);

        return redirect()->route('admin.single-banners.index')
            ->with('success', 'Banner criado com sucesso!');
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(SingleBanner $singleBanner)
    {
        return view('admin.single-banners.edit', compact('singleBanner'));
    }

    /**
     * Atualiza banner existente
     * Substitui imagens se novos uploads forem fornecidos
     */
    public function update(Request $request, SingleBanner $singleBanner)
    {
        // Validação (imagens são opcionais no update)
        $data = $request->validate([
            'order' => 'required|integer|unique:single_banners,order,' . $singleBanner->id,
            'desktop_image' => 'nullable|image|max:2048',
            'mobile_image' => 'nullable|image|max:2048',
            'link' => 'nullable|url|max:255',
            'alt_text' => 'nullable|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'active' => 'boolean',
        ]);

        // Se houver nova imagem desktop, remove a antiga e salva a nova
        if ($request->hasFile('desktop_image')) {
            Storage::disk('public')->delete($singleBanner->desktop_image_path);
            $data['desktop_image_path'] = $request->file('desktop_image')->store('single-banners', 'public');
        }

        // Se houver nova imagem mobile, remove a antiga e salva a nova
        if ($request->hasFile('mobile_image')) {
            Storage::disk('public')->delete($singleBanner->mobile_image_path);
            $data['mobile_image_path'] = $request->file('mobile_image')->store('single-banners', 'public');
        }

        // Define active como false se não foi enviado (checkbox desmarcado)
        $data['active'] = $request->has('active') ? true : false;

        $singleBanner->update($data);

        return redirect()->route('admin.single-banners.index')
            ->with('success', 'Banner atualizado com sucesso!');
    }

    /**
     * Remove banner e suas imagens do storage
     */
    public function destroy(SingleBanner $singleBanner)
    {
        // Remove imagens do storage
        Storage::disk('public')->delete($singleBanner->desktop_image_path);
        Storage::disk('public')->delete($singleBanner->mobile_image_path);

        // Remove registro do banco
        $singleBanner->delete();

        return redirect()->route('admin.single-banners.index')
            ->with('success', 'Banner excluído com sucesso!');
    }
}
