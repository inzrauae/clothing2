<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', ['s' => setting_all()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'whatsapp' => ['required', 'string', 'max:20', 'regex:/^\+?[\d\s]{9,16}$/'],
            'whatsapp_display' => ['required', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:20'],
            'phone_display' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:120'],
            'group' => ['nullable', 'string', 'max:120'],
            'facebook' => ['nullable', 'url:http,https', 'max:255'],
            'instagram' => ['nullable', 'url:http,https', 'max:255'],
            'tiktok' => ['nullable', 'url:http,https', 'max:255'],
            'youtube' => ['nullable', 'url:http,https', 'max:255'],
            'announcement' => ['nullable', 'string', 'max:1000'],
            'delivery_fee' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ], [
            'whatsapp.regex' => 'Enter the WhatsApp number in international format, e.g. 94761400696.',
        ]);

        $data['whatsapp'] = preg_replace('/\D/', '', $data['whatsapp']);
        $data['phone'] = preg_replace('/[^\d+]/', '', (string) ($data['phone'] ?? ''));
        $data['delivery_fee'] = (string) (int) ($data['delivery_fee'] ?? 0);
        $data = array_map(fn ($v) => $v ?? '', $data);

        $before = setting_all();
        Setting::put($data);
        Audit::log('settings.updated', ['changed' => array_keys(array_filter($data, fn ($v, $k) => (string) ($before[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH))]);

        return back()->with('ok', 'Settings saved. The website has been updated.');
    }
}
