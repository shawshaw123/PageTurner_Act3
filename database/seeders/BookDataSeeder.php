<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookDataSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Fiction' => [
                [
                    'title' => 'The Night Circus',
                    'author' => 'Erin Morgenstern',
                    'isbn' => '978-0307744432',
                    'price' => 16.99,
                    'stock_quantity' => 25,
                    'description' => 'A mysterious, traveling circus appears without warning, open only at night. Within its black-and-white tents, two young illusionists, Celia and Marco, are bound in a fierce magical competition.',
                ],
                [
                    'title' => 'Project Hail Mary',
                    'author' => 'Andy Weir',
                    'isbn' => '978-0593135204',
                    'price' => 28.99,
                    'stock_quantity' => 30,
                    'description' => 'Ryland Grace wakes up on a spaceship with no memory of who he is or how he got there. He soon discovers he is the sole survivor on a desperate, last-chance mission to save humanity.',
                ],
                [
                    'title' => 'Piranesi',
                    'author' => 'Susanna Clarke',
                    'isbn' => '978-1635573175',
                    'price' => 18.99,
                    'stock_quantity' => 20,
                    'description' => 'Piranesi lives in the House, a vast labyrinth of endless marble halls filled with statues and drowned by tides. He carefully documents its wonders and its only other resident, "the Other."',
                ],
            ],
            'Horror' => [
                [
                    'title' => 'The Haunting of Hill House',
                    'author' => 'Shirley Jackson',
                    'isbn' => '978-0143039983',
                    'price' => 15.00,
                    'stock_quantity' => 10,
                    'description' => "The groundbreaking novel about four people who enter a notoriously haunted mansion to prove the existence of the supernatural. It's a profound study in psychological terror, isolation, and the unraveling of a fragile mind. \"No live organism can continue for long to exist sanely under conditions of absolute reality...\"",
                ],
                [
                    'title' => 'Mexican Gothic',
                    'author' => 'Silvia Moreno-Garcia',
                    'isbn' => '978-0525620785',
                    'price' => 17.00,
                    'stock_quantity' => 15,
                    'description' => "In 1950s Mexico, socialite Noemí travels to a remote, decaying mansion to check on her cousin, who claims her English husband is poisoning her. She uncovers family secrets tied to eugenics, fungal horror, and a house that is very much alive.",
                ],
                [
                    'title' => 'The Shining',
                    'author' => 'Stephen King',
                    'isbn' => '978-0307743657',
                    'price' => 18.00,
                    'stock_quantity' => 20,
                    'description' => "A masterpiece of supernatural and psychological horror. Struggling writer Jack Torrance becomes the winter caretaker of the isolated Overlook Hotel, bringing his wife and psychic son, Danny. As the hotel's malevolent forces close in, the family's sanity and survival are pushed to the limit.",
                ],
            ],
            'Romance' => [
                [
                    'title' => 'Pride and Prejudice',
                    'author' => 'Jane Austen',
                    'isbn' => '978-0141439518',
                    'price' => 12.00,
                    'stock_quantity' => 25,
                    'description' => "The timeless classic. The spirited Elizabeth Bennet clashes with the proud, wealthy Mr. Darcy in a witty and incisive exploration of love, class, reputation, and first impressions.",
                ],
                [
                    'title' => "The Time Traveler's Wife",
                    'author' => 'Audrey Niffenegger',
                    'isbn' => '978-0156029438',
                    'price' => 16.00,
                    'stock_quantity' => 15,
                    'description' => "A uniquely structured love story. Clare has known Henry all her life, as he is a time traveler who appears at different points in her childhood.",
                ],
                [
                    'title' => 'The Kiss Quotient',
                    'author' => 'Helen Hoang',
                    'isbn' => '978-0515156089',
                    'price' => 15.00,
                    'stock_quantity' => 20,
                    'description' => "A heartwarming and steamy contemporary romance. Stella Lane hires escort Michael Phan to teach her about dating and intimacy.",
                ],
            ],
            'Fantasy' => [
                [
                    'title' => 'The Name of the Wind',
                    'author' => 'Patrick Rothfuss',
                    'isbn' => '978-0756404741',
                    'price' => 20.00,
                    'stock_quantity' => 15,
                    'description' => "The first-person chronicle of Kvothe, a legendary wizard, musician, and adventurer, now living in hiding as a simple innkeeper.",
                ],
                [
                    'title' => 'The City of Brass',
                    'author' => 'S.A. Chakraborty',
                    'isbn' => '978-0062678102',
                    'price' => 17.00,
                    'stock_quantity' => 12,
                    'description' => "Set in 18th-century Cairo, it follows con artist Nahri, who accidentally summons a mysterious djinn warrior.",
                ],
                [
                    'title' => 'Jonathan Strange & Mr Norrell',
                    'author' => 'Susanna Clarke',
                    'isbn' => '978-1582344164',
                    'price' => 19.00,
                    'stock_quantity' => 10,
                    'description' => "A historical fantasy set in an alternate Napoleonic-era England where reclusive Mr. Norrell proves he can actually do magic.",
                ],
            ],
            'Science' => [
                [
                    'title' => 'A Short History of Nearly Everything',
                    'author' => 'Bill Bryson',
                    'isbn' => '978-0767908184',
                    'price' => 16.00,
                    'stock_quantity' => 20,
                    'description' => "The ultimate beginner's guide to science. Bryson sets out to understand how we went from knowing nothing to understanding the cosmos, geology, physics, and life itself.",
                ],
                [
                    'title' => 'The Immortal Life of Henrietta Lacks',
                    'author' => 'Rebecca Skloot',
                    'isbn' => '978-1400052189',
                    'price' => 17.00,
                    'stock_quantity' => 15,
                    'description' => "The gripping story of HeLa cells, taken from a poor Black tobacco farmer without her knowledge in 1951.",
                ],
            ],
            'Children' => [
                [
                    'title' => "Charlotte's Web",
                    'author' => 'E.B. White',
                    'isbn' => '978-0061124952',
                    'price' => 10.00,
                    'stock_quantity' => 30,
                    'description' => "The tender story of a pig named Wilbur and his loyal friend, Charlotte, a clever spider who weaves words into her web to save him.",
                ],
                [
                    'title' => "Harry Potter and the Sorcerer's Stone",
                    'author' => 'J.K. Rowling',
                    'isbn' => '978-0590353427',
                    'price' => 15.00,
                    'stock_quantity' => 50,
                    'description' => "On his 11th birthday, orphaned Harry Potter learns he is a wizard and is whisked away to Hogwarts School of Witchcraft and Wizardry.",
                ],
            ],
            'History' => [
                [
                    'title' => 'The Splendid and the Vile',
                    'author' => 'Erik Larson',
                    'isbn' => '978-0385348713',
                    'price' => 18.00,
                    'stock_quantity' => 15,
                    'description' => "Larson uses diaries and original documents to create a day-by-day chronicle of Winston Churchill's first year as Prime Minister.",
                ],
                [
                    'title' => "A People's History of the United States",
                    'author' => 'Howard Zinn',
                    'isbn' => '978-0062397348',
                    'price' => 19.00,
                    'stock_quantity' => 10,
                    'description' => "A landmark work that tells American history \"from the bottom up,\" through the eyes of its marginalized groups.",
                ],
            ],
            'Non-Fiction' => [
                [
                    'title' => 'In Cold Blood',
                    'author' => 'Truman Capote',
                    'isbn' => '978-0679745587',
                    'price' => 16.00,
                    'stock_quantity' => 15,
                    'description' => "The book that invented the \"nonfiction novel.\" Capote reconstructs the 1959 murders of the Clutter family.",
                ],
                [
                    'title' => 'Educated',
                    'author' => 'Tara Westover',
                    'isbn' => '978-0399588525',
                    'price' => 18.00,
                    'stock_quantity' => 20,
                    'description' => "A stunning memoir. Westover was born to survivalist parents in the Idaho mountains and never set foot in a classroom until she was 17.",
                ],
            ],
        ];

        foreach ($data as $categoryName => $books) {
            $category = Category::updateOrCreate(
                ['name' => $categoryName],
                ['description' => "Books in the $categoryName category."]
            );

            foreach ($books as $bookData) {
                Book::updateOrCreate(
                    ['isbn' => $bookData['isbn']],
                    array_merge($bookData, ['category_id' => $category->id])
                );
            }
        }
    }
}
