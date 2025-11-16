# Configuración de Backup Automático con Spatie Laravel Backup

## Instalación

```bash
# Con Laravel Sail
./vendor/bin/sail composer require spatie/laravel-backup

# O sin Docker
composer require spatie/laravel-backup
```

## Publicar Configuración

```bash
./vendor/bin/sail artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

Esto creará: `config/backup.php`

## Configuración Básica

Editar `.env`:

```env
# Backup Configuration
BACKUP_ARCHIVE_PASSWORD=tu_contraseña_super_segura_aqui
BACKUP_MAIL_TO=admin@turestaurante.com

# Para backups en la nube (opcional)
# AWS S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=restaurant-backups
```

## Editar `config/backup.php`

```php
return [
    'backup' => [
        'name' => env('APP_NAME', 'restaurant-pos'),

        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('storage/framework/cache'),
                    base_path('storage/framework/sessions'),
                    base_path('storage/framework/views'),
                    base_path('storage/logs'),
                ],
                'follow_links' => false,
            ],

            'databases' => [
                'sqlite', // o 'mysql' según tu .env
            ],
        ],

        'destination' => [
            'filename_prefix' => '',
            'disks' => [
                'local',  // Backup local
                // 's3',  // Descomentar para backups en la nube
            ],
        ],
    ],

    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => [],
        ],

        'mail' => [
            'to' => env('BACKUP_MAIL_TO', 'admin@example.com'),
        ],
    ],

    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'restaurant-pos'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
];
```

## Comandos Principales

### Crear Backup Manual

```bash
# Backup completo (base de datos + archivos)
./vendor/bin/sail artisan backup:run

# Solo base de datos
./vendor/bin/sail artisan backup:run --only-db

# Solo archivos
./vendor/bin/sail artisan backup:run --only-files
```

### Listar Backups

```bash
./vendor/bin/sail artisan backup:list
```

### Limpiar Backups Antiguos

```bash
./vendor/bin/sail artisan backup:clean
```

### Monitorear Salud de Backups

```bash
./vendor/bin/sail artisan backup:monitor
```

## Configurar Backups Automáticos (Cron)

Editar `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Backup diario a las 2:00 AM
    $schedule->command('backup:run --only-db')
             ->dailyAt('02:00')
             ->emailOutputOnFailure('admin@turestaurante.com');

    // Backup completo semanal (domingos a las 3:00 AM)
    $schedule->command('backup:run')
             ->weekly()
             ->sundays()
             ->at('03:00')
             ->emailOutputOnFailure('admin@turestaurante.com');

    // Limpieza semanal de backups antiguos
    $schedule->command('backup:clean')
             ->weekly()
             ->mondays()
             ->at('04:00');

    // Monitoreo diario de salud
    $schedule->command('backup:monitor')
             ->daily()
             ->at('05:00');
}
```

## Activar Cron en Producción

En servidor Linux, editar crontab:

```bash
crontab -e
```

Agregar:

```
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## Verificar Funcionamiento

```bash
# 1. Crear un backup de prueba
./vendor/bin/sail artisan backup:run --only-db

# 2. Verificar que se creó
./vendor/bin/sail artisan backup:list

# 3. Verificar archivos físicos
ls -lh storage/app/restaurant-pos/

# 4. Probar restauración (CUIDADO - esto sobrescribe la BD)
# sqlite: copiar archivo .sqlite del backup
# mysql: usar mysqldump restore
```

## Restaurar desde Backup

### SQLite (Base de Datos)

```bash
# 1. Detener la aplicación
./vendor/bin/sail artisan down

# 2. Extraer el backup
cd storage/app/restaurant-pos/
unzip <nombre-del-backup>.zip

# 3. Copiar la base de datos
cp db-dumps/sqlite-database.sql ../../../../database/database.sqlite

# 4. Reiniciar la aplicación
./vendor/bin/sail artisan up
```

### MySQL (Base de Datos)

```bash
# 1. Extraer el backup
unzip <nombre-del-backup>.zip

# 2. Restaurar con mysql
./vendor/bin/sail mysql restaurant_pos < db-dumps/mysql-restaurant_pos.sql

# O sin Sail:
mysql -u root -p restaurant_pos < db-dumps/mysql-restaurant_pos.sql
```

## Backups en la Nube (Recomendado para Producción)

### Configurar AWS S3

1. Crear bucket en AWS S3
2. Obtener credenciales IAM
3. Configurar en `.env`:

```env
AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
AWS_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=restaurant-backups
```

4. Editar `config/filesystems.php` - ya debería estar configurado

5. Editar `config/backup.php`:

```php
'destination' => [
    'disks' => [
        'local',
        's3',  // Agregar S3
    ],
],
```

## Notificaciones por Email

Configurar en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@turestaurante.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Monitoreo y Alertas

El paquete enviará emails automáticamente si:
- Un backup falla
- No hay backups recientes (más de 1 día)
- Los backups ocupan demasiado espacio
- La limpieza de backups falla

## Mejores Prácticas

1. **Múltiples Destinos**: Guardar backups en local Y en la nube
2. **Encriptación**: Usar `BACKUP_ARCHIVE_PASSWORD` para proteger backups
3. **Retención**: Mantener backups diarios por 7 días, semanales por 8 semanas
4. **Pruebas**: Probar restauración mensualmente
5. **Monitoreo**: Revisar logs de backup regularmente

## Troubleshooting

### Error: "Could not create directory"

```bash
# Dar permisos a storage
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Backup muy grande

```php
// Ajustar exclusiones en config/backup.php
'exclude' => [
    base_path('vendor'),
    base_path('node_modules'),
    base_path('public/build'),  // Agregar assets compilados
    base_path('storage/app/tickets'),  // Tickets ya procesados
],
```

### No llegan notificaciones

```bash
# Probar configuración de email
./vendor/bin/sail artisan tinker
Mail::raw('Test email', function($message) {
    $message->to('admin@example.com')->subject('Test');
});
```

## Comandos de Referencia Rápida

```bash
# Backup manual ahora
./vendor/bin/sail artisan backup:run

# Ver todos los backups
./vendor/bin/sail artisan backup:list

# Limpiar backups antiguos
./vendor/bin/sail artisan backup:clean

# Ver estado de salud
./vendor/bin/sail artisan backup:monitor

# Forzar limpieza (eliminar todos los backups)
./vendor/bin/sail artisan backup:clean --disable-signal-trapping
```

## Seguridad

⚠️ **IMPORTANTE**:
- NUNCA commitear backups en Git
- Agregar `storage/app/*/` a `.gitignore`
- Usar contraseñas fuertes para encriptar archivos
- Limitar acceso a buckets S3 (solo IPs permitidas)
- Rotar credenciales AWS regularmente

## Costos

- **Local**: Gratis, limitado por espacio en disco
- **AWS S3**: ~$0.023/GB/mes (Standard Storage)
- **Backup diario de 100MB**: ~$0.07/mes
- **Backup semanal de 500MB**: ~$0.35/mes

**Total estimado**: < $1 USD/mes para restaurante pequeño
