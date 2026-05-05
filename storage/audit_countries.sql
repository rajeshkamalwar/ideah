SELECT id, language_id, name FROM ideah.countries WHERE language_id IN (20,21) AND (name LIKE '%Netherland%' OR name LIKE '%Amsterdam%' OR name LIKE '%India%') LIMIT 20;
SELECT id, country_id, language_id, name FROM ideah.states WHERE language_id=20 LIMIT 15;
SELECT id, state_id, country_id, language_id, name FROM ideah.cities WHERE language_id=20 AND (name LIKE '%Amsterdam%' OR name LIKE '%Utrecht%') LIMIT 15;
