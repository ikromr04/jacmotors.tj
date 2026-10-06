<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings;
use App\Filament\Resources\Cars\Pages\EditCar;
use App\Models\Car;
use App\Models\Dealer;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
    }

    public function test_every_admin_screen_opens(): void
    {
        $this->actingAs(User::factory()->create());

        $car = Car::query()->firstWhere('slug', 'jac-t6');
        $car->sections()->create([
            'title' => ['ru' => 'Экстерьер'],
            'items' => [['title' => ['ru' => 'Фары'], 'text' => ['ru' => 'LED'], 'image' => null]],
        ]);
        $lead = Lead::query()->create(['type' => 'callback', 'phone' => '+992900000000']);

        $urls = [
            '/admin',
            '/admin/leads', "/admin/leads/{$lead->id}/edit",
            '/admin/cars', '/admin/cars/create', "/admin/cars/{$car->slug}/edit",
            '/admin/car-categories', '/admin/car-categories/create',
            '/admin/slides', '/admin/slides/create',
            '/admin/dealers', '/admin/dealers/create', '/admin/dealers/'.Dealer::query()->value('id').'/edit',
            '/admin/posts', '/admin/posts/create',
            '/admin/pages', '/admin/pages/'.Page::query()->value('id').'/edit',
            '/admin/documents', '/admin/documents/create',
            '/admin/users', '/admin/settings',
        ];

        foreach ($urls as $url) {
            $this->assertSame(200, $this->get($url)->status(), "Failed: {$url}");
        }
    }

    public function test_car_can_be_edited_with_translations(): void
    {
        $this->actingAs(User::factory()->create());
        $car = Car::query()->firstWhere('slug', 'jac-t6');

        Livewire::test(EditCar::class, ['record' => $car->getRouteKey()])
            ->fillForm([
                'tagline' => ['ru' => 'Новый слоган', 'tg' => 'Шиори нав', 'en' => 'New slogan'],
                'price_from' => 350000,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $car->refresh();
        $this->assertSame('Шиори нав', $car->tr('tagline', 'tg'));
        $this->assertSame(350000, (int) $car->price_from);
    }

    public function test_settings_are_saved(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Settings::class)
            ->fillForm(['phone' => '+992 00 000 00 00', 'lead_emails' => 'a@b.tj'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('+992 00 000 00 00', Setting::get('phone'));
        $this->assertSame('a@b.tj', Setting::get('lead_emails'));
    }
}
