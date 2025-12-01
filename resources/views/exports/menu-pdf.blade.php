<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú - {{ $settings->restaurant_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid {{ $settings->primary_color }};
        }
        .logo {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 15px;
        }
        h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: {{ $settings->primary_color }};
        }
        .contact-info {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        .contact-info p {
            margin: 2px 0;
        }
        .menu-grid {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        .menu-item {
            display: table-row;
            page-break-inside: avoid;
            margin-bottom: 15px;
        }
        .menu-item-name {
            display: table-cell;
            padding: 10px 5px;
            border-bottom: 1px solid #e0e0e0;
            width: 70%;
        }
        .menu-item-price {
            display: table-cell;
            padding: 10px 5px;
            border-bottom: 1px solid #e0e0e0;
            text-align: right;
            width: 30%;
            color: {{ $settings->secondary_color }};
            font-weight: bold;
            font-size: 16px;
        }
        .item-name {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        .item-description {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
            font-style: italic;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($settings->logo_path)
            <img src="{{ public_path(str_replace('/storage/', 'storage/app/public/', $settings->logo_path)) }}" class="logo" alt="Logo">
        @endif

        <h1>{{ $settings->restaurant_name }}</h1>

        <div class="contact-info">
            @if($settings->restaurant_address)
                <p><strong>Dirección:</strong> {{ $settings->restaurant_address }}</p>
            @endif
            @if($settings->restaurant_phone)
                <p><strong>Teléfono:</strong> {{ $settings->restaurant_phone }}</p>
            @endif
            @if($settings->restaurant_email)
                <p><strong>Email:</strong> {{ $settings->restaurant_email }}</p>
            @endif
        </div>
    </div>

    <div class="menu-grid">
        @foreach($menuItems as $item)
            <div class="menu-item">
                <div class="menu-item-name">
                    <div class="item-name">{{ $item->name }}</div>
                    @if($options['include_descriptions'] && $item->description)
                        <div class="item-description">{{ $item->description }}</div>
                    @endif
                </div>
                @if($options['include_prices'])
                    <div class="menu-item-price">
                        ${{ number_format($item->price, 2) }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="footer">
        <p>Menú generado el {{ $generatedAt->format('d/m/Y H:i') }}</p>
        @if($settings->footer_message)
            <p style="margin-top: 10px;">{{ $settings->footer_message }}</p>
        @endif
    </div>
</body>
</html>
