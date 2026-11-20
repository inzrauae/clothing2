<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Honeypot: pretend success so bots don't retry, but save nothing
        if (filled($request->input('website'))) {
            \App\Support\Audit::log('spam.message_blocked');

            return response()->json(['ok' => true, 'whatsapp_url' => wa_link()], 201);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'topic' => ['nullable', Rule::in(config('store.message_topics'))],
            'order_ref' => ['nullable', 'string', 'max:60'],
            'message' => ['required', 'string', 'min:2', 'max:3000'],
        ]);

        $message = Message::query()->create($data + ['ip' => $request->ip()]);

        $text = implode("\n", array_filter([
            'Hi '.setting('store_name', 'dooo').'! ('.($message->topic ?: 'Enquiry').')',
            '',
            $message->message,
            '',
            "Name: {$message->name}",
            $message->phone ? "Phone: {$message->phone}" : null,
            $message->order_ref ? "Ref: {$message->order_ref}" : null,
        ], fn ($l) => $l !== null));

        return response()->json(['ok' => true, 'whatsapp_url' => wa_link($text)], 201);
    }
}
