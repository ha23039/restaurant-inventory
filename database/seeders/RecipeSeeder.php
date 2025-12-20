<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\Product;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar productos por nombre
        $panHamburguesa = Product::where('name', 'Pan de Hamburguesa')->first();
        $panHotDog = Product::where('name', 'Pan para Hot Dog')->first();
        $carneMolida = Product::where('name', 'Carne Molida de Res')->first();
        $quesoCheddar = Product::where('name', 'Queso Cheddar')->first();
        $ketchup = Product::where('name', 'Ketchup Heinz')->first();
        $mayonesa = Product::where('name', 'Mayonesa McCormick')->first();
        $mostaza = Product::where('name', 'Mostaza')->first();
        $cocaCola = Product::where('name', 'Coca Cola 355ml')->first();
        $pepsi = Product::where('name', 'Pepsi 355ml')->first();
        $pechubaPollo = Product::where('name', 'Pechuga de Pollo')->first();
        $papas = Product::where('name', 'Papas Fritas Congeladas')->first();
        $aceite = Product::where('name', 'Aceite Vegetal')->first();
        $salchicha = Product::where('name', 'Salchicha de Pollo')->first();

        // Buscar menu items por nombre
        $hamburguesaClasica = MenuItem::where('name', 'Hamburguesa Clásica')->first();
        $comboHamburguesa = MenuItem::where('name', 'Combo Hamburguesa + Soda')->first();
        $hamburguesaDoble = MenuItem::where('name', 'Hamburguesa Doble')->first();
        $hotDog = MenuItem::where('name', 'Hot Dog Especial')->first();
        $alitas = MenuItem::where('name', 'Alitas de Pollo (6 pcs)')->first();
        $papasFritas = MenuItem::where('name', 'Papas Fritas Medianas')->first();
        $comboHotDog = MenuItem::where('name', 'Combo Hot Dog + Papas + Soda')->first();

        // Solo crear recetas si existen los productos y menu items necesarios
        if ($hamburguesaClasica && $panHamburguesa && $carneMolida) {
            // HAMBURGUESA CLÁSICA
            Recipe::create(['menu_item_id' => $hamburguesaClasica->id, 'product_id' => $panHamburguesa->id, 'quantity_needed' => 1, 'unit' => 'pcs']);
            Recipe::create(['menu_item_id' => $hamburguesaClasica->id, 'product_id' => $carneMolida->id, 'quantity_needed' => 0.150, 'unit' => 'kg']);
            if ($quesoCheddar) Recipe::create(['menu_item_id' => $hamburguesaClasica->id, 'product_id' => $quesoCheddar->id, 'quantity_needed' => 0.030, 'unit' => 'kg']);
            if ($ketchup) Recipe::create(['menu_item_id' => $hamburguesaClasica->id, 'product_id' => $ketchup->id, 'quantity_needed' => 0.015, 'unit' => 'kg']);
            if ($mayonesa) Recipe::create(['menu_item_id' => $hamburguesaClasica->id, 'product_id' => $mayonesa->id, 'quantity_needed' => 0.010, 'unit' => 'kg']);
        }

        if ($comboHamburguesa && $panHamburguesa && $carneMolida) {
            // COMBO HAMBURGUESA + SODA
            Recipe::create(['menu_item_id' => $comboHamburguesa->id, 'product_id' => $panHamburguesa->id, 'quantity_needed' => 1, 'unit' => 'pcs']);
            Recipe::create(['menu_item_id' => $comboHamburguesa->id, 'product_id' => $carneMolida->id, 'quantity_needed' => 0.150, 'unit' => 'kg']);
            if ($quesoCheddar) Recipe::create(['menu_item_id' => $comboHamburguesa->id, 'product_id' => $quesoCheddar->id, 'quantity_needed' => 0.030, 'unit' => 'kg']);
            if ($ketchup) Recipe::create(['menu_item_id' => $comboHamburguesa->id, 'product_id' => $ketchup->id, 'quantity_needed' => 0.015, 'unit' => 'kg']);
            if ($mayonesa) Recipe::create(['menu_item_id' => $comboHamburguesa->id, 'product_id' => $mayonesa->id, 'quantity_needed' => 0.010, 'unit' => 'kg']);
            if ($cocaCola) Recipe::create(['menu_item_id' => $comboHamburguesa->id, 'product_id' => $cocaCola->id, 'quantity_needed' => 1, 'unit' => 'pcs']);
        }

        if ($hamburguesaDoble && $panHamburguesa && $carneMolida) {
            // HAMBURGUESA DOBLE
            Recipe::create(['menu_item_id' => $hamburguesaDoble->id, 'product_id' => $panHamburguesa->id, 'quantity_needed' => 1, 'unit' => 'pcs']);
            Recipe::create(['menu_item_id' => $hamburguesaDoble->id, 'product_id' => $carneMolida->id, 'quantity_needed' => 0.300, 'unit' => 'kg']);
            if ($quesoCheddar) Recipe::create(['menu_item_id' => $hamburguesaDoble->id, 'product_id' => $quesoCheddar->id, 'quantity_needed' => 0.060, 'unit' => 'kg']);
            if ($ketchup) Recipe::create(['menu_item_id' => $hamburguesaDoble->id, 'product_id' => $ketchup->id, 'quantity_needed' => 0.020, 'unit' => 'kg']);
            if ($mayonesa) Recipe::create(['menu_item_id' => $hamburguesaDoble->id, 'product_id' => $mayonesa->id, 'quantity_needed' => 0.015, 'unit' => 'kg']);
        }

        if ($hotDog && $panHotDog && $salchicha) {
            // HOT DOG ESPECIAL
            Recipe::create(['menu_item_id' => $hotDog->id, 'product_id' => $panHotDog->id, 'quantity_needed' => 1, 'unit' => 'pcs']);
            Recipe::create(['menu_item_id' => $hotDog->id, 'product_id' => $salchicha->id, 'quantity_needed' => 0.080, 'unit' => 'kg']);
            if ($ketchup) Recipe::create(['menu_item_id' => $hotDog->id, 'product_id' => $ketchup->id, 'quantity_needed' => 0.010, 'unit' => 'kg']);
            if ($mostaza) Recipe::create(['menu_item_id' => $hotDog->id, 'product_id' => $mostaza->id, 'quantity_needed' => 0.008, 'unit' => 'kg']);
        }

        if ($alitas && $pechubaPollo) {
            // ALITAS DE POLLO
            Recipe::create(['menu_item_id' => $alitas->id, 'product_id' => $pechubaPollo->id, 'quantity_needed' => 0.350, 'unit' => 'kg']);
            if ($aceite) Recipe::create(['menu_item_id' => $alitas->id, 'product_id' => $aceite->id, 'quantity_needed' => 0.05, 'unit' => 'lt']);
        }

        if ($papasFritas && $papas) {
            // PAPAS FRITAS MEDIANAS
            Recipe::create(['menu_item_id' => $papasFritas->id, 'product_id' => $papas->id, 'quantity_needed' => 0.150, 'unit' => 'kg']);
            if ($aceite) Recipe::create(['menu_item_id' => $papasFritas->id, 'product_id' => $aceite->id, 'quantity_needed' => 0.02, 'unit' => 'lt']);
        }

        if ($comboHotDog && $panHotDog && $salchicha) {
            // COMBO HOT DOG + PAPAS + SODA
            Recipe::create(['menu_item_id' => $comboHotDog->id, 'product_id' => $panHotDog->id, 'quantity_needed' => 1, 'unit' => 'pcs']);
            Recipe::create(['menu_item_id' => $comboHotDog->id, 'product_id' => $salchicha->id, 'quantity_needed' => 0.080, 'unit' => 'kg']);
            if ($ketchup) Recipe::create(['menu_item_id' => $comboHotDog->id, 'product_id' => $ketchup->id, 'quantity_needed' => 0.010, 'unit' => 'kg']);
            if ($mostaza) Recipe::create(['menu_item_id' => $comboHotDog->id, 'product_id' => $mostaza->id, 'quantity_needed' => 0.008, 'unit' => 'kg']);
            if ($papas) Recipe::create(['menu_item_id' => $comboHotDog->id, 'product_id' => $papas->id, 'quantity_needed' => 0.120, 'unit' => 'kg']);
            if ($aceite) Recipe::create(['menu_item_id' => $comboHotDog->id, 'product_id' => $aceite->id, 'quantity_needed' => 0.02, 'unit' => 'lt']);
            if ($pepsi) Recipe::create(['menu_item_id' => $comboHotDog->id, 'product_id' => $pepsi->id, 'quantity_needed' => 1, 'unit' => 'pcs']);
        }
    }
}
