@extends('layouts.app')

@section('title', 'Comanda confirmata | ReclamDesign Modern')

@section('content')
<section class="section-shell success-page">
    <div class="success-card">
        <span class="success-icon">✅</span>
        <h1>Comanda a fost inregistrata</h1>
        <p>ID comanda: <strong>{{ $order->order_uuid }}</strong></p>
        <p>Total: <strong>{{ number_format((float) $order->total, 2) }} MDL</strong></p>
        @if($order->promocode_code)
            <p>Promocod: <strong>{{ $order->promocode_code }}</strong> (-{{ number_format((float) $order->discount_percent, 0) }}%)</p>
        @endif
        <a class="primary-btn" href="{{ route('home') }}">Inapoi la pagina principala</a>
    </div>

    <div class="order-summary-card">
        <h2>Detalii comanda</h2>
        <div class="summary-lines">
            <p><strong>Nume:</strong> {{ $order->customer_name }}</p>
            <p><strong>Telefon:</strong> {{ $order->customer_phone }}</p>
            <p><strong>Email:</strong> {{ $order->customer_email }}</p>
            <p><strong>Subtotal:</strong> {{ number_format((float) $order->total_before_discount, 2) }} MDL</p>
            <p><strong>Reducere:</strong> {{ number_format((float) $order->discount_amount, 2) }} MDL</p>
            <p><strong>Total:</strong> {{ number_format((float) $order->total, 2) }} MDL</p>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Produs</th>
                        <th>Categorie</th>
                        <th>Optiuni</th>
                        <th>Design</th>
                        <th>Cantitate</th>
                        <th>Pret</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php $previews = $item->options['design_previews'] ?? []; @endphp
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->category_name }}</td>
                            <td>
                                @if(!empty($item->options['selected_size'])) Marime: {{ $item->options['selected_size'] }}<br>@endif
                                @if(!empty($item->options['selected_color'])) Culoare: {{ $item->options['selected_color'] }}<br>@endif
                                @if(!empty($item->options['modification_label'])) {{ $item->options['modification_label'] }}<br>@endif
                                @if(!empty($item->options['custom_design_fee'])) +{{ number_format((float) $item->options['custom_design_fee'], 2) }} MDL personalizare @endif
                            </td>
                            <td class="preview-cell">
                                @foreach($previews as $side => $preview)
                                    <div class="mini-preview">
                                        <span>{{ $side === 'front' ? 'Față' : ($side === 'back' ? 'Spate' : 'Cană') }}</span>
                                        <img src="{{ $preview }}" alt="Design {{ $side }}">
                                    </div>
                                @endforeach
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format((float) $item->price, 2) }} MDL</td>
                            <td>{{ number_format((float) $item->subtotal, 2) }} MDL</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    window.ReclamCartClear = true;
</script>
@endpush
