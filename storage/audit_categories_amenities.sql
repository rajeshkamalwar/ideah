-- Imported listings: category per language 20
SELECT lc.listing_id,
       lc.title,
       SUBSTRING_INDEX(lc.slug, '-', -1) AS slug_trailing_segment,
       lc.category_id,
       cat.name AS category_name,
       cat.slug AS category_slug
FROM ideah.listing_contents lc
JOIN ideah.listing_categories cat ON cat.id = lc.category_id AND cat.language_id = lc.language_id
WHERE lc.language_id = 20
ORDER BY lc.listing_id;

-- Legacy: business -> category
SELECT b.id AS legacy_biz_id,
       b.business_name,
       b.business_category_id,
       bc.category_name,
       bc.category_slug
FROM ideahub.businesses b
JOIN ideahub.business_categories bc ON bc.id = b.business_category_id
WHERE b.deleted_at IS NULL
ORDER BY b.id;

-- Amenities column on listing_contents
SELECT COUNT(*) AS lc_rows,
       SUM(aminities IS NULL OR TRIM(COALESCE(aminities,'')) = '' OR LOWER(TRIM(aminities)) = 'null') AS empty_aminities
FROM ideah.listing_contents;

SELECT id, listing_id, language_id, LEFT(COALESCE(aminities,'(null)'), 60) AS aminities_preview
FROM ideah.listing_contents
WHERE language_id = 20
LIMIT 8;

-- Master amenity definitions (not per listing)
SELECT COUNT(*) AS aminite_definitions FROM ideah.aminites;
SELECT id, language_id, title FROM ideah.aminites WHERE language_id = 20 LIMIT 15;
