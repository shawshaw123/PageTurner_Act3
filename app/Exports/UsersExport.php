<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UsersExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStyles, ShouldQueue
{
    protected $filters;
    protected $columns;
    protected $redactPII;

    public function __construct(array $filters = [], array $columns = [], bool $redactPII = false)
    {
        $this->filters = $filters;
        $this->columns = $columns ?: ['id', 'name', 'email', 'role', 'email_verified_at', 'created_at'];
        $this->redactPII = $redactPII;
    }

    public function query(): Builder
    {
        $query = User::query();

        // Apply filters
        if (!empty($this->filters['role'])) {
            $query->where('role', $this->filters['role']);
        }

        if (!empty($this->filters['email_verified'])) {
            if ($this->filters['email_verified'] === 'verified') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
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
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
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
                    $headings[] = 'User ID';
                    break;
                case 'name':
                    $headings[] = 'Name';
                    break;
                case 'email':
                    $headings[] = 'Email';
                    break;
                case 'role':
                    $headings[] = 'Role';
                    break;
                case 'email_verified_at':
                    $headings[] = 'Email Verified At';
                    break;
                case 'created_at':
                    $headings[] = 'Created At';
                    break;
                case 'updated_at':
                    $headings[] = 'Updated At';
                    break;
                case 'last_login_at':
                    $headings[] = 'Last Login At';
                    break;
                default:
                    $headings[] = ucfirst(str_replace('_', ' ', $column));
            }
        }

        return $headings;
    }

    public function map($user): array
    {
        $row = [];
        
        foreach ($this->columns as $column) {
            switch ($column) {
                case 'email':
                    if ($this->redactPII) {
                        $row[] = $this->redactEmail($user->email);
                    } else {
                        $row[] = $user->email;
                    }
                    break;
                case 'name':
                    if ($this->redactPII) {
                        $row[] = $this->redactName($user->name);
                    } else {
                        $row[] = $user->name;
                    }
                    break;
                case 'email_verified_at':
                case 'created_at':
                case 'updated_at':
                case 'last_login_at':
                    $row[] = $user->$column ? $user->$column->format('Y-m-d H:i:s') : '';
                    break;
                default:
                    $row[] = $user->$column ?? '';
            }
        }

        return $row;
    }

    protected function redactEmail($email)
    {
        if (!$email) return '';
        
        $parts = explode('@', $email);
        $username = $parts[0];
        $domain = $parts[1] ?? '';
        
        // Show first 2 and last 2 characters of username
        if (strlen($username) <= 4) {
            $redactedUsername = str_repeat('*', strlen($username));
        } else {
            $redactedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 4) . substr($username, -2);
        }
        
        return $redactedUsername . '@' . $domain;
    }

    protected function redactName($name)
    {
        if (!$name) return '';
        
        $words = explode(' ', $name);
        $redactedWords = [];
        
        foreach ($words as $word) {
            if (strlen($word) <= 2) {
                $redactedWords[] = str_repeat('*', strlen($word));
            } else {
                $redactedWords[] = substr($word, 0, 1) . str_repeat('*', strlen($word) - 1);
            }
        }
        
        return implode(' ', $redactedWords);
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Created At
            'G' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Updated At
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Set column widths
            'A' => ['width' => 10], // User ID
            'B' => ['width' => 30], // Name
            'C' => ['width' => 35], // Email
            'D' => ['width' => 15], // Role
            'E' => ['width' => 20], // Email Verified At
            'F' => ['width' => 20], // Created At
            'G' => ['width' => 20], // Updated At
        ];
    }
}
