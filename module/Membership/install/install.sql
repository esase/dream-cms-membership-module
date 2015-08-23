SET sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE';

SET @moduleId = __module_id__;

-- application admin menu

SET @maxOrder = (SELECT `order` + 1 FROM `application_admin_menu` ORDER BY `order` DESC LIMIT 1);
INSERT INTO `application_admin_menu_category` (`name`, `module`, `icon`) VALUES
('Membership Levels', @moduleId, 'membership_menu_item.png');

SET @menuCategoryId = (SELECT LAST_INSERT_ID());
SET @menuPartId = (SELECT `id` from `application_admin_menu_part` where `name` = 'Modules');

INSERT INTO `application_admin_menu` (`name`, `controller`, `action`, `module`, `order`, `category`, `part`) VALUES
('List of membership levels', 'memberships-administration', 'list', @moduleId, @maxOrder, @menuCategoryId, @menuPartId),
('Settings', 'memberships-administration', 'settings', @moduleId, @maxOrder + 1, @menuCategoryId, @menuPartId);

-- acl resources

INSERT INTO `acl_resource` (`resource`, `description`, `module`) VALUES
('memberships_administration_list', 'ACL - Viewing list of membership levels  in admin area', @moduleId),
('memberships_administration_add_role', 'ACL - Adding membership roles in admin area', @moduleId),
('memberships_administration_edit_role', 'ACL - Editing membership roles in admin area', @moduleId),
('memberships_administration_settings', 'ACL - Editing membership settings in admin area', @moduleId),
('memberships_administration_delete_roles', 'ACL - Deleting membership roles in admin area', @moduleId);

-- application events

INSERT INTO `application_event` (`name`, `module`, `description`) VALUES
('add_membership_role', @moduleId, 'Event - Adding membership roles'),
('edit_membership_role', @moduleId, 'Event - Editing membership roles'),
('delete_membership_role', @moduleId, 'Event - Deleting membership roles'),
('delete_membership_connection', @moduleId, 'Event - Deleting membership connections'),
('activate_membership_connection', @moduleId, 'Event - Activating membership connections');

-- system pages

INSERT INTO `page_system` (`slug`, `title`, `module`, `disable_menu`, `privacy`, `forced_visibility`, `disable_user_menu`, `disable_site_map`, `disable_footer_menu`, `disable_seo`, `disable_xml_map`, `pages_provider`, `dynamic_page`) VALUES
('buy-membership', 'Buy membership', @moduleId, NULL, 'Membership\\PagePrivacy\\MembershipBuyPrivacy', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
SET @buyMembershipPageId   = (SELECT LAST_INSERT_ID());
SET @paymentShopingCartPageId = (SELECT `id` FROM `page_system` WHERE `slug` = 'shopping-cart');

INSERT INTO `page_system_page_depend` (`page_id`, `depend_page_id`) VALUES
(@buyMembershipPageId, 1),
(@buyMembershipPageId, @paymentShopingCartPageId);

INSERT INTO `page_widget` (`name`, `module`, `type`, `description`, `duplicate`, `forced_visibility`, `depend_page_id`) VALUES
('membershipLevelWidget', @moduleId, 'public', 'Membership levels', NULL, 1, NULL);
SET @membershipLevelWidgetId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_system_widget_depend` (`page_id`, `widget_id`, `order`) VALUES
(@buyMembershipPageId,  @membershipLevelWidgetId,  1);

INSERT INTO `page_widget_page_depend` (`page_id`, `widget_id`) VALUES
(@buyMembershipPageId,  @membershipLevelWidgetId);

INSERT INTO `page_widget_setting_category` (`name`, `module`) VALUES
('Display options', @moduleId);
SET @displaySettingCategoryId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('membership_sorting_menu_membership_levels', @membershipLevelWidgetId, 'Show the sorting menu', 'checkbox', NULL, 1, @displaySettingCategoryId, NULL, NULL, NULL, NULL);
SET @membershipWidgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@membershipWidgetSettingId, '1', NULL);

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('membership_per_page_menu_membership_levels', @membershipLevelWidgetId, 'Show the per page menu', 'checkbox', NULL, 2, @displaySettingCategoryId, NULL, NULL, NULL, NULL);
SET @membershipWidgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@membershipWidgetSettingId, '1', NULL);

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('membership_list_item_width_medium', @membershipLevelWidgetId, 'Membership items width for medium devices desktops (<=992px)', 'select', 1, 3, @displaySettingCategoryId, NULL, NULL, NULL, NULL);
SET @widgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@widgetSettingId, 'col-md-3', NULL);

INSERT INTO `page_widget_setting_predefined_value` (`setting_id`, `value`) VALUES
(@widgetSettingId, 'col-md-3'),
(@widgetSettingId, 'col-md-4'),
(@widgetSettingId, 'col-md-6'),
(@widgetSettingId, 'col-md-12');

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('membership_list_item_width_small', @membershipLevelWidgetId, 'Membership items width for small devices tablets (<=768px)', 'select', 1, 4, @displaySettingCategoryId, NULL, NULL, NULL, NULL);
SET @widgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@widgetSettingId, 'col-sm-4', NULL);

INSERT INTO `page_widget_setting_predefined_value` (`setting_id`, `value`) VALUES
(@widgetSettingId, 'col-sm-3'),
(@widgetSettingId, 'col-sm-4'),
(@widgetSettingId, 'col-sm-6'),
(@widgetSettingId, 'col-sm-12');

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('membership_list_item_width_extra_small', @membershipLevelWidgetId, 'Membership items width for extra small devices phones (<768px)', 'select', 1, 5, @displaySettingCategoryId, NULL, NULL, NULL, NULL);
SET @widgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@widgetSettingId, 'col-xs-6', NULL);

INSERT INTO `page_widget_setting_predefined_value` (`setting_id`, `value`) VALUES
(@widgetSettingId, 'col-xs-3'),
(@widgetSettingId, 'col-xs-4'),
(@widgetSettingId, 'col-xs-6'),
(@widgetSettingId, 'col-xs-12');

-- application settings

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('membership_image_width', 'Image width', '', 'integer', 1, 1, 1, @moduleId, 0, '', '', '');

SET @settingId = (SELECT LAST_INSERT_ID());
INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId,  '800', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('membership_image_height', 'Image height', '', 'integer', 1, 2, 1, @moduleId, 0, '', '', '');

SET @settingId = (SELECT LAST_INSERT_ID());
INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId,  '600', NULL);

INSERT INTO `application_setting_category` (`name`, `module`) VALUES
('Email notifications', @moduleId);

SET @settingCategoryId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('membership_expiring_send', 'Send notification about expiring membership levels', '', 'checkbox', 0, 3, @settingCategoryId, @moduleId, 0, '', '', '');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId,  '1', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('membership_expiring_send_title', 'An expiring membership level title', 'Expiring membership level email notification', 'notification_title', 1, 4, @settingCategoryId, @moduleId, 1, '', '', '');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId,  'Your selected membership level will expire soon', NULL),
(@settingId,  'Выбранный вами уровень членства скоро истекает', 'ru');

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('membership_expiring_send_message', 'An expiring membership level message', '', 'notification_message', 1, 5, @settingCategoryId, @moduleId, 1, '', '', '');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId,  '<p>Dear <b>__RealName__</b> your membership level - "__Role__" will expire at __ExpireDate__, do not forget to renew it or buy a new one</p>', NULL),
(@settingId,  '<p>Уважаемый(я) <b>__RealName__</b> ваш уровень членства - "__Role__" истекает __ExpireDate__, не забудьте продлить его или купить новый</p>', 'ru');

-- payment integration

INSERT INTO `payment_module` (`module`, `update_event`, `delete_event`, `page_name`, `countable`, `multi_costs`, `must_login`, `handler`, `extra_options`) VALUES
(@moduleId, 'edit_membership_role', 'delete_membership_role', 'buy-membership', 0, 0, 1, '\\Membership\\PaymentHandler\\MembershipHandler', NULL);

-- module's tables

CREATE TABLE IF NOT EXISTS `membership_level` (
    `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(50) NOT NULL,
    `role_id` SMALLINT(5) UNSIGNED NOT NULL,
    `cost` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0,
    `lifetime` SMALLINT(5) UNSIGNED NOT NULL,
    `expiration_notification` SMALLINT(5) UNSIGNED NOT NULL,
    `description` TEXT NOT NULL,
    `language` CHAR(2) NOT NULL,
    `image` VARCHAR(100) DEFAULT NULL,
    `active` TINYINT(1) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `title` (`title`),
    KEY `cost` (`cost`),
    KEY `lifetime` (`lifetime`),
    KEY `role` (`role_id`),
    KEY `active` (`active`, `language`),
    FOREIGN KEY (`language`) REFERENCES `localization_list`(`language`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `membership_level_connection` (
    `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(10) UNSIGNED NOT NULL,
    `membership_id` SMALLINT(5) UNSIGNED DEFAULT NULL,
    `active` TINYINT(1) UNSIGNED NOT NULL,
    `expire_date` INT(10) UNSIGNED NOT NULL,
    `notify_date` INT(10) UNSIGNED NOT NULL,
    `notified` TINYINT(1) NOT NULL,
    `expire_value` SMALLINT(5) UNSIGNED NOT NULL,
    `notify_value` SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `expire_date` (`active`, `expire_date`),
    KEY `notify_date` (`active`, `notify_date`, `notified`),
    FOREIGN KEY (`user_id`) REFERENCES `user_list`(`user_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (`membership_id`) REFERENCES `membership_level`(`id`)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
