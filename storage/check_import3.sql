SELECT id, business_name, business_slug FROM ideahub.businesses WHERE deleted_at IS NULL ORDER BY id LIMIT 10;
SELECT lc.listing_id, lc.title FROM ideah.listing_contents lc
WHERE lc.language_id=20 AND (lc.title LIKE '%Royal%' OR lc.title LIKE '%Thai%');
