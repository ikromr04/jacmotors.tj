<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $preferred = request()->getPreferredLanguage(array_keys(config('app.locales')));

    return redirect()->route('home', ['locale' => $preferred ?: config('app.locale')]);
});

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::post('leads', [LeadController::class, 'store'])
    ->middleware('throttle:leads')
    ->name('leads.store');

Route::prefix('{locale}')
    ->where(['locale' => implode('|', array_keys(config('app.locales')))])
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');

        Route::get('models', [CarController::class, 'index'])->name('cars.index');
        Route::get('models/{car}', [CarController::class, 'show'])->name('cars.show');

        Route::get('promotions', [PostController::class, 'promotions'])->name('promotions.index');
        Route::get('promotions/{post}', [PostController::class, 'showPromotion'])->name('promotions.show');
        Route::get('news', [PostController::class, 'news'])->name('news.index');
        Route::get('news/{post}', [PostController::class, 'showNews'])->name('news.show');

        Route::get('dealers', DealerController::class)->name('dealers');
        Route::get('service', [DocumentController::class, 'service'])->name('service');
        Route::get('manuals', [DocumentController::class, 'manuals'])->name('manuals');
        Route::get('support', [PageController::class, 'support'])->name('support');

        Route::get('{page:slug}', [PageController::class, 'show'])
            ->whereIn('page', ['about', 'guarantee', 'privacy'])
            ->name('pages.show');
    });

/*
| Old URLs of the previous site, kept for search engines and bookmarks.
*/
$legacyCars = [
    'jac-t9' => 'jac-t9', 't8-pro' => 'jac-t8-pro', 'jac-t6' => 'jac-t6', 'jac-j7' => 'jac-j7',
    'jac-j7-plus' => 'jac-j7-plus', 'jac-js8' => 'jac-js8', 'jac-js4' => 'jac-js4', 'js6' => 'jac-js6',
    'N35' => 'jac-n35', 'N56' => 'jac-n56', 'N80' => 'jac-n80', 'N120' => 'jac-n120', 'N350' => 'jac-n350',
    'sunray' => 'jac-sunray', 'm4' => 'jac-m4',
];

foreach ($legacyCars as $old => $slug) {
    Route::permanentRedirect($old, "/ru/models/{$slug}");
}

foreach ([
    'about' => '/ru/about', 'guarantee' => '/ru/guarantee', 'models' => '/ru/models', 'news' => '/ru/news',
    'promotions' => '/ru/promotions', 'feedback' => '/ru/support', 'manuals' => '/ru/manuals', 'regulations' => '/ru/service',
] as $old => $new) {
    Route::permanentRedirect($old, $new);
}
