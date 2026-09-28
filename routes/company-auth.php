<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest:company')
    ->prefix('entreprise')
    ->name('company.')
    ->group(function () {
        Route::get('inscription', \App\Livewire\Company\Auth\Register::class)->name('register');
        Route::get('connexion', \App\Livewire\Company\Auth\Login::class)->name('login');
    });

Route::middleware('auth:company')
    ->prefix('entreprise')
    ->name('company.')
    ->group(function () {
        Route::post('deconnexion', function () {
            auth('company')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('logout');

        Route::get('dashboard', function () {
            $company = auth('company')->user();
            $productIds = $company->products()->pluck('id');

            $reviews = \App\Models\Review::whereIn('product_id', $productIds);

            $stats = [
                'products_count' => $productIds->count(),
                'total_views' => $company->products()->sum('views_count'),
                'total_favorites' => \App\Models\Favorite::whereIn('product_id', $productIds)->count(),
                'avg_rating' => round($reviews->avg('rating') ?? 0, 1),
                'reviews_count' => $reviews->count(),
            ];

            return view('company.dashboard', compact('company', 'stats'));
        })->name('dashboard');

        Route::get('produits', \App\Livewire\Company\ProductsIndex::class)->name('products.index');
        Route::get('avis', \App\Livewire\Company\ReviewsIndex::class)->name('reviews.index');
        Route::get('questions', \App\Livewire\Company\QuestionsIndex::class)->name('questions.index');
        Route::get('parametres', \App\Livewire\Company\Settings::class)->name('settings.edit');

        Route::resource('produits', \App\Http\Controllers\Company\ProductController::class)
            ->except(['index'])
            ->parameters(['produits' => 'product'])
            ->names('products');
    });