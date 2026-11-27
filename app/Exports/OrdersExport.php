<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, ShouldQueue
{
    protected $filters;
    protected $columns;

    public function __construct(array $filters = [], array $columns = [])
    {
        $this->filters = $filters;
        $this->columns = $columns ?: ['id', 'order_number', 'customer_name', 'email', 'status', 'total_amount', 'items_count', 'created_at'];
    }

    public function query(): Builder
    {
        $query = Order::query()->with(['user', 'items']);

        // Apply filters
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['customer_id'])) {
            $query->where('user_id', $this->filters['customer_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['amount_min'])) {
            $query->where('total_amount', '>=', $this->filters['amount_min']);
        }

        if (!empty($this->filters['amount_max'])) {
            $query->where('total_amount', '<=', $this->filters['amount_max']);
        }

        return $query;
    }

    public function headings(): array
    {
        $headings = [];
        
        foreach ($this->columns as $column) {
            switch ($column) {
                case 'id':
                    $headings[] = 'Order ID';
                    break;
                case 'order_number':
                    $headings[] = 'Order Number';
                    break;
                case 'customer_name':
                    $headings[] = 'Customer Name';
                    break;
                case 'email':
                    $headings[] = 'Customer Email';
                    break;
                case 'status':
                    $headings[] = 'Status';
                    break;
                case 'total_amount':
                    $headings[] = 'Total Amount';
                    break;
                case 'items_count':
                    $headings[] = 'Items Count';
                    break;
                case 'shipping_address':
                    $headings[] = 'Shipping Address';
                    break;
                case 'payment_method':
                    $headings[] = 'Payment Method';
                    break;
                case 'created_at':
                    $headings[] = 'Order Date';
                    break;
                case 'updated_at':
                    $headings[] = 'Last Updated';
                    break;
                default:
                    $headings[] = ucfirst(str_replace('_', ' ', $column));
            }
        }

        return $headings;
    }

    public function map($order): array
    {
        $row = [];
        
        foreach ($this->columns as $column) {
            switch ($column) {
                case 'customer_name':
                    $row[] = $order->user ? $order->user->name : 'Guest';
                    break;
                case 'email':
                    $row[] = $order->user ? $order->user->email : 'N/A';
                    break;
                case 'status':
                    $row[] = ucfirst($order->status);
                    break;
                case 'total_amount':
                    $row[] = $order->total_amount;
                    break;
                case 'items_count':
                    $row[] = $order->items->count();
                    break;
                case 'shipping_address':
                    $address = $order->shipping_address;
                    $row[] = $address ? implode(', ', array_filter([
                        $address['address_line_1'] ?? '',
                        $address['address_line_2'] ?? '',
                        $address['city'] ?? '',
                        $address['state'] ?? '',
                        $address['postal_code'] ?? '',
                        $address['country'] ?? ''
                    ])) : '';
                    break;
                case 'payment_method':
                    $row[] = $order->payment_method ?? 'N/A';
                    break;
                case 'created_at':
                case 'updated_at':
                    $row[] = $order->$column ? $order->$column->format('Y-m-d H:i:s') : '';
                    break;
                default:
                    $row[] = $order->$column ?? '';
            }
        }

        return $row;
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total Amount
            'G' => NumberFormat::FORMAT_NUMBER, // Items Count
            'I' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Order Date
            'J' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Updated Date
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Set column widths
            'A' => ['width' => 10], // Order ID
            'B' => ['width' => 20], // Order Number
            'C' => ['width' => 25], // Customer Name
            'D' => ['width' => 30], // Email
            'E' => ['width' => 15], // Status
            'F' => ['width' => 15], // Total Amount
            'G' => ['width' => 12], // Items Count
            'H' => ['width' => 40], // Shipping Address
            'I' => ['width' => 20], // Order Date
            'J' => ['width' => 20], // Updated Date
        ];
    }
}
