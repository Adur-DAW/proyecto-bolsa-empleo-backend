-- bolsa_empleo_adur.cache definition

CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.cache_locks definition

CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.familias_profesionales definition

CREATE TABLE `familias_profesionales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `familias_profesionales_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.migrations definition

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.personal_access_tokens definition

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.tipos_contrato definition

CREATE TABLE `tipos_contrato` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_contrato_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.usuarios definition

CREATE TABLE `usuarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rol` varchar(20) NOT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.demandantes definition

CREATE TABLE `demandantes` (
  `id_demandante` bigint(20) unsigned NOT NULL,
  `dni` varchar(9) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `apellido1` varchar(45) NOT NULL,
  `apellido2` varchar(45) NOT NULL,
  `telefono_movil` varchar(9) NOT NULL,
  `email` varchar(45) NOT NULL,
  `situacion` tinyint(4) NOT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `id_familia_profesional` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_demandante`),
  UNIQUE KEY `demandantes_dni_unique` (`dni`),
  KEY `demandantes_id_familia_profesional_foreign` (`id_familia_profesional`),
  CONSTRAINT `demandantes_id_demandante_foreign` FOREIGN KEY (`id_demandante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `demandantes_id_familia_profesional_foreign` FOREIGN KEY (`id_familia_profesional`) REFERENCES `familias_profesionales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.empresas definition

CREATE TABLE `empresas` (
  `id_empresa` bigint(20) unsigned NOT NULL,
  `validado` tinyint(4) NOT NULL,
  `cif` varchar(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `localidad` varchar(45) NOT NULL,
  `telefono` varchar(9) NOT NULL,
  `imagen_url` varchar(191) DEFAULT NULL,
  `id_familia_profesional` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_empresa`),
  UNIQUE KEY `empresas_cif_unique` (`cif`),
  KEY `empresas_id_familia_profesional_foreign` (`id_familia_profesional`),
  CONSTRAINT `empresas_id_empresa_foreign` FOREIGN KEY (`id_empresa`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `empresas_id_familia_profesional_foreign` FOREIGN KEY (`id_familia_profesional`) REFERENCES `familias_profesionales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.ofertas definition

CREATE TABLE `ofertas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) DEFAULT NULL,
  `fecha_publicacion` date DEFAULT NULL,
  `numero_puestos` tinyint(4) DEFAULT NULL,
  `tipo_contrato` varchar(45) DEFAULT NULL,
  `horario` varchar(45) DEFAULT NULL,
  `dias_descanso` varchar(100) DEFAULT NULL,
  `obs` varchar(45) DEFAULT NULL,
  `abierta` tinyint(4) DEFAULT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `id_empresa` bigint(20) unsigned NOT NULL,
  `id_tipo_contrato` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ofertas_id_empresa_foreign` (`id_empresa`),
  KEY `ofertas_id_tipo_contrato_foreign` (`id_tipo_contrato`),
  CONSTRAINT `ofertas_id_empresa_foreign` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`),
  CONSTRAINT `ofertas_id_tipo_contrato_foreign` FOREIGN KEY (`id_tipo_contrato`) REFERENCES `tipos_contrato` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.titulos definition

CREATE TABLE `titulos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_familia_profesional` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titulos_nombre_unique` (`nombre`),
  KEY `titulos_id_familia_profesional_foreign` (`id_familia_profesional`),
  CONSTRAINT `titulos_id_familia_profesional_foreign` FOREIGN KEY (`id_familia_profesional`) REFERENCES `familias_profesionales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.titulos_demandante definition

CREATE TABLE `titulos_demandante` (
  `id_demandante` bigint(20) unsigned NOT NULL,
  `id_titulo` bigint(20) unsigned NOT NULL,
  `centro` varchar(45) DEFAULT NULL,
  `año` varchar(45) DEFAULT NULL,
  `cursando` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_demandante`,`id_titulo`),
  KEY `titulos_demandante_id_titulo_foreign` (`id_titulo`),
  CONSTRAINT `titulos_demandante_id_demandante_foreign` FOREIGN KEY (`id_demandante`) REFERENCES `demandantes` (`id_demandante`) ON DELETE CASCADE,
  CONSTRAINT `titulos_demandante_id_titulo_foreign` FOREIGN KEY (`id_titulo`) REFERENCES `titulos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.titulos_empresa definition

CREATE TABLE `titulos_empresa` (
  `id_empresa` bigint(20) unsigned NOT NULL,
  `id_titulo` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_empresa`,`id_titulo`),
  KEY `titulos_empresa_id_titulo_foreign` (`id_titulo`),
  CONSTRAINT `titulos_empresa_id_empresa_foreign` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`),
  CONSTRAINT `titulos_empresa_id_titulo_foreign` FOREIGN KEY (`id_titulo`) REFERENCES `titulos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.titulos_oferta definition

CREATE TABLE `titulos_oferta` (
  `id_oferta` bigint(20) unsigned NOT NULL,
  `id_titulo` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_oferta`,`id_titulo`),
  KEY `titulos_oferta_id_titulo_foreign` (`id_titulo`),
  CONSTRAINT `titulos_oferta_id_oferta_foreign` FOREIGN KEY (`id_oferta`) REFERENCES `ofertas` (`id`),
  CONSTRAINT `titulos_oferta_id_titulo_foreign` FOREIGN KEY (`id_titulo`) REFERENCES `titulos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- bolsa_empleo_adur.demandantes_oferta definition

CREATE TABLE `demandantes_oferta` (
  `id_oferta` bigint(20) unsigned NOT NULL,
  `id_demandante` bigint(20) unsigned NOT NULL,
  `adjudicada` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_oferta`,`id_demandante`),
  KEY `demandantes_oferta_id_demandante_foreign` (`id_demandante`),
  CONSTRAINT `demandantes_oferta_id_demandante_foreign` FOREIGN KEY (`id_demandante`) REFERENCES `demandantes` (`id_demandante`),
  CONSTRAINT `demandantes_oferta_id_oferta_foreign` FOREIGN KEY (`id_oferta`) REFERENCES `ofertas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
