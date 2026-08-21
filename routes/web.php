<?php



use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ItemController;

use App\Http\Controllers\DashboardController;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\BorrowingController;

use App\Http\Controllers\UserController;

use App\Http\Controllers\RawMaterialController;

use App\Http\Controllers\MenuImageController;
use App\Http\Controllers\MenuController;

use App\Http\Controllers\SupplierController;

use App\Http\Controllers\BorrowingApprovalController;

use App\Http\Controllers\BorrowingApprovalActionController;

use App\Http\Controllers\ExportController;
use App\Http\Controllers\BorrowingImageController;
use App\Http\Controllers\PosReportController;
use App\Http\Controllers\Tools\SmartDecisionController;

Route::get('/', function () {



    if (Auth::check()) {

        return redirect()->route('dashboard');

    }



    return redirect()->route('login');

});

// Public — POS mengambil gambar menu tanpa session login inventory.
Route::get('/menus/images/{path}', [MenuImageController::class, 'show'])
    ->where('path', '.*')
    ->name('menus.image');

Route::middleware('auth')->group(function () {

    Route::get('/borrowings/images/{path}', [BorrowingImageController::class, 'show'])
        ->where('path', '.*')
        ->name('borrowings.image');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');



    Route::middleware('role:admin,owner')->group(function () {

        Route::resource('items', ItemController::class);

        Route::resource('raw-materials', RawMaterialController::class)->except(['show']);

        Route::resource('suppliers', SupplierController::class);

        Route::resource('menus', MenuController::class)->except(['show']);

    });



    Route::middleware('role:admin,owner,staff')->group(function () {

        Route::get('menus/sell', [MenuController::class, 'sellIndex'])->name('menus.sell.index');

        Route::post('menus/{menu}/sell', [MenuController::class, 'sell'])->name('menus.sell');

        Route::resource('borrowings', BorrowingController::class)->only(['index', 'create', 'store']);

    });



    Route::middleware('role:admin')->group(function () {

        Route::resource('users', UserController::class)->except(['show', 'destroy']);

    });



    Route::middleware('role:admin,owner')->group(function () {

        Route::get('/approvals', [BorrowingApprovalController::class, 'index'])

            ->name('approvals.index');



        Route::post('/approvals/{borrowing}/approve', [BorrowingApprovalActionController::class, 'approve'])

            ->name('approvals.approve');



        Route::post('/approvals/{borrowing}/reject', [BorrowingApprovalActionController::class, 'reject'])

            ->name('approvals.reject');



        Route::get('/borrowings/{borrowing}/return-form', [BorrowingController::class, 'returnForm'])

            ->name('borrowings.return.form');



        Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnItem'])

            ->name('borrowings.return');



        Route::get('/export/borrowings', [ExportController::class, 'borrowings'])

            ->name('export.borrowings');



        Route::get('/export/menu-sales', [ExportController::class, 'menuSales'])

            ->name('export.menu-sales');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/orders/export', [PosReportController::class, 'exportOrders'])->name('orders.export');
            Route::get('/orders', [PosReportController::class, 'indexOrders'])->name('orders.index');
            Route::get('/orders/{order}', [PosReportController::class, 'showOrder'])->name('orders.show');

            Route::get('/payments/export', [PosReportController::class, 'exportPayments'])->name('payments.export');
            Route::get('/payments', [PosReportController::class, 'indexPayments'])->name('payments.index');
            Route::get('/payments/{payment}', [PosReportController::class, 'showPayment'])->name('payments.show');
        });

        Route::prefix('tools')->name('tools.')->group(function () {
            Route::get('/smart', [SmartDecisionController::class, 'index'])->name('smart.index');
            Route::post('/smart/apply-favorite', [SmartDecisionController::class, 'applyFavorite'])->name('smart.apply');
        });

    });

});





require __DIR__ . '/auth.php';

