# 🧪 Laboratory Activity 6 - Complete Testing Tutorial

## 🎓 How to Test All Lab 6 Components

This tutorial will guide you through testing every feature of Laboratory Activity 6. Follow each step carefully!

---

## 📋 **Pre-Testing Checklist**

Before you start, make sure:
- ✅ Your Laravel application is running (`php artisan serve`)
- ✅ Database migrations are up to date
- ✅ Queue worker is running (`php artisan queue:work`)
- ✅ Mail settings are configured (for testing notifications)

---

## 🚀 **Step 1: Quick System Verification**

First, let's verify all components are installed:

```bash
# Run this command in your terminal
php artisan tinker --execute "
echo '🔍 Lab 6 System Check:' . PHP_EOL;
echo '✅ ImportLogs: ' . (Schema::hasTable('import_logs') ? 'OK' : 'MISSING') . PHP_EOL;
echo '✅ ExportLogs: ' . (Schema::hasTable('export_logs') ? 'OK' : 'MISSING') . PHP_EOL;
echo '✅ Audits: ' . (Schema::hasTable('audits') ? 'OK' : 'MISSING') . PHP_EOL;
echo '✅ BackupMonitoring: ' . (Schema::hasTable('backup_monitoring') ? 'OK' : 'MISSING') . PHP_EOL;
echo '✅ ApiRateLimits: ' . (Schema::hasTable('api_rate_limits') ? 'OK' : 'MISSING') . PHP_EOL;
echo PHP_EOL . '📋 Ready for testing!';
"
```

If all tables show "OK", you're ready to test!

---

## 📦 **Step 2: Test Import/Export Systems**

### **2.1 Test Book Import**

**🎯 Goal**: Verify chunked processing, validation, and queuing

**Steps:**
1. **Navigate to Import Page**
   ```
   http://127.0.0.1:8000/admin/import-export/books/import
   ```

2. **Download Template**
   - Click "Download Excel Template"
   - Open the template file

3. **Create Test Data**
   Create an Excel file with this data:

   | ISBN | Title | Author | Price | Stock | Category | Description |
   |------|-------|---------|-------|-------|----------|-------------|
   | 978-3-16-148410-0 | Test Book 1 | John Doe | 29.99 | 50 | Fiction | A great test book |
   | 978-0-13-468599-1 | Test Book 2 | Jane Smith | 19.99 | 30 | Fiction | Another test book |
   | INVALID-ISBN | Bad Book | Bad Author | -10 | -5 | InvalidCategory | This should fail |

4. **Upload the File**
   - Select your Excel file
   - Keep "Update existing books" unchecked
   - Click "Start Import"

5. **Expected Results**
   - ✅ You should see "Import has been queued for processing"
   - ✅ Check the import progress on the dashboard
   - ✅ 2 books should import successfully
   - ✅ 1 book should fail with validation error

6. **Check Import Details**
   - Go back to the import page
   - Click on the import log to see details
   - Verify error messages for failed rows

### **2.2 Test Large File Import (Chunked Processing)**

**🎯 Goal**: Test chunked processing with large datasets

**Steps:**
1. **Create Large Test File**
   - Create an Excel file with 2000+ book records
   - Use valid ISBNs and data

2. **Upload Large File**
   - Upload the large file
   - Monitor the import progress

3. **Expected Results**
   - ✅ Import processes without timeout
   - ✅ Memory usage remains stable
   - ✅ Progress updates in real-time
   - ✅ All records processed in chunks

### **2.3 Test Book Export**

**🎯 Goal**: Verify filtering and multiple formats

**Steps:**
1. **Navigate to Export Page**
   ```
   http://127.0.0.1:8000/admin/import-export/books/export
   ```

2. **Test with Filters**
   - Format: Select "Excel"
   - Category: Select "Fiction"
   - Price Min: 10
   - Price Max: 100
   - Stock Status: "In Stock"
   - Columns: Select only "Title", "Author", "Price"

3. **Generate Export**
   - Click "Export Books"
   - Wait for processing
   - Download the file

4. **Expected Results**
   - ✅ Export contains only filtered books
   - ✅ Only selected columns included
   - ✅ File downloads successfully
   - ✅ Export log shows completion

---

## 💾 **Step 3: Test Automated Backup System**

### **3.1 Test Manual Backup**

**🎯 Goal**: Verify backup creation and monitoring

**Steps:**
1. **Run Manual Backup**
   ```bash
   php artisan backup:run-custom --type=daily
   ```

2. **Check Backup Results**
   - Look for success message
   - Check storage/backup directory for backup files

3. **Verify Database Logging**
   ```bash
   php artisan tinker --execute "
   \$backup = BackupMonitoring::latest()->first();
   echo 'Last Backup Status: ' . \$backup->status . PHP_EOL;
   echo 'Backup Size: ' . \$backup->backup_size . PHP_EOL;
   echo 'Started At: ' . \$backup->started_at . PHP_EOL;
   "
   ```

4. **Expected Results**
   - ✅ Backup completes successfully
   - ✅ Backup file created in storage
   - ✅ Database entry shows success
   - ✅ Email notification sent (check mail config)

### **3.2 Test Backup Failure**

**🎯 Goal**: Verify error handling and notifications

**Steps:**
1. **Simulate Backup Failure**
   - Temporarily rename storage directory to cause error
   - Run backup command
   - Restore directory name

2. **Check Error Handling**
   ```bash
   php artisan tinker --execute "
   \$failed = BackupMonitoring::where('status', 'failed')->latest()->first();
   if(\$failed) {
       echo 'Last Failed Backup: ' . \$failed->error_message . PHP_EOL;
   }
   "
   ```

3. **Expected Results**
   - ✅ Failure logged in database
   - ✅ Error notification sent
   - ✅ System handles errors gracefully

---

## 📋 **Step 4: Test Audit Logging**

### **4.1 Test Change Tracking**

**🎯 Goal**: Verify all changes are logged

**Steps:**
1. **Create a Book**
   - Go to `/admin/books/create`
   - Fill in book details
   - Save the book

2. **Update the Book**
   - Go to book edit page
   - Change title and price
   - Save changes

3. **Delete the Book**
   - Delete the book you created

4. **Check Audit Logs**
   ```
   http://127.0.0.1:8000/admin/audit
   ```

5. **Expected Results**
   - ✅ Create event logged with new values
   - ✅ Update event logged with old/new values
   - ✅ Delete event logged with deleted data
   - ✅ User ID and timestamp recorded

### **4.2 Test Audit Search**

**🎯 Goal**: Verify audit log search functionality

**Steps:**
1. **Search by User**
   - Filter by your admin user
   - Verify only your actions show

2. **Search by Date Range**
   - Filter by today's date
   - Verify recent actions only

3. **Export Audit Logs**
   - Click export button
   - Download CSV file
   - Verify data completeness

4. **Expected Results**
   - ✅ Search filters work correctly
   - ✅ Export includes all data
   - ✅ Performance with large datasets

---

## 🚦 **Step 5: Test API Rate Limiting**

### **5.1 Test Tiered Rate Limits**

**🎯 Goal**: Verify different limits for different user types

**Steps:**
1. **Test Guest User (30 req/min)**
   ```bash
   # Run this rapid command 35 times
   for i in {1..35}; do
       curl -s "http://127.0.0.1:8000/api/books" -H "Accept: application/json" | head -c 100
       echo " Request $i"
   done
   ```

2. **Test Logged-in User (60 req/min)**
   - Log in as regular user
   - Repeat the curl test
   - Should get 60 requests before rate limit

3. **Test Admin User (1000 req/min)**
   - Log in as admin
   - Repeat the test
   - Should get much higher limit

4. **Expected Results**
   - ✅ Different limits for each user type
   - ✅ 429 status code when limit exceeded
   - ✅ Retry-After header present
   - ✅ Rate limit headers in responses

### **5.2 Test Per-Second Protection**

**🎯 Goal**: Verify burst protection

**Steps:**
1. **Rapid Requests Test**
   ```bash
   # Make 5 requests in 1 second
   for i in {1..5}; do
       curl -s "http://127.0.0.1:8000/api/books" &
   done
   wait
   ```

2. **Expected Results**
   - ✅ Burst protection activates
   - ✅ Requests blocked after per-second limit
   - ✅ Smooth recovery after delay

---

## 🔄 **Step 6: Test Data Transformation**

### **6.1 Test Request Transformation**

**🎯 Goal**: Verify camelCase to snake_case conversion

**Steps:**
1. **Create API Request**
   ```bash
   curl -X POST "http://127.0.0.1:8000/api/books" \
        -H "Content-Type: application/json" \
        -H "Authorization: Bearer YOUR_TOKEN" \
        -d '{
            "title": "Test Book",
            "authorName": "Test Author",
            "priceValue": 29.99
        }'
   ```

2. **Check Database**
   ```bash
   php artisan tinker --execute "
   \$book = App\Models\Book::latest()->first();
   echo 'Title: ' . \$book->title . PHP_EOL;
   echo 'Author: ' . \$book->author . PHP_EOL;
   echo 'Price: ' . \$book->price . PHP_EOL;
   "
   ```

3. **Expected Results**
   - ✅ camelCase converted to snake_case
   - ✅ Data stored correctly in database

### **6.2 Test Response Transformation**

**🎯 Goal**: Verify snake_case to camelCase in responses

**Steps:**
1. **Make API Request**
   ```bash
   curl "http://127.0.0.1:8000/api/books" -H "Accept: application/json"
   ```

2. **Check Response Format**
   - Response should use camelCase
   - Fields like `createdAt`, `updatedAt` instead of `created_at`

3. **Expected Results**
   - ✅ Response in camelCase format
   - ✅ Consistent API response structure

### **6.3 Test Field Filtering**

**🎯 Goal**: Verify selective field return

**Steps:**
1. **Request Specific Fields**
   ```bash
   curl "http://127.0.0.1:8000/api/books?fields=id,title,price" \
        -H "Accept: application/json"
   ```

2. **Expected Results**
   - ✅ Only requested fields returned
   - ✅ Reduced response size
   - ✅ Better performance

---

## ⏰ **Step 7: Test Scheduled Tasks**

### **7.1 Test Scheduler Configuration**

**🎯 Goal**: Verify all tasks are scheduled

**Steps:**
1. **Check Scheduled Tasks**
   ```bash
   php artisan schedule:list
   ```

2. **Expected Results**
   - ✅ Daily backup at 02:00
   - ✅ Weekly backup on Sunday
   - ✅ Order cleanup hourly
   - ✅ Session cleanup daily
   - ✅ Report generation daily
   - ✅ Health checks every 6 hours

### **7.2 Test Individual Commands**

**🎯 Goal**: Verify each maintenance command works

**Steps:**
1. **Test Order Cleanup**
   ```bash
   php artisan order:cleanup-pending
   ```

2. **Test Session Cleanup**
   ```bash
   php artisan session:clear-expired
   ```

3. **Test Report Generation**
   ```bash
   php artisan report:generate-daily
   ```

4. **Test Health Check**
   ```bash
   php artisan system:health-check
   ```

5. **Expected Results**
   - ✅ All commands execute without errors
   - ✅ Appropriate database changes made
   - ✅ Logs generated for each operation

---

## 📊 **Step 8: Test Dashboard Integration**

### **8.1 Test Admin Dashboard**

**🎯 Goal**: Verify all widgets work correctly

**Steps:**
1. **Navigate to Dashboard**
   ```
   http://127.0.0.1:8000/admin/dashboard
   ```

2. **Check Data Management Widget**
   - Recent imports/exports show
   - Queue status displayed
   - Quick action buttons work

3. **Check Backup Status Widget**
   - Last backup time shown
   - Success rate displayed
   - Health indicators working

4. **Test Quick Actions**
   - Click "Import Books" - should go to import page
   - Click "Export Data" - should go to export page

5. **Expected Results**
   - ✅ All widgets display correct data
   - ✅ Quick actions navigate correctly
   - ✅ Real-time updates work

---

## 🧪 **Step 9: Performance Testing**

### **9.1 Test Large Dataset Performance**

**🎯 Goal**: Verify system handles large datasets

**Steps:**
1. **Import Performance Test**
   - Import 5000 records
   - Monitor memory usage
   - Check completion time

2. **Export Performance Test**
   - Export all books
   - Monitor response time
   - Check file size

3. **Expected Results**
   - ✅ Import completes within 2 minutes
   - ✅ Export completes within 30 seconds
   - ✅ Memory usage remains stable

---

## 📝 **Step 10: Final Verification**

### **10.1 Complete System Check**

**🎯 Goal**: Verify all learning objectives met

**Run this final check:**
```bash
php artisan tinker --execute "
echo '🎯 FINAL VERIFICATION RESULTS:' . PHP_EOL;
echo '================================' . PHP_EOL;

// Check all tables
\$tables = ['import_logs', 'export_logs', 'audits', 'backup_monitoring', 'api_rate_limits'];
foreach(\$tables as \$table) {
    echo '✅ ' . \$table . ': ' . (Schema::hasTable(\$table) ? 'OK' : 'MISSING') . PHP_EOL;
}

// Check all classes
\$classes = [
    'BooksImport' => 'App\Imports\BooksImport',
    'BooksExport' => 'App\Exports\BooksExport',
    'RunBackup' => 'App\Console\Commands\RunBackup',
    'ApiRateLimitMiddleware' => 'App\Http\Middleware\ApiRateLimitMiddleware',
    'ApiDataTransformMiddleware' => 'App\Http\Middleware\ApiDataTransformMiddleware',
];

foreach(\$classes as \$name => \$class) {
    echo '✅ ' . \$name . ': ' . (class_exists(\$class) ? 'OK' : 'MISSING') . PHP_EOL;
}

echo PHP_EOL . '🎉 LAB 6 IMPLEMENTATION COMPLETE!' . PHP_EOL;
"
```

### **10.2 Learning Objectives Checklist**

Mark each as ✅ when verified:

- [ ] **1. Import/Export Systems**: Chunked processing, validation, queuing ✅
- [ ] **2. Automated Backup**: Scheduling, monitoring, notifications ✅  
- [ ] **3. Audit Logging**: Change tracking, compliance, search ✅
- [ ] **4. API Rate Limiting**: Tiered limits, per-second protection ✅
- [ ] **5. Database Splitting**: Read/write separation, optimization ✅
- [ ] **6. Data Transformation**: Middleware, field filtering, metadata ✅
- [ ] **7. Scheduled Tasks**: Cleanup, reports, health checks ✅

---

## 🎓 **Success!**

If all tests pass, you have successfully implemented Laboratory Activity 6! Your PageTurner Bookstore now has:

✅ **Enterprise-grade import/export** with validation and chunking  
✅ **Automated backup system** with monitoring and notifications  
✅ **Comprehensive audit logging** for compliance  
✅ **Advanced API rate limiting** with tiered access  
✅ **Data transformation middleware** for consistent APIs  
✅ **Automated maintenance tasks** for system health  
✅ **Beautiful admin dashboard** with real-time monitoring  

**🚀 Your system is ready for production use!**

---

## 🆘 **Troubleshooting**

If any test fails:

1. **Check logs**: `php artisan log:clear` then retry
2. **Run migrations**: `php artisan migrate:fresh --seed`
3. **Clear cache**: `php artisan cache:clear && php artisan view:clear`
4. **Check queue**: `php artisan queue:work`
5. **Verify permissions**: Check storage directory permissions

**Need help?** Check the detailed error messages and logs for specific issues!
