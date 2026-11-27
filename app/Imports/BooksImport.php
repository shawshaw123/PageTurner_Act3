<?php

namespace App\Imports;

use App\Models\Book;
use App\Models\Category;
use App\Models\ImportLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

HeadingRowFormatter::default('none');

class BooksImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnFailure, ShouldQueue
{
    use SkipsFailures;

    protected $importLog;
    protected $options;
    protected $processedRows = 0;
    protected $successfulRows = 0;
    protected $failedRows = 0;
    protected $errors = [];

    public function __construct(ImportLog $importLog, array $options = [])
    {
        $this->importLog = $importLog;
        $this->options = $options;
    }

    public function collection(Collection $rows)
    {
        $this->importLog->update([
            'status' => 'processing',
            'started_at' => now(),
            'total_rows' => $rows->count()
        ]);

        foreach ($rows as $index => $row) {
            $this->processedRows++;
            
            try {
                $this->processRow($row, $index + 2); // +2 for header row and 0-based index
                $this->successfulRows++;
            } catch (\Exception $e) {
                $this->failedRows++;
                $this->errors[] = [
                    'row' => $index + 2,
                    'data' => $row->toArray(),
                    'error' => $e->getMessage()
                ];
            }
        }

        $this->importLog->update([
            'status' => 'completed',
            'completed_at' => now(),
            'processed_rows' => $this->processedRows,
            'successful_rows' => $this->successfulRows,
            'failed_rows' => $this->failedRows,
            'errors' => $this->errors
        ]);
    }

    protected function processRow($row, $rowNumber)
    {
        // Validate ISBN format
        $isbn = $this->cleanIsbn($row['ISBN']);
        if (!$this->isValidIsbn($isbn)) {
            throw new \Exception("Invalid ISBN format: {$isbn}");
        }

        // Check for duplicates
        $existingBook = Book::where('isbn', $isbn)->first();
        
        if ($existingBook) {
            if (!($this->options['update_existing'] ?? false)) {
                throw new \Exception("Book with ISBN {$isbn} already exists");
            }
            
            // Update existing book
            $existingBook->update([
                'title' => $row['Title'],
                'author' => $row['Author'],
                'price' => $row['Price'],
                'stock' => $row['Stock'],
                'description' => $row['Description'] ?? '',
                'category_id' => $this->getCategoryId($row['Category']),
            ]);
        } else {
            // Create new book
            Book::create([
                'isbn' => $isbn,
                'title' => $row['Title'],
                'author' => $row['Author'],
                'price' => $row['Price'],
                'stock' => $row['Stock'],
                'description' => $row['Description'] ?? '',
                'category_id' => $this->getCategoryId($row['Category']),
            ]);
        }
    }

    protected function cleanIsbn($isbn)
    {
        return preg_replace('/[^0-9X]/i', '', $isbn);
    }

    protected function isValidIsbn($isbn)
    {
        // ISBN-10 validation
        if (strlen($isbn) == 10) {
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $sum += (10 - $i) * intval($isbn[$i]);
            }
            $checksum = strtoupper($isbn[9]);
            $sum += ($checksum == 'X') ? 1 : intval($checksum);
            return ($sum % 11) == 0;
        }
        
        // ISBN-13 validation
        if (strlen($isbn) == 13) {
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += ($i % 2 == 0) ? intval($isbn[$i]) : intval($isbn[$i]) * 3;
            }
            $checksum = (10 - ($sum % 10)) % 10;
            return $checksum == intval($isbn[12]);
        }
        
        return false;
    }

    protected function getCategoryId($categoryName)
    {
        $category = Category::where('name', $categoryName)->first();
        if (!$category) {
            throw new \Exception("Category '{$categoryName}' does not exist");
        }
        return $category->id;
    }

    public function rules(): array
    {
        return [
            'ISBN' => 'required|string',
            'Title' => 'required|string|max:255',
            'Author' => 'required|string|max:255',
            'Price' => 'required|numeric|between:0,9999.99',
            'Stock' => 'required|integer|min:0',
            'Category' => 'required|string|exists:categories,name',
            'Description' => 'nullable|string|max:2000',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'ISBN.required' => 'ISBN is required',
            'Title.required' => 'Title is required',
            'Author.required' => 'Author is required',
            'Price.required' => 'Price is required',
            'Price.numeric' => 'Price must be a number',
            'Price.between' => 'Price must be between 0 and 9999.99',
            'Stock.required' => 'Stock is required',
            'Stock.integer' => 'Stock must be an integer',
            'Stock.min' => 'Stock must be non-negative',
            'Category.required' => 'Category is required',
            'Category.exists' => 'Category does not exist',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
