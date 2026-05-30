@extends('layouts.app')

@section('title', 'Cos produse | ReclamDesign Modern')

@section('content')
<section class="section-shell cart-layout" id="cart-page">
    <div class="cart-left-column">
        <div class="cart-panel">
            <div class="section-heading left-heading">
                <span>Cos produse</span>
                <h1>Produsele tale</h1>
            </div>
            <div id="cart-items" class="cart-items"></div>
            <div class="cart-total-row">
                <span>Subtotal</span>
                <strong id="cart-subtotal">0.00 MDL</strong>
            </div>
            <div class="cart-discount-row" id="cart-discount-row" hidden>
                <span>Reducere</span>
                <strong id="cart-discount">-0.00 MDL</strong>
            </div>
            <div class="cart-total-row final-total">
                <span>Total</span>
                <strong id="cart-total">0.00 MDL</strong>
            </div>
        </div>

        <div class="cart-panel promo-panel">
            <div class="section-heading left-heading compact-heading">
                <span>Promocode</span>
                <h2>Code</h2>
            </div>
            <div class="promo-controls">
                <input id="promocode-input" name="promo_view" placeholder="Scrie codul" autocomplete="off">
                <button class="secondary-btn" type="button" id="apply-promocode">Aplică</button>
                <button class="secondary-btn" type="button" id="clear-promocode" hidden>Șterge</button>
            </div>
            <p class="promo-message" id="promo-message"></p>
        </div>
    </div>

    <form class="checkout-card" method="POST" action="{{ route('cart.checkout') }}" id="checkout-form" novalidate>
        @csrf
        <h2>Formular comanda</h2>
        <label>Nume
            <input name="name" value="{{ old('name') }}" required maxlength="120" data-required-message="Introduceți Nume">
        </label>
        <label>Telefon
            <input name="phone" value="{{ old('phone') }}" required maxlength="40" data-required-message="Introduceți Telefon">
        </label>
        <label>Email
            <input name="email" type="email" value="{{ old('email') }}" required maxlength="160" data-required-message="Introduceți Email">
        </label>
        <input type="hidden" name="promocode" id="promocode-hidden" value="{{ old('promocode') }}">
        <input type="hidden" name="cart_payload" id="cart-payload" value="{{ old('cart_payload') }}">
        <div class="checkout-validation" id="checkout-validation" hidden></div>
        <button class="primary-btn wide" type="submit" id="checkout-button">Comandează</button>
    </form>
</section>
@endsection
