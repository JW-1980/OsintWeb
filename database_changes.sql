-- Database Optimization and Normalization Changes

-- 1. Optimize Database (2026_02_01_000001_optimize_database.php)

-- Drop redundant index on users.email (since it is already unique)
-- Note: Check if the index name is 'users_email_index' in your specific database.
-- Using DROP INDEX IF EXISTS to prevent errors if the index is already removed.
DROP INDEX IF EXISTS users_email_index ON users;

-- Drop redundant index on countries.name (since it is already unique)
-- Note: Check if the index name is 'countries_name_index' in your specific database.
DROP INDEX IF EXISTS countries_name_index ON countries;


-- 2. Normalize Conflicts (2026_02_01_000002_normalize_conflicts.php)

CREATE TABLE `conflict_countries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conflict_id` bigint(20) unsigned NOT NULL,
  `country_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conflict_countries_conflict_id_country_id_unique` (`conflict_id`,`country_id`),
  KEY `conflict_countries_country_id_foreign` (`country_id`),
  CONSTRAINT `conflict_countries_conflict_id_foreign` FOREIGN KEY (`conflict_id`) REFERENCES `conflicts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conflict_countries_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 3. Normalize Conflict Parties (2026_02_01_000004_normalize_conflict_parties.php)

CREATE TABLE `conflict_party_relations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conflict_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned NOT NULL,
  `related_actor_id` bigint(20) unsigned NOT NULL,
  `type` enum('support','oppose') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conflict_party_relations_unique` (`conflict_id`,`actor_id`,`related_actor_id`,`type`),
  KEY `conflict_party_relations_actor_id_foreign` (`actor_id`),
  KEY `conflict_party_relations_related_actor_id_foreign` (`related_actor_id`),
  CONSTRAINT `conflict_party_relations_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conflict_party_relations_conflict_id_foreign` FOREIGN KEY (`conflict_id`) REFERENCES `conflicts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conflict_party_relations_related_actor_id_foreign` FOREIGN KEY (`related_actor_id`) REFERENCES `actors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
