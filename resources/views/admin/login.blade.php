@extends('layouts.app')

@section('title', 'Admin Login | ReclamDesign Modern')

@section('content')
@php
    $activeLockUntil = (int) (session('lock_until') ?? ($lockUntil ?? 0));
@endphp
<section class="section-shell auth-page">
    <form class="auth-card" method="POST" action="{{ route('admin.login.submit') }}" data-admin-login-form>
        @csrf
        <h1>Autentificare Admin</h1>
        <label>Nume admin
            <input name="name" value="{{ old('name') }}" required autofocus @disabled($activeLockUntil > now()->timestamp)>
        </label>
        <label>Parola
            <input name="password" type="password" required @disabled($activeLockUntil > now()->timestamp)>
        </label>
        <button class="primary-btn wide" type="submit" @disabled($activeLockUntil > now()->timestamp)>Intra in admin</button>
    </form>
</section>

<div class="admin-lock-modal" id="admin-lock-modal" data-lock-until="{{ $activeLockUntil }}" @if($activeLockUntil <= now()->timestamp) hidden @endif>
    <div class="admin-lock-card">
        <img src="{{ asset('assets/fără_success/unsuccess.gif') }}" alt="Acces blocat">
        <h2>Parola gresita de 5 ori</h2>
        <p>Acces blocat temporar. Incearca din nou peste <strong id="admin-lock-countdown">10</strong> secunde.</p>
    </div>
</div>
@endsection
