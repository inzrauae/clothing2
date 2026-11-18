@extends('layouts.admin')
@section('title', 'My profile')

@section('content')
<div class="page-head">
  <div><div class="crumb">Account</div><h1>My profile</h1><p>Update your name, sign-in email and password.</p></div>
</div>

<form method="post" action="{{ route('admin.profile.update') }}" class="grid g-2" novalidate>
  @csrf @method('PUT')
  <section class="card">
    <div class="card-h"><h2>Your details</h2></div>
    <div class="card-b" style="display:grid;gap:16px">
      <div class="who"><span class="avatar lg">{{ auth()->user()->initials }}</span><div><b style="font-size:16px">{{ auth()->user()->name }}</b><small style="text-transform:capitalize">{{ auth()->user()->role }}</small></div></div>
      <div class="f {{ $errors->has('name') ? 'bad' : '' }}"><label for="name">Name</label><input class="in" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required></div>
      <div class="f {{ $errors->has('email') ? 'bad' : '' }}"><label for="email">Email</label><input class="in" id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="username"></div>
    </div>
  </section>
  <section class="card">
    <div class="card-h"><h2>Change password</h2><small>Leave empty to keep your current password</small></div>
    <div class="card-b" style="display:grid;gap:16px">
      <div class="f {{ $errors->has('current_password') ? 'bad' : '' }}"><label for="current_password">Current password</label><div class="pw"><input class="in" id="current_password" type="password" name="current_password" autocomplete="current-password"><button type="button" aria-label="Show password"><x-i n="eye"/></button></div></div>
      <div class="f {{ $errors->has('password') ? 'bad' : '' }}"><label for="password">New password</label><div class="pw"><input class="in" id="password" type="password" name="password" autocomplete="new-password" minlength="8"><button type="button" aria-label="Show password"><x-i n="eye"/></button></div><span class="hint">At least 8 characters.</span></div>
      <div class="f"><label for="password_confirmation">Confirm new password</label><input class="in" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"></div>
    </div>
  </section>
  <div class="full" style="grid-column:1/-1"><button class="btn btn-primary" type="submit"><x-i n="check"/>Save profile</button></div>
</form>
@endsection
