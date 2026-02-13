-- ============================================================================
-- GADIUM - Schema for MariaDB / MySQL
-- Converted from SQLite schema
-- ============================================================================
-- Usage: mysql -u root -p gadium < mariadb-schema.sql
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================================================
-- FRAMEWORK TABLES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `migrations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key` VARCHAR(255) NOT NULL,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL,
    `cancelled_at` INT NULL DEFAULT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SPATIE PERMISSION TABLES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `guard_name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `guard_name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_permissions` (
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `model_type` VARCHAR(255) NOT NULL,
    `model_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
    KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
    CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_roles` (
    `role_id` BIGINT UNSIGNED NOT NULL,
    `model_type` VARCHAR(255) NOT NULL,
    `model_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `model_id`, `model_type`),
    KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
    CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_has_permissions` (
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `role_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`permission_id`, `role_id`),
    CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SANCTUM
-- ============================================================================

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tokenable_type` VARCHAR(255) NOT NULL,
    `tokenable_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `abilities` TEXT NULL,
    `last_used_at` TIMESTAMP NULL DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`),
    UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
    KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: CLIENTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `clients` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `nombre_normalizado` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `clients_nombre_normalizado_index` (`nombre_normalizado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `client_aliases` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `client_id` BIGINT UNSIGNED NOT NULL,
    `alias` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `client_aliases_alias_unique` (`alias`),
    CONSTRAINT `client_aliases_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: PROJECTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `projects` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `client_id` BIGINT UNSIGNED NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'activo',
    `quality_status` VARCHAR(50) NOT NULL DEFAULT 'normal',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: SUPPLIERS & COST CENTERS
-- (Must be created before budgets and purchases that reference them)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `suppliers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `tax_id` VARCHAR(50) NULL DEFAULT NULL,
    `address` VARCHAR(255) NULL DEFAULT NULL,
    `email` VARCHAR(255) NULL DEFAULT NULL,
    `phone` VARCHAR(50) NULL DEFAULT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `name_normalized` VARCHAR(255) NULL DEFAULT NULL,
    KEY `suppliers_name_normalized_index` (`name_normalized`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cost_centers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `cost_centers_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supplier_aliases` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `supplier_id` BIGINT UNSIGNED NOT NULL,
    `alias` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `supplier_aliases_alias_unique` (`alias`),
    CONSTRAINT `supplier_aliases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: HOUR DETAIL CATALOGS
-- (Must be created before hour_details that references them)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `job_functions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `job_functions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guardias` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `guardias_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_aliases` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `alias` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `user_aliases_alias_index` (`alias`),
    UNIQUE KEY `user_aliases_alias_unique` (`alias`),
    CONSTRAINT `user_aliases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: SALES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sales` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `fecha` DATE NOT NULL,
    `client_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `cliente_nombre` VARCHAR(255) NOT NULL,
    `monto` DECIMAL(16,2) NOT NULL,
    `comprobante` VARCHAR(255) NOT NULL,
    `hash` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `moneda` VARCHAR(10) NOT NULL DEFAULT 'ARS',
    `cod_cli` VARCHAR(50) NULL DEFAULT NULL,
    `n_remito` VARCHAR(50) NULL DEFAULT NULL,
    `t_comp` VARCHAR(50) NULL DEFAULT NULL,
    `cond_vta` VARCHAR(50) NULL DEFAULT NULL,
    `porc_desc` DECIMAL(10,4) NULL DEFAULT NULL,
    `cotiz` DECIMAL(16,4) NULL DEFAULT NULL,
    `cod_transp` VARCHAR(50) NULL DEFAULT NULL,
    `nom_transp` VARCHAR(255) NULL DEFAULT NULL,
    `cod_articu` VARCHAR(50) NULL DEFAULT NULL,
    `descripcio` TEXT NULL,
    `cod_dep` VARCHAR(50) NULL DEFAULT NULL,
    `um` VARCHAR(20) NULL DEFAULT NULL,
    `cantidad` DECIMAL(16,4) NULL DEFAULT NULL,
    `precio` DECIMAL(16,4) NULL DEFAULT NULL,
    `tot_s_imp` DECIMAL(16,2) NULL DEFAULT NULL,
    `n_comp_rem` VARCHAR(50) NULL DEFAULT NULL,
    `cant_rem` DECIMAL(16,4) NULL DEFAULT NULL,
    `fecha_rem` DATE NULL DEFAULT NULL,
    UNIQUE KEY `sales_hash_unique` (`hash`),
    CONSTRAINT `sales_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: BUDGETS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `budgets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `fecha` DATE NOT NULL,
    `client_id` BIGINT UNSIGNED NOT NULL,
    `monto` DECIMAL(16,2) NOT NULL,
    `moneda` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `hash` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `cliente_nombre` VARCHAR(255) NULL DEFAULT NULL,
    `comprobante` VARCHAR(255) NULL DEFAULT NULL,
    `centro_costo` VARCHAR(50) NULL DEFAULT NULL,
    `nombre_proyecto` VARCHAR(255) NULL DEFAULT NULL,
    `fecha_oc` DATE NULL DEFAULT NULL,
    `fecha_estimada_culminacion` DATE NULL DEFAULT NULL,
    `estado_proyecto_dias` INT NULL DEFAULT NULL,
    `fecha_culminacion_real` DATE NULL DEFAULT NULL,
    `estado` VARCHAR(50) NULL DEFAULT NULL,
    `enviado_facturar` VARCHAR(50) NULL DEFAULT NULL,
    `nro_factura` VARCHAR(50) NULL DEFAULT NULL,
    `porc_facturacion` VARCHAR(20) NULL DEFAULT NULL,
    `saldo` DECIMAL(16,2) NULL DEFAULT NULL,
    `horas_ponderadas` DECIMAL(10,2) NULL DEFAULT NULL,
    `project_id` VARCHAR(255) NULL DEFAULT NULL,
    `cost_center_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    UNIQUE KEY `budgets_hash_unique` (`hash`),
    KEY `budgets_fecha_index` (`fecha`),
    KEY `budgets_project_id_index` (`project_id`),
    KEY `budgets_cost_center_id_index` (`cost_center_id`),
    CONSTRAINT `budgets_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
    CONSTRAINT `budgets_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
    CONSTRAINT `budgets_cost_center_id_foreign` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: HOUR DETAILS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `hour_details` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `dia` VARCHAR(20) NOT NULL,
    `fecha` DATE NOT NULL,
    `ano` INT NOT NULL,
    `mes` INT NOT NULL,
    `personal` VARCHAR(255) NOT NULL,
    `funcion` VARCHAR(255) NOT NULL,
    `proyecto` VARCHAR(255) NOT NULL,
    `horas_ponderadas` DECIMAL(10,4) NOT NULL,
    `ponderador` DECIMAL(10,4) NOT NULL,
    `hs` DECIMAL(10,2) NOT NULL,
    `hs_comun` DECIMAL(10,2) NOT NULL,
    `hs_50` DECIMAL(10,2) NOT NULL,
    `hs_100` DECIMAL(10,2) NOT NULL,
    `hs_viaje` DECIMAL(10,2) NOT NULL,
    `hs_pernoctada` VARCHAR(10) NOT NULL DEFAULT 'No',
    `hs_adeudadas` DECIMAL(10,2) NOT NULL,
    `vianda` VARCHAR(10) NOT NULL DEFAULT '0',
    `observacion` TEXT NULL,
    `programacion` VARCHAR(255) NULL DEFAULT NULL,
    `hash` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `job_function_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `guardia_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `project_id` VARCHAR(255) NULL DEFAULT NULL,
    UNIQUE KEY `hour_details_hash_unique` (`hash`),
    KEY `hour_details_ano_index` (`ano`),
    KEY `hour_details_fecha_index` (`fecha`),
    KEY `hour_details_mes_index` (`mes`),
    KEY `hour_details_personal_index` (`personal`),
    KEY `hour_details_proyecto_index` (`proyecto`),
    KEY `hour_details_user_id_index` (`user_id`),
    KEY `hour_details_job_function_id_index` (`job_function_id`),
    KEY `hour_details_project_id_index` (`project_id`),
    CONSTRAINT `hour_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `hour_details_job_function_id_foreign` FOREIGN KEY (`job_function_id`) REFERENCES `job_functions` (`id`) ON DELETE SET NULL,
    CONSTRAINT `hour_details_guardia_id_foreign` FOREIGN KEY (`guardia_id`) REFERENCES `guardias` (`id`) ON DELETE SET NULL,
    CONSTRAINT `hour_details_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: PURCHASES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `purchase_details` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `moneda` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `cc` VARCHAR(50) NOT NULL,
    `ano` INT NOT NULL,
    `empresa` VARCHAR(255) NOT NULL,
    `descripcion` VARCHAR(255) NOT NULL,
    `materiales_presupuestados` DECIMAL(16,2) NOT NULL DEFAULT 0,
    `materiales_comprados` DECIMAL(16,2) NOT NULL DEFAULT 0,
    `resto_valor` DECIMAL(16,2) NOT NULL DEFAULT 0,
    `resto_porcentaje` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `porcentaje_facturacion` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `hash` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `supplier_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `cost_center_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    UNIQUE KEY `purchase_details_hash_unique` (`hash`),
    KEY `purchase_details_ano_cc_index` (`ano`, `cc`),
    KEY `purchase_details_empresa_index` (`empresa`),
    KEY `purchase_details_supplier_id_index` (`supplier_id`),
    KEY `purchase_details_cost_center_id_index` (`cost_center_id`),
    CONSTRAINT `purchase_details_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `purchase_details_cost_center_id_foreign` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: BOARDS (TABLEROS)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `board_details` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ano` INT NOT NULL,
    `proyecto_numero` VARCHAR(255) NOT NULL,
    `cliente` VARCHAR(255) NOT NULL,
    `descripcion_proyecto` TEXT NOT NULL,
    `columnas` INT NOT NULL DEFAULT 0,
    `gabinetes` INT NOT NULL DEFAULT 0,
    `potencia` INT NOT NULL DEFAULT 0,
    `pot_control` INT NOT NULL DEFAULT 0,
    `control` INT NOT NULL DEFAULT 0,
    `intervencion` INT NOT NULL DEFAULT 0,
    `documento_correccion_fallas` INT NOT NULL DEFAULT 0,
    `hash` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `project_id` VARCHAR(255) NULL DEFAULT NULL,
    `client_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    UNIQUE KEY `board_details_hash_unique` (`hash`),
    KEY `board_details_project_id_index` (`project_id`),
    KEY `board_details_client_id_index` (`client_id`),
    CONSTRAINT `board_details_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
    CONSTRAINT `board_details_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: AUTOMATION PROJECTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `automation_projects` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `proyecto_id` VARCHAR(255) NOT NULL,
    `cliente` VARCHAR(255) NOT NULL,
    `proyecto_descripcion` TEXT NOT NULL,
    `fat` VARCHAR(10) NOT NULL DEFAULT 'NO',
    `pem` VARCHAR(10) NOT NULL DEFAULT 'NO',
    `hash` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `project_id` VARCHAR(255) NULL DEFAULT NULL,
    `client_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    UNIQUE KEY `automation_projects_hash_unique` (`hash`),
    KEY `automation_projects_cliente_index` (`cliente`),
    KEY `automation_projects_fat_index` (`fat`),
    KEY `automation_projects_pem_index` (`pem`),
    KEY `automation_projects_proyecto_id_index` (`proyecto_id`),
    KEY `automation_projects_project_id_index` (`project_id`),
    KEY `automation_projects_client_id_index` (`client_id`),
    CONSTRAINT `automation_projects_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
    CONSTRAINT `automation_projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: CLIENT SATISFACTION
-- ============================================================================

CREATE TABLE IF NOT EXISTS `client_satisfaction_responses` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `fecha` DATE NOT NULL,
    `client_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `cliente_nombre` VARCHAR(255) NOT NULL,
    `proyecto` VARCHAR(255) NULL DEFAULT NULL,
    `pregunta_1` INT NOT NULL DEFAULT 0,
    `pregunta_2` INT NOT NULL DEFAULT 0,
    `pregunta_3` INT NOT NULL DEFAULT 0,
    `pregunta_4` INT NOT NULL DEFAULT 0,
    `hash` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `client_satisfaction_responses_hash_unique` (`hash`),
    CONSTRAINT `client_satisfaction_responses_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `client_satisfaction_analysis` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `periodo` VARCHAR(20) NOT NULL,
    `client_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `total_respuestas` INT NOT NULL DEFAULT 0,
    `pregunta_1_esperado` INT NOT NULL DEFAULT 0,
    `pregunta_1_obtenido` INT NOT NULL DEFAULT 0,
    `pregunta_1_porcentaje` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `pregunta_2_esperado` INT NOT NULL DEFAULT 0,
    `pregunta_2_obtenido` INT NOT NULL DEFAULT 0,
    `pregunta_2_porcentaje` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `pregunta_3_esperado` INT NOT NULL DEFAULT 0,
    `pregunta_3_obtenido` INT NOT NULL DEFAULT 0,
    `pregunta_3_porcentaje` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `pregunta_4_esperado` INT NOT NULL DEFAULT 0,
    `pregunta_4_obtenido` INT NOT NULL DEFAULT 0,
    `pregunta_4_porcentaje` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `client_satisfaction_analysis_periodo_client_id_unique` (`periodo`, `client_id`),
    CONSTRAINT `client_satisfaction_analysis_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: STAFF SATISFACTION
-- ============================================================================

CREATE TABLE IF NOT EXISTS `staff_satisfaction_responses` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `personal` VARCHAR(255) NOT NULL,
    `fecha` DATE NULL DEFAULT NULL,
    `p1_mal` TINYINT(1) NOT NULL DEFAULT 0,
    `p1_normal` TINYINT(1) NOT NULL DEFAULT 0,
    `p1_bien` TINYINT(1) NOT NULL DEFAULT 0,
    `p2_mal` TINYINT(1) NOT NULL DEFAULT 0,
    `p2_normal` TINYINT(1) NOT NULL DEFAULT 0,
    `p2_bien` TINYINT(1) NOT NULL DEFAULT 0,
    `p3_mal` TINYINT(1) NOT NULL DEFAULT 0,
    `p3_normal` TINYINT(1) NOT NULL DEFAULT 0,
    `p3_bien` TINYINT(1) NOT NULL DEFAULT 0,
    `p4_mal` TINYINT(1) NOT NULL DEFAULT 0,
    `p4_normal` TINYINT(1) NOT NULL DEFAULT 0,
    `p4_bien` TINYINT(1) NOT NULL DEFAULT 0,
    `hash` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `staff_satisfaction_responses_hash_unique` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `staff_satisfaction_analysis` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `periodo` VARCHAR(20) NOT NULL,
    `p1_mal_count` INT NOT NULL DEFAULT 0,
    `p1_normal_count` INT NOT NULL DEFAULT 0,
    `p1_bien_count` INT NOT NULL DEFAULT 0,
    `p1_mal_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p1_normal_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p1_bien_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p2_mal_count` INT NOT NULL DEFAULT 0,
    `p2_normal_count` INT NOT NULL DEFAULT 0,
    `p2_bien_count` INT NOT NULL DEFAULT 0,
    `p2_mal_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p2_normal_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p2_bien_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p3_mal_count` INT NOT NULL DEFAULT 0,
    `p3_normal_count` INT NOT NULL DEFAULT 0,
    `p3_bien_count` INT NOT NULL DEFAULT 0,
    `p3_mal_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p3_normal_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p3_bien_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p4_mal_count` INT NOT NULL DEFAULT 0,
    `p4_normal_count` INT NOT NULL DEFAULT 0,
    `p4_bien_count` INT NOT NULL DEFAULT 0,
    `p4_mal_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p4_normal_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `p4_bien_pct` DECIMAL(8,2) NOT NULL DEFAULT 0,
    `total_respuestas` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `staff_satisfaction_analysis_periodo_unique` (`periodo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BUSINESS DOMAIN: MANUFACTURING & PRODUCTION
-- ============================================================================

CREATE TABLE IF NOT EXISTS `manufacturing_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `project_id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `units_produced` INT NOT NULL,
    `correction_documents` INT NOT NULL DEFAULT 0,
    `recorded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `hours_clock` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `hours_weighted` DECIMAL(10,2) NOT NULL DEFAULT 0,
    CONSTRAINT `manufacturing_logs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `manufacturing_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `weighting_factors` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(255) NOT NULL,
    `value` DECIMAL(10,4) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `weighting_factors_role_name_start_date_end_date_index` (`role_name`, `start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- METRICS & AUDITING
-- ============================================================================

CREATE TABLE IF NOT EXISTS `daily_metrics_aggregates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `metric_date` DATE NOT NULL,
    `metric_type` VARCHAR(50) NOT NULL,
    `metric_data` JSON NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `daily_metrics_aggregates_metric_date_metric_type_unique` (`metric_date`, `metric_type`),
    KEY `daily_metrics_aggregates_metric_date_index` (`metric_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audits` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_type` VARCHAR(255) NULL DEFAULT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `event` VARCHAR(255) NOT NULL,
    `auditable_type` VARCHAR(255) NOT NULL,
    `auditable_id` BIGINT UNSIGNED NOT NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `url` TEXT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` VARCHAR(1023) NULL DEFAULT NULL,
    `tags` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `audits_auditable_type_auditable_id_index` (`auditable_type`, `auditable_id`),
    KEY `audits_user_id_user_type_index` (`user_id`, `user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- MIGRATIONS REGISTRY
-- ============================================================================

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_08_221418_create_permission_tables', 1),
(5, '2026_01_08_232436_add_soft_deletes_to_users_table', 1),
(6, '2026_01_09_235756_create_clients_table', 1),
(7, '2026_01_09_235801_create_sales_table', 1),
(8, '2026_01_09_235805_create_client_aliases_table', 1),
(9, '2026_01_10_021748_create_projects_table', 1),
(10, '2026_01_10_021806_create_manufacturing_logs_table', 1),
(11, '2026_01_10_023027_create_weighting_factors_table', 1),
(12, '2026_01_10_023234_add_hours_to_manufacturing_logs_table', 1),
(13, '2026_01_10_024653_create_personal_access_tokens_table', 1),
(14, '2026_01_10_025433_add_moneda_to_sales_table', 1),
(15, '2026_01_10_130235_create_daily_metrics_aggregates_table', 1),
(16, '2026_01_12_233134_create_budgets_table', 1),
(17, '2026_01_13_120629_add_cliente_nombre_and_comprobante_to_budgets_table', 1),
(18, '2026_01_13_203449_add_tango_columns_to_sales_table', 1),
(19, '2026_01_13_203453_add_all_columns_to_budgets_table', 1),
(20, '2026_01_15_165500_create_hour_details_table', 1),
(21, '2026_01_15_174254_create_purchase_details_table', 1),
(22, '2026_01_15_184000_create_board_details_table', 1),
(23, '2026_01_16_120000_create_automation_projects_table', 1),
(24, '2026_01_17_160207_create_client_satisfaction_analysis_table', 1),
(25, '2026_01_17_160207_create_client_satisfaction_responses_table', 1),
(26, '2026_01_17_180000_create_staff_satisfaction_responses_table', 1),
(27, '2026_01_17_180001_create_staff_satisfaction_analysis_table', 1),
(28, '2026_01_18_162545_create_hours_normalization_tables', 1),
(29, '2026_01_18_162557_add_normalization_columns_to_hour_details_table', 1),
(30, '2026_01_18_164552_create_suppliers_table', 1),
(31, '2026_01_18_164555_create_cost_centers_table', 1),
(32, '2026_01_18_173955_add_supplier_id_to_purchase_details_table', 1),
(33, '2026_01_19_004328_create_supplier_aliases_table', 1),
(34, '2026_01_19_004415_add_name_normalized_to_suppliers_table', 1),
(35, '2026_01_19_101603_add_foreign_keys_to_board_details_table', 1),
(36, '2026_01_19_101606_add_foreign_keys_to_automation_projects_table', 1),
(37, '2026_01_19_101612_add_cost_center_id_to_purchase_details_table', 1),
(38, '2026_01_19_101615_add_project_id_to_hour_details_table', 1),
(39, '2026_01_19_103847_add_foreign_keys_to_budgets_table', 1),
(40, '2026_01_19_163000_fix_automation_column_name', 1),
(41, '2026_01_20_104103_create_audits_table', 1);

SET FOREIGN_KEY_CHECKS = 1;
