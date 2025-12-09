<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Distribuidora La Cosecha',
                'contact_person' => 'Carlos Martínez',
                'phone' => '(503) 2234-5678',
                'email' => 'ventas@lacosecha.com.sv',
                'address' => 'Boulevard del Ejército Nacional, Km 5.5, San Salvador',
                'is_active' => true,
            ],
            [
                'name' => 'Carnes y Embutidos El Buen Sabor',
                'contact_person' => 'María González',
                'phone' => '(503) 2345-6789',
                'email' => 'info@elbuensabor.com',
                'address' => 'Calle Principal #234, Santa Tecla',
                'is_active' => true,
            ],
            [
                'name' => 'Verduras Frescas del Campo',
                'contact_person' => 'José Rodríguez',
                'phone' => '(503) 7123-4567',
                'email' => 'contacto@verdurasdelcampo.com',
                'address' => 'Mercado Central, Local 45-46, San Salvador',
                'is_active' => true,
            ],
            [
                'name' => 'Lácteos y Derivados Santa Rosa',
                'contact_person' => 'Ana Patricia Hernández',
                'phone' => '(503) 2456-7890',
                'email' => 'ventas@lacteossantarosa.com',
                'address' => 'Carretera Panamericana Km 12, La Libertad',
                'is_active' => true,
            ],
            [
                'name' => 'Bebidas y Licores Centroamérica',
                'contact_person' => 'Roberto Castillo',
                'phone' => '(503) 2567-8901',
                'email' => 'info@bebidasca.com',
                'address' => 'Zona Industrial, San Salvador',
                'is_active' => true,
            ],
            [
                'name' => 'Abarrotes Don Juan',
                'contact_person' => 'Juan Alberto Pérez',
                'phone' => '(503) 7234-5678',
                'email' => 'donjuan@abarrotes.com',
                'address' => 'Avenida España #123, San Salvador',
                'is_active' => true,
            ],
            [
                'name' => 'Panadería y Pastelería La Delicia',
                'contact_person' => 'Carmen Flores',
                'phone' => '(503) 2678-9012',
                'email' => 'ventas@ladelicia.com.sv',
                'address' => 'Colonia Escalón, Calle Mirador #456',
                'is_active' => true,
            ],
            [
                'name' => 'Mariscos Frescos del Pacífico',
                'contact_person' => 'Luis Fernando García',
                'phone' => '(503) 7345-6789',
                'email' => 'mariscos@delpacifico.com',
                'address' => 'Puerto de La Libertad, Local 12',
                'is_active' => true,
            ],
            [
                'name' => 'Especias y Condimentos Gourmet',
                'contact_person' => 'Patricia Mendoza',
                'phone' => '(503) 2789-0123',
                'email' => 'info@especiasgourmet.com',
                'address' => 'Centro Comercial Multiplaza, Local 234',
                'is_active' => true,
            ],
            [
                'name' => 'Descartables y Empaques EcoSmart',
                'contact_person' => 'Fernando Aguilar',
                'phone' => '(503) 2890-1234',
                'email' => 'ventas@ecosmart.com.sv',
                'address' => 'Boulevard Los Próceres, Oficentro Los Arcos',
                'is_active' => false, // Este está inactivo como ejemplo
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
