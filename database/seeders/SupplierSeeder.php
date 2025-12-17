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
                'name' => 'Mercado el Tiangue',
                'contact_person' => 'Blanca',
                'phone' => '(503) 2234-5678',
                'email' => 'ventas@tiangue.com.sv',
                'address' => 'Mercado principal, Cojutepeque',
                'is_active' => true,
            ],
            [
                'name' => 'Super Selectos',
                'contact_person' => 'Ninguno',
                'phone' => '(503) 2345-6789',
                'email' => 'info@selectos.com.sv',
                'address' => 'Parque Central',
                'is_active' => true,
            ],
            [
                'name' => 'Super San Carlos',
                'contact_person' => 'José Rodríguez',
                'phone' => '(503) 7123-4567',
                'email' => 'contacto@supersancarlos.com',
                'address' => 'El punto',
                'is_active' => true,
            ],
            [
                'name' => 'Super Alameda',
                'contact_person' => 'Ana Patricia Hernández',
                'phone' => '(503) 2456-7890',
                'email' => 'ventas@lacteossantarosa.com',
                'address' => 'Alameda',
                'is_active' => true,
            ],
            [
                'name' => 'Jumbo',
                'contact_person' => 'Ana Patricia Hernández',
                'phone' => '(503) 2456-7890',
                'email' => 'ventas@jumbo.com',
                'address' => 'Cuarta Zona',
                'is_active' => true,
            ],
            [
                'name' => 'Agromercados',
                'contact_person' => 'Roberto Castillo',
                'phone' => '(503) 2567-8901',
                'email' => 'info@agromercados.com',
                'address' => 'Alameda',
                'is_active' => true,
            ],
            [
                'name' => 'Queso',
                'contact_person' => 'Sofia Martínez',
                'phone' => '(503) 2567-8901',
                'email' => 'info@queso.com',
                'address' => 'Alameda',
                'is_active' => true,
            ],
            [
                'name' => 'Deposito Soda',
                'contact_person' => 'Fernando Aguilar',
                'phone' => '(503) 2890-1234',
                'email' => 'ventas@deposito.com.sv',
                'address' => 'Cojutepeque, Calle Principal #123',
                'is_active' => false, // Este está inactivo como ejemplo
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
