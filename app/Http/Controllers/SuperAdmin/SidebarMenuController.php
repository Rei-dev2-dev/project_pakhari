<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SidebarMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SidebarMenuController extends Controller
{
    /**
     * Display a listing of sidebar menus for configuration.
     */
    public function index(): View
    {
        $menus = SidebarMenu::orderBy('sort_order', 'asc')->get();

        return view('superadmin.menus.index', [
            'menus' => $menus,
        ]);
    }

    /**
     * Show the form for creating a new custom sidebar menu.
     */
    public function create(): View
    {
        return view('superadmin.menus.create');
    }

    /**
     * Store a newly created sidebar menu.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'in:staff,admin,superadmin'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? (SidebarMenu::max('sort_order') + 1);

        $menu = SidebarMenu::create($validated);

        return redirect()->route('superadmin.menus.index')->with('success', "Menu '{$menu->title}' berhasil ditambahkan ke sidebar.");
    }

    /**
     * Show the form for editing the specified sidebar menu.
     */
    public function edit(SidebarMenu $menu): View
    {
        return view('superadmin.menus.edit', [
            'menu' => $menu,
        ]);
    }

    /**
     * Update the specified sidebar menu.
     */
    public function update(Request $request, SidebarMenu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'in:staff,admin,superadmin'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? $menu->sort_order;

        $menu->update($validated);

        return redirect()->route('superadmin.menus.index')->with('success', "Pengaturan menu '{$menu->title}' berhasil diperbarui.");
    }

    /**
     * Toggle visibility (show/hide) of a sidebar menu.
     */
    public function toggle(SidebarMenu $menu): JsonResponse|RedirectResponse
    {
        $menu->update([
            'is_active' => ! $menu->is_active,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $menu->is_active,
                'message' => "Visibilitas menu '{$menu->title}' berhasil diubah.",
            ]);
        }

        $statusText = $menu->is_active ? 'ditampilkan' : 'disembunyikan';

        return back()->with('success', "Menu '{$menu->title}' sekarang {$statusText} di sidebar.");
    }

    /**
     * Remove the specified sidebar menu.
     */
    public function destroy(SidebarMenu $menu): RedirectResponse
    {
        $title = $menu->title;
        $menu->delete();

        return redirect()->route('superadmin.menus.index')->with('success', "Menu '{$title}' berhasil dihapus dari sidebar.");
    }
}
