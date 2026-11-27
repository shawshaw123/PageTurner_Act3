<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    /**
     * Cache category IDs to avoid repeated database queries
     */
    protected static array $categoryIds = [];
    
    /**
     * Pre-defined real-world publishers for realistic data
     */
    protected static array $publishers = [
        'Penguin Random House',
        'HarperCollins',
        'Simon & Schuster',
        'Hachette Book Group',
        'Macmillan Publishers',
        'Scholastic Corporation',
        'Hachette Book Group',
        'Pearson Education',
        'Wiley',
        'Springer Nature',
        'Oxford University Press',
        'Cambridge University Press',
        'Harvard University Press',
        'Yale University Press',
        'Princeton University Press',
    ];

    /**
     * Book formats with realistic pricing distributions
     */
    protected static array $formats = [
        'Hardcover' => ['min' => 24.99, 'max' => 49.99],
        'Paperback' => ['min' => 9.99, 'max' => 24.99],
        'Ebook' => ['min' => 4.99, 'max' => 19.99],
        'Audiobook' => ['min' => 14.99, 'max' => 34.99],
    ];

    public function definition(): array
    {
        // Load category IDs once and cache them
        if (empty(self::$categoryIds)) {
            self::$categoryIds = Category::pluck('id')->toArray();
        }

        // Select format and determine price range
        $format = fake()->randomElement(array_keys(self::$formats));
        $priceRange = self::$formats[$format];
        $basePrice = fake()->randomFloat(2, $priceRange['min'], $priceRange['max']);

        return [
            'isbn' => $this->generateValidIsbn13(),
            'title' => $this->generateRealisticTitle(),
            'author' => fake()->name(),
            'publisher' => fake()->randomElement(self::$publishers),
            'price' => $basePrice,
            'stock_quantity' => fake()->numberBetween(0, 1000),
            'category_id' => fake()->randomElement(self::$categoryIds),
            'format' => $format,
            'description' => fake()->paragraphs(rand(1, 3), true),
            'cover_image' => null,
            'is_active' => fake()->boolean(85), // 85% of books are active
            'published_at' => fake()->dateTimeBetween('-10 years', 'now'),
            'pages' => fake()->numberBetween(100, 1200),
            'language' => fake()->randomElement(['English', 'Spanish', 'French', 'German', 'Italian']),
            'dimensions' => fake()->randomElement(['6 x 9', '5.5 x 8.5', '7 x 10', '8.5 x 11']),
            'weight' => fake()->randomFloat(2, 0.5, 5.0), // in pounds
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Generate a valid ISBN-13 with proper checksum
     */
    protected function generateValidIsbn13(): string
    {
        // Generate 12 random digits
        $isbn12 = '';
        for ($i = 0; $i < 12; $i++) {
            $isbn12 .= random_int(0, 9);
        }
        
        // Add 978 prefix for books
        $isbn12 = '978' . substr($isbn12, 0, 9);
        
        // Calculate checksum
        $checksum = $this->calculateIsbn13Checksum($isbn12);
        
        return $isbn12 . $checksum;
    }

    /**
     * Calculate ISBN-13 checksum using modulo 10
     */
    protected function calculateIsbn13Checksum(string $isbn12): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $isbn12[$i];
            $sum += $digit * ($i % 2 === 0 ? 1 : 3);
        }
        
        $checksum = (10 - ($sum % 10)) % 10;
        return $checksum;
    }

    /**
     * Generate more realistic book titles
     */
    protected function generateRealisticTitle(): string
    {
        $titlePatterns = [
            'The {adjective} {noun} of {place}',
            '{adjective} {noun}: A {noun} Story',
            'Beyond the {noun}',
            'The {noun} {verb}',
            '{noun} and {noun}',
            'A {adjective} {noun}',
            'The Last {noun}',
            '{noun}: The {adjective} {noun}',
        ];

        $adjectives = ['Silent', 'Golden', 'Dark', 'Lost', 'Hidden', 'Ancient', 'Forgotten', 'Mysterious', 'Sacred', 'Eternal'];
        $nouns = ['Kingdom', 'Journey', 'Secret', 'Shadow', 'Light', 'Path', 'Dream', 'Truth', 'Legend', 'Prophecy'];
        $places = ['Eden', 'Atlantis', 'Avalon', 'Camelot', 'Olympus', 'Shangri-La', 'Valhalla', 'Tir na nOg'];
        $verbs = ['Rises', 'Falls', 'Returns', 'Awakens', 'Endures', 'Vanishes', 'Transforms', 'Prevails'];

        $pattern = fake()->randomElement($titlePatterns);
        
        return ucfirst(str_replace([
            '{adjective}', '{noun}', '{place}', '{verb}'
        ], [
            fake()->randomElement($adjectives),
            fake()->randomElement($nouns),
            fake()->randomElement($places),
            fake()->randomElement($verbs)
        ], $pattern));
    }

    /**
     * State for bestseller books (high stock, always active)
     */
    public function bestseller(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => fake()->numberBetween(500, 1000),
            'is_active' => true,
            'price' => fake()->randomFloat(2, 19.99, 39.99),
        ]);
    }

    /**
     * State for rare books (low stock, higher price)
     */
    public function rare(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => fake()->numberBetween(0, 10),
            'price' => fake()->randomFloat(2, 49.99, 199.99),
            'published_at' => fake()->dateTimeBetween('-50 years', '-10 years'),
        ]);
    }

    /**
     * State for new releases
     */
    public function newRelease(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'is_active' => true,
            'stock_quantity' => fake()->numberBetween(100, 500),
        ]);
    }

    /**
     * State for academic books
     */
    public function academic(): static
    {
        return $this->state(fn (array $attributes) => [
            'publisher' => fake()->randomElement([
                'Oxford University Press',
                'Cambridge University Press',
                'Harvard University Press',
                'Yale University Press',
                'Princeton University Press',
            ]),
            'price' => fake()->randomFloat(2, 49.99, 149.99),
            'format' => 'Hardcover',
            'pages' => fake()->numberBetween(300, 1200),
        ]);
    }
}
