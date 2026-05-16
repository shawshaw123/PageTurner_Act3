# PageTurner Online Bookstore Management System

## Laboratory Activity 7: Mass Data Seeding, Performance Optimization, and Scalability Engineering

**Course:** ITSD 82 - Web Software Tools (Fundamentals of Laravel)  
**Section:** BSIT 3C  
**Schedule:** Thursday 1:00 PM - 3:00 PM  
**Room:** CISC Room 3

---

## Hardware Specifications

| Component       | Specification                          |
|-----------------|----------------------------------------|
| OS              | Windows 10/11                          |
| Web Server      | XAMPP (Apache + MySQL/MariaDB)         |
| PHP Version     | 8.2+                                   |
| Database        | MySQL/MariaDB via XAMPP                |
| Framework       | Laravel 12.x                           |
| Cache Driver    | File (Redis-ready with graceful fallback) |
| Search Engine   | Laravel Scout (Database Driver)        |
| RAM             | 8 GB+                                  |
| Storage         | SSD                                    |

---

## Achieved Benchmark Times

### 7.1 Seeding Performance

| Metric                        | Target       | Achieved        | Status |
|-------------------------------|--------------|-----------------|--------|
| 1M records seeded             | < 10 min     | 1,000,000 records | PASS   |
| Memory usage                  | < 512 MB     | 32 MB           | PASS   |
| ISBNs valid (checksum)        | All valid    | All valid       | PASS   |
| Foreign keys valid            | All valid    | All valid       | PASS   |
| Realistic data distributions  | Varied       | 2000 unique authors, $9.99-$49.99 | PASS |

### 7.2 Query Performance (100 iterations each)

| Query Type       | Target    | Achieved  | Status |
|------------------|-----------|-----------|--------|
| ISBN Lookup      | < 50 ms   | 0.38 ms   | PASS   |
| Catalog Listing  | < 100 ms  | 1.03 ms   | PASS   |
| Category Filter  | < 150 ms  | 1.31 ms   | PASS   |
| Full-Text Search | < 300 ms  | 25.68 ms  | PASS   |
| N+1 Detection    | None      | 0 found   | PASS   |

### 7.3 Cache Validation

| Metric                     | Target    | Achieved        | Status |
|----------------------------|-----------|-----------------|--------|
| Cache retrieval            | < 10 ms   | 0.714 ms        | PASS   |
| Cache invalidation         | Working   | Working         | PASS   |
| Cache store configured     | Yes       | File (Redis-ready) | PASS |
| Cache tags support         | Yes       | Graceful fallback | PASS  |

### 7.4 Load Testing

| Metric                     | Target       | Achieved              | Status |
|----------------------------|--------------|-----------------------|--------|
| 50 concurrent requests     | No errors    | 50/50 (avg 386ms)     | PASS   |
| Rate limiting              | Configured   | Throttle middleware    | PASS   |
| Scout indexing             | Configured   | Database driver, sync  | PASS   |

### 7.5 Data Integrity

| Metric                     | Target       | Achieved              | Status |
|----------------------------|--------------|-----------------------|--------|
| 1M Eloquent query          | No timeout   | 1M books (325ms)      | PASS   |
| Export 50K records          | No OOM       | 50K in 3.4s (46MB)    | PASS   |
| Partition pruning           | Working      | 7 partitions          | PASS   |

**Overall Score: 30/30 tests passed (100%)**

---

## Project Architecture

### Source Code Repository (Deliverables 8.1)

| File | Description |
|------|-------------|
| `database/factories/BookFactory.php` | Optimized factory with valid ISBN-13 generation |
| `database/seeders/MassBookSeeder.php` | Chunked batch insert seeder for 1M records |
| `app/Repositories/BookRepository.php` | Optimized data access layer with cursor pagination |
| `app/Repositories/SimpleBookRepository.php` | File-cache compatible repository (no Redis required) |
| `app/Services/BookCacheService.php` | Redis caching abstraction with tag support and graceful fallback |
| `app/Observers/BookObserver.php` | Cache invalidation logic triggered by model events |
| `app/Console/Commands/BenchmarkBookQueries.php` | Automated performance benchmarking command |
| `app/Console/Commands/SimpleBenchmarkBookQueries.php` | Benchmark command for non-Redis environments |
| `app/Console/Commands/Lab7Validation.php` | Complete Lab 7 validation suite (30 tests) |
| `app/Jobs/WarmCategoryCache.php` | Async background cache warming job |
| `config/scout.php` | Laravel Scout search configuration |
| `database/migrations/*_optimize_books_*` | Index optimization migrations |
| `database/migrations/*_partition_books_*` | Table partitioning by year |
| `database/migrations/*_materialized_views*` | Materialized view tables for reporting |

### Configuration Files (Deliverables 8.2)

| File | Description |
|------|-------------|
| `.env.example` | Redis, read replica, and Scout environment variable templates |
| `config/database.php` | Read/write splitting and Redis connection configuration |
| `config/cache.php` | Redis tag-based cache store configuration |

---

## Performance Optimization Strategies

### Database Indexing
- **Composite Index**: `idx_books_catalog_filter` on (category_id, published_at, is_active)
- **Covering Index**: `idx_books_price_stock` on (price, stock_quantity, id)
- **Full-Text Index**: `idx_books_fulltext` on (title, description)
- **Active Filter**: `idx_books_active` on (is_active)
- **ISBN Lookup**: `idx_books_isbn_lookup` on (isbn)

### Table Partitioning
The books table is partitioned by publication year using `RANGE(YEAR(published_at))`:
- `p_old`: Before 2000
- `p2000`: 2000-2004
- `p2005`: 2005-2009
- `p2010`: 2010-2014
- `p2015`: 2015-2019
- `p2020`: 2020-2024
- `p_future`: 2025+

### N+1 Query Prevention
- Global scope on Book model: `withAvg('reviews', 'rating')` and `withCount('reviews')`
- Eager loading via `with('category')` on all queries
- Bulk `whereIn()` queries in CartController instead of per-item lookups

### Cache Architecture
- Graceful degradation: Supports Redis tags when available, falls back to file cache
- BookObserver triggers cache invalidation on create/update/delete
- WarmCategoryCache job pre-populates popular category data

---

## How to Run Tests

### Full Validation (30 tests)
```bash
php artisan lab7:validate
```

### Query Performance Benchmark
```bash
php artisan benchmark:simple-book-queries
```

### Additional Tests (N+1, Export, Partitions)
```bash
php lab7_tests.php
```

---

## Setup Instructions

1. Clone the repository
2. Copy `.env.example` to `.env` and configure database credentials
3. Run `composer install`
4. Run `php artisan key:generate`
5. Run `php artisan migrate`
6. Run `php artisan db:seed --class=MassBookSeeder` (seeds 1M records)
7. Run `php artisan lab7:validate` to verify all requirements
