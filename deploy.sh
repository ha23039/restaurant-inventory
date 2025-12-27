#!/bin/bash

echo "======================================"
echo "🚀 DEPLOY - Restaurant Inventory POS"
echo "======================================"
echo ""

echo "🧹 Paso 1: Limpiando cachés de Laravel..."
php artisan optimize:clear
echo "✅ Cachés limpiados"
echo ""

echo "🗑️  Paso 2: Eliminando archivos compilados..."
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
echo "✅ Archivos compilados eliminados"
echo ""

echo "📦 Paso 3: Optimizando autoload de Composer..."
composer dump-autoload --optimize
echo "✅ Autoload optimizado"
echo ""

echo "🔧 Paso 4: Regenerando cachés de producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo "✅ Cachés regenerados"
echo ""

echo "🔒 Paso 5: Arreglando permisos..."
chmod -R 755 public/build
chmod -R 775 storage bootstrap/cache
chown -R rocabist:nobody storage bootstrap/cache 2>/dev/null || chown -R $USER:$USER storage bootstrap/cache
echo "✅ Permisos arreglados"
echo ""

echo "======================================"
echo "✅ DEPLOY COMPLETADO EXITOSAMENTE!"
echo "======================================"
echo ""
echo "📝 Próximos pasos:"
echo "   1. Verifica que la app cargue correctamente"
echo "   2. Limpia cache del navegador si es necesario (Ctrl+F5)"
echo ""
