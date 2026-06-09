<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Item;
use App\Models\Menu;
use App\Models\MenuSale;
use App\Models\RawMaterial;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $totalStock = Item::sum('stock');
        $totalRawMaterials = RawMaterial::count();
        $totalRawStock = RawMaterial::sum('stock');
        $totalMenus = Menu::where('is_active', true)->count();
        $totalSuppliers = Supplier::count();
        $pendingBorrowings = Borrowing::where('status', 'pending')->count();
        $todayMenuSales = MenuSale::whereDate('created_at', today())->sum('quantity');

        $lowStockMaterials = RawMaterial::where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        $recentMenuSales = MenuSale::with(['menu', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        $suppliers = Supplier::with('rawMaterials')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalItems',
            'totalStock',
            'totalRawMaterials',
            'totalRawStock',
            'totalMenus',
            'totalSuppliers',
            'pendingBorrowings',
            'todayMenuSales',
            'lowStockMaterials',
            'recentMenuSales',
            'suppliers',
        ));
    }
}
