SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DELETE FROM `prefix_booki_project` ;
DELETE FROM `prefix_booki_cascading_list` ;
DELETE FROM `prefix_booki_cascading_item` ;
DELETE FROM `prefix_booki_calendar` ;
DELETE FROM `prefix_booki_calendar_day` ;
DELETE FROM `prefix_booki_coupons` ;
DELETE FROM `prefix_booki_event_log` ;
DELETE FROM `prefix_booki_form_element` ;
DELETE FROM `prefix_booki_optional` ;
DELETE FROM `prefix_booki_order` ;
DELETE FROM `prefix_booki_order_cascading_item` ;
DELETE FROM `prefix_booki_order_days` ;
DELETE FROM `prefix_booki_order_form_elements` ;
DELETE FROM `prefix_booki_order_optionals` ;
DELETE FROM `prefix_booki_settings` ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;