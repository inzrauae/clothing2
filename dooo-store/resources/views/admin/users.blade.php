@extends('layouts.admin')
@section('title', 'Team')

@section('content')
<div class="page-head">
  <div><div class="crumb">Store</div><h1>Team</h1><p>People who can sign in to this admin panel.</p></div>
  <div class="actions"><button class="btn btn-primary" type="button" data-open="newUser"><x-i n="plus"/>Add team member</button></div>
</div>

<div class="grid g-main">
  <section class="card">
    <div class="table-wrap">
      <table class="t">
        <thead><tr><th>Name</th><th>Role</th><th>Last sign-in</th><th class="num">Actions</th></tr></thead>
        <tbody>
        @foreach($users as $u)
          <tr>
            <td><div class="who"><span class="avatar">{{ $u->initials }}</span><div><b>{{ $u->name }} @if($u->is(auth()->user()))<span class="badge plain s-signature">You</span>@endif</b><small>{{ $u->email }}</small></div></div></td>
            <td><span class="badge plain {{ $u->role === 'owner' ? 's-signature' : ($u->role === 'admin' ? 's-confirmed' : 's-returned') }}">{{ \App\Models\User::ROLES[$u->role] ?? ucfirst($u->role) }}</span></td>
            <td class="muted">{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
            <td class="num"><div class="actions" style="justify-content:flex-end;flex-wrap:nowrap">
              <button class="btn btn-ghost xs" type="button" data-open="user{{ $u->id }}"><x-i n="edit"/>Edit</button>
              @unless($u->is(auth()->user()))
                <form method="post" action="{{ route('admin.users.destroy', $u) }}" data-confirm="Remove {{ $u->name }}? They will no longer be able to sign in.">@csrf @method('DELETE')
                  <button class="btn btn-danger xs icon-only" type="submit" aria-label="Remove {{ $u->name }}"><x-i n="trash"/></button></form>
              @endunless
            </div></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </section>
  <section class="card">
    <div class="card-h"><h2>Roles</h2></div>
    <div class="card-b" style="display:grid;gap:14px;font-size:14px">
      <div><span class="badge plain s-signature">Owner</span><p class="muted" style="color:var(--muted);margin-top:6px">Full access, including settings and team. There must always be at least one owner.</p></div>
      <div><span class="badge plain s-confirmed">Admin</span><p style="color:var(--muted);margin-top:6px">Full access, including settings and team.</p></div>
      <div><span class="badge plain s-returned">Staff</span><p style="color:var(--muted);margin-top:6px">Orders, products, customers and messages, but not settings or team.</p></div>
    </div>
  </section>
</div>

<dialog class="modal" id="newUser">
  <form method="post" action="{{ route('admin.users.store') }}">
    @csrf
    <div class="modal-h"><h3>Add team member</h3><button type="button" class="btn btn-ghost xs icon-only" data-close-dialog aria-label="Close"><x-i n="x"/></button></div>
    <div class="modal-b" style="display:grid;gap:16px">
      <div class="f"><label>Name <i>*</i></label><input class="in" name="name" value="{{ old('name') }}" required></div>
      <div class="f"><label>Email <i>*</i></label><input class="in" type="email" name="email" value="{{ old('email') }}" required autocomplete="off"></div>
      <div class="f"><label>Role</label><select class="in" name="role">@foreach(\App\Models\User::ROLES as $k => $l)<option value="{{ $k }}" @selected(old('role', 'staff') === $k)>{{ $l }}</option>@endforeach</select></div>
      <div class="f"><label>Password <i>*</i></label><input class="in" type="password" name="password" minlength="10" required autocomplete="new-password"><span class="hint">At least 10 characters, with letters and numbers. Share it with them privately.</span></div>
    </div>
    <div class="modal-f"><button type="button" class="btn btn-ghost sm" data-close-dialog>Cancel</button><button class="btn btn-primary sm" type="submit">Add member</button></div>
  </form>
</dialog>

@foreach($users as $u)
<dialog class="modal" id="user{{ $u->id }}">
  <form method="post" action="{{ route('admin.users.update', $u) }}">
    @csrf @method('PUT')
    <div class="modal-h"><h3>Edit {{ $u->name }}</h3><button type="button" class="btn btn-ghost xs icon-only" data-close-dialog aria-label="Close"><x-i n="x"/></button></div>
    <div class="modal-b" style="display:grid;gap:16px">
      <div class="f"><label>Name</label><input class="in" name="name" value="{{ $u->name }}" required></div>
      <div class="f"><label>Email</label><input class="in" value="{{ $u->email }}" disabled></div>
      <div class="f"><label>Role</label><select class="in" name="role">@foreach(\App\Models\User::ROLES as $k => $l)<option value="{{ $k }}" @selected($u->role === $k)>{{ $l }}</option>@endforeach</select></div>
      <div class="f"><label>New password</label><input class="in" type="password" name="password" minlength="10" autocomplete="new-password" placeholder="Leave empty to keep current"></div>
    </div>
    <div class="modal-f"><button type="button" class="btn btn-ghost sm" data-close-dialog>Cancel</button><button class="btn btn-primary sm" type="submit">Save</button></div>
  </form>
</dialog>
@endforeach
@endsection
