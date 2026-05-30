@extends('layouts.app')

@section('title', 'Editeaza produs | Admin')

@section('content')
<section class="section-shell admin-form-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h1>Editeaza produs</h1>
        </div>
        <a class="secondary-btn" href="{{ route('admin.dashboard') }}">Inapoi</a>
    </div>
    <form class="admin-form" method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.products._form')
        <button class="primary-btn wide" type="submit">Actualizeaza produs</button>
    </form>
</section>
@endsection
