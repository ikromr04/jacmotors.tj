<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Models\Car;
use App\Models\Post;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public static function locales(): array
    {
        return [['ru', ''], ['tg', '/tj'], ['en', '/en']];
    }

    #[DataProvider('locales')]
    public function test_public_pages_render_in_every_locale(string $locale, string $prefix): void
    {
        Post::query()->create([
            'type' => PostType::Promotion,
            'slug' => 'spring-offer',
            'title' => ['ru' => 'Весенняя акция', 'en' => 'Spring offer'],
            'body' => ['ru' => '<p>Текст</p>'],
            'published_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);

        $pages = [
            '', '/models', '/models/jac-t6', '/promotions', '/promotions/spring-offer', '/news',
            '/dealers', '/service', '/manuals', '/support', '/about', '/guarantee', '/privacy',
        ];

        foreach ($pages as $page) {
            $this->get($prefix.$page ?: '/')
                ->assertOk()
                ->assertSee('<html lang="'.$locale.'">', false)
                ->assertSee('hreflang="tg"', false);
        }
    }

    public function test_language_alternates_point_to_the_same_page(): void
    {
        $this->get('/models/jac-t6')
            ->assertOk()
            ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/en/models/jac-t6').'">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/models/jac-t6').'">', false);
    }

    public function test_paginated_lists_keep_the_page_in_canonical(): void
    {
        $this->get('/news?page=2')->assertSee('<link rel="canonical" href="'.url('/news?page=2').'">', false);
        $this->get('/news?page=1&utm_source=x')->assertSee('<link rel="canonical" href="'.url('/news').'">', false);
    }

    public function test_pages_describe_themselves_with_structured_data(): void
    {
        Setting::put(['phone' => '+992 00 000 0000']);

        $this->get('/')->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('"@type":"AutoDealer"', false)
            ->assertSee('"telephone":"+992 00 000 0000"', false);

        $this->get('/models/jac-t6')->assertOk()->assertSee('"@type":"Car"', false)->assertDontSee('"Product"', false);

        Car::query()->where('slug', 'jac-t6')->update(['price_from' => 250000]);

        $this->get('/models/jac-t6')->assertOk()
            ->assertSee('"@type":["Product","Car"]', false)
            ->assertSee('"price":250000,"priceCurrency":"TJS"', false);
    }

    public function test_missing_pages_are_not_indexed(): void
    {
        $this->get('/models/unknown')->assertNotFound()->assertSee('<meta name="robots" content="noindex', false);
        $this->get('/models')->assertDontSee('<meta name="robots"', false);
    }

    public function test_robots_txt_allows_indexing_and_points_to_the_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')
            ->assertDontSee("Disallow: /\n")
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }

    public function test_translations_fall_back_to_russian(): void
    {
        $this->get('/en/promotions')->assertSee('Offers');

        Car::query()->where('slug', 'jac-t6')->update(['tagline' => json_encode(['ru' => 'Покоритель дорог'])]);

        $this->get('/en/models/jac-t6')->assertSee('Покоритель дорог');
    }

    public function test_russian_is_served_without_a_language_prefix(): void
    {
        $this->get('/', ['Accept-Language' => 'en-US,en;q=0.9'])->assertOk()->assertSee('<html lang="ru">', false);
    }

    public function test_tajik_pages_live_under_tj_but_keep_the_tg_language_code(): void
    {
        $this->get('/tj/models')->assertOk()
            ->assertSee('<html lang="tg">', false)
            ->assertSee('href="'.url('/tj/models/jac-t6').'"', false);

        $this->get('/models')->assertSee('<link rel="alternate" hreflang="tg" href="'.url('/tj/models').'">', false);
        $this->get('/tj/models/unknown')->assertNotFound()->assertSee('<html lang="tg">', false);
    }

    public function test_former_language_prefixes_redirect_permanently(): void
    {
        $this->get('/ru')->assertStatus(301)->assertRedirect('/');
        $this->get('/ru/models/jac-t6?utm_source=x')->assertStatus(301)->assertRedirect('/models/jac-t6?utm_source=x');
        $this->get('/tg')->assertStatus(301)->assertRedirect('/tj');
        $this->get('/tg/models/jac-t6')->assertStatus(301)->assertRedirect('/tj/models/jac-t6');
    }

    public function test_unknown_pages_and_hidden_models_return_404(): void
    {
        $this->get('/fr/models')->assertNotFound();
        $this->get('/models/unknown')->assertNotFound();
        $this->get('/promotions/unknown')->assertNotFound();

        Car::query()->where('slug', 'jac-t6')->update(['is_published' => false]);
        $this->get('/models/jac-t6')->assertNotFound();
    }

    public function test_news_and_promotions_do_not_leak_into_each_other(): void
    {
        Post::query()->create([
            'type' => PostType::News,
            'slug' => 'opening',
            'title' => ['ru' => 'Открытие'],
            'published_at' => now()->subDay(),
        ]);
        Post::query()->create([
            'type' => PostType::News,
            'slug' => 'future',
            'title' => ['ru' => 'Будущее'],
            'published_at' => now()->addDay(),
        ]);

        $this->get('/news/opening')->assertOk();
        $this->get('/promotions/opening')->assertNotFound();
        $this->get('/news/future')->assertNotFound();
    }

    public function test_old_site_urls_redirect_permanently(): void
    {
        $this->get('/jac-t6')->assertStatus(301)->assertRedirect('/models/jac-t6');
        $this->get('/js6')->assertRedirect('/models/jac-js6');
        $this->get('/feedback')->assertRedirect('/support');
    }

    public function test_sitemap_lists_models_in_all_languages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(url('/tj/models/jac-t9'), false)
            ->assertSee(url('/en/about'), false);
    }

    public function test_pages_share_a_1200x630_jpeg_preview(): void
    {
        Storage::fake('public');
        $hero = UploadedFile::fake()->image('hero.webp', 2500, 1175);
        Car::query()->where('slug', 'jac-t6')->update(['hero_image' => $hero->storeAs('cars/hero', 'hero.webp', 'public')]);

        $this->get('/en/about')->assertOk()
            ->assertSee('<meta property="og:image" content="'.asset('images/og-default.jpg').'">', false)
            ->assertSee('<meta property="og:locale:alternate" content="ru_RU">', false);

        $html = $this->get('/en/models/jac-t6')->assertOk()->getContent();
        preg_match('~<meta property="og:image" content="[^"]*/storage/(og/[^"]+\.jpg)">~', $html, $match);

        $this->assertNotEmpty($match, 'The model page should share its own generated preview.');
        [$width, $height, $type] = getimagesize(Storage::disk('public')->path($match[1]));
        $this->assertSame([1200, 630, IMAGETYPE_JPEG], [$width, $height, $type]);
    }

    public function test_analytics_counters_load_only_in_production(): void
    {
        Setting::put(['google_analytics_id' => 'G-TEST123', 'yandex_metrika_id' => '12345678']);

        $this->get('/')->assertOk()->assertDontSee('googletagmanager.com', false)->assertDontSee('mc.yandex.ru', false);

        $this->app['env'] = 'production';

        $this->get('/')->assertOk()->assertSee('gtag/js?id=G-TEST123', false)->assertSee('mc.yandex.ru/metrika/tag.js', false)->assertSee('mc.yandex.ru/watch/12345678', false);
    }
}
