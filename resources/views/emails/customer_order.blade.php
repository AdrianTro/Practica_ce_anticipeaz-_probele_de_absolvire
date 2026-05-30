<!doctype html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; color: #172033; margin: 0; padding: 24px; }
        .card { max-width: 920px; margin: auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 45px rgba(28, 44, 74, .12); }
        .header { background: linear-gradient(135deg, #0969ff, #ffd21e); color: #ffffff; padding: 28px; }
        .content { padding: 28px; }
        table { border-collapse: collapse; width: 100%; margin-top: 18px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 12px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .total { font-size: 20px; font-weight: 800; text-align: right; }
        .muted { color: #6b7280; font-size: 12px; line-height: 1.4; }
        .image-box { display: inline-block; margin: 4px 10px 10px 0; vertical-align: top; }
        .image-box img { width: 130px; max-width: 130px; border-radius: 12px; border: 1px solid #e5e7eb; display: block; }
        .image-box span { display: block; font-size: 12px; font-weight: 700; color: #6b7280; margin-bottom: 4px; }
        .product-img { width: 120px; max-width: 120px; border-radius: 12px; border: 1px solid #e5e7eb; display: block; }
    </style>
</head>
<body>
@php
    $sideLabel = fn ($side) => $side === 'front' ? 'Față' : ($side === 'back' ? 'Spate' : ($side === 'mug' ? 'Cană' : ucfirst((string) $side)));

    $imageUrl = function (?string $url, ?string $path = null): ?string {
        if ($url) {
            return $url;
        }

        if (! $path) {
            return null;
        }

        return \App\Support\StoredImage::url($path);
    };
@endphp
    <div class="card">
        <div class="header">
            <h1>Comandă confirmată</h1>
            <p>ID comandă: <strong>{{ $order->order_uuid }}</strong></p>
        </div>
        <div class="content">
            <p>Bună, {{ $order->customer_name }}!</p>
            <p>Am primit comanda ta. Mai jos sunt produsul, imaginile încărcate și rezultatul aplicat pe produs:</p>
            <table>
                <thead>
                    <tr>
                        <th>Produs</th>
                        <th>Imagine produs</th>
                        <th>Opțiuni</th>
                        <th>Imagini încărcate</th>
                        <th>Aplicat pe produs</th>
                        <th>Cant.</th>
                        <th>Preț</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $productImage = $item->options['product_image'] ?? $item->product?->mainImagePath();
                            $productImageSrc = $imageUrl($item->options['product_image_url'] ?? null, $productImage);
                            $designItems = collect($item->options['design_items'] ?? [])->filter(fn ($design) => is_array($design) && (!empty($design['image_url']) || !empty($design['image_path'])));
                            $previews = $item->options['design_previews'] ?? [];
                        @endphp
                        <tr>
                            <td><strong>{{ $item->product_name }}</strong><br><span class="muted">{{ $item->category_name }}</span></td>
                            <td>
                                @if($productImageSrc)
                                    <img class="product-img" src="{{ $productImageSrc }}" alt="{{ $item->product_name }}">
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if(!empty($item->options['selected_size'])) Mărime: {{ $item->options['selected_size'] }}<br>@endif
                                @if(!empty($item->options['selected_color'])) Culoare: {{ $item->options['selected_color'] }}<br>@endif
                                @if(!empty($item->options['modification_label'])) {{ $item->options['modification_label'] }}<br>@endif
                                @if(!empty($item->options['custom_design_fee'])) Personalizare: +{{ number_format((float) $item->options['custom_design_fee'], 2) }} MDL @endif
                            </td>
                            <td>
                                @forelse($designItems as $index => $design)
                                    @php $designSrc = $imageUrl($design['image_url'] ?? null, $design['image_path'] ?? null); @endphp
                                    @if($designSrc)
                                        <span class="image-box">
                                            <span>Design {{ $index + 1 }} / {{ $sideLabel($design['side'] ?? '') }}</span>
                                            <img src="{{ $designSrc }}" alt="Design încărcat {{ $index + 1 }}">
                                        </span>
                                    @endif
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td>
                                @forelse($previews as $side => $preview)
                                    @php $previewSrc = $imageUrl($item->options['design_preview_urls'][$side] ?? null, $item->options['design_preview_paths'][$side] ?? null); @endphp
                                    @if($previewSrc)
                                        <span class="image-box">
                                            <span>{{ $sideLabel($side) }}</span>
                                            <img src="{{ $previewSrc }}" alt="Design aplicat {{ $side }}">
                                        </span>
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
            <p class="total">Subtotal: {{ number_format((float) $order->total_before_discount, 2) }} MDL</p>
            @if($order->discount_amount > 0)
                <p class="total">Promocod {{ $order->promocode_code }}: -{{ number_format((float) $order->discount_amount, 2) }} MDL</p>
            @endif
            <p class="total">Total: {{ number_format((float) $order->total, 2) }} MDL</p>
            <p>Îți mulțumim,<br>ReclamDesign Modern</p>
        </div>
    </div>
</body>
</html>
