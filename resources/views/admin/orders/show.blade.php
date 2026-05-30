@extends('layouts.app')

@section('title', 'Comanda '.$order->order_uuid.' | Admin')

@section('content')
<section class="section-shell admin-order-page">
    <div class="admin-header">
        <div>
            <span class="eyebrow">Comanda</span>
            <h1>{{ $order->order_uuid }}</h1>
            <p>{{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>
        <a class="secondary-btn" href="{{ route('admin.dashboard') }}">Inapoi</a>
    </div>

    <div class="order-summary-card">
        <h2>Date client</h2>
        <div class="summary-lines">
            <p><strong>Nume:</strong> {{ $order->customer_name }}</p>
            <p><strong>Telefon:</strong> {{ $order->customer_phone }}</p>
            <p><strong>Email:</strong> {{ $order->customer_email }}</p>
            <p><strong>Subtotal:</strong> {{ number_format((float) $order->total_before_discount, 2) }} MDL</p>
            <p><strong>Reducere:</strong> {{ number_format((float) $order->discount_amount, 2) }} MDL</p>
            <p><strong>Total:</strong> {{ number_format((float) $order->total, 2) }} MDL</p>
            <p><strong>Promocod:</strong> {{ $order->promocode_code ?: '—' }}</p>
            <p><strong>Status:</strong> {{ $order->status }}</p>
        </div>
        @php
            $sideLabel = fn ($side) => $side === 'front' ? 'Față' : ($side === 'back' ? 'Spate' : ($side === 'mug' ? 'Cană' : ucfirst((string) $side)));

            $imageSrc = function (?string $url = null, ?string $path = null, ?string $fallbackDataUri = null): ?string {
                if ($url) {
                    return $url;
                }

                if ($path) {
                    return \App\Support\StoredImage::url($path);
                }

                return $fallbackDataUri;
            };
        @endphp

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Produs</th>
                        <th>Imagine produs</th>
                        <th>Categorie</th>
                        <th>Optiuni</th>
                        <th>Imagini aplicate</th>
                        <th>Produs modificat</th>
                        <th>Cantitate</th>
                        <th>Pret</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $options = $item->options ?? [];
                            $productImagePath = $options['product_image'] ?? $item->product?->mainImagePath();
                            $productImage = $imageSrc($options['product_image_url'] ?? null, $productImagePath);
                            $designItems = collect($options['design_items'] ?? [])
                                ->filter(fn ($design) => is_array($design) && (!empty($design['image_url']) || !empty($design['image_path']) || !empty($design['image'])));
                            $previews = $options['design_previews'] ?? [];
                        @endphp
                        <tr>
                            <td><strong>{{ $item->product_name }}</strong></td>
                            <td class="preview-cell">
                                @if($productImage)
                                    <div class="mini-preview">
                                        <span>Original</span>
                                        <img src="{{ $productImage }}" alt="{{ $item->product_name }}">
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $item->category_name }}</td>
                            <td>
                                @if(!empty($options['selected_size'])) Marime: {{ $options['selected_size'] }}<br>@endif
                                @if(!empty($options['selected_color'])) Culoare: {{ $options['selected_color'] }}<br>@endif
                                @if(!empty($options['modification_label'])) {{ $options['modification_label'] }}<br>@endif
                                @if(!empty($options['custom_design_fee'])) Personalizare: +{{ number_format((float) $options['custom_design_fee'], 2) }} MDL @endif
                            </td>
                            <td class="preview-cell">
                                @forelse($designItems as $index => $design)
                                    @php $designImage = $imageSrc($design['image_url'] ?? null, $design['image_path'] ?? null, $design['image'] ?? null); @endphp
                                    @if($designImage)
                                        <div class="mini-preview">
                                            <span>img{{ $index + 1 }} / {{ $sideLabel($design['side'] ?? '') }}</span>
                                            <img src="{{ $designImage }}" alt="Imagine aplicata {{ $index + 1 }}">
                                        </div>
                                    @endif
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td class="preview-cell">
                                @forelse($previews as $side => $preview)
                                    @php $previewImage = $imageSrc($options['design_preview_urls'][$side] ?? null, $options['design_preview_paths'][$side] ?? null, $preview); @endphp
                                    @if($previewImage)
                                        <div class="mini-preview">
                                            <span>{{ $sideLabel($side) }}</span>
                                            <img src="{{ $previewImage }}" alt="Produs modificat {{ $side }}">
                                        </div>
                                    @endif
                                @empty
                                    —
                                @endforelse
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
