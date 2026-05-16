<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            // Check if MySQL/MariaDB version supports partitioning
            $version = DB::select('SELECT VERSION() as version')[0]->version;
            $isMariaDB = str_contains($version, 'MariaDB');
            
            // Partitioning is supported in MariaDB 10.0+ and MySQL 5.1+
            // But we'll stick to a safe 8.0/10.0 check or similar
            
            // Check if table is already partitioned
            $partitionInfo = DB::select("
                SELECT PARTITION_NAME 
                FROM INFORMATION_SCHEMA.PARTITIONS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'books' 
                AND PARTITION_NAME IS NOT NULL
                LIMIT 1
            ");

            if (!empty($partitionInfo)) {
                return;
            }

            // Add partitioning by publication year
            // Note: In some MariaDB versions, primary keys must include the partitioning column
            DB::statement("
                ALTER TABLE books 
                PARTITION BY RANGE (YEAR(published_at)) (
                    PARTITION p_old VALUES LESS THAN (2000),
                    PARTITION p2000 VALUES LESS THAN (2005),
                    PARTITION p2005 VALUES LESS THAN (2010),
                    PARTITION p2010 VALUES LESS THAN (2015),
                    PARTITION p2015 VALUES LESS THAN (2020),
                    PARTITION p2020 VALUES LESS THAN (2025),
                    PARTITION p_future VALUES LESS THAN MAXVALUE
                )
            ");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Partitioning failed: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        try {
            // Check if table is partitioned
            $partitionInfo = DB::select("
                SELECT PARTITION_NAME 
                FROM INFORMATION_SCHEMA.PARTITIONS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'books' 
                AND PARTITION_NAME IS NOT NULL
                LIMIT 1
            ");

            if (empty($partitionInfo)) {
                return;
            }

            // Remove partitioning
            DB::statement('ALTER TABLE books REMOVE PARTITIONING');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to remove partitioning: ' . $e->getMessage());
        }
    }
};
