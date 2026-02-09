<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Get the Fiction category
$fictionCategory = \App\Models\Category::where('name', 'Fiction')->first();

if (!$fictionCategory) {
    echo "Fiction category not found!\n";
    exit(1);
}

// Books to update
$booksToUpdate = [
    [
        'title' => 'The Night Circus',
        'author' => 'Erin Morgenstern',
        'description' => 'A mysterious, traveling circus appears without warning, open only at night. Within its black-and-white tents, two young illusionists, Celia and Marco, are bound in a fierce magical competition. Unbeknownst to them, this is a duel where only one can be left standing, and the circus is the stage. As they fall deeply in love, their game threatens the very existence of the circus and everyone in it. A novel about enchantment, love, and destiny, told with breathtaking visual prose.'
    ],
    [
        'title' => 'Project Hail Mary',
        'author' => 'Andy Weir',
        'description' => 'Ryland Grace wakes up on a spaceship with no memory of who he is or how he got there. He soon discovers he is the sole survivor on a desperate, last-chance mission to save humanity from a star-eating microbe threatening to extinct all life on Earth. Alone, he must use his scientific knowledge to solve an interstellar mystery. The story is a brilliant, thrilling, and surprisingly humorous tale of friendship, ingenuity, and survival against impossible odds.'
    ],
    [
        'title' => 'Piranesi',
        'author' => 'Susanna Clarke',
        'description' => 'Piranesi lives in the House, a vast labyrinth of endless marble halls filled with statues and drowned by tides. He carefully documents its wonders and its only other resident, "the Other." But as mysterious messages appear and his own memories begin to unravel, Piranesi is forced to question the nature of the House and his own identity. This is a haunting, beautiful, and deeply original novel about loneliness, discovery, and the power of the human spirit.'
    ],
    [
        'title' => 'The Seven Husbands of Evelyn Hugo',
        'author' => 'Taylor Jenkins Reid',
        'description' => 'Aging and reclusive Hollywood icon Evelyn Hugo finally chooses an unknown journalist, Monique Grant, to write her tell-all biography. Over a series of interviews, Evelyn recounts her glamorous, scandalous life and her seven marriages, revealing the ruthless ambition and unexpected great love of her life. It\'s a sweeping story of Old Hollywood, forbidden love, and the complex sacrifices behind fame and identity.'
    ],
    [
        'title' => 'Klara and the Sun',
        'author' => 'Kazuo Ishiguro',
        'description' => 'Told from the perspective of Klara, an "Artificial Friend" with exceptional observational abilities, this novel explores a near-future world changed by technology. Klara is chosen by a sickly young girl named Josie. From her place in the store and then in the family home, Klara watches human behavior closely, hoping to understand the mysteries of love, promise, and what it truly means to be human. A poignant and profound meditation on the heart and its choices.'
    ]
];

// Get existing fiction books
$fictionBooks = $fictionCategory->books()->take(5)->get();

echo "Found " . $fictionBooks->count() . " fiction books to update\n";

foreach ($booksToUpdate as $index => $bookData) {
    if ($index < $fictionBooks->count()) {
        $book = $fictionBooks[$index];
        $book->update($bookData);
        echo "Updated: " . $book->title . " by " . $book->author . "\n";
    } else {
        // Create new book if needed
        $newBook = \App\Models\Book::create(array_merge($bookData, [
            'category_id' => $fictionCategory->id,
            'isbn' => '978-' . rand(1000000000, 9999999999),
            'price' => rand(1499, 3499) / 100,
            'stock_quantity' => rand(10, 50),
        ]));
        echo "Created new: " . $newBook->title . " by " . $newBook->author . "\n";
    }
}

echo "Fiction books update completed!\n";
