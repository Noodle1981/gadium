CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "roles_name_guard_name_unique" on "roles"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "model_has_permissions"(
  "permission_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  primary key("permission_id", "model_id", "model_type")
);
CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "model_has_roles"(
  "role_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("role_id", "model_id", "model_type")
);
CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "role_has_permissions"(
  "permission_id" integer not null,
  "role_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("permission_id", "role_id")
);
CREATE TABLE IF NOT EXISTS "clients"(
  "id" integer primary key autoincrement not null,
  "nombre" varchar not null,
  "nombre_normalizado" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "clients_nombre_normalizado_index" on "clients"(
  "nombre_normalizado"
);
CREATE TABLE IF NOT EXISTS "sales"(
  "id" integer primary key autoincrement not null,
  "fecha" date not null,
  "client_id" integer,
  "cliente_nombre" varchar not null,
  "monto" numeric not null,
  "comprobante" varchar not null,
  "hash" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "moneda" varchar not null default 'ARS',
  "cod_cli" varchar,
  "n_remito" varchar,
  "t_comp" varchar,
  "cond_vta" varchar,
  "porc_desc" numeric,
  "cotiz" numeric,
  "cod_transp" varchar,
  "nom_transp" varchar,
  "cod_articu" varchar,
  "descripcio" text,
  "cod_dep" varchar,
  "um" varchar,
  "cantidad" numeric,
  "precio" numeric,
  "tot_s_imp" numeric,
  "n_comp_rem" varchar,
  "cant_rem" numeric,
  "fecha_rem" date,
  foreign key("client_id") references "clients"("id") on delete set null
);
CREATE UNIQUE INDEX "sales_hash_unique" on "sales"("hash");
CREATE TABLE IF NOT EXISTS "client_aliases"(
  "id" integer primary key autoincrement not null,
  "client_id" integer not null,
  "alias" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("client_id") references "clients"("id") on delete cascade
);
CREATE UNIQUE INDEX "client_aliases_alias_unique" on "client_aliases"("alias");
CREATE TABLE IF NOT EXISTS "projects"(
  "id" varchar not null,
  "name" varchar not null,
  "client_id" integer not null,
  "status" varchar not null default 'activo',
  "quality_status" varchar not null default 'normal',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  foreign key("client_id") references "clients"("id") on delete cascade,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "manufacturing_logs"(
  "id" integer primary key autoincrement not null,
  "project_id" varchar not null,
  "user_id" integer not null,
  "units_produced" integer not null,
  "correction_documents" integer not null default '0',
  "recorded_at" datetime not null default CURRENT_TIMESTAMP,
  "created_at" datetime,
  "updated_at" datetime,
  "hours_clock" numeric not null default '0',
  "hours_weighted" numeric not null default '0',
  foreign key("project_id") references "projects"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "weighting_factors"(
  "id" integer primary key autoincrement not null,
  "role_name" varchar not null,
  "value" numeric not null,
  "start_date" date not null,
  "end_date" date,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE INDEX "weighting_factors_role_name_start_date_end_date_index" on "weighting_factors"(
  "role_name",
  "start_date",
  "end_date"
);
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" text not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens"(
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "daily_metrics_aggregates"(
  "id" integer primary key autoincrement not null,
  "metric_date" date not null,
  "metric_type" varchar not null,
  "metric_data" text not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "daily_metrics_aggregates_metric_date_metric_type_unique" on "daily_metrics_aggregates"(
  "metric_date",
  "metric_type"
);
CREATE INDEX "daily_metrics_aggregates_metric_date_index" on "daily_metrics_aggregates"(
  "metric_date"
);
CREATE TABLE IF NOT EXISTS "client_satisfaction_analysis"(
  "id" integer primary key autoincrement not null,
  "periodo" varchar not null,
  "client_id" integer,
  "total_respuestas" integer not null default '0',
  "pregunta_1_esperado" integer not null default '0',
  "pregunta_1_obtenido" integer not null default '0',
  "pregunta_1_porcentaje" numeric not null default '0',
  "pregunta_2_esperado" integer not null default '0',
  "pregunta_2_obtenido" integer not null default '0',
  "pregunta_2_porcentaje" numeric not null default '0',
  "pregunta_3_esperado" integer not null default '0',
  "pregunta_3_obtenido" integer not null default '0',
  "pregunta_3_porcentaje" numeric not null default '0',
  "pregunta_4_esperado" integer not null default '0',
  "pregunta_4_obtenido" integer not null default '0',
  "pregunta_4_porcentaje" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("client_id") references "clients"("id") on delete cascade
);
CREATE UNIQUE INDEX "client_satisfaction_analysis_periodo_client_id_unique" on "client_satisfaction_analysis"(
  "periodo",
  "client_id"
);
CREATE TABLE IF NOT EXISTS "client_satisfaction_responses"(
  "id" integer primary key autoincrement not null,
  "fecha" date not null,
  "client_id" integer,
  "cliente_nombre" varchar not null,
  "proyecto" varchar,
  "pregunta_1" integer not null default '0',
  "pregunta_2" integer not null default '0',
  "pregunta_3" integer not null default '0',
  "pregunta_4" integer not null default '0',
  "hash" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("client_id") references "clients"("id") on delete set null
);
CREATE UNIQUE INDEX "client_satisfaction_responses_hash_unique" on "client_satisfaction_responses"(
  "hash"
);
CREATE TABLE IF NOT EXISTS "staff_satisfaction_responses"(
  "id" integer primary key autoincrement not null,
  "personal" varchar not null,
  "fecha" date,
  "p1_mal" tinyint(1) not null default '0',
  "p1_normal" tinyint(1) not null default '0',
  "p1_bien" tinyint(1) not null default '0',
  "p2_mal" tinyint(1) not null default '0',
  "p2_normal" tinyint(1) not null default '0',
  "p2_bien" tinyint(1) not null default '0',
  "p3_mal" tinyint(1) not null default '0',
  "p3_normal" tinyint(1) not null default '0',
  "p3_bien" tinyint(1) not null default '0',
  "p4_mal" tinyint(1) not null default '0',
  "p4_normal" tinyint(1) not null default '0',
  "p4_bien" tinyint(1) not null default '0',
  "hash" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "staff_satisfaction_responses_hash_unique" on "staff_satisfaction_responses"(
  "hash"
);
CREATE TABLE IF NOT EXISTS "staff_satisfaction_analysis"(
  "id" integer primary key autoincrement not null,
  "periodo" varchar not null,
  "p1_mal_count" integer not null default '0',
  "p1_normal_count" integer not null default '0',
  "p1_bien_count" integer not null default '0',
  "p1_mal_pct" numeric not null default '0',
  "p1_normal_pct" numeric not null default '0',
  "p1_bien_pct" numeric not null default '0',
  "p2_mal_count" integer not null default '0',
  "p2_normal_count" integer not null default '0',
  "p2_bien_count" integer not null default '0',
  "p2_mal_pct" numeric not null default '0',
  "p2_normal_pct" numeric not null default '0',
  "p2_bien_pct" numeric not null default '0',
  "p3_mal_count" integer not null default '0',
  "p3_normal_count" integer not null default '0',
  "p3_bien_count" integer not null default '0',
  "p3_mal_pct" numeric not null default '0',
  "p3_normal_pct" numeric not null default '0',
  "p3_bien_pct" numeric not null default '0',
  "p4_mal_count" integer not null default '0',
  "p4_normal_count" integer not null default '0',
  "p4_bien_count" integer not null default '0',
  "p4_mal_pct" numeric not null default '0',
  "p4_normal_pct" numeric not null default '0',
  "p4_bien_pct" numeric not null default '0',
  "total_respuestas" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "staff_satisfaction_analysis_periodo_unique" on "staff_satisfaction_analysis"(
  "periodo"
);
CREATE TABLE IF NOT EXISTS "job_functions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE UNIQUE INDEX "job_functions_name_unique" on "job_functions"("name");
CREATE TABLE IF NOT EXISTS "guardias"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE UNIQUE INDEX "guardias_name_unique" on "guardias"("name");
CREATE TABLE IF NOT EXISTS "user_aliases"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "alias" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "user_aliases_alias_index" on "user_aliases"("alias");
CREATE UNIQUE INDEX "user_aliases_alias_unique" on "user_aliases"("alias");
CREATE TABLE IF NOT EXISTS "suppliers"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "tax_id" varchar,
  "address" varchar,
  "email" varchar,
  "phone" varchar,
  "status" varchar not null default 'active',
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "name_normalized" varchar
);
CREATE TABLE IF NOT EXISTS "cost_centers"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "description" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime
);
CREATE UNIQUE INDEX "cost_centers_code_unique" on "cost_centers"("code");
CREATE TABLE IF NOT EXISTS "supplier_aliases"(
  "id" integer primary key autoincrement not null,
  "supplier_id" integer not null,
  "alias" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("supplier_id") references "suppliers"("id") on delete cascade
);
CREATE UNIQUE INDEX "supplier_aliases_alias_unique" on "supplier_aliases"(
  "alias"
);
CREATE INDEX "suppliers_name_normalized_index" on "suppliers"(
  "name_normalized"
);
CREATE TABLE IF NOT EXISTS "board_details"(
  "id" integer primary key autoincrement not null,
  "ano" integer not null,
  "proyecto_numero" varchar not null,
  "cliente" varchar not null,
  "descripcion_proyecto" text not null,
  "columnas" integer not null default('0'),
  "gabinetes" integer not null default('0'),
  "potencia" integer not null default('0'),
  "pot_control" integer not null default('0'),
  "control" integer not null default('0'),
  "intervencion" integer not null default('0'),
  "documento_correccion_fallas" integer not null default('0'),
  "hash" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "project_id" varchar,
  "client_id" integer,
  foreign key("project_id") references "projects"("id") on delete set null,
  foreign key("client_id") references "clients"("id") on delete set null
);
CREATE UNIQUE INDEX "board_details_hash_unique" on "board_details"("hash");
CREATE INDEX "board_details_project_id_index" on "board_details"("project_id");
CREATE INDEX "board_details_client_id_index" on "board_details"("client_id");
CREATE TABLE IF NOT EXISTS "automation_projects"(
  "id" integer primary key autoincrement not null,
  "proyecto_id" varchar not null,
  "cliente" varchar not null,
  "proyecto_descripcion" text not null,
  "fat" varchar not null default('NO'),
  "pem" varchar not null default('NO'),
  "hash" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "project_id" varchar,
  "client_id" integer,
  foreign key("project_id") references "projects"("id") on delete set null,
  foreign key("client_id") references "clients"("id") on delete set null
);
CREATE INDEX "automation_projects_cliente_index" on "automation_projects"(
  "cliente"
);
CREATE INDEX "automation_projects_fat_index" on "automation_projects"("fat");
CREATE UNIQUE INDEX "automation_projects_hash_unique" on "automation_projects"(
  "hash"
);
CREATE INDEX "automation_projects_pem_index" on "automation_projects"("pem");
CREATE INDEX "automation_projects_proyecto_id_index" on "automation_projects"(
  "proyecto_id"
);
CREATE INDEX "automation_projects_project_id_index" on "automation_projects"(
  "project_id"
);
CREATE INDEX "automation_projects_client_id_index" on "automation_projects"(
  "client_id"
);
CREATE TABLE IF NOT EXISTS "purchase_details"(
  "id" integer primary key autoincrement not null,
  "moneda" varchar not null default('USD'),
  "cc" varchar not null,
  "ano" integer not null,
  "empresa" varchar not null,
  "descripcion" varchar not null,
  "materiales_presupuestados" numeric not null default('0'),
  "materiales_comprados" numeric not null default('0'),
  "resto_valor" numeric not null default('0'),
  "resto_porcentaje" numeric not null default('0'),
  "porcentaje_facturacion" numeric not null default('0'),
  "hash" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "supplier_id" integer,
  "cost_center_id" integer,
  foreign key("supplier_id") references suppliers("id") on delete set null on update no action,
  foreign key("cost_center_id") references "cost_centers"("id") on delete set null
);
CREATE INDEX "purchase_details_ano_cc_index" on "purchase_details"(
  "ano",
  "cc"
);
CREATE INDEX "purchase_details_empresa_index" on "purchase_details"("empresa");
CREATE UNIQUE INDEX "purchase_details_hash_unique" on "purchase_details"(
  "hash"
);
CREATE INDEX "purchase_details_supplier_id_index" on "purchase_details"(
  "supplier_id"
);
CREATE INDEX "purchase_details_cost_center_id_index" on "purchase_details"(
  "cost_center_id"
);
CREATE TABLE IF NOT EXISTS "hour_details"(
  "id" integer primary key autoincrement not null,
  "dia" varchar not null,
  "fecha" date not null,
  "ano" integer not null,
  "mes" integer not null,
  "personal" varchar not null,
  "funcion" varchar not null,
  "proyecto" varchar not null,
  "horas_ponderadas" numeric not null,
  "ponderador" numeric not null,
  "hs" numeric not null,
  "hs_comun" numeric not null,
  "hs_50" numeric not null,
  "hs_100" numeric not null,
  "hs_viaje" numeric not null,
  "hs_pernoctada" varchar not null default('No'),
  "hs_adeudadas" numeric not null,
  "vianda" varchar not null default('0'),
  "observacion" text,
  "programacion" varchar,
  "hash" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "user_id" integer,
  "job_function_id" integer,
  "guardia_id" integer,
  "project_id" varchar,
  foreign key("guardia_id") references guardias("id") on delete set null on update no action,
  foreign key("job_function_id") references job_functions("id") on delete set null on update no action,
  foreign key("user_id") references users("id") on delete set null on update no action,
  foreign key("project_id") references "projects"("id") on delete set null
);
CREATE INDEX "hour_details_ano_index" on "hour_details"("ano");
CREATE INDEX "hour_details_fecha_index" on "hour_details"("fecha");
CREATE UNIQUE INDEX "hour_details_hash_unique" on "hour_details"("hash");
CREATE INDEX "hour_details_job_function_id_index" on "hour_details"(
  "job_function_id"
);
CREATE INDEX "hour_details_mes_index" on "hour_details"("mes");
CREATE INDEX "hour_details_personal_index" on "hour_details"("personal");
CREATE INDEX "hour_details_proyecto_index" on "hour_details"("proyecto");
CREATE INDEX "hour_details_user_id_index" on "hour_details"("user_id");
CREATE INDEX "hour_details_project_id_index" on "hour_details"("project_id");
CREATE TABLE IF NOT EXISTS "budgets"(
  "id" integer primary key autoincrement not null,
  "fecha" date not null,
  "client_id" integer not null,
  "monto" numeric not null,
  "moneda" varchar not null default('USD'),
  "hash" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "cliente_nombre" varchar,
  "comprobante" varchar,
  "centro_costo" varchar,
  "nombre_proyecto" varchar,
  "fecha_oc" date,
  "fecha_estimada_culminacion" date,
  "estado_proyecto_dias" integer,
  "fecha_culminacion_real" date,
  "estado" varchar,
  "enviado_facturar" varchar,
  "nro_factura" varchar,
  "porc_facturacion" varchar,
  "saldo" numeric,
  "horas_ponderadas" numeric,
  "project_id" varchar,
  "cost_center_id" integer,
  foreign key("client_id") references clients("id") on delete cascade on update no action,
  foreign key("project_id") references "projects"("id") on delete set null,
  foreign key("cost_center_id") references "cost_centers"("id") on delete set null
);
CREATE INDEX "budgets_fecha_index" on "budgets"("fecha");
CREATE UNIQUE INDEX "budgets_hash_unique" on "budgets"("hash");
CREATE INDEX "budgets_project_id_index" on "budgets"("project_id");
CREATE INDEX "budgets_cost_center_id_index" on "budgets"("cost_center_id");
CREATE TABLE IF NOT EXISTS "audits"(
  "id" integer primary key autoincrement not null,
  "user_type" varchar,
  "user_id" integer,
  "event" varchar not null,
  "auditable_type" varchar not null,
  "auditable_id" integer not null,
  "old_values" text,
  "new_values" text,
  "url" text,
  "ip_address" varchar,
  "user_agent" varchar,
  "tags" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "audits_auditable_type_auditable_id_index" on "audits"(
  "auditable_type",
  "auditable_id"
);
CREATE INDEX "audits_user_id_user_type_index" on "audits"(
  "user_id",
  "user_type"
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_01_08_221418_create_permission_tables',1);
INSERT INTO migrations VALUES(5,'2026_01_08_232436_add_soft_deletes_to_users_table',1);
INSERT INTO migrations VALUES(6,'2026_01_09_235756_create_clients_table',1);
INSERT INTO migrations VALUES(7,'2026_01_09_235801_create_sales_table',1);
INSERT INTO migrations VALUES(8,'2026_01_09_235805_create_client_aliases_table',1);
INSERT INTO migrations VALUES(9,'2026_01_10_021748_create_projects_table',1);
INSERT INTO migrations VALUES(10,'2026_01_10_021806_create_manufacturing_logs_table',1);
INSERT INTO migrations VALUES(11,'2026_01_10_023027_create_weighting_factors_table',1);
INSERT INTO migrations VALUES(12,'2026_01_10_023234_add_hours_to_manufacturing_logs_table',1);
INSERT INTO migrations VALUES(13,'2026_01_10_024653_create_personal_access_tokens_table',1);
INSERT INTO migrations VALUES(14,'2026_01_10_025433_add_moneda_to_sales_table',1);
INSERT INTO migrations VALUES(15,'2026_01_10_130235_create_daily_metrics_aggregates_table',1);
INSERT INTO migrations VALUES(16,'2026_01_12_233134_create_budgets_table',1);
INSERT INTO migrations VALUES(17,'2026_01_13_120629_add_cliente_nombre_and_comprobante_to_budgets_table',1);
INSERT INTO migrations VALUES(18,'2026_01_13_203449_add_tango_columns_to_sales_table',1);
INSERT INTO migrations VALUES(19,'2026_01_13_203453_add_all_columns_to_budgets_table',1);
INSERT INTO migrations VALUES(20,'2026_01_15_165500_create_hour_details_table',1);
INSERT INTO migrations VALUES(21,'2026_01_15_174254_create_purchase_details_table',1);
INSERT INTO migrations VALUES(22,'2026_01_15_184000_create_board_details_table',1);
INSERT INTO migrations VALUES(23,'2026_01_16_120000_create_automation_projects_table',1);
INSERT INTO migrations VALUES(24,'2026_01_17_160207_create_client_satisfaction_analysis_table',1);
INSERT INTO migrations VALUES(25,'2026_01_17_160207_create_client_satisfaction_responses_table',1);
INSERT INTO migrations VALUES(26,'2026_01_17_180000_create_staff_satisfaction_responses_table',1);
INSERT INTO migrations VALUES(27,'2026_01_17_180001_create_staff_satisfaction_analysis_table',1);
INSERT INTO migrations VALUES(28,'2026_01_18_162545_create_hours_normalization_tables',1);
INSERT INTO migrations VALUES(29,'2026_01_18_162557_add_normalization_columns_to_hour_details_table',1);
INSERT INTO migrations VALUES(30,'2026_01_18_164552_create_suppliers_table',1);
INSERT INTO migrations VALUES(31,'2026_01_18_164555_create_cost_centers_table',1);
INSERT INTO migrations VALUES(32,'2026_01_18_173955_add_supplier_id_to_purchase_details_table',1);
INSERT INTO migrations VALUES(33,'2026_01_19_004328_create_supplier_aliases_table',1);
INSERT INTO migrations VALUES(34,'2026_01_19_004415_add_name_normalized_to_suppliers_table',1);
INSERT INTO migrations VALUES(35,'2026_01_19_101603_add_foreign_keys_to_board_details_table',1);
INSERT INTO migrations VALUES(36,'2026_01_19_101606_add_foreign_keys_to_automation_projects_table',1);
INSERT INTO migrations VALUES(37,'2026_01_19_101612_add_cost_center_id_to_purchase_details_table',1);
INSERT INTO migrations VALUES(38,'2026_01_19_101615_add_project_id_to_hour_details_table',1);
INSERT INTO migrations VALUES(39,'2026_01_19_103847_add_foreign_keys_to_budgets_table',1);
INSERT INTO migrations VALUES(40,'2026_01_19_163000_fix_automation_column_name',1);
INSERT INTO migrations VALUES(41,'2026_01_20_104103_create_audits_table',1);
