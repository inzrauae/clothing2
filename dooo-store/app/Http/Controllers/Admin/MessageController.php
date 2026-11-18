<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = Message::query()
            ->when($request->input('filter') === 'unread', fn (Builder $q) => $q->where('is_read', false))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $t = trim((string) $request->input('q'));
                $q->where(fn (Builder $w) => $w->where('name', 'like', "%{$t}%")->orWhere('message', 'like', "%{$t}%")
                    ->orWhere('phone', 'like', "%{$t}%")->orWhere('order_ref', 'like', "%{$t}%"));
            })
            ->orderBy('is_read')->latest()->paginate(20)->withQueryString();

        return view('admin.messages.index', [
            'messages' => $messages,
            'unread' => Message::query()->where('is_read', false)->count(),
        ]);
    }

    public function show(Message $message): View
    {
        if (! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.messages.show', compact('message'));
    }

    public function unread(Message $message): RedirectResponse
    {
        $message->update(['is_read' => false]);

        return redirect()->route('admin.messages.index')->with('ok', 'Marked as unread.');
    }

    public function readAll(): RedirectResponse
    {
        Message::query()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('ok', 'All messages marked as read.');
    }

    public function destroy(Message $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('ok', 'Message deleted.');
    }
}
