<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Mail\LeadReceived;
use App\Models\Car;
use App\Models\Lead;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        Mail::fake();
    }

    public function test_test_drive_request_is_saved_and_emailed(): void
    {
        Setting::put(['lead_emails' => 'sales@jacmotors.tj, boss@jacmotors.tj']);
        $car = Car::query()->firstWhere('slug', 'jac-t6');

        $this->postJson('/leads', [
            'type' => 'test_drive',
            'name' => 'Фирдавс',
            'phone' => '+992 44 655 81 81',
            'car_id' => $car->id,
            'preferred_date' => now()->addDays(2)->toDateString(),
            'locale' => 'tg',
            'utm_source' => 'instagram',
            'website' => '',
        ])->assertOk()->assertJsonStructure(['message']);

        $lead = Lead::query()->sole();
        $this->assertSame(LeadType::TestDrive, $lead->type);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertTrue($lead->car->is($car));
        $this->assertSame('tg', $lead->locale);
        $this->assertSame(['utm_source' => 'instagram'], $lead->utm);

        Mail::assertSent(LeadReceived::class, fn (LeadReceived $mail) => $mail->hasTo('sales@jacmotors.tj') && $mail->hasTo('boss@jacmotors.tj'));
    }

    public function test_callback_needs_only_a_phone(): void
    {
        $this->postJson('/leads', ['type' => 'callback', 'phone' => '+992900000000'])->assertOk();

        $this->assertSame(1, Lead::query()->count());
    }

    public function test_validation_errors_are_translated(): void
    {
        $this->postJson('/leads', ['type' => 'test_drive', 'phone' => 'abc', 'locale' => 'ru'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'car_id'])
            ->assertJsonPath('errors.name.0', 'Заполните поле «имя».');

        $this->postJson('/leads', ['type' => 'feedback', 'phone' => '+992900000000', 'locale' => 'tg'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'subject', 'message'])
            ->assertJsonPath('errors.message.0', 'Майдони «паём»-ро пур кунед.');

        $this->assertSame(0, Lead::query()->count());
        Mail::assertNothingSent();
    }

    public function test_bots_filling_the_honeypot_are_rejected(): void
    {
        $this->postJson('/leads', ['type' => 'callback', 'phone' => '+992900000000', 'website' => 'http://spam'])
            ->assertUnprocessable();

        $this->assertSame(0, Lead::query()->count());
    }

    public function test_requests_are_rate_limited(): void
    {
        foreach (range(1, 3) as $i) {
            $this->postJson('/leads', ['type' => 'callback', 'phone' => "+99290000000{$i}"])->assertOk();
        }

        $this->postJson('/leads', ['type' => 'callback', 'phone' => '+992900000009'])->assertTooManyRequests();
    }

    public function test_mail_failure_does_not_lose_the_lead(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->postJson('/leads', ['type' => 'callback', 'phone' => '+992900000000'])->assertOk();

        $this->assertSame(1, Lead::query()->count());
    }
}
