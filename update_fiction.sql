-- Update Fiction books with specific content
USE pageturner_bookstore;

-- First, let's see what we have
SELECT id, name FROM categories WHERE name = 'Fiction';

-- Delete existing fiction books
DELETE FROM books WHERE category_id = (SELECT id FROM categories WHERE name = 'Fiction');

-- Insert the new fiction books
INSERT INTO books (category_id, title, author, isbn, price, stock_quantity, description, created_at, updated_at) VALUES
((SELECT id FROM categories WHERE name = 'Fiction'), 'The Night Circus', 'Erin Morgenstern', '978-0307744432', 16.99, 25, 'A mysterious, traveling circus appears without warning, open only at night. Within its black-and-white tents, two young illusionists, Celia and Marco, are bound in a fierce magical competition. Unbeknownst to them, this is a duel where only one can be left standing, and the circus is the stage. As they fall deeply in love, their game threatens the very existence of the circus and everyone in it. A novel about enchantment, love, and destiny, told with breathtaking visual prose.', NOW(), NOW()),
((SELECT id FROM categories WHERE name = 'Fiction'), 'Project Hail Mary', 'Andy Weir', '978-0593135204', 28.99, 30, 'Ryland Grace wakes up on a spaceship with no memory of who he is or how he got there. He soon discovers he is the sole survivor on a desperate, last-chance mission to save humanity from a star-eating microbe threatening to extinct all life on Earth. Alone, he must use his scientific knowledge to solve an interstellar mystery. The story is a brilliant, thrilling, and surprisingly humorous tale of friendship, ingenuity, and survival against impossible odds.', NOW(), NOW()),
((SELECT id FROM categories WHERE name = 'Fiction'), 'Piranesi', 'Susanna Clarke', '978-1635573175', 18.99, 20, 'Piranesi lives in the House, a vast labyrinth of endless marble halls filled with statues and drowned by tides. He carefully documents its wonders and its only other resident, "the Other." But as mysterious messages appear and his own memories begin to unravel, Piranesi is forced to question the nature of the House and his own identity. This is a haunting, beautiful, and deeply original novel about loneliness, discovery, and the power of the human spirit.', NOW(), NOW()),
((SELECT id FROM categories WHERE name = 'Fiction'), 'The Seven Husbands of Evelyn Hugo', 'Taylor Jenkins Reid', '978-1501161933', 17.99, 35, 'Aging and reclusive Hollywood icon Evelyn Hugo finally chooses an unknown journalist, Monique Grant, to write her tell-all biography. Over a series of interviews, Evelyn recounts her glamorous, scandalous life and her seven marriages, revealing the ruthless ambition and unexpected great love of her life. It\'s a sweeping story of Old Hollywood, forbidden love, and the complex sacrifices behind fame and identity.', NOW(), NOW()),
((SELECT id FROM categories WHERE name = 'Fiction'), 'Klara and the Sun', 'Kazuo Ishiguro', '978-0593318171', 19.99, 28, 'Told from the perspective of Klara, an "Artificial Friend" with exceptional observational abilities, this novel explores a near-future world changed by technology. Klara is chosen by a sickly young girl named Josie. From her place in the store and then in the family home, Klara watches human behavior closely, hoping to understand the mysteries of love, promise, and what it truly means to be human. A poignant and profound meditation on the heart and its choices.', NOW(), NOW());

-- Verify the updates
SELECT title, author, price FROM books WHERE category_id = (SELECT id FROM categories WHERE name = 'Fiction') ORDER BY id;
