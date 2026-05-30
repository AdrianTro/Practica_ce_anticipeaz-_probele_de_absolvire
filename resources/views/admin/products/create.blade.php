@extends('layouts.app')

@section('title', 'Adauga produs | Admin')

@section('content')
<section class="section-shell admin-form-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1>Adauga produs</h1>
        </div>
        <a class="secondary-btn" href="{{ route('admin.dashboard') }}">Inapoi</a>
    </div>
    <form class="admin-form" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.products._form')
        <button class="primary-btn wide" type="submit">Salveaza produs</button>
    </form>
</section>
@endsection
