SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

ALTER TABLE prefix_booki_calendar
  DROP FOREIGN KEY prefix_booki_project_projectId_calendar_projectId;

ALTER TABLE prefix_booki_calendar_day
  DROP FOREIGN KEY prefix_booki_calendarId_booking_calendarId;

ALTER TABLE prefix_booki_form_element
  DROP FOREIGN KEY prefix_booki_projectId_form_element_projectId;

ALTER TABLE prefix_booki_optional
  DROP FOREIGN KEY prefix_booki_projectId_optional_projectId;

ALTER TABLE prefix_booki_order
  DROP FOREIGN KEY booki_order_wp_users_id;

ALTER TABLE prefix_booki_order_days
  DROP FOREIGN KEY booki_order_days_order_id;

ALTER TABLE prefix_booki_order_days
  DROP FOREIGN KEY booki_order_days_project;

ALTER TABLE prefix_booki_order_form_elements
  DROP FOREIGN KEY bookie_form_element_order_id;

ALTER TABLE prefix_booki_order_form_elements
  DROP FOREIGN KEY bookie_form_element_project;

ALTER TABLE prefix_booki_order_optionals
  DROP FOREIGN KEY booki_order_option_order_id;

ALTER TABLE prefix_booki_order_optionals
  DROP FOREIGN KEY prefix_booki_order_optionals_project;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;