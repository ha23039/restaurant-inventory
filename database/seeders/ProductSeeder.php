<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ==========================================
            // ABARROTES (category_id: 1)
            // ==========================================
            ['category_id' => 1, 'name' => 'Arroz Grano de Oro', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 1.10],
            ['category_id' => 1, 'name' => 'Frijol Rojo de Seda', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 1.50],
            ['category_id' => 1, 'name' => 'Frijol Negro', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 1.65],
            ['category_id' => 1, 'name' => 'Azúcar Blanca', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 0.95],
            ['category_id' => 1, 'name' => 'Aceite Vegetal', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 2.50],
            ['category_id' => 1, 'name' => 'Aceite de Oliva', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 8.50],
            ['category_id' => 1, 'name' => 'Harina de Trigo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 0.85],
            ['category_id' => 1, 'name' => 'Harina de Maíz MASECA', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 1.20],
            ['category_id' => 1, 'name' => 'Sal de Mesa', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 0.45],
            ['category_id' => 1, 'name' => 'Pasta Espagueti', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 1.25],
            ['category_id' => 1, 'name' => 'Salsa de Tomate (lata)', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 0.65],
            ['category_id' => 1, 'name' => 'Atún en Agua', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 1.25],
            ['category_id' => 1, 'name' => 'Consomé de Pollo', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 20, 'unit_cost' => 0.15],
            ['category_id' => 1, 'name' => 'Papas Fritas Congeladas', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 3.50],

            // ==========================================
            // BEBIDAS (category_id: 2)
            // ==========================================
            ['category_id' => 2, 'name' => 'Coca Cola 355ml', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 24, 'unit_cost' => 0.55],
            ['category_id' => 2, 'name' => 'Coca Cola 600ml', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 12, 'unit_cost' => 0.85],
            ['category_id' => 2, 'name' => 'Coca Cola 2L', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 6, 'unit_cost' => 1.75],
            ['category_id' => 2, 'name' => 'Pepsi 355ml', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 24, 'unit_cost' => 0.45],
            ['category_id' => 2, 'name' => 'Sprite 355ml', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 12, 'unit_cost' => 0.55],
            ['category_id' => 2, 'name' => 'Fanta Naranja 355ml', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 12, 'unit_cost' => 0.55],
            ['category_id' => 2, 'name' => 'Agua Cristal 600ml', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 24, 'unit_cost' => 0.35],
            ['category_id' => 2, 'name' => 'Agua Cristal 1L', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 12, 'unit_cost' => 0.55],
            ['category_id' => 2, 'name' => 'Jugo Del Valle 355ml', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 12, 'unit_cost' => 0.65],
            ['category_id' => 2, 'name' => 'Cerveza Pilsener', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 24, 'unit_cost' => 0.85],
            ['category_id' => 2, 'name' => 'Cerveza Corona', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 12, 'unit_cost' => 1.50],
            ['category_id' => 2, 'name' => 'Café Molido', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 8.50],
            ['category_id' => 2, 'name' => 'Horchata en Polvo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 4.50],

            // ==========================================
            // CARNES (category_id: 3)
            // ==========================================
            ['category_id' => 3, 'name' => 'Pechuga de Pollo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 4.50],
            ['category_id' => 3, 'name' => 'Muslo de Pollo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 3.25],
            ['category_id' => 3, 'name' => 'Pollo Entero', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 2.85],
            ['category_id' => 3, 'name' => 'Carne Molida de Res', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 5.50],
            ['category_id' => 3, 'name' => 'Bistec de Res', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 7.50],
            ['category_id' => 3, 'name' => 'Costilla de Res', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 5.75],
            ['category_id' => 3, 'name' => 'Lomo de Cerdo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 4.85],
            ['category_id' => 3, 'name' => 'Costilla de Cerdo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 4.25],
            ['category_id' => 3, 'name' => 'Chicharrón', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 6.50],
            ['category_id' => 3, 'name' => 'Carne para Asar', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 6.75],

            // ==========================================
            // PANADERÍA (category_id: 4)
            // ==========================================
            ['category_id' => 4, 'name' => 'Pan Francés', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 50, 'unit_cost' => 0.10],
            ['category_id' => 4, 'name' => 'Pan de Hamburguesa', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 20, 'unit_cost' => 0.35],
            ['category_id' => 4, 'name' => 'Pan para Hot Dog', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 20, 'unit_cost' => 0.30],
            ['category_id' => 4, 'name' => 'Pan Dulce Surtido', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 20, 'unit_cost' => 0.25],
            ['category_id' => 4, 'name' => 'Tortilla de Maíz', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.05],
            ['category_id' => 4, 'name' => 'Tortilla de Harina', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 30, 'unit_cost' => 0.15],
            ['category_id' => 4, 'name' => 'Semita Alta', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 0.75],

            // ==========================================
            // CONDIMENTOS (category_id: 5)
            // ==========================================
            ['category_id' => 5, 'name' => 'Ketchup Heinz', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 4.50],
            ['category_id' => 5, 'name' => 'Mayonesa McCormick', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 5.25],
            ['category_id' => 5, 'name' => 'Mostaza', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 3.75],
            ['category_id' => 5, 'name' => 'Salsa Inglesa', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 3.25],
            ['category_id' => 5, 'name' => 'Salsa de Tomate Naturas', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 2.15],
            ['category_id' => 5, 'name' => 'Salsa Picante Tabasco', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 2.85],
            ['category_id' => 5, 'name' => 'Vinagre Blanco', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 1.25],
            ['category_id' => 5, 'name' => 'Salsa de Soya', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 2.50],
            ['category_id' => 5, 'name' => 'Ajo en Polvo', 'unit_type' => 'g', 'current_stock' => 0, 'min_stock' => 200, 'unit_cost' => 0.025],
            ['category_id' => 5, 'name' => 'Comino Molido', 'unit_type' => 'g', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.035],
            ['category_id' => 5, 'name' => 'Pimienta Negra Molida', 'unit_type' => 'g', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.045],
            ['category_id' => 5, 'name' => 'Orégano', 'unit_type' => 'g', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.030],
            ['category_id' => 5, 'name' => 'Achiote en Pasta', 'unit_type' => 'g', 'current_stock' => 0, 'min_stock' => 200, 'unit_cost' => 0.015],

            // ==========================================
            // LÁCTEOS (category_id: 6)
            // ==========================================
            ['category_id' => 6, 'name' => 'Queso Fresco', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 5.50],
            ['category_id' => 6, 'name' => 'Queso Duro Blando', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 7.25],
            ['category_id' => 6, 'name' => 'Queso Mozzarella', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 8.50],
            ['category_id' => 6, 'name' => 'Queso Cheddar', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 9.25],
            ['category_id' => 6, 'name' => 'Queso Crema', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 6.75],
            ['category_id' => 6, 'name' => 'Crema Fresca', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 3.25],
            ['category_id' => 6, 'name' => 'Leche Entera', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 1.15],
            ['category_id' => 6, 'name' => 'Mantequilla', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 5.50],
            ['category_id' => 6, 'name' => 'Huevos', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 60, 'unit_cost' => 0.18],

            // ==========================================
            // VERDURAS (category_id: 7)
            // ==========================================
            ['category_id' => 7, 'name' => 'Tomate', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 1.25],
            ['category_id' => 7, 'name' => 'Cebolla Blanca', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 0.95],
            ['category_id' => 7, 'name' => 'Chile Verde', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 1.75],
            ['category_id' => 7, 'name' => 'Chile Jalapeño', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 2.25],
            ['category_id' => 7, 'name' => 'Ajo Fresco', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 4.50],
            ['category_id' => 7, 'name' => 'Lechuga', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 0.65],
            ['category_id' => 7, 'name' => 'Repollo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 0.55],
            ['category_id' => 7, 'name' => 'Zanahoria', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 0.85],
            ['category_id' => 7, 'name' => 'Papa', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 0.75],
            ['category_id' => 7, 'name' => 'Güisquil', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 0.65],
            ['category_id' => 7, 'name' => 'Pepino', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 0.95],
            ['category_id' => 7, 'name' => 'Aguacate', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 15, 'unit_cost' => 0.45],
            ['category_id' => 7, 'name' => 'Loroco', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 8.50],
            ['category_id' => 7, 'name' => 'Cilantro', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 0.25],
            ['category_id' => 7, 'name' => 'Hierba Buena', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 0.25],
            ['category_id' => 7, 'name' => 'Rábano', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 0.15],
            ['category_id' => 7, 'name' => 'Elote', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 0.35],

            // ==========================================
            // FRUTAS (category_id: 8)
            // ==========================================
            ['category_id' => 8, 'name' => 'Limón', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 1.50],
            ['category_id' => 8, 'name' => 'Naranja', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 0.85],
            ['category_id' => 8, 'name' => 'Piña', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 1.25],
            ['category_id' => 8, 'name' => 'Sandía', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 3.50],
            ['category_id' => 8, 'name' => 'Plátano Maduro', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 0.65],
            ['category_id' => 8, 'name' => 'Guineo (Banano)', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 0.55],
            ['category_id' => 8, 'name' => 'Mango', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 1.25],
            ['category_id' => 8, 'name' => 'Papaya', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 0.95],

            // ==========================================
            // EMBUTIDOS (category_id: 9)
            // ==========================================
            ['category_id' => 9, 'name' => 'Salchicha de Pollo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 3.75],
            ['category_id' => 9, 'name' => 'Salchicha de Res', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 4.25],
            ['category_id' => 9, 'name' => 'Chorizo Argentino', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 5.50],
            ['category_id' => 9, 'name' => 'Chorizo Mexicano', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 4.85],
            ['category_id' => 9, 'name' => 'Jamón de Pavo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 6.50],
            ['category_id' => 9, 'name' => 'Jamón de Cerdo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 5.25],
            ['category_id' => 9, 'name' => 'Tocino', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 7.50],
            ['category_id' => 9, 'name' => 'Longaniza', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 4.50],

            // ==========================================
            // MARISCOS (category_id: 10)
            // ==========================================
            ['category_id' => 10, 'name' => 'Camarón Mediano', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 12.50],
            ['category_id' => 10, 'name' => 'Camarón Jumbo', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 18.50],
            ['category_id' => 10, 'name' => 'Filete de Pescado', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 8.75],
            ['category_id' => 10, 'name' => 'Pescado Entero (Tilapia)', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 5.50],
            ['category_id' => 10, 'name' => 'Calamar', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 9.50],
            ['category_id' => 10, 'name' => 'Jaiba', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 7.50],
            ['category_id' => 10, 'name' => 'Conchas', 'unit_type' => 'kg', 'current_stock' => 0, 'min_stock' => 1, 'unit_cost' => 11.50],

            // ==========================================
            // DESECHABLES (category_id: 11)
            // ==========================================
            ['category_id' => 11, 'name' => 'Platos Desechables 9"', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.08],
            ['category_id' => 11, 'name' => 'Platos Desechables 7"', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.06],
            ['category_id' => 11, 'name' => 'Vasos Desechables 12oz', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.04],
            ['category_id' => 11, 'name' => 'Vasos Desechables 16oz', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.05],
            ['category_id' => 11, 'name' => 'Tenedores Desechables', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.02],
            ['category_id' => 11, 'name' => 'Cucharas Desechables', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.02],
            ['category_id' => 11, 'name' => 'Cuchillos Desechables', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.02],
            ['category_id' => 11, 'name' => 'Servilletas', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 500, 'unit_cost' => 0.01],
            ['category_id' => 11, 'name' => 'Bolsas Plásticas Pequeñas', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 200, 'unit_cost' => 0.02],
            ['category_id' => 11, 'name' => 'Bolsas Plásticas Grandes', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 100, 'unit_cost' => 0.05],
            ['category_id' => 11, 'name' => 'Contenedores para Llevar', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 50, 'unit_cost' => 0.15],
            ['category_id' => 11, 'name' => 'Papel Aluminio', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 3.50],
            ['category_id' => 11, 'name' => 'Papel Film (Plástico)', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 2.75],

            // ==========================================
            // LIMPIEZA (category_id: 12)
            // ==========================================
            ['category_id' => 12, 'name' => 'Jabón Líquido para Platos', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 2.25],
            ['category_id' => 12, 'name' => 'Cloro', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 1.15],
            ['category_id' => 12, 'name' => 'Desinfectante Multiusos', 'unit_type' => 'lt', 'current_stock' => 0, 'min_stock' => 3, 'unit_cost' => 2.50],
            ['category_id' => 12, 'name' => 'Esponja para Platos', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 0.35],
            ['category_id' => 12, 'name' => 'Guantes de Hule', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 5, 'unit_cost' => 1.25],
            ['category_id' => 12, 'name' => 'Bolsas de Basura Grandes', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 50, 'unit_cost' => 0.15],
            ['category_id' => 12, 'name' => 'Papel Toalla', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 10, 'unit_cost' => 1.75],
            ['category_id' => 12, 'name' => 'Escoba', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 3.50],
            ['category_id' => 12, 'name' => 'Trapeador', 'unit_type' => 'pcs', 'current_stock' => 0, 'min_stock' => 2, 'unit_cost' => 2.75],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
