-- Imported listings: location IDs vs address text (English rows)
SELECT lc.listing_id,
       lc.title,
       lc.country_id,
       lc.state_id,
       lc.city_id,
       CHAR_LENGTH(lc.address) AS addr_len,
       lc.slug
FROM ideah.listing_contents lc
WHERE lc.language_id = 20
ORDER BY lc.listing_id;

-- Legacy source strings (sample)
SELECT id, business_name, country, state, city, business_slug
FROM ideahub.businesses
WHERE deleted_at IS NULL
ORDER BY id
LIMIT 15;
