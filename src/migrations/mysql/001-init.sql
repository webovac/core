SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `file`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `identifier` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `modern_identifier` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `compatible_identifier` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `extension` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `content_type` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `type` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `identifier`(`identifier` ASC),
    INDEX `file_created_by_person_id_idx`(`created_by_person_id` ASC),
    CONSTRAINT `FK_file_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `index` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `language_id` int UNSIGNED NULL DEFAULT NULL,
    `module_id` int UNSIGNED NULL DEFAULT NULL,
    `page_id` int UNSIGNED NULL DEFAULT NULL,
    `web_id` int UNSIGNED NULL DEFAULT NULL,
    UNIQUE INDEX `language_id` (`language_id` ASC),
    UNIQUE INDEX `module_id` (`module_id`),
    UNIQUE INDEX `page_id` (`page_id`),
    UNIQUE INDEX `web_id` (`web_id`),
    INDEX `index_language_id_idx`(`language_id` ASC),
    INDEX `index_module_id_idx`(`module_id` ASC),
    INDEX `index_page_id_idx`(`page_id` ASC),
    INDEX `index_web_id_idx`(`web_id` ASC),
    PRIMARY KEY (`id`),
    CONSTRAINT `FK_index_language` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_index_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_index_page` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_index_web` FOREIGN KEY (`web_id`) REFERENCES `web` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `language`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `updated_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `shortcut` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `rank` int UNSIGNED NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `shortcut`(`shortcut` ASC),
    UNIQUE INDEX `rank`(`rank` ASC),
    INDEX `language_created_by_person_id_idx`(`created_by_person_id` ASC),
    INDEX `language_updated_by_person_id_idx`(`updated_by_person_id` ASC),
    CONSTRAINT `FK_language_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_language_person_2` FOREIGN KEY (`updated_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `language_translation`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `language_id` int UNSIGNED NOT NULL,
    `translation_language_id` int UNSIGNED NOT NULL,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `updated_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `title` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `language_id_translation_language_id`(`language_id` ASC, `translation_language_id` ASC),
    INDEX `language_translation_created_by_person_id_idx`(`created_by_person_id` ASC),
    INDEX `language_translation_language_id_idx`(`language_id` ASC),
    INDEX `language_translation_translation_language_id_idx`(`translation_language_id` ASC),
    INDEX `language_translation_updated_by_person_id_idx`(`updated_by_person_id` ASC),
    CONSTRAINT `FK_language_translation_language` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_language_translation_language_2` FOREIGN KEY (`translation_language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_language_translation_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_language_translation_person_2` FOREIGN KEY (`updated_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `log` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `language_id` int UNSIGNED NULL DEFAULT NULL,
    `module_id` int UNSIGNED NULL DEFAULT NULL,
    `page_id` int UNSIGNED NULL DEFAULT NULL,
    `web_id` int UNSIGNED NULL DEFAULT NULL,
    `type` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `date` timestamp NOT NULL,
    INDEX `log_language_id_idx`(`language_id` ASC),
    INDEX `log_module_id_idx`(`module_id` ASC),
    INDEX `log_page_id_idx`(`page_id` ASC),
    INDEX `log_web_id_idx`(`web_id` ASC),
    INDEX `log_created_by_person_id_idx`(`created_by_person_id` ASC),
    PRIMARY KEY (`id`),
    CONSTRAINT `FK_log_language` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_log_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_log_page` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_log_web` FOREIGN KEY (`web_id`) REFERENCES `web` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_log_created_by_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE `module`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `home_page_id` int UNSIGNED NULL DEFAULT NULL,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `updated_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `icon` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `name`(`name` ASC),
    INDEX `module_created_by_person_id_idx`(`created_by_person_id` ASC),
    INDEX `module_updated_by_person_id_idx`(`updated_by_person_id` ASC),
    INDEX `home_page_id`(`home_page_id` ASC),
    CONSTRAINT `FK_module_page` FOREIGN KEY (`home_page_id`) REFERENCES `page` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_module_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_module_person_2` FOREIGN KEY (`updated_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `module2web`  (
    `module_id` int UNSIGNED NOT NULL,
    `web_id` int UNSIGNED NOT NULL,
    PRIMARY KEY (`module_id`, `web_id`),
    INDEX `module2web_module_id_idx`(`module_id` ASC),
    INDEX `module2web_web_id_idx`(`web_id` ASC),
    CONSTRAINT `FK_module2web_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_module2web_web` FOREIGN KEY (`web_id`) REFERENCES `web` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `module_translation`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_id` int UNSIGNED NOT NULL,
    `language_id` int UNSIGNED NOT NULL,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `updated_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `title` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_520_ci NULL,
    `base_path` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `module_id_language_id`(`module_id` ASC, `language_id` ASC),
    INDEX `module_translation_created_by_person_id_idx`(`created_by_person_id` ASC),
    INDEX `module_translation_language_id_idx`(`language_id` ASC),
    INDEX `module_translation_module_id_idx`(`module_id` ASC),
    INDEX `module_translation_updated_by_person_id_idx`(`updated_by_person_id` ASC),
    CONSTRAINT `FK_module_translation_language` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_module_translation_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_module_translation_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_module_translation_person_2` FOREIGN KEY (`updated_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `page`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `web_id` int UNSIGNED NULL DEFAULT NULL,
    `module_id` int UNSIGNED NULL DEFAULT NULL,
    `parent_page_id` int UNSIGNED NULL DEFAULT NULL,
    `template_page_id` int UNSIGNED NULL DEFAULT NULL,
    `target_page_id` int UNSIGNED NULL DEFAULT NULL,
    `redirect_page_id` int UNSIGNED NULL DEFAULT NULL,
    `image_file_id` int UNSIGNED NULL DEFAULT NULL,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `updated_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `has_parameter` tinyint NOT NULL DEFAULT 0,
    `icon` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `name` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `style` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `repository` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `provides_navigation` tinyint NOT NULL DEFAULT 0,
    `rank` int NOT NULL,
    `hide_in_navigation` tinyint NOT NULL DEFAULT 0,
    `has_parent_parameter` tinyint NOT NULL DEFAULT 0,
    `parent_repository` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `provides_buttons` tinyint NOT NULL DEFAULT 0,
    `dont_inherit_path` tinyint NOT NULL DEFAULT 0,
    `dont_inherit_access_setup` tinyint NOT NULL DEFAULT 0,
    `target_parameter` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `target_parent_parameter` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `target_url` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `type` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `access_for` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `authorizing_tag` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `authorizing_parent_tag` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `target_signal` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `stretched` tinyint NOT NULL DEFAULT 0,
    `published_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `page_created_by_person_id_idx`(`created_by_person_id` ASC),
    INDEX `page_module_id_idx`(`module_id` ASC),
    INDEX `page_parent_page_id_idx`(`parent_page_id` ASC),
    INDEX `page_redirect_page_id_idx`(`redirect_page_id` ASC),
    INDEX `page_template_page_id_idx`(`template_page_id` ASC),
    INDEX `page_image_file_id_idx`(`redirect_page_id` ASC),
    INDEX `page_updated_by_person_id_idx`(`updated_by_person_id` ASC),
    INDEX `page_web_id_idx`(`web_id` ASC),
    INDEX `target_page_id`(`target_page_id` ASC),
    CONSTRAINT `FK_page_module` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_page_page` FOREIGN KEY (`parent_page_id`) REFERENCES `page` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_page_page_2` FOREIGN KEY (`template_page_id`) REFERENCES `page` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_page_page_3` FOREIGN KEY (`target_page_id`) REFERENCES `page` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_page_page_4` FOREIGN KEY (`redirect_page_id`) REFERENCES `page` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_page_file` FOREIGN KEY (`image_file_id`) REFERENCES `page` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_page_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_page_person_2` FOREIGN KEY (`updated_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_page_web` FOREIGN KEY (`web_id`) REFERENCES `web` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `page2authorized_person`  (
    `page_id` int UNSIGNED NOT NULL,
    `person_id` int UNSIGNED NOT NULL,
    PRIMARY KEY (`page_id`, `person_id`),
    INDEX `page2authorized_person_page_id_idx`(`page_id` ASC),
    INDEX `page2authorized_person_person_id_idx`(`person_id` ASC),
    CONSTRAINT `FK_page2authorized_person_page` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_page2authorized_person_person` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `page2authorized_role`  (
    `page_id` int UNSIGNED NOT NULL,
    `role_id` int UNSIGNED NOT NULL,
    PRIMARY KEY (`page_id`, `role_id`),
    INDEX `FK_page2authorized_page_page`(`page_id` ASC),
    INDEX `FK_page2authorized_role_role`(`role_id` ASC),
    CONSTRAINT `FK_page2authorized_role_page` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_page2authorized_role_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `page_translation`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_id` int UNSIGNED NOT NULL,
    `language_id` int UNSIGNED NOT NULL,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `updated_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `path` varchar(255) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `title` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `description` text COLLATE utf8mb4_unicode_520_ci NULL,
    `onclick` text COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `content` text COLLATE utf8mb4_unicode_520_ci NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `page_id_language_id` (`page_id`, `language_id`),
    INDEX `page_translation_created_by_person_id_idx`(`created_by_person_id` ASC),
    INDEX `page_translation_language_id_idx`(`language_id` ASC),
    INDEX `page_translation_page_id_idx`(`page_id` ASC),
    INDEX `page_translation_updated_by_person_id_idx`(`updated_by_person_id` ASC),
    CONSTRAINT `FK_page_translation_language` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_page_translation_page` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_page_translation_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_page_translation_person_2` FOREIGN KEY (`updated_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `person`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `first_name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `last_name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `last_login_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `email`(`email` ASC)
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `preference`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `person_id` int UNSIGNED NOT NULL,
    `web_id` int UNSIGNED NOT NULL,
    `language_id` int UNSIGNED NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `FK_preference_person`(`person_id` ASC),
    INDEX `FK_preference_web`(`web_id` ASC),
    INDEX `FK_preference_language`(`language_id` ASC),
    CONSTRAINT `FK_preference_language` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
    CONSTRAINT `FK_preference_person` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_preference_web` FOREIGN KEY (`web_id`) REFERENCES `web` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `role`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `role2person` (
    `role_id` int UNSIGNED NOT NULL,
    `person_id` int UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `person_id`),
    INDEX `role2person_role_id_idx`(`role_id` ASC),
    INDEX `role2person_person_id_idx`(`person_id` ASC),
    CONSTRAINT `FK_role2person_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_role2person_person` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `web`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `home_page_id` int UNSIGNED NULL DEFAULT NULL,
    `file_id` int UNSIGNED NULL DEFAULT NULL,
    `icon_file_id` int UNSIGNED NULL DEFAULT NULL,
    `large_icon_file_id` int UNSIGNED NULL DEFAULT NULL,
    `logo_file_id` int UNSIGNED NULL DEFAULT NULL,
    `background_file_id` int UNSIGNED NULL DEFAULT NULL,
    `default_language_id` int UNSIGNED NULL DEFAULT NULL,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `updated_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `code` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `host` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `base_path` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
    `color` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `complementary_color` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '#888888',
    `icon_background_color` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '#ffffff',
    `published_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `code`(`code` ASC),
    UNIQUE INDEX `host_base_path`(`host` ASC, `base_path` ASC),
    INDEX `web_created_by_person_id_idx`(`created_by_person_id` ASC),
    INDEX `web_file_id_idx`(`file_id` ASC),
    INDEX `web_icon_file_id_idx`(`icon_file_id` ASC),
    INDEX `web_large_icon_file_id_idx`(`icon_file_id` ASC),
    INDEX `web_logo_file_id_idx`(`logo_file_id` ASC),
    INDEX `web_background_file_id_idx`(`background_file_id` ASC),
    INDEX `web_home_page_id_idx`(`home_page_id` ASC),
    INDEX `web_default_language_id_idx`(`default_language_id` ASC),
    INDEX `web_updated_by_person_id_idx`(`updated_by_person_id` ASC),
    INDEX `FK_web_language`(`default_language_id` ASC),
    CONSTRAINT `FK_web_file` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_web_file_2` FOREIGN KEY (`icon_file_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_web_file_3` FOREIGN KEY (`logo_file_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_web_file_4` FOREIGN KEY (`background_file_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_web_file_5` FOREIGN KEY (`large_icon_file_id`) REFERENCES `file` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_web_language` FOREIGN KEY (`default_language_id`) REFERENCES `language` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_web_page` FOREIGN KEY (`home_page_id`) REFERENCES `page` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_web_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_web_person_2` FOREIGN KEY (`updated_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

CREATE TABLE `web_translation`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `web_id` int UNSIGNED NOT NULL,
    `language_id` int UNSIGNED NOT NULL,
    `created_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `updated_by_person_id` int UNSIGNED NULL DEFAULT NULL,
    `title` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
    `footer` varchar(50) COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `web_id_language_id`(`web_id` ASC, `language_id` ASC),
    INDEX `web_translation_created_by_person_id_idx`(`created_by_person_id` ASC),
    INDEX `web_translation_language_id_idx`(`language_id` ASC),
    INDEX `web_translation_updated_by_person_id_idx`(`updated_by_person_id` ASC),
    INDEX `web_translation_web_id_idx`(`web_id` ASC),
    CONSTRAINT `FK_web_translation_language` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_web_translation_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_web_translation_person_2` FOREIGN KEY (`updated_by_person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `FK_web_translation_web` FOREIGN KEY (`web_id`) REFERENCES `web` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;

SET FOREIGN_KEY_CHECKS = 1;
