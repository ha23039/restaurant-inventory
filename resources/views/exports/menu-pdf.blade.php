<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú - {{ $settings->restaurant_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 10mm;
            size: A4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1a202c;
            line-height: 1.5;
            background: #f9fafb;
        }

        .page-wrapper {
            border: 3px solid {{ $settings->primary_color ?? '#3b82f6' }};
            background: #ffffff;
            padding: 15px;
            min-height: 277mm;
        }

        /* Elegant Header */
        .header {
            text-align: center;
            padding: 25px 20px;
            background-color: {{ $settings->primary_color ?? '#3b82f6' }};
            color: #ffffff !important;
            margin-bottom: 30px;
            position: relative;
            border-bottom: 4px solid {{ $settings->secondary_color ?? '#8b5cf6' }};
        }

        .header * {
            color: #ffffff !important;
        }

        .logo-container {
            margin-bottom: 12px;
        }

        .logo {
            max-width: 80px;
            max-height: 80px;
            border-radius: 50%;
            background: white;
            padding: 5px;
            border: 3px solid white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .restaurant-name {
            font-size: 28px;
            font-weight: bold;
            margin: 12px 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .tagline {
            font-size: 12px;
            font-style: italic;
            opacity: 0.95;
            margin-bottom: 12px;
            font-weight: 300;
        }

        .contact-info {
            font-size: 9px;
            opacity: 0.9;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }

        .contact-info span {
            margin: 0 8px;
        }

        /* Menu Title Section */
        .menu-section {
            margin-bottom: 20px;
        }

        .menu-title {
            font-size: 20px;
            font-weight: bold;
            color: {{ $settings->primary_color ?? '#3b82f6' }};
            text-align: center;
            margin: 20px 0 18px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
        }

        .menu-title::before,
        .menu-title::after {
            content: '';
            margin: 0 15px;
            opacity: 0.6;
        }

        /* Two Column Layout */
        .menu-container {
            width: 100%;
        }

        .two-columns {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
        }

        .column {
            width: 50%;
            vertical-align: top;
        }

        /* Elegant Menu Item Card */
        .menu-item {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-left: 4px solid {{ $settings->primary_color ?? '#3b82f6' }};
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .menu-item:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .item-header {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .item-name-container {
            display: table-cell;
            width: 70%;
            vertical-align: middle;
        }

        .item-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a202c;
            margin-bottom: 3px;
        }

        .item-price-container {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: middle;
        }

        .item-price {
            font-size: 16px;
            font-weight: bold;
            color: {{ $settings->secondary_color ?? '#8b5cf6' }};
            background: rgba({{ hexToRgb($settings->secondary_color ?? '#8b5cf6') }}, 0.1);
            padding: 4px 10px;
            border-radius: 4px;
            display: inline-block;
        }

        .item-description {
            font-size: 10px;
            color: #4a5568;
            line-height: 1.4;
            margin-top: 6px;
            font-style: italic;
            clear: both;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            float: right;
            margin-left: 10px;
            margin-bottom: 5px;
        }

        .item-content {
            overflow: hidden;
        }

        .service-badge {
            display: inline-block;
            background: {{ $settings->secondary_color ?? '#8b5cf6' }};
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 6px;
            vertical-align: middle;
        }

        /* Single column layout para pocos items */
        .single-column {
            width: 100%;
        }

        .menu-item-wide {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-left: 5px solid {{ $settings->primary_color ?? '#3b82f6' }};
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 14px;
            page-break-inside: avoid;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .menu-item-wide .item-name {
            font-size: 16px;
        }

        .menu-item-wide .item-price {
            font-size: 18px;
            padding: 5px 12px;
        }

        .menu-item-wide .item-description {
            font-size: 11px;
            margin-top: 8px;
        }

        /* Decorative elements */
        .divider {
            height: 1px;
            background: {{ $settings->primary_color ?? '#3b82f6' }};
            margin: 15px 0;
            opacity: 0.3;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 9px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            margin-top: 25px;
        }

        .footer p {
            margin: 4px 0;
        }

        .footer-message {
            font-style: italic;
            color: #4a5568;
            margin-top: 8px;
            font-size: 10px;
        }

        /* Price dots */
        .price-dots {
            border-bottom: 1px dotted #cbd5e0;
            flex-grow: 1;
            margin: 0 8px;
            height: 1em;
        }

        /* Category badge */
        .category-badge {
            display: inline-block;
            background: #f7fafc;
            color: {{ $settings->primary_color ?? '#3b82f6' }};
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            border: 1px solid {{ $settings->primary_color ?? '#3b82f6' }};
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
    <!-- Header -->
    <div class="header">
        @php
            $logoPath = null;
            if ($settings->logo_path) {
                // Convertir /storage/logos/... a ruta absoluta
                $relativePath = str_replace('/storage/', '', $settings->logo_path);
                $absolutePath = storage_path('app/public/' . $relativePath);
                if (file_exists($absolutePath)) {
                    $logoPath = $absolutePath;
                }
            }
        @endphp

        @if($logoPath)
            <div class="logo-container">
                <img src="{{ $logoPath }}" class="logo" alt="{{ $settings->restaurant_name ?? 'Logo' }}">
            </div>
        @endif

        <h1 class="restaurant-name">{{ $settings->restaurant_name ?? 'Restaurant POS' }}</h1>

        @if($settings->tagline ?? false)
            <p class="tagline">{{ $settings->tagline }}</p>
        @endif

        <div class="contact-info">
            @if($settings->restaurant_address ?? false)
                <span>{{ $settings->restaurant_address }}</span>
            @endif
            @if($settings->restaurant_phone ?? false)
                <span>•</span>
                <span>{{ $settings->restaurant_phone }}</span>
            @endif
            @if($settings->restaurant_email ?? false)
                <span>•</span>
                <span>{{ $settings->restaurant_email }}</span>
            @endif
        </div>
    </div>

    <!-- Menu Title -->
    <h2 class="menu-title">Nuestro Menú</h2>

    <!-- Menu Items -->
    <div class="menu-container">
        @php
            $itemCount = $menuItems->count();
            // Usar 2 columnas si hay más de 4 items Y no incluye imágenes
            $useTwoColumns = $itemCount > 4 && !$options['include_images'];
            $halfCount = ceil($itemCount / 2);
        @endphp

        @if($useTwoColumns)
            <!-- Two Column Layout -->
            <table class="two-columns">
                <tr>
                    <!-- Left Column -->
                    <td class="column">
                        @foreach($menuItems->take($halfCount) as $item)
                            <div class="menu-item">
                                <div class="item-header">
                                    <div class="item-name-container">
                                        <div class="item-name">
                                            {{ $item->name }}
                                            @if($item->is_service)
                                                <span class="service-badge">Servicio</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($options['include_prices'])
                                        <div class="item-price-container">
                                            <span class="item-price">${{ number_format($item->price, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                                @if($options['include_descriptions'] && $item->description)
                                    <div class="item-description">{{ $item->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </td>

                    <!-- Right Column -->
                    <td class="column">
                        @foreach($menuItems->skip($halfCount) as $item)
                            <div class="menu-item">
                                <div class="item-header">
                                    <div class="item-name-container">
                                        <div class="item-name">
                                            {{ $item->name }}
                                            @if($item->is_service)
                                                <span class="service-badge">Servicio</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($options['include_prices'])
                                        <div class="item-price-container">
                                            <span class="item-price">${{ number_format($item->price, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                                @if($options['include_descriptions'] && $item->description)
                                    <div class="item-description">{{ $item->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </td>
                </tr>
            </table>
        @else
            <!-- Single Column Layout -->
            <div class="single-column">
                @foreach($menuItems as $item)
                    <div class="menu-item-wide">
                        @php
                            $itemImagePath = null;
                            if ($options['include_images'] && $item->image_path) {
                                $relativePath = str_replace('/storage/', '', $item->image_path);
                                $absolutePath = storage_path('app/public/' . $relativePath);
                                if (file_exists($absolutePath)) {
                                    $itemImagePath = $absolutePath;
                                }
                            }
                        @endphp

                        <div class="item-header">
                            <div class="item-name-container">
                                <div class="item-name">
                                    {{ $item->name }}
                                    @if($item->is_service)
                                        <span class="service-badge">Servicio</span>
                                    @endif
                                </div>
                            </div>
                            @if($options['include_prices'])
                                <div class="item-price-container">
                                    <span class="item-price">${{ number_format($item->price, 2) }}</span>
                                </div>
                            @endif
                        </div>

                        @if($itemImagePath)
                            <img src="{{ $itemImagePath }}" class="item-image" alt="{{ $item->name }}">
                        @endif

                        @if($options['include_descriptions'] && $item->description)
                            <div class="item-description">{{ $item->description }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Menú generado el {{ $generatedAt->format('d/m/Y H:i') }}</strong></p>
        @if($settings->footer_message)
            <p class="footer-message">{{ $settings->footer_message }}</p>
        @endif
        <p style="margin-top: 8px;">Gracias por visitarnos • {{ $settings->restaurant_name ?? 'Restaurant POS' }}</p>
    </div>
    </div><!-- /page-wrapper -->
</body>
</html>

@php
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
        $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
        $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return "$r, $g, $b";
}
@endphp
