SELECT
  COUNT(*) AS total,
  SUM(features IS NOT NULL AND TRIM(features) != '') AS has_features_json
FROM ideah.listing_contents
WHERE language_id = 20;

SELECT listing_id, LEFT(title, 40) AS title, LEFT(features, 120) AS feat_preview
FROM ideah.listing_contents
WHERE language_id = 20 AND features IS NOT NULL
LIMIT 5;
