-- ideah: listing_contents location vs address
SELECT
  COUNT(*) AS total_lc,
  SUM(country_id IS NULL AND state_id IS NULL AND city_id IS NULL) AS all_loc_null,
  SUM(address IS NOT NULL AND TRIM(address) != '') AS has_address
FROM ideah.listing_contents
WHERE language_id = 20;

-- Sample
SELECT lc.listing_id, lc.title, lc.country_id, lc.state_id, lc.city_id, LEFT(lc.address, 60) AS addr
FROM ideah.listing_contents lc
WHERE lc.language_id = 20
LIMIT 10;

-- countries NL / Netherlands for lang 20
SELECT id, language_id, name FROM ideah.countries
WHERE language_id = 20 AND (name LIKE '%Netherland%' OR name LIKE '%Belgium%' OR name LIKE '%Nederland%')
ORDER BY id;

-- total countries lang 20
SELECT COUNT(*) AS n FROM ideah.countries WHERE language_id = 20;

-- legacy businesses location columns (all active)
SELECT id, business_name, country, state, city
FROM ideahub.businesses
WHERE deleted_at IS NULL
ORDER BY id;
