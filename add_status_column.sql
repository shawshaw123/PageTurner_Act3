-- Add status column to reviews table
ALTER TABLE reviews ADD COLUMN status VARCHAR(20) DEFAULT 'approved' AFTER comment;

-- Add index for better performance
ALTER TABLE reviews ADD INDEX status (status);

-- Update existing reviews to have 'approved' status
UPDATE reviews SET status = 'approved' WHERE status IS NULL OR status = '';

-- Show the result
SELECT COUNT(*) as total_reviews,
       SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
       SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
FROM reviews;
