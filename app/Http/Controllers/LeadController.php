<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Mail\LeadReceived;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = Lead::create([
            ...Arr::except($request->validated(), ['website']),
            'ip' => $request->ip(),
            'utm' => collect($request->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term']))
                ->filter(fn ($value) => is_string($value) && $value !== '')
                ->map(fn (string $value) => Str::limit($value, 100, ''))
                ->all() ?: null,
        ]);

        $recipients = array_filter(array_map('trim', explode(',', (string) setting('lead_emails'))));

        if ($recipients) {
            // The lead is already saved, so a mail failure must not break the visitor's request.
            try {
                Mail::to($recipients)->send(new LeadReceived($lead));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return response()->json(['message' => __('forms.success')]);
    }
}
