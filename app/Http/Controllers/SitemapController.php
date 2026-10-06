<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $locales = array_keys(config('app.locales'));
        $urls = [];

        $add = function (string $route, array $params = [], $updatedAt = null) use (&$urls, $locales) {
            $alternates = collect($locales)->mapWithKeys(fn ($locale) => [
                $locale => route($route, [...$params, 'locale' => $locale]),
            ]);

            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'lastmod' => $updatedAt?->toAtomString(), 'alternates' => $alternates];
            }
        };

        foreach (['home', 'cars.index', 'promotions.index', 'news.index', 'dealers', 'service', 'manuals', 'support'] as $route) {
            $add($route);
        }

        Car::query()->published()->each(fn (Car $car) => $add('cars.show', ['car' => $car->slug], $car->updated_at));
        Page::query()->where('is_published', true)->each(fn (Page $page) => $add('pages.show', ['page' => $page->slug], $page->updated_at));
        Post::query()->published()->each(fn (Post $post) => $add(
            $post->type->value === 'promotion' ? 'promotions.show' : 'news.show',
            ['post' => $post->slug],
            $post->updated_at,
        ));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
