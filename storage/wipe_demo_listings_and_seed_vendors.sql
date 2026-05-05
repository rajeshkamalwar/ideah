-- DEPRECATED for mixed real+demo DBs: this file deletes *all* listings.
-- Prefer: php artisan ideah:remove-demo-data   (only @example.com / @example.org vendors + their data)
-- Original full wipe (empty DB before import): backup first, then mysql ... < this file

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM listing_product_images;
DELETE FROM listing_product_contents;
DELETE FROM listing_products;
DELETE FROM listing_images;
DELETE FROM listing_socail_medias;
DELETE FROM listing_messages;
DELETE FROM listing_reviews;
DELETE FROM listing_faqs;
DELETE FROM listing_feature_contents;
DELETE FROM listing_features;
DELETE FROM business_hours;
DELETE FROM listing_contents;
DELETE FROM claim_listings;
DELETE FROM feature_orders;
DELETE FROM wishlists;
DELETE FROM visitors;
DELETE FROM products WHERE listing_id IS NOT NULL;
DELETE FROM listings;

-- Demo vendors from theme seed (emails like *@example.com)
DELETE pm FROM product_messages pm
  INNER JOIN vendors v ON v.id = pm.vendor_id
  WHERE v.email LIKE '%@example.com';
DELETE ppi FROM product_purchase_items ppi
  INNER JOIN product_orders po ON po.id = ppi.product_order_id
  INNER JOIN vendors v ON v.id = po.vendor_id
  WHERE v.email LIKE '%@example.com';
DELETE po FROM product_orders po
  INNER JOIN vendors v ON v.id = po.vendor_id
  WHERE v.email LIKE '%@example.com';
DELETE pc FROM product_coupons pc
  INNER JOIN vendors v ON v.id = pc.vendor_id
  WHERE v.email LIKE '%@example.com';
DELETE p FROM products p
  INNER JOIN vendors v ON v.id = p.vendor_id
  WHERE v.email LIKE '%@example.com';
DELETE w FROM withdraws w
  INNER JOIN vendors v ON v.id = w.vendor_id
  WHERE v.email LIKE '%@example.com';
DELETE f FROM forms f
  INNER JOIN vendors v ON v.id = f.vendor_id
  WHERE v.email LIKE '%@example.com';
DELETE m FROM memberships m
  INNER JOIN vendors v ON v.id = m.vendor_id
  WHERE v.email LIKE '%@example.com';
DELETE vi FROM vendor_infos vi
  INNER JOIN vendors v ON v.id = vi.vendor_id
  WHERE v.email LIKE '%@example.com';
DELETE FROM vendors WHERE email LIKE '%@example.com';

SET FOREIGN_KEY_CHECKS = 1;
