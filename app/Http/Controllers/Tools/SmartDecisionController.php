<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\SmartFavoriteMenuService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartDecisionController extends Controller
{
    public function index(Request $request, SmartFavoriteMenuService $service): View
    {
        $monthInput = $request->string('month')->toString();
        $month = $monthInput !== ''
            ? Carbon::createFromFormat('Y-m', $monthInput, 'Asia/Jakarta')->startOfMonth()
            : now('Asia/Jakarta')->startOfMonth();

        $periodStart = $month->copy()->startOfMonth()->utc();
        $periodEnd = $month->copy()->endOfMonth()->utc();

        $weights = [
            'quantity' => $request->input('w_quantity', SmartFavoriteMenuService::CRITERIA['quantity']['default_weight']),
            'frequency' => $request->input('w_frequency', SmartFavoriteMenuService::CRITERIA['frequency']['default_weight']),
            'revenue' => $request->input('w_revenue', SmartFavoriteMenuService::CRITERIA['revenue']['default_weight']),
            'avg_qty' => $request->input('w_avg_qty', SmartFavoriteMenuService::CRITERIA['avg_qty']['default_weight']),
        ];

        $result = $service->analyze($periodStart, $periodEnd, $weights);

        return view('tools.smart.index', [
            'month' => $month->format('Y-m'),
            'monthLabel' => $month->timezone('Asia/Jakarta')->translatedFormat('F Y'),
            'criteria' => SmartFavoriteMenuService::CRITERIA,
            'weights' => $result['weights'],
            'result' => $result,
        ]);
    }

    public function applyFavorite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_code' => ['required', 'string', 'max:64'],
            'month' => ['nullable', 'date_format:Y-m'],
            'w_quantity' => ['nullable', 'numeric', 'min:0'],
            'w_frequency' => ['nullable', 'numeric', 'min:0'],
            'w_revenue' => ['nullable', 'numeric', 'min:0'],
            'w_avg_qty' => ['nullable', 'numeric', 'min:0'],
        ]);

        $menu = Menu::query()->where('code', $validated['menu_code'])->firstOrFail();

        Menu::query()->where('most_ordered', true)->update(['most_ordered' => false]);
        $menu->update(['most_ordered' => true]);

        return redirect()
            ->route('tools.smart.index', [
                'month' => $validated['month'] ?? now('Asia/Jakarta')->format('Y-m'),
                'w_quantity' => $validated['w_quantity'] ?? null,
                'w_frequency' => $validated['w_frequency'] ?? null,
                'w_revenue' => $validated['w_revenue'] ?? null,
                'w_avg_qty' => $validated['w_avg_qty'] ?? null,
            ])
            ->with('success', "Flag \"Paling Laris\" diperbarui ke {$menu->name} berdasarkan hasil SMART.");
    }
}
