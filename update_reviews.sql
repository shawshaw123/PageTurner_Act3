-- Update all existing reviews to have 'approved' status
UPDATE reviews SET status = 'approved' WHERE status IS NULL OR status = '';

-- Show the results
SELECT 
    COUNT(*) as total_reviews,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
FROM reviews;
