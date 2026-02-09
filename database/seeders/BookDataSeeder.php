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
                [
                    'title' => 'The Seven Husbands of Evelyn Hugo',
                    'author' => 'Taylor Jenkins Reid',
                    'isbn' => '978-1501161933',
                    'price' => 17.99,
                    'stock_quantity' => 35,
                    'description' => 'Aging and reclusive Hollywood icon Evelyn Hugo finally chooses an unknown journalist, Monique Grant, to write her tell-all biography.',
                ],
                [
                    'title' => 'Klara and the Sun',
                    'author' => 'Kazuo Ishiguro',
                    'isbn' => '978-0593318171',
                    'price' => 19.99,
                    'stock_quantity' => 28,
                    'description' => 'Told from the perspective of Klara, an "Artificial Friend" with exceptional observational abilities, this novel explores a near-future world changed by technology.',
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
                [
                    'title' => 'Ring',
                    'author' => 'Koji Suzuki',
                    'isbn' => '978-1932234411',
                    'price' => 14.00,
                    'stock_quantity' => 12,
                    'description' => "The source of the iconic Japanese horror film. A journalist investigates a series of mysterious deaths linked to a cursed videotape, leading her to a terrifying discovery about a vengeful psychic entity.",
                ],
                [
                    'title' => 'A Head Full of Ghosts',
                    'author' => 'Paul Tremblay',
                    'isbn' => '978-0062363237',
                    'price' => 16.00,
                    'stock_quantity' => 18,
                    'description' => "A chilling, metafictional horror tale. The story is told by a woman who, as a child, saw her family torn apart when her older sister was believed to be possessed.",
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
                [
                    'title' => 'Outlander',
                    'author' => 'Diana Gabaldon',
                    'isbn' => '978-0440212560',
                    'price' => 18.00,
                    'stock_quantity' => 30,
                    'description' => "A sweeping historical romance with a time-travel twist. In 1945, former combat nurse Claire Randall is transported back to 1743 Scotland.",
                ],
                [
                    'title' => 'The Unhoneymooners',
                    'author' => 'Christina Lauren',
                    'isbn' => '978-1501128035',
                    'price' => 14.00,
                    'stock_quantity' => 22,
                    'description' => "A hilarious and enemies-to-lovers rom-com. Olive is forced to go on her twin sister's non-refundable honeymoon with her nemesis, Ethan.",
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
                [
                    'title' => 'The Poppy War',
                    'author' => 'R.F. Kuang',
                    'isbn' => '978-0062662569',
                    'price' => 18.00,
                    'stock_quantity' => 20,
                    'description' => "A dark, grimdark military fantasy inspired by 20th-century Chinese history. RIN discovers a shocking, shamanic power.",
                ],
                [
                    'title' => 'The Priory of the Orange Tree',
                    'author' => 'Samantha Shannon',
                    'isbn' => '978-1635570298',
                    'price' => 22.00,
                    'stock_quantity' => 8,
                    'description' => "A standalone epic fantasy. A world is divided between those who worship dragons as gods and those who see them as monsters.",
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
                [
                    'title' => 'Sapiens: A Brief History of Humankind',
                    'author' => 'Yuval Noah Harari',
                    'isbn' => '978-0062316097',
                    'price' => 18.00,
                    'stock_quantity' => 25,
                    'description' => "A sweeping narrative of humanity's journey from insignificant foragers to the planet's dominant force.",
                ],
                [
                    'title' => 'The Sixth Extinction: An Unnatural History',
                    'author' => 'Elizabeth Kolbert',
                    'isbn' => '978-1250062185',
                    'price' => 16.00,
                    'stock_quantity' => 12,
                    'description' => "A powerful and urgent book that explains how human activity is triggering a modern mass extinction event.",
                ],
                [
                    'title' => 'Cosmos',
                    'author' => 'Carl Sagan',
                    'isbn' => '978-0345331359',
                    'price' => 19.00,
                    'stock_quantity' => 10,
                    'description' => "The classic, lyrical companion to the iconic TV series. Sagan traces the universe's 14-billion-year evolution.",
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
                [
                    'title' => 'Where the Red Fern Grows',
                    'author' => 'Wilson Rawls',
                    'isbn' => '978-0440412670',
                    'price' => 12.00,
                    'stock_quantity' => 20,
                    'description' => "A deeply emotional story set in the Ozarks. A young boy, Billy, works tirelessly to buy two coonhound pups.",
                ],
                [
                    'title' => 'The Phantom Tollbooth',
                    'author' => 'Norton Juster',
                    'isbn' => '978-0394820378',
                    'price' => 11.00,
                    'stock_quantity' => 15,
                    'description' => "A whimsical and brilliant fantasy. A bored boy named Milo drives through a magical tollbooth into the Lands Beyond.",
                ],
                [
                    'title' => 'Percy Jackson and the Lightning Thief',
                    'author' => 'Rick Riordan',
                    'isbn' => '978-0786838653',
                    'price' => 14.00,
                    'stock_quantity' => 25,
                    'description' => "Twelve-year-old Percy Jackson discovers he is a demigod, the son of Poseidon.",
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
                [
                    'title' => 'SPQR: A History of Ancient Rome',
                    'author' => 'Mary Beard',
                    'isbn' => '978-1631492228',
                    'price' => 17.00,
                    'stock_quantity' => 12,
                    'description' => "A fresh and authoritative history of Rome from its mythic beginnings to the 3rd century CE.",
                ],
                [
                    'title' => 'The Warmth of Other Suns',
                    'author' => 'Isabel Wilkerson',
                    'isbn' => '978-0679763888',
                    'price' => 18.00,
                    'stock_quantity' => 15,
                    'description' => "A masterful account of the six-million-person exodus of Black Americans from the Jim Crow South to Northern and Western cities.",
                ],
                [
                    'title' => 'Guns, Germs, and Steel',
                    'author' => 'Jared Diamond',
                    'isbn' => '978-0393354324',
                    'price' => 18.00,
                    'stock_quantity' => 10,
                    'description' => "A Pulitzer Prize-winning exploration of why Eurasian civilizations conquered or displace others.",
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
                [
                    'title' => 'The Year of Magical Thinking',
                    'author' => 'Joan Didion',
                    'isbn' => '978-1400078431',
                    'price' => 15.00,
                    'stock_quantity' => 12,
                    'description' => "A raw, literary masterpiece of grief. Didion chronicles the year following the sudden death of her husband.",
                ],
                [
                    'title' => 'Into Thin Air',
                    'author' => 'Jon Krakauer',
                    'isbn' => '978-0385494786',
                    'price' => 16.00,
                    'stock_quantity' => 15,
                    'description' => "A harrowing first-person narrative of the deadliest season in Everest's history up to that point (1996).",
                ],
                [
                    'title' => 'Bad Blood: Secrets and Lies in a Silicon Valley Startup',
                    'author' => 'John Carreyrou',
                    'isbn' => '978-1524731656',
                    'price' => 18.00,
                    'stock_quantity' => 20,
                    'description' => "A riveting, investigative thriller about the rise and fall of Theranos.",
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
