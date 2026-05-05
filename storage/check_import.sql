SELECT 'ideahub.businesses (active)' AS what, COUNT(*) AS n FROM ideahub.businesses WHERE deleted_at IS NULL;
SELECT 'ideah.listings' AS what, COUNT(*) AS n FROM ideah.listings;
SELECT 'ideah.vendors' AS what, COUNT(*) AS n FROM ideah.vendors;
SELECT 'ideah.listing_contents' AS what, COUNT(*) AS n FROM ideah.listing_contents;
SELECT 'ideah.listing_categories' AS what, COUNT(*) AS n FROM ideah.listing_categories;
SELECT 'ideah.listing_products' AS what, COUNT(*) AS n FROM ideah.listing_products;
