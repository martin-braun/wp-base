UPDATE wp_users
SET
	user_login = substring(MD5(RAND()),1,20),
	user_nicename = substring(MD5(RAND()),1,20),
	user_email = CONCAT(substring_index(user_email, '@', 1), FLOOR(RAND() * 90000 + 10000), '@example.org')
WHERE ID > 8 AND ID != 146;

UPDATE wp_usermeta
SET	meta_value = ''
WHERE	( user_id > 8 AND user_id != 146 ) AND ( 
	LOWER(meta_key) LIKE '%name%' OR
	LOWER(meta_key) LIKE '%address%' OR
	LOWER(meta_key) LIKE '%city%' OR
	LOWER(meta_key) LIKE '%postcode%' OR
	LOWER(meta_key) LIKE '%zip%' OR
	LOWER(meta_key) LIKE '%street%'
);

UPDATE wp_postmeta
SET	meta_value = ''
WHERE	( 
	LOWER(meta_key) LIKE '%name%' OR
	LOWER(meta_key) LIKE '%address%' OR
	LOWER(meta_key) LIKE '%city%' OR
	LOWER(meta_key) LIKE '%postcode%' OR
	LOWER(meta_key) LIKE '%zip%' OR
	LOWER(meta_key) LIKE '%street%'
);

UPDATE wp_usermeta
SET	meta_value = CONCAT(substring_index(meta_value, '@', 1), FLOOR(RAND() * 90000 + 10000), '@example.org')
WHERE	( user_id > 8 AND user_id != 146 ) AND LOWER(meta_key) LIKE '%email%';

UPDATE wp_postmeta
SET	meta_value = CONCAT(substring_index(meta_value, '@', 1), FLOOR(RAND() * 90000 + 10000), '@example.org')
WHERE	LOWER(meta_key) LIKE '%email%';