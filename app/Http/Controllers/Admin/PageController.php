<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Controller para gerenciar Páginas Internas no painel Admin
 *
 * CRUD completo para páginas institucionais dinâmicas.
 */
class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('created_at', 'desc')->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:pages,slug',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', // apenas letras minúsculas, números e hífens
                function ($attribute, $value, $fail) {
                    if (Page::isReservedSlug($value)) {
                        $fail('Esta URL está reservada pelo sistema e não pode ser usada.');
                    }
                },
            ],
            'content' => 'required',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->has('active');
        $data['slug'] = Str::slug($data['slug']); // Garante formato correto

        Page::create($data);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Página criada com sucesso!');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($page->id),
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                function ($attribute, $value, $fail) {
                    if (Page::isReservedSlug($value)) {
                        $fail('Esta URL está reservada pelo sistema e não pode ser usada.');
                    }
                },
            ],
            'content' => 'required',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->has('active');
        $data['slug'] = Str::slug($data['slug']);

        $page->update($data);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Página atualizada com sucesso!');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Página removida com sucesso!');
    }
}
