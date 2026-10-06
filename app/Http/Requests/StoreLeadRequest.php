<?php

namespace App\Http\Requests;

use App\Enums\LeadType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (array_key_exists($this->input('locale'), config('app.locales'))) {
            app()->setLocale($this->input('locale'));
        }
    }

    public function rules(): array
    {
        $type = LeadType::tryFrom((string) $this->input('type'));
        $needsName = $type !== LeadType::Callback;
        $needsCar = in_array($type, [LeadType::TestDrive, LeadType::Purchase, LeadType::Service], true);

        return [
            'type' => ['required', Rule::enum(LeadType::class)],
            'name' => [$needsName ? 'required' : 'nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[\d\s\-()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:120'],
            'car_id' => [$needsCar ? 'required' : 'nullable', Rule::exists('cars', 'id')],
            'dealer_id' => ['nullable', Rule::exists('dealers', 'id')],
            'city' => ['nullable', 'string', 'max:80'],
            'subject' => [$type === LeadType::Feedback ? 'required' : 'nullable', 'string', 'max:120'],
            'payment_method' => ['nullable', 'string', 'max:60'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'message' => [$type === LeadType::Feedback ? 'required' : 'nullable', 'string', 'max:2000'],
            'locale' => ['nullable', Rule::in(array_keys(config('app.locales')))],
            'page_url' => ['nullable', 'url', 'max:255'],
            // Honeypot: real visitors never see this field.
            'website' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return __('forms.attributes');
    }
}
