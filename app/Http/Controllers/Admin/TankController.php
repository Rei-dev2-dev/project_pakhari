<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TankController extends Controller
{
    /**
     * Display a listing of tanks for administration.
     */
    public function index(): View
    {
        $tanks = Tank::withCount('telemetries')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15);

        return view('admin.tanks.index', [
            'tanks' => $tanks,
        ]);
    }

    /**
     * Show the form for creating a new tank.
     */
    public function create(): View
    {
        return view('admin.tanks.create');
    }

    /**
     * Store a newly created tank in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:tanks,code'],
            'length_cm' => ['required', 'numeric', 'min:1', 'max:10000'],
            'width_cm' => ['required', 'numeric', 'min:1', 'max:10000'],
            'height_cm' => ['required', 'numeric', 'min:1', 'max:10000'],
            'diameter_cm' => ['required', 'numeric', 'min:1', 'max:10000'],
            'capacity_liters' => ['nullable', 'numeric', 'min:0.1', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['capacity_liters'] = (isset($validated['capacity_liters']) && $validated['capacity_liters'] > 0)
            ? (float) $validated['capacity_liters']
            : Tank::calculateCapacity(
                (float) $validated['length_cm'],
                (float) $validated['width_cm'],
                (float) $validated['height_cm'],
                (float) $validated['diameter_cm']
            );

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? (Tank::max('sort_order') + 1);

        $tank = Tank::create($validated);

        // Seed initial telemetry for the new tank (Default: Kosong 0.0 Liter)
        $tank->telemetries()->create([
            'user_id' => auth()->id(),
            'volume_liters' => 0.0,
            'percentage' => 0.0,
            'height_cm' => 0.0,
            'status' => 'empty',
            'source' => 'system_init',
            'device_id' => 'ADMIN-'.(auth()->user()?->username ?? 'System'),
            'notes' => 'Inisialisasi tangki baru '.$tank->name.' (Kondisi Awal Kosong)',
        ]);

        return redirect()->route('admin.tanks.index')->with('success', "Tangki {$tank->name} berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified tank.
     */
    public function edit(Tank $tank): View
    {
        return view('admin.tanks.edit', [
            'tank' => $tank,
        ]);
    }

    /**
     * Update the specified tank in storage.
     */
    public function update(Request $request, Tank $tank): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', Rule::unique('tanks', 'code')->ignore($tank->id)],
            'length_cm' => ['required', 'numeric', 'min:1', 'max:10000'],
            'width_cm' => ['required', 'numeric', 'min:1', 'max:10000'],
            'height_cm' => ['required', 'numeric', 'min:1', 'max:10000'],
            'diameter_cm' => ['required', 'numeric', 'min:1', 'max:10000'],
            'capacity_liters' => ['nullable', 'numeric', 'min:0.1', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['capacity_liters'] = (isset($validated['capacity_liters']) && $validated['capacity_liters'] > 0)
            ? (float) $validated['capacity_liters']
            : Tank::calculateCapacity(
                (float) $validated['length_cm'],
                (float) $validated['width_cm'],
                (float) $validated['height_cm'],
                (float) $validated['diameter_cm']
            );

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? $tank->sort_order;

        $tank->update($validated);

        return redirect()->route('admin.tanks.index')->with('success', "Data tangki {$tank->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified tank from storage.
     */
    public function destroy(Tank $tank): RedirectResponse
    {
        $name = $tank->name;
        $tank->delete();

        return redirect()->route('admin.tanks.index')->with('success', "Tangki {$name} berhasil dihapus.");
    }
}
