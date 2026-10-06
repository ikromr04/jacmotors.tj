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
        return [['ru'], ['tg'], ['en']];
    }

    #[DataProvider('locales')]
    public function test_public_pages_render_in_every_locale(string $locale): void
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
            $this->get("/{$locale}{$page}")
                ->assertOk()
                ->assertSee('<html lang="'.$locale.'">', false)
                ->assertSee('hreflang="tg"', false);
        }
    }

    public function test_translations_fall_back_to_russian(): void
    {
        $this->get('/en/promotions')->assertSee('Offers');

        Car::query()->where('slug', 'jac-t6')->update(['tagline' => json_encode(['ru' => 'Покоритель дорог'])]);

        $this->get('/en/models/jac-t6')->assertSee('Покоритель дорог');
    }

    public function test_root_redirects_to_preferred_language(): void
    {
        $this->get('/', ['Accept-Language' => 'en-US,en;q=0.9'])->assertRedirect('/en');
        $this->get('/', ['Accept-Language' => 'de-DE'])->assertRedirect('/ru');
    }

    public function test_unknown_pages_and_hidden_models_return_404(): void
    {
        $this->get('/fr/models')->assertNotFound();
        $this->get('/ru/models/unknown')->assertNotFound();
        $this->get('/ru/promotions/unknown')->assertNotFound();

        Car::query()->where('slug', 'jac-t6')->update(['is_published' => false]);
        $this->get('/ru/models/jac-t6')->assertNotFound();
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

        $this->get('/ru/news/opening')->assertOk();
        $this->get('/ru/promotions/opening')->assertNotFound();
        $this->get('/ru/news/future')->assertNotFound();
    }

    public function test_old_site_urls_redirect_permanently(): void
    {
        $this->get('/jac-t6')->assertStatus(301)->assertRedirect('/ru/models/jac-t6');
        $this->get('/js6')->assertRedirect('/ru/models/jac-js6');
        $this->get('/feedback')->assertRedirect('/ru/support');
    }

    public function test_sitemap_lists_models_in_all_languages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(url('/tg/models/jac-t9'), false)
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

        $this->get('/ru')->assertOk()->assertDontSee('googletagmanager.com', false)->assertDontSee('mc.yandex.ru', false);

        $this->app['env'] = 'production';

        $this->get('/ru')->assertOk()->assertSee('gtag/js?id=G-TEST123', false)->assertSee('mc.yandex.ru/metrika/tag.js', false);
    }
}
