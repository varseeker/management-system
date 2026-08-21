<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuSale;
use App\Models\PosOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SmartFavoriteMenuService
{
    public const CRITERIA = [
        'quantity' => [
            'label' => 'Jumlah terjual',
            'type' => 'benefit',
            'default_weight' => 0.35,
        ],
        'frequency' => [
            'label' => 'Frekuensi pesanan',
            'type' => 'benefit',
            'default_weight' => 0.30,
        ],
        'revenue' => [
            'label' => 'Total pendapatan',
            'type' => 'benefit',
            'default_weight' => 0.25,
        ],
        'avg_qty' => [
            'label' => 'Rata-rata porsi/pesanan',
            'type' => 'benefit',
            'default_weight' => 0.10,
        ],
    ];

    /**
     * @param  array<string, float|int|string>  $weights
     * @return array{
     *     period_start: Carbon,
     *     period_end: Carbon,
     *     period_label: string,
     *     weights: array<string, float>,
     *     rankings: list<array<string, mixed>>,
     *     favorite: ?array<string, mixed>,
     *     empty: bool
     * }
     */
    public function analyze(Carbon $periodStart, Carbon $periodEnd, array $weights = []): array
    {
        $normalizedWeights = $this->normalizeWeights($weights);
        $rows = $this->collectAlternatives($periodStart, $periodEnd);

        if ($rows->isEmpty()) {
            return [
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'period_label' => $this->periodLabel($periodStart, $periodEnd),
                'weights' => $normalizedWeights,
                'rankings' => [],
                'favorite' => null,
                'empty' => true,
            ];
        }

        $utilities = $this->computeUtilities($rows);
        $rankings = $rows->map(function (array $row, string $key) use ($utilities, $normalizedWeights) {
            $utility = $utilities[$key];
            $score = 0.0;

            foreach ($normalizedWeights as $criterion => $weight) {
                $score += $weight * $utility[$criterion];
            }

            return [
                ...$row,
                'utilities' => $utility,
                'score' => round($score, 6),
            ];
        })
            ->sortByDesc('score')
            ->values()
            ->map(function (array $row, int $index) {
                $row['rank'] = $index + 1;

                return $row;
            })
            ->all();

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'period_label' => $this->periodLabel($periodStart, $periodEnd),
            'weights' => $normalizedWeights,
            'rankings' => $rankings,
            'favorite' => $rankings[0] ?? null,
            'empty' => false,
        ];
    }

    /**
     * @param  array<string, float|int|string>  $weights
     * @return array<string, float>
     */
    public function normalizeWeights(array $weights): array
    {
        $result = [];

        foreach (self::CRITERIA as $key => $meta) {
            $value = isset($weights[$key]) ? (float) $weights[$key] : (float) $meta['default_weight'];
            $result[$key] = max(0.0, $value);
        }

        $sum = array_sum($result);

        if ($sum <= 0) {
            foreach (self::CRITERIA as $key => $meta) {
                $result[$key] = (float) $meta['default_weight'];
            }

            return $result;
        }

        foreach ($result as $key => $value) {
            $result[$key] = round($value / $sum, 6);
        }

        return $result;
    }

    /**
     * @return Collection<string, array<string, mixed>>
     */
    private function collectAlternatives(Carbon $start, Carbon $end): Collection
    {
        /** @var array<string, array<string, mixed>> $bucket */
        $bucket = [];

        $posRows = PosOrderItem::query()
            ->select([
                'pos_order_items.menu_code',
                'pos_order_items.menu_name',
                DB::raw('SUM(pos_order_items.quantity) as total_qty'),
                DB::raw('COUNT(DISTINCT pos_order_items.pos_order_id) as order_count'),
                DB::raw('SUM(pos_order_items.subtotal) as total_revenue'),
            ])
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->whereBetween('pos_orders.ordered_at', [$start, $end])
            ->where(function ($query) {
                $query->where('pos_orders.payment_status', 'success')
                    ->orWhere('pos_orders.order_status', 'paid');
            })
            ->groupBy('pos_order_items.menu_code', 'pos_order_items.menu_name')
            ->get();

        foreach ($posRows as $row) {
            $code = (string) $row->menu_code;
            $this->mergeBucket($bucket, $code, (string) $row->menu_name, (int) $row->total_qty, (int) $row->order_count, (int) $row->total_revenue);
        }

        $directSales = MenuSale::query()
            ->select([
                'menus.code as menu_code',
                'menus.name as menu_name',
                'menus.price as menu_price',
                DB::raw('SUM(menu_sales.quantity) as total_qty'),
                DB::raw('COUNT(menu_sales.id) as order_count'),
            ])
            ->join('menus', 'menus.id', '=', 'menu_sales.menu_id')
            ->whereBetween('menu_sales.created_at', [$start, $end])
            ->whereNull('menu_sales.external_order_id')
            ->groupBy('menus.code', 'menus.name', 'menus.price')
            ->get();

        foreach ($directSales as $row) {
            $qty = (int) $row->total_qty;
            $revenue = $qty * (int) $row->menu_price;
            $this->mergeBucket($bucket, (string) $row->menu_code, (string) $row->menu_name, $qty, (int) $row->order_count, $revenue);
        }

        $menus = Menu::query()
            ->whereIn('code', array_keys($bucket))
            ->get()
            ->keyBy('code');

        return collect($bucket)->map(function (array $row) use ($menus) {
            $menu = $menus->get($row['code']);
            $avgQty = $row['frequency'] > 0
                ? round($row['quantity'] / $row['frequency'], 4)
                : 0.0;

            return [
                'code' => $row['code'],
                'name' => $menu?->name ?? $row['name'],
                'category' => $menu?->category,
                'is_bundle' => (bool) ($menu?->is_bundle ?? false),
                'menu_id' => $menu?->id,
                'quantity' => (float) $row['quantity'],
                'frequency' => (float) $row['frequency'],
                'revenue' => (float) $row['revenue'],
                'avg_qty' => (float) $avgQty,
            ];
        })->keyBy('code');
    }

    /**
     * @param  array<string, array<string, mixed>>  $bucket
     */
    private function mergeBucket(array &$bucket, string $code, string $name, int $qty, int $frequency, int $revenue): void
    {
        if ($code === '') {
            return;
        }

        if (! isset($bucket[$code])) {
            $bucket[$code] = [
                'code' => $code,
                'name' => $name,
                'quantity' => 0,
                'frequency' => 0,
                'revenue' => 0,
            ];
        }

        $bucket[$code]['quantity'] += $qty;
        $bucket[$code]['frequency'] += $frequency;
        $bucket[$code]['revenue'] += $revenue;

        if ($name !== '') {
            $bucket[$code]['name'] = $name;
        }
    }

    /**
     * @param  Collection<string, array<string, mixed>>  $rows
     * @return array<string, array<string, float>>
     */
    private function computeUtilities(Collection $rows): array
    {
        $minmax = [];

        foreach (array_keys(self::CRITERIA) as $criterion) {
            $values = $rows->pluck($criterion)->map(fn ($v) => (float) $v);
            $minmax[$criterion] = [
                'min' => (float) $values->min(),
                'max' => (float) $values->max(),
            ];
        }

        $utilities = [];

        foreach ($rows as $key => $row) {
            $utilities[$key] = [];

            foreach (self::CRITERIA as $criterion => $meta) {
                $value = (float) $row[$criterion];
                $min = $minmax[$criterion]['min'];
                $max = $minmax[$criterion]['max'];

                if ($max == $min) {
                    $utilities[$key][$criterion] = 1.0;
                    continue;
                }

                if ($meta['type'] === 'cost') {
                    $utilities[$key][$criterion] = ($max - $value) / ($max - $min);
                } else {
                    $utilities[$key][$criterion] = ($value - $min) / ($max - $min);
                }
            }
        }

        return $utilities;
    }

    private function periodLabel(Carbon $start, Carbon $end): string
    {
        return $start->timezone('Asia/Jakarta')->translatedFormat('d M Y')
            .' – '
            .$end->timezone('Asia/Jakarta')->translatedFormat('d M Y');
    }
}
