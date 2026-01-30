<?php

namespace Database\Seeders;

use App\Models\Combo;
use App\Models\ComboComponent;
use App\Models\ComboComponentOption;
use App\Models\MenuItem;
use App\Models\SimpleProduct;
use Illuminate\Database\Seeder;

class ComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar productos existentes para el ejemplo
        $hamburguesa = MenuItem::where('name', 'like', '%hamburguesa%')->first();
        $papas = SimpleProduct::where('name', 'like', '%papa%')->first();

        // Buscar bebidas
        $frescoDelDia = SimpleProduct::where('name', 'like', '%fresco%')->first();
        $sodaCascada = SimpleProduct::where('name', 'like', '%cascada%')->first();
        $sodaLata = SimpleProduct::where('name', 'like', '%lata%')->first();

        // Si no existen productos, crear un combo de ejemplo básico
        if (!$hamburguesa) {
            $this->command->info('No se encontraron productos para crear combo de ejemplo.');
            $this->command->info('Crea al menos un MenuItem y algunos SimpleProducts para ver el combo completo.');

            // Crear combo vacío de ejemplo
            $combo = Combo::create([
                'name' => 'Combo de Ejemplo',
                'description' => 'Este es un combo de ejemplo. Edítalo agregando componentes reales.',
                'base_price' => 9.99,
                'category' => 'Combos',
                'is_available' => false, // Desactivado hasta configurar
            ]);

            $this->command->info("Combo '{$combo->name}' creado (desactivado).");
            return;
        }

        // Crear combo completo
        $combo = Combo::create([
            'name' => 'Combo Hamburguesa',
            'description' => 'Deliciosa hamburguesa con papas y bebida a elegir',
            'base_price' => 8.99,
            'category' => 'Combos',
            'is_available' => true,
            'show_in_menu' => true,
            'show_in_pos' => true,
        ]);

        $this->command->info("Combo '{$combo->name}' creado.");

        // Componente fijo: Hamburguesa
        ComboComponent::create([
            'combo_id' => $combo->id,
            'component_type' => 'fixed',
            'name' => null,
            'quantity' => 1,
            'sellable_type' => 'menu_item',
            'sellable_id' => $hamburguesa->id,
            'sort_order' => 1,
        ]);
        $this->command->info("  + Componente fijo: {$hamburguesa->name}");

        // Componente fijo: Papas (si existe)
        if ($papas) {
            ComboComponent::create([
                'combo_id' => $combo->id,
                'component_type' => 'fixed',
                'name' => null,
                'quantity' => 1,
                'sellable_type' => 'simple_product',
                'sellable_id' => $papas->id,
                'sort_order' => 2,
            ]);
            $this->command->info("  + Componente fijo: {$papas->name}");
        }

        // Componente de elección: Bebida
        $bebidaComponent = ComboComponent::create([
            'combo_id' => $combo->id,
            'component_type' => 'choice',
            'name' => 'Bebida',
            'quantity' => 1,
            'is_required' => true,
            'sort_order' => 3,
        ]);
        $this->command->info("  + Componente choice: Bebida");

        // Agregar opciones de bebida
        $sortOrder = 1;

        if ($frescoDelDia) {
            ComboComponentOption::create([
                'combo_component_id' => $bebidaComponent->id,
                'sellable_type' => 'simple_product',
                'sellable_id' => $frescoDelDia->id,
                'price_adjustment' => 0,
                'is_default' => true,
                'sort_order' => $sortOrder++,
            ]);
            $this->command->info("    - Opción: {$frescoDelDia->name} (incluido, default)");
        }

        if ($sodaCascada) {
            ComboComponentOption::create([
                'combo_component_id' => $bebidaComponent->id,
                'sellable_type' => 'simple_product',
                'sellable_id' => $sodaCascada->id,
                'price_adjustment' => 0,
                'is_default' => false,
                'sort_order' => $sortOrder++,
            ]);
            $this->command->info("    - Opción: {$sodaCascada->name} (incluido)");
        }

        if ($sodaLata) {
            ComboComponentOption::create([
                'combo_component_id' => $bebidaComponent->id,
                'sellable_type' => 'simple_product',
                'sellable_id' => $sodaLata->id,
                'price_adjustment' => 1.00,
                'is_default' => false,
                'sort_order' => $sortOrder++,
            ]);
            $this->command->info("    - Opción: {$sodaLata->name} (+\$1.00)");
        }

        // Si no hay bebidas, informar
        if (!$frescoDelDia && !$sodaCascada && !$sodaLata) {
            $this->command->warn("  ! No se encontraron bebidas. Agrega opciones manualmente.");
        }

        $this->command->info("Combo completo creado con " . $combo->components()->count() . " componentes.");
    }
}
