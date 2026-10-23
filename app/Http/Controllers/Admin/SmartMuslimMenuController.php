<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmartMuslimMenu;
use App\Models\TourPackagePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmartMuslimMenuController extends Controller
{
    public function index(): View
    {
        $tourPackagePages = TourPackagePage::query()
            ->whereNull('smart_muslim_menu_id')
            ->orderBy('name')
            ->get();

        return view('Admin.smart-muslim-menus.index', [
            'menus' => SmartMuslimMenu::query()
                ->whereNull('parent_id')
                ->with(['children', 'pages'])
                ->orderBy('position')
                ->orderBy('id')
                ->get(),
            'privatePages' => $tourPackagePages->reject(fn (TourPackagePage $page): bool => $page->isSeries()),
            'seriesPages' => $tourPackagePages->filter(fn (TourPackagePage $page): bool => $page->isSeries()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'icon' => ['nullable', 'string', Rule::in(array_keys(SmartMuslimMenu::ICONS))],
        ]);

        SmartMuslimMenu::query()->create([
            'label' => trim($validated['label']),
            'icon' => $validated['icon'] ?? 'compass',
            'position' => (int) SmartMuslimMenu::query()->whereNull('parent_id')->max('position') + 1,
        ]);

        return redirect()->route('admin.smart-muslim.menus.index')
            ->with('status', 'Smart Muslim menu created.');
    }

    public function update(Request $request, SmartMuslimMenu $menu): RedirectResponse
    {
        abort_if($menu->parent_id !== null, 404);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'icon' => ['required', 'string', Rule::in(array_keys(SmartMuslimMenu::ICONS))],
        ]);

        $menu->update([
            'label' => trim($validated['label']),
            'icon' => $validated['icon'],
        ]);

        return redirect()->route('admin.smart-muslim.menus.index')
            ->with('status', 'Menu updated.');
    }

    public function storeChild(Request $request, SmartMuslimMenu $menu): RedirectResponse
    {
        abort_if($menu->parent_id !== null, 404);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
        ]);

        $menu->children()->create([
            'label' => trim($validated['label']),
            'position' => (int) $menu->children()->max('position') + 1,
        ]);

        return redirect()->route('admin.smart-muslim.menus.index')
            ->with('status', 'Submenu label added.');
    }

    public function destroy(SmartMuslimMenu $menu): RedirectResponse
    {
        if ($menu->parent_id === null && $menu->pages()->exists()) {
            return redirect()->route('admin.smart-muslim.menus.index')
                ->withErrors(['menu' => 'Delete the pages in this dropdown before deleting its menu label.']);
        }

        $menu->delete();

        return redirect()->route('admin.smart-muslim.menus.index')
            ->with('status', 'Menu label deleted.');
    }
}
