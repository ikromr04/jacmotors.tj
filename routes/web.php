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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('robots.txt', fn () => response(implode("\n", [
    'User-agent: *',
    'Disallow: /admin',
    '',
    'Sitemap: '.route('sitemap'),
])."\n")->header('Content-Type', 'text/plain; charset=UTF-8'))->name('robots');

Route::post('leads', [LeadController::class, 'store'])
    ->middleware('throttle:leads')
    ->name('leads.store');

/*
| Public pages: the main language (Russian) lives at the root, the others under /tj and /en.
| Each copy is named after its language ("ru.home", "tg.home"); route('home') picks the current one,
| see AppServiceProvider.
*/
$pages = function () {
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
};

foreach (array_keys(config('app.locales')) as $locale) {
    Route::prefix(locale_prefix($locale))
        ->name("{$locale}.")
        ->middleware(SetLocale::class.':'.$locale)
        ->group($pages);
}

/*
| Former language prefixes: Russian pages used to live under /ru, Tajik ones under /tg.
*/
foreach (array_keys(config('app.locales')) as $locale) {
    if (($prefix = locale_prefix($locale)) === $locale) {
        continue;
    }

    Route::get($locale.'/{path?}', function (Request $request, string $path = '') use ($prefix) {
        $query = $request->getQueryString();

        return redirect('/'.trim("{$prefix}/{$path}", '/').($query ? "?{$query}" : ''), 301);
    })->where('path', '.*');
}

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
    Route::permanentRedirect($old, "/models/{$slug}");
}

foreach (['feedback' => '/support', 'regulations' => '/service'] as $old => $new) {
    Route::permanentRedirect($old, $new);
}
