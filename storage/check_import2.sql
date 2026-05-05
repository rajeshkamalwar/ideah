SELECT MIN(id) AS min_id, MAX(id) AS max_id, COUNT(*) AS c FROM ideah.listings;
SELECT lc.id, lc.title, lc.slug, lc.listing_id
FROM ideah.listing_contents lc
WHERE lc.language_id = 20
ORDER BY lc.listing_id DESC
LIMIT 15;
