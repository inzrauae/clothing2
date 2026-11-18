@extends('layouts.admin')
@section('title', 'Messages')

@section('content')
<div class="page-head">
  <div><div class="crumb">Sales</div><h1>Messages</h1><p>Enquiries sent from the website's contact form.</p></div>
  @if($unread)
  <div class="actions"><form method="post" action="{{ route('admin.messages.readAll') }}">@csrf<button class="btn btn-ghost" type="submit"><x-i n="mail-open"/>Mark all as read</button></form></div>
  @endif
</div>

@php($f = request('filter'))
<nav class="tabs">
  <a href="{{ route('admin.messages.index', request()->except('filter', 'page')) }}" class="{{ $f !== 'unread' ? 'on' : '' }}">All</a>
  <a href="{{ route('admin.messages.index', array_merge(request()->except('page'), ['filter' => 'unread'])) }}" class="{{ $f === 'unread' ? 'on' : '' }}">Unread <em>{{ $unread }}</em></a>
</nav>

<section class="card">
  <form class="filters" method="get">
    @if($f)<input type="hidden" name="filter" value="{{ $f }}">@endif
    <div class="search"><x-i n="search"/><input class="in" type="search" name="q" value="{{ request('q') }}" placeholder="Search messages" aria-label="Search messages"></div>
    <button class="btn btn-primary sm" type="submit">Search</button>
  </form>
  @forelse($messages as $m)
    <a class="msg-row {{ $m->is_read ? '' : 'unread' }}" href="{{ route('admin.messages.show', $m) }}">
      <span class="avatar">{{ strtoupper(mb_substr($m->name, 0, 1)) }}</span>
      <div class="msg-body">
        <div class="msg-name"><span>{{ $m->name }}</span>@if($m->topic)<span class="badge plain s-returned">{{ $m->topic }}</span>@endif @if($m->order_ref)<span class="muted" style="font-size:12px;color:var(--muted)">Ref {{ $m->order_ref }}</span>@endif</div>
        <div class="msg-text">{{ $m->message }}</div>
      </div>
      <span class="msg-time">{{ $m->created_at->diffForHumans() }}</span>
    </a>
  @empty
    <div class="empty"><x-i n="messages"/><b>{{ $f === 'unread' ? 'You’re all caught up' : 'No messages yet' }}</b>Messages from the contact page appear here.</div>
  @endforelse
  {{ $messages->links() }}
</section>
@endsection
