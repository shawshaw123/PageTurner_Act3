<?php

namespace App\Imports;

use App\Models\User;
use App\Models\ImportLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
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

class UsersImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnFailure, ShouldQueue
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
        // Check for duplicates
        $existingUser = User::where('email', $row['Email'])->first();
        
        if ($existingUser) {
            if (!($this->options['update_existing'] ?? false)) {
                throw new \Exception("User with email {$row['Email']} already exists");
            }
            
            // Update existing user
            $updateData = [
                'name' => $row['Name'],
            ];

            if (!empty($row['Password'])) {
                $updateData['password'] = Hash::make($row['Password']);
            }

            if (!empty($row['Role'])) {
                $role = strtolower($row['Role']);
                if (in_array($role, ['admin', 'customer'])) {
                    $updateData['role'] = $role;
                }
            }

            $existingUser->update($updateData);
        } else {
            // Create new user
            $userData = [
                'name' => $row['Name'],
                'email' => $row['Email'],
                'password' => Hash::make($row['Password'] ?? 'password123'), // Default password
                'email_verified_at' => now(),
            ];

            // Set role if provided
            if (!empty($row['Role'])) {
                $role = strtolower($row['Role']);
                if (in_array($role, ['admin', 'customer'])) {
                    $userData['role'] = $role;
                }
            }

            User::create($userData);
        }
    }

    public function rules(): array
    {
        return [
            'Name' => 'required|string|max:255',
            'Email' => 'required|email|max:255',
            'Password' => 'nullable|string|min:8',
            'Role' => 'nullable|in:admin,customer',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'Name.required' => 'Name is required',
            'Email.required' => 'Email is required',
            'Email.email' => 'Email must be a valid email address',
            'Email.max' => 'Email must not exceed 255 characters',
            'Password.min' => 'Password must be at least 8 characters',
            'Role.in' => 'Role must be either admin or customer',
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
