# Guía de Deployment a Producción - Restaurant POS

## 📋 Resumen

Esta guía te ayudará a desplegar tu aplicación Restaurant POS a producción en tu servidor con dominio **rocabistro.com**.

---

## 🚀 Pre-requisitos en el Servidor

### 1. Software Requerido

```bash
# PHP 8.2 o superior con extensiones
php -v  # Debe ser >= 8.2

# Extensiones PHP necesarias
php -m | grep -E '(mysql|pdo|mbstring|tokenizer|xml|ctype|json|bcmath|fileinfo|gd)'

# Composer
composer --version

# Node.js y NPM (para compilar assets)
node -v  # >= 18.x
npm -v

# MySQL / MariaDB
mysql --version

# Nginx o Apache
nginx -v  # o apache2 -v
```

### 2. Crear Base de Datos MySQL

```bash
# Conectar a MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE restaurant_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Crear usuario
CREATE USER 'restaurant_user'@'localhost' IDENTIFIED BY 'TU_CONTRASEÑA_SEGURA';

# Dar permisos
GRANT ALL PRIVILEGES ON restaurant_pos.* TO 'restaurant_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 📦 Preparación del Proyecto

### 1. En tu Máquina Local - Compilar Assets

```bash
# Navegar al proyecto
cd /home/coder/DEV/restaurant-inventory/restaurant-inventory

# Instalar dependencias NPM
npm install

# Compilar assets para producción
npm run build

# Verificar que se creó la carpeta public/build/
ls -la public/build/
```

### 2. Preparar Archivos para Subir

**Archivos/Carpetas a SUBIR al servidor:**
```
├── app/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── build/          ← IMPORTANTE: Assets compilados
│   ├── .htaccess
│   └── index.php
├── resources/
│   └── views/          ← Solo las vistas Blade
├── routes/
├── storage/
│   └── app/
│       └── public/     ← Mantener estructura vacía
├── vendor/             ← Se instalará en servidor
├── .env.example
├── artisan
├── composer.json
├── composer.lock
└── package.json
```

**Archivos/Carpetas a NO SUBIR:**
```
❌ node_modules/       ← Muy pesado, no necesario en producción
❌ .env                ← Se crea en servidor
❌ database/database.sqlite  ← Desarrollo local
❌ .git/               ← Opcional (o usar git clone)
❌ tests/              ← Opcional
❌ storage/logs/*      ← Logs locales
❌ storage/framework/cache/*
❌ storage/framework/sessions/*
❌ storage/framework/views/*
```

---

## 🌐 Configuración en el Servidor

### Opción A: Subir por FTP/SFTP

1. **Comprimir el proyecto** (sin node_modules):
```bash
# En tu máquina local
cd /home/coder/DEV/restaurant-inventory/
tar -czf restaurant-pos.tar.gz --exclude='node_modules' --exclude='.git' --exclude='storage/logs/*' restaurant-inventory/
```

2. **Subir con SFTP/FTP** a tu servidor (por ejemplo a `/var/www/rocabistro/`):
```bash
# Usando scp (si tienes acceso SSH)
scp restaurant-pos.tar.gz usuario@rocabistro.com:/var/www/

# O usa FileZilla / WinSCP con interfaz gráfica
```

3. **En el servidor, descomprimir**:
```bash
ssh usuario@rocabistro.com
cd /var/www/
tar -xzf restaurant-pos.tar.gz
mv restaurant-inventory restaurant-pos
cd restaurant-pos
```

### Opción B: Clonar con Git (Recomendado)

```bash
ssh usuario@rocabistro.com
cd /var/www/
git clone https://github.com/tu-usuario/restaurant-pos.git
cd restaurant-pos
```

---

## ⚙️ Configuración en el Servidor

### 1. Instalar Dependencias PHP

```bash
cd /var/www/restaurant-pos

# Instalar dependencias de Composer (sin dev)
composer install --optimize-autoloader --no-dev

# Si no funciona, instalar con:
php composer.phar install --optimize-autoloader --no-dev
```

### 2. Configurar Archivo .env

```bash
# Copiar el ejemplo
cp .env.example .env

# Editar con nano o vim
nano .env
```

**Configuración importante del .env:**

```env
APP_NAME="RocaBistro POS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://rocabistro.com/pos  # Ajustar según tu URL

# Generar después con: php artisan key:generate
APP_KEY=

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurant_pos
DB_USERNAME=restaurant_user
DB_PASSWORD=TU_CONTRASEÑA_SEGURA

# Caché y Sesiones (base de datos por defecto)
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Mail (opcional, para recuperación de contraseña)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@rocabistro.com
MAIL_FROM_NAME="RocaBistro POS"

# Información del Restaurante (para recibos)
RESTAURANT_NAME="RocaBistro"
RESTAURANT_ADDRESS="Dirección del restaurante"
RESTAURANT_PHONE="(555) 123-4567"
RESTAURANT_TAX_ID="RFC123456789"

# Impresoras Térmicas (si las usas)
KITCHEN_PRINTER_IP=192.168.1.100
KITCHEN_PRINTER_PORT=9100
CUSTOMER_PRINTER_IP=192.168.1.101
CUSTOMER_PRINTER_PORT=9100
AUTO_PRINT_KITCHEN=true
AUTO_PRINT_CUSTOMER=true
```

### 3. Generar Clave de Aplicación

```bash
php artisan key:generate
```

### 4. Ejecutar Migraciones

```bash
# Ejecutar migraciones (crea las tablas)
php artisan migrate --force

# OPCIONAL: Sembrar datos de ejemplo (solo en primera instalación)
php artisan db:seed --force
```

### 5. Configurar Permisos

```bash
# Dar permisos a directorios de storage y cache
sudo chown -R www-data:www-data /var/www/restaurant-pos
sudo chmod -R 775 /var/www/restaurant-pos/storage
sudo chmod -R 775 /var/www/restaurant-pos/bootstrap/cache

# Crear link simbólico para storage (imágenes públicas)
php artisan storage:link
```

### 6. Optimizar para Producción

```bash
# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar autoloader de Composer
composer dump-autoload --optimize
```

---

## 🌍 Configuración del Servidor Web

### Opción A: Nginx (Recomendado)

Crear archivo de configuración: `/etc/nginx/sites-available/rocabistro-pos`

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name rocabistro.com www.rocabistro.com;

    # Si tienes WordPress en la raíz, el POS puede estar en un subdirectorio
    # Por ejemplo: rocabistro.com/pos/

    root /var/www/restaurant-pos/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/rocabistro-pos-access.log;
    error_log /var/log/nginx/rocabistro-pos-error.log;

    # Configuración para Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Denegar acceso a archivos ocultos
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Activar el sitio:**

```bash
# Crear link simbólico
sudo ln -s /etc/nginx/sites-available/rocabistro-pos /etc/nginx/sites-enabled/

# Verificar configuración
sudo nginx -t

# Recargar Nginx
sudo systemctl reload nginx
```

### Opción B: Apache

Crear archivo: `/etc/apache2/sites-available/rocabistro-pos.conf`

```apache
<VirtualHost *:80>
    ServerName rocabistro.com
    ServerAlias www.rocabistro.com
    DocumentRoot /var/www/restaurant-pos/public

    <Directory /var/www/restaurant-pos/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/rocabistro-pos-error.log
    CustomLog ${APACHE_LOG_DIR}/rocabistro-pos-access.log combined
</VirtualHost>
```

**Activar el sitio:**

```bash
# Habilitar mod_rewrite
sudo a2enmod rewrite

# Activar sitio
sudo a2ensite rocabistro-pos

# Recargar Apache
sudo systemctl reload apache2
```

---

## 🔐 Configurar HTTPS con Let's Encrypt

```bash
# Instalar certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Obtener certificado SSL (para Nginx)
sudo certbot --nginx -d rocabistro.com -d www.rocabistro.com

# O para Apache:
# sudo certbot --apache -d rocabistro.com -d www.rocabistro.com

# Certbot automáticamente:
# - Obtiene el certificado
# - Configura HTTPS
# - Redirige HTTP → HTTPS
# - Configura renovación automática
```

Verificar renovación automática:
```bash
sudo certbot renew --dry-run
```

---

## 🔄 Configurar Colas de Trabajo (Opcional pero Recomendado)

Las colas manejan trabajos en segundo plano como notificaciones.

### Crear Servicio Systemd

Crear archivo: `/etc/systemd/system/restaurant-pos-worker.service`

```ini
[Unit]
Description=Restaurant POS Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/restaurant-pos
ExecStart=/usr/bin/php /var/www/restaurant-pos/artisan queue:work --sleep=3 --tries=3 --timeout=90
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

**Activar el servicio:**

```bash
# Recargar systemd
sudo systemctl daemon-reload

# Iniciar worker
sudo systemctl start restaurant-pos-worker

# Habilitar en inicio del sistema
sudo systemctl enable restaurant-pos-worker

# Ver estado
sudo systemctl status restaurant-pos-worker

# Ver logs
sudo journalctl -u restaurant-pos-worker -f
```

---

## 🗄️ Respaldo de Base de Datos

### Script de Backup Automático

Crear: `/var/www/scripts/backup-restaurant-pos.sh`

```bash
#!/bin/bash

# Configuración
DB_NAME="restaurant_pos"
DB_USER="restaurant_user"
DB_PASS="TU_CONTRASEÑA"
BACKUP_DIR="/var/backups/restaurant-pos"
DATE=$(date +"%Y%m%d_%H%M%S")
DAYS_TO_KEEP=30

# Crear directorio si no existe
mkdir -p $BACKUP_DIR

# Backup de base de datos
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup de archivos storage (imágenes, etc)
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/restaurant-pos/storage/app/public

# Eliminar backups antiguos
find $BACKUP_DIR -name "*.gz" -type f -mtime +$DAYS_TO_KEEP -delete

echo "Backup completado: $DATE"
```

**Hacer ejecutable y programar con cron:**

```bash
# Hacer ejecutable
chmod +x /var/www/scripts/backup-restaurant-pos.sh

# Editar crontab
crontab -e

# Agregar línea para backup diario a las 3 AM
0 3 * * * /var/www/scripts/backup-restaurant-pos.sh >> /var/log/restaurant-pos-backup.log 2>&1
```

---

## 🔧 Mantenimiento

### Actualizar la Aplicación

```bash
# 1. Hacer backup primero
/var/www/scripts/backup-restaurant-pos.sh

# 2. Activar modo mantenimiento
php artisan down

# 3. Actualizar código (si usas git)
git pull origin main

# 4. Actualizar dependencias
composer install --optimize-autoloader --no-dev

# 5. Compilar assets (si cambiaron)
npm run build

# 6. Ejecutar migraciones nuevas
php artisan migrate --force

# 7. Limpiar cachés
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 8. Recrear cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Desactivar modo mantenimiento
php artisan up
```

### Limpiar Cachés en Producción

```bash
# Limpiar todo
php artisan optimize:clear

# O limpiar específicos
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Ver Logs

```bash
# Logs de Laravel
tail -f /var/www/restaurant-pos/storage/logs/laravel.log

# Logs de Nginx
tail -f /var/log/nginx/rocabistro-pos-error.log

# Logs de Apache
tail -f /var/log/apache2/rocabistro-pos-error.log

# O usar Laravel Pail (si está instalado)
php artisan pail
```

---

## 🖨️ Configuración de Impresoras Térmicas

### 1. Verificar Conectividad

```bash
# Probar conexión a impresora de cocina
nc -zv 192.168.1.100 9100

# Probar conexión a impresora de cliente
nc -zv 192.168.1.101 9100
```

### 2. Configurar IPs en .env

Ya lo hiciste arriba, pero verifica:

```env
KITCHEN_PRINTER_IP=192.168.1.100
KITCHEN_PRINTER_PORT=9100
CUSTOMER_PRINTER_IP=192.168.1.101
CUSTOMER_PRINTER_PORT=9100
AUTO_PRINT_KITCHEN=true
AUTO_PRINT_CUSTOMER=true
```

### 3. Probar Impresión

```bash
# Desde Laravel Tinker
php artisan tinker

# Dentro de tinker:
$service = app(App\Services\ThermalTicketService::class);
$sale = App\Models\Sale::latest()->first();
$service->printKitchenOrder($sale);
$service->printCustomerReceipt($sale);
```

---

## 👥 Crear Usuario Administrador

```bash
# Usando Tinker
php artisan tinker

# Dentro de tinker:
$user = new App\Models\User();
$user->name = 'Administrador';
$user->email = 'admin@rocabistro.com';
$user->password = bcrypt('contraseña_segura');
$user->role = 'admin';
$user->is_active = true;
$user->save();
```

---

## 📍 Rutas de la Aplicación

Una vez desplegado, tu aplicación estará disponible en:

```
https://rocabistro.com/              ← WordPress (si lo tienes)
https://rocabistro.com/pos/          ← Laravel POS (si está en subdirectorio)

O si todo el dominio es para el POS:
https://rocabistro.com/              ← Laravel POS

Rutas principales del POS:
├── /login                           ← Login
├── /dashboard                       ← Dashboard
├── /sales/pos                       ← Punto de Venta
├── /sales                           ← Historial de ventas
├── /inventory/products              ← Inventario
├── /menu/items                      ← Menú
├── /cashregister                    ← Caja registradora
└── /settings/business               ← Configuración
```

---

## ⚠️ Solución de Problemas Comunes

### Error 500 - Internal Server Error

```bash
# 1. Verificar permisos
sudo chown -R www-data:www-data /var/www/restaurant-pos
sudo chmod -R 775 /var/www/restaurant-pos/storage

# 2. Verificar logs
tail -f /var/www/restaurant-pos/storage/logs/laravel.log

# 3. Verificar .env
php artisan config:clear
php artisan config:cache

# 4. Verificar que APP_KEY está generado
grep APP_KEY .env
```

### Error 404 - Página no encontrada

```bash
# Verificar mod_rewrite (Apache)
sudo a2enmod rewrite
sudo systemctl restart apache2

# Verificar .htaccess existe en public/
ls -la /var/www/restaurant-pos/public/.htaccess

# Limpiar caché de rutas
php artisan route:clear
php artisan route:cache
```

### Base de Datos no Conecta

```bash
# Probar conexión a MySQL
mysql -u restaurant_user -p restaurant_pos

# Verificar credenciales en .env
cat .env | grep DB_

# Verificar que MySQL acepta conexiones
sudo systemctl status mysql
```

### Storage Link no Funciona (imágenes no cargan)

```bash
# Recrear link simbólico
rm /var/www/restaurant-pos/public/storage
php artisan storage:link

# Verificar que existe
ls -la /var/www/restaurant-pos/public/storage
```

---

## 📊 Monitoreo y Performance

### Instalar Opcache (Recomendado para PHP)

```bash
# Instalar
sudo apt install php8.2-opcache

# Verificar
php -m | grep opcache

# Reiniciar PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Habilitar Compresión Gzip (Nginx)

Agregar en `/etc/nginx/nginx.conf`:

```nginx
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
```

Reiniciar:
```bash
sudo systemctl reload nginx
```

---

## ✅ Checklist Final

- [ ] Base de datos MySQL creada
- [ ] Archivos subidos al servidor
- [ ] Dependencias instaladas (`composer install`)
- [ ] Archivo `.env` configurado
- [ ] Clave de aplicación generada (`php artisan key:generate`)
- [ ] Migraciones ejecutadas (`php artisan migrate`)
- [ ] Permisos configurados (storage y cache)
- [ ] Storage link creado (`php artisan storage:link`)
- [ ] Cachés optimizados (config, route, view)
- [ ] Servidor web configurado (Nginx o Apache)
- [ ] HTTPS configurado con Let's Encrypt
- [ ] Usuario administrador creado
- [ ] Worker de colas configurado (opcional)
- [ ] Backups automáticos configurados
- [ ] Impresoras térmicas configuradas (si aplica)
- [ ] Aplicación accesible desde navegador
- [ ] Login funciona correctamente

---

## 🆘 Soporte

Si encuentras problemas:

1. **Revisar logs**:
   - Laravel: `/var/www/restaurant-pos/storage/logs/laravel.log`
   - Nginx: `/var/log/nginx/rocabistro-pos-error.log`
   - PHP-FPM: `/var/log/php8.2-fpm.log`

2. **Modo debug temporal** (SOLO para diagnosticar, no dejar así):
   ```env
   APP_DEBUG=true
   ```
   Luego limpiar caché: `php artisan config:clear`

3. **Contactar soporte**: Guarda los mensajes de error completos

---

## 📝 Notas Adicionales

### Wordpress + Laravel en el mismo dominio

Si quieres tener WordPress en la raíz (`rocabistro.com`) y Laravel en un subdirectorio (`rocabistro.com/pos`):

1. **Configuración Nginx**:

```nginx
# WordPress en raíz
server {
    server_name rocabistro.com www.rocabistro.com;
    root /var/www/wordpress;
    index index.php;

    # Laravel en subdirectorio /pos
    location /pos {
        alias /var/www/restaurant-pos/public;
        try_files $uri $uri/ @laravel;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
        }
    }

    location @laravel {
        rewrite /pos/(.*)$ /pos/index.php?/$1 last;
    }

    # WordPress en raíz
    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
}
```

2. **Actualizar .env de Laravel**:
```env
APP_URL=https://rocabistro.com/pos
```

3. **Limpiar cachés**:
```bash
php artisan config:clear
php artisan config:cache
```

---

¡Listo! Tu aplicación Restaurant POS debería estar funcionando en producción. 🎉


