<?php

$file = fopen('mock_books.csv', 'w');

// Headers
fputcsv($file, ['ISBN', 'Title', 'Author', 'Price', 'Stock', 'Category', 'Description']);

$categories = [1, 2, 3, 4, 5]; // Assuming these category IDs exist

for ($i = 1; $i <= 1000; $i++) {
    $isbn = '978' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
    $title = "Sample Book Title " . $i;
    $author = "Author Name " . rand(1, 50);
    $price = number_format(rand(500, 5000) / 100, 2);
    $stock = rand(0, 100);
    $category = $categories[array_rand($categories)];
    $description = "This is a comprehensive description for sample book " . $i . ".";

    fputcsv($file, [$isbn, $title, $author, $price, $stock, $category, $description]);
}

fclose($file);
echo "Successfully generated mock_books.csv with 1000 rows.\n";
