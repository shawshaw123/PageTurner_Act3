<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuickTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 Quick Test Seeder - Basic Books Only');
        
        // Clear existing books
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::table('books')->truncate();
        } catch (\Exception $e) {
            DB::table('books')->delete();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Get categories
        $categoryIds = DB::table('categories')->pluck('id')->toArray();
        if (empty($categoryIds)) {
            $this->error('❌ No categories found. Please run CategorySeeder first.');
            return;
        }
        
        // Generate 1000 test books (only using basic columns that exist)
        $books = [];
        for ($i = 1; $i <= 1000; $i++) {
            $books[] = [
                'isbn' => '978' . str_pad($i, 9, '0', STR_PAD_LEFT) . mt_rand(0, 9),
                'title' => 'Test Book ' . $i,
                'author' => 'Author ' . mt_rand(1, 100),
                'price' => mt_rand(999, 4999) / 100,
                'stock_quantity' => mt_rand(0, 1000),
                'category_id' => $categoryIds[mt_rand(0, count($categoryIds) - 1)],
                'description' => 'Test description for book ' . $i,
                'cover_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert in chunks
        $chunks = array_chunk($books, 500);
        foreach ($chunks as $chunk) {
            DB::table('books')->insert($chunk);
        }
        
        $this->info('✅ Created 1000 test books successfully!');
        $this->info('📊 Total books: ' . DB::table('books')->count());
    }
}
