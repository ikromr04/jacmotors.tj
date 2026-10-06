<?php

namespace Tests\Feature;

use App\Enums\PostType;
use App\Models\Car;
use App\Models\Post;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
