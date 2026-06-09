<?php



use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ItemController;

use App\Http\Controllers\DashboardController;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\BorrowingController;

use App\Http\Controllers\UserController;

use App\Http\Controllers\RawMaterialController;

use App\Http\Controllers\MenuController;

use App\Http\Controllers\SupplierController;

use App\Http\Controllers\BorrowingApprovalController;

use App\Http\Controllers\BorrowingApprovalActionController;

use App\Http\Controllers\ExportController;
use App\Support\BorrowingImageStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

Route::get('/', function () {



    if (Auth::check()) {

        return redirect()->route('dashboard');

    }



    return redirect()->route('login');

});



Route::middleware('auth')->group(function () {

    Route::get('/borrowings/images/{path}', function (string $path): BinaryFileResponse {
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        $absolute = BorrowingImageStorage::absolutePath($path);

        abort_unless($absolute && is_file($absolute), 404);

        return response()->file($absolute);
    })->where('path', '.*')->name('borrowings.image');

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

    });

});





require __DIR__ . '/auth.php';

