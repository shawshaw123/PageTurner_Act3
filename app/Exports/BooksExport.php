<?php

namespace App\Exports;

use App\Models\Book;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BooksExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, WithChunkReading, ShouldQueue
{
    protected $filters;
    protected $columns;

    public function __construct(array $filters = [], array $columns = [])
    {
        $this->filters = $filters;
        $this->columns = $columns ?: ['id', 'isbn', 'title', 'author', 'price', 'stock', 'category', 'description', 'created_at'];
    }

    public function query(): Builder
    {
        $query = Book::query()->with('category');

        // Apply filters
        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (!empty($this->filters['price_min'])) {
            $query->where('price', '>=', $this->filters['price_min']);
        }

        if (!empty($this->filters['price_max'])) {
            $query->where('price', '<=', $this->filters['price_max']);
        }

        if (!empty($this->filters['stock_status'])) {
            switch ($this->filters['stock_status']) {
                case 'in_stock':
                    $query->where('stock', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock', '=', 0);
                    break;
                case 'low_stock':
                    $query->where('stock', '<', 10)->where('stock', '>', 0);
                    break;
            }
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        $headings = [];
        
        foreach ($this->columns as $column) {
            switch ($column) {
                case 'id':
                    $headings[] = 'ID';
                    break;
                case 'isbn':
                    $headings[] = 'ISBN';
                    break;
                case 'title':
                    $headings[] = 'Title';
                    break;
                case 'author':
                    $headings[] = 'Author';
                    break;
                case 'price':
                    $headings[] = 'Price';
                    break;
                case 'stock':
                    $headings[] = 'Stock';
                    break;
                case 'category':
                    $headings[] = 'Category';
                    break;
                case 'description':
                    $headings[] = 'Description';
                    break;
                case 'created_at':
                    $headings[] = 'Created At';
                    break;
                case 'updated_at':
                    $headings[] = 'Updated At';
                    break;
                default:
                    $headings[] = ucfirst(str_replace('_', ' ', $column));
            }
        }

        return $headings;
    }

    public function map($book): array
    {
        $row = [];
        
        foreach ($this->columns as $column) {
            switch ($column) {
                case 'category':
                    $row[] = $book->category ? $book->category->name : '';
                    break;
                case 'price':
                    $row[] = $book->price;
                    break;
                case 'created_at':
                case 'updated_at':
                    $row[] = $book->$column ? $book->$column->format('Y-m-d H:i:s') : '';
                    break;
                default:
                    $row[] = $book->$column ?? '';
            }
        }

        return $row;
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price
            'F' => NumberFormat::FORMAT_NUMBER, // Stock
            'I' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Created At
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Set column widths
            'A' => ['width' => 10], // ID
            'B' => ['width' => 20], // ISBN
            'C' => ['width' => 40], // Title
            'D' => ['width' => 30], // Author
            'E' => ['width' => 12], // Price
            'F' => ['width' => 8],  // Stock
            'G' => ['width' => 15], // Category
            'H' => ['width' => 50], // Description
        ];
    }

    /**
     * Chunk size for efficient memory usage during export
     */
    public function chunkSize(): int
    {
        return 2000;
    }
}
