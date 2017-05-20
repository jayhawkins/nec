-- Valentina Studio --
-- MySQL dump --
-- ---------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
-- ---------------------------------------------------------


-- CREATE DATABASE "nec" -----------------------------------
CREATE DATABASE IF NOT EXISTS `nec` CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `nec`;
-- ---------------------------------------------------------


-- CREATE TABLE "carrier_needs" ----------------------------
-- CREATE TABLE "carrier_needs" --------------------------------
CREATE TABLE `carrier_needs` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`originationCity` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationState` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationZip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`destinationCity` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationState` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationZip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`originationLng` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationLat` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`destinationLng` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationLat` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`needsDataPoints` JSON NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Open',
	`qty` Smallint( 5 ) UNSIGNED NULL DEFAULT '0',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`contactEmails` JSON NOT NULL,
	`availableDate` Date NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 4;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "contact_types" ----------------------------
-- CREATE TABLE "contact_types" --------------------------------
CREATE TABLE `contact_types` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 4;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "contacts" ---------------------------------
-- CREATE TABLE "contacts" -------------------------------------
CREATE TABLE `contacts` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`contactTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`firstName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`lastName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`title` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`emailAddress` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`primaryPhone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`secondaryPhone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`fax` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`contactRating` TinyInt( 2 ) UNSIGNED NOT NULL DEFAULT '0',
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 34;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "documents" --------------------------------
-- CREATE TABLE "documents" ------------------------------------
CREATE TABLE `documents` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`documentID` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`documentURL` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "email_templates" --------------------------
-- CREATE TABLE "email_templates" ------------------------------
CREATE TABLE `email_templates` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`title` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`body` Text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`subject` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 3;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "entities" ---------------------------------
-- CREATE TABLE "entities" -------------------------------------
CREATE TABLE `entities` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`comments` Text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`urls` JSON NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`logoURL` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`entityRating` TinyInt( 2 ) UNSIGNED NOT NULL DEFAULT '0',
	`assignedMemberID` Int( 11 ) UNSIGNED NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 35;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "entity_types" -----------------------------
-- CREATE TABLE "entity_types" ---------------------------------
CREATE TABLE `entity_types` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 4;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "insurance_carriers" -----------------------
-- CREATE TABLE "insurance_carriers" ---------------------------
CREATE TABLE `insurance_carriers` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`contactName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`contactPhone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`policyNumber` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`policyExpirationDate` Date NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 2;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "links" ------------------------------------
-- CREATE TABLE "links" ----------------------------------------
CREATE TABLE `links` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`link` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL DEFAULT '0',
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 2;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "location_types" ---------------------------
-- CREATE TABLE "location_types" -------------------------------
CREATE TABLE `location_types` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NULL DEFAULT '0',
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 10;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "locations" --------------------------------
-- CREATE TABLE "locations" ------------------------------------
CREATE TABLE `locations` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`locationTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`address1` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`address2` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`city` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`state` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`zip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`latitude` Float( 10, 6 ) NOT NULL,
	`longitude` Float( 10, 6 ) NOT NULL,
	`timeZone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 40;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "locations_contacts" -----------------------
-- CREATE TABLE "locations_contacts" ---------------------------
CREATE TABLE `locations_contacts` ( 
	`location_id` Int( 11 ) UNSIGNED NOT NULL,
	`contact_id` Int( 11 ) UNSIGNED NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "members" ----------------------------------
-- CREATE TABLE "members" --------------------------------------
CREATE TABLE `members` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`userID` Int( 11 ) UNSIGNED NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`firstName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`lastName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 33;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "object_type_data_point_values" ------------
-- CREATE TABLE "object_type_data_point_values" ----------------
CREATE TABLE `object_type_data_point_values` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`object_type_data_point_id` Int( 11 ) UNSIGNED NOT NULL,
	`value` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`status` VarChar( 24 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 26;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "object_type_data_points" ------------------
-- CREATE TABLE "object_type_data_points" ----------------------
CREATE TABLE `object_type_data_points` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NULL DEFAULT '0',
	`objectTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`columnName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`title` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 16;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "object_types" -----------------------------
-- CREATE TABLE "object_types" ---------------------------------
CREATE TABLE `object_types` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 2;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "objects" ----------------------------------
-- CREATE TABLE "objects" --------------------------------------
CREATE TABLE `objects` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`objectTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`data` JSON NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "regions" ----------------------------------
-- CREATE TABLE "regions" --------------------------------------
CREATE TABLE `regions` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`geofencingCoordinates` JSON NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 3;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "requisition_quotes" -----------------------
-- CREATE TABLE "requisition_quotes" ---------------------------
CREATE TABLE `requisition_quotes` ( 
	`id` Int( 11 ) UNSIGNED NOT NULL,
	`requisitionID` Int( 11 ) UNSIGNED NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`memberID` Int( 11 ) UNSIGNED NOT NULL,
	`rate` Float( 9, 2 ) NOT NULL DEFAULT '0.00',
	`platesNeeded` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'No',
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Open',
	`notes` JSON NOT NULL,
	`originLocationID` Int( 11 ) UNSIGNED NOT NULL,
	`deliveryLocationID` Int( 11 ) UNSIGNED NOT NULL,
	`estimatedPickupDate` DateTime NOT NULL,
	`estimatedDeliveryDate` DateTime NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "requisition_tracking" ---------------------
-- CREATE TABLE "requisition_tracking" -------------------------
CREATE TABLE `requisition_tracking` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`requistionID` Int( 11 ) UNSIGNED NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`notes` JSON NOT NULL,
	`latitude` Float( 10, 6 ) UNSIGNED NOT NULL,
	`longitude` Float( 10, 6 ) UNSIGNED NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "requisitions" -----------------------------
-- CREATE TABLE "requisitions" ---------------------------------
CREATE TABLE `requisitions` ( 
	`id` Int( 11 ) UNSIGNED NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`objectID` Int( 11 ) UNSIGNED NOT NULL,
	`memberID` Int( 11 ) UNSIGNED NOT NULL,
	`contactID` Int( 11 ) UNSIGNED NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`releasedDate` DateTime NOT NULL,
	`notes` Text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originLocationID` Int( 11 ) UNSIGNED NOT NULL,
	`deliveryLocationID` Int( 11 ) UNSIGNED NOT NULL,
	`totalPayout` Float( 12, 2 ) NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "states" -----------------------------------
-- CREATE TABLE "states" ---------------------------------------
CREATE TABLE `states` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`abbreviation` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NULL,
	`updatedAt` DateTime NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 52;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "users" ------------------------------------
-- CREATE TABLE "users" ----------------------------------------
CREATE TABLE `users` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`username` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`password` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Inactive',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ),
	CONSTRAINT `unique_username` UNIQUE( `username` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 53;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "customer_needs" ---------------------------
-- CREATE TABLE "customer_needs" -------------------------------
CREATE TABLE `customer_needs` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`originationCity` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationState` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationZip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`destinationCity` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationState` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationZip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`originationLng` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationLat` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`destinationLng` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationLat` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`needsDataPoints` JSON NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Open',
	`qty` Smallint( 5 ) UNSIGNED NULL DEFAULT '0',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`contactEmails` JSON NOT NULL,
	`availableDate` Date NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 4;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- Dump data of "carrier_needs" ----------------------------
-- ---------------------------------------------------------


-- Dump data of "contact_types" ----------------------------
INSERT INTO `contact_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '1', 'Main', '2017-04-06 08:00:00', '2017-04-06 08:00:00' );
INSERT INTO `contact_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '2', 'Billing', '2017-04-06 08:00:00', '2017-04-06 08:00:00' );
INSERT INTO `contact_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '3', 'Shipping', '2017-04-06 08:00:00', '2017-04-06 08:00:00' );
INSERT INTO `contact_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '4', 'Operations', '2017-05-18 00:00:00', '2017-05-18 00:00:00' );
INSERT INTO `contact_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '5', 'Administration', '2017-05-18 00:00:00', '2017-05-18 00:00:00' );
INSERT INTO `contact_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '6', 'Maintenance', '2017-05-18 00:00:00', '2017-05-18 00:00:00' );
INSERT INTO `contact_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '7', 'Customer Service', '2017-05-18 00:00:00', '2017-05-18 00:00:00' );
-- ---------------------------------------------------------


-- Dump data of "contacts" ---------------------------------
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '26', '29', '1', 'Joe', 'Carrier', 'VP of Finance', 'jhawkins@dynamasys.com', '789-345-1234', '9376891030', '9376891030', '0', 'Active', '2017-04-08 09:45:51', '2017-04-20 07:23:59' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '27', '30', '1', 'Biggy G', 'Express', 'President', 'jhawkins@dynamasys.com', '800-684-9140', '', '', '0', 'Active', '2017-04-08 09:59:04', '2017-05-18 19:53:25' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '28', '30', '4', 'Jere', 'Caplenor', 'VP of Operations', 'jerecaplenor@biggexpress.com', '(800) 684-9140 x-5861', '(800) 684-9140 x-5855', '', '4', 'Active', '2017-04-18 23:10:34', '2017-05-18 19:14:20' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '29', '31', '1', 'John', 'Opoku', 'Administrative Assistant', 'john.opoku@gmail.com', '5135120122', '', '5135120122', '0', 'Active', '2017-04-20 07:43:01', '2017-04-20 06:04:18' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '30', '32', '1', 'John', 'Carrier', '', 'jopoku@dubtel.com', '5135120122', '', '5135120122', '0', 'Active', '2017-04-20 08:45:45', '2017-04-20 08:45:45' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '31', '29', '2', 'Bobby Joe', 'Hooper', 'Billing Manager', 'bobby@mail.com', '444-333-2222', '', '', '0', 'Active', '2017-04-22 16:38:14', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '32', '32', '1', 'Diana', 'Taylor', 'Billing Manager', 'diana.taylor@implesay.com', '6143787279', '', '', '1', 'Active', '2017-05-04 11:28:47', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '33', '0', '1', 'Troy', 'Eggers', 'President', 'troy@nationwide-equipment.com', '800-622-7737', '', '', '0', 'Active', '2017-05-16 21:54:01', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '34', '35', '1', 'Jay', 'Hawkins', 'President', 'jaycarl.hawkins@gmail.com', '937-689-1030', '', '', '0', 'Active', '2017-05-18 10:14:19', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '35', '35', '3', 'Bob', 'Smith', 'Shipping Manager', 'jay@mail.com', '222-033-4455', '', '', '0', 'Active', '2017-05-18 11:03:22', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '36', '30', '4', 'Melisa', 'Crockett', 'Customer Service Manager', 'melisacrockett@biggexpress.com', '(800) 684-9140 x-5855', '(800) 684-9140 x-5861', '', '0', 'Active', '2017-05-18 19:15:41', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '37', '30', '7', 'Brett', 'Bagwell', 'Customer Service Rep - Northeast', 'brettbagwell@biggexpress.com', '(800) 684-9140 x-7015', '', '', '0', 'Active', '2017-05-18 19:17:22', '2017-05-18 19:22:28' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '38', '30', '7', 'Jo', 'Parker', 'Customer Service Rep - SC/NC', 'joparker@biggexpress.com', '(800) 684-9140 x-7003', '', '', '0', 'Active', '2017-05-18 19:22:19', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '39', '30', '7', 'Bob', 'Cain', 'Customer Service Rep - FL/GA', 'bobcain@biggexpress.com', '(800)684-9140 x-7012', '', '', '0', 'Active', '2017-05-18 19:23:37', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '40', '30', '7', 'Anthony', 'Tester', 'Customer Service Rep - East TN/S.AL', 'anthonytester@biggexpress.com', '(800)684-9140 x-7011', '', '', '0', 'Active', '2017-05-18 19:25:27', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '41', '30', '7', 'Stephanie', 'Marsh', 'Customer Service Rep - IN/KY', 'stephaniemarsh@biggexpress.com', '(800)684-9140 x-7001', '', '', '0', 'Active', '2017-05-18 19:26:34', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '42', '30', '7', 'Lisa', 'Oakly', 'Customer Service Rep - OH/MI/WV', 'lisaoakley@biggexpress.com', '(800)684-9140 x-7000', '', '', '0', 'Active', '2017-05-18 19:27:47', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '43', '30', '7', 'Teresa', 'Ward', 'Customer Service Rep - IL/WI/MO', 'teresaward@biggexpress.com', '(800)684-9140 x-7008', '', '', '0', 'Active', '2017-05-18 19:28:36', '0000-00-00 00:00:00' );
INSERT INTO `contacts`(`id`,`entityID`,`contactTypeID`,`firstName`,`lastName`,`title`,`emailAddress`,`primaryPhone`,`secondaryPhone`,`fax`,`contactRating`,`status`,`createdAt`,`updatedAt`) VALUES ( '44', '36', '1', 'Allison', 'Horn', 'Logistics Coordinator', 'tenahawkins@yahoo.com', '866-706-1144', '', '888-395-4403', '0', 'Active', '2017-05-20 09:24:37', '2017-05-20 11:14:06' );
-- ---------------------------------------------------------


-- Dump data of "documents" --------------------------------
-- ---------------------------------------------------------


-- Dump data of "email_templates" --------------------------
INSERT INTO `email_templates`(`id`,`title`,`body`,`entityID`,`createdAt`,`updatedAt`,`subject`) VALUES ( '1', 'Authorize Account', '<b>Welcome to Nationwide Equipment Control!</b><br /><br />
To complete your registration  please , just click the link below.<br /><br />
', '0', '2017-03-26 15:07:00', '2017-03-26 15:07:00', 'Please Authorize Your Account' );
INSERT INTO `email_templates`(`id`,`title`,`body`,`entityID`,`createdAt`,`updatedAt`,`subject`) VALUES ( '2', 'Carrier Need Notification', 'NEC - Carrier Need Notification!<br /><br />There is a carrier need in the system.', '0', '2017-05-16 00:00:00', '2017-05-16 00:00:00', 'Nationwide Equipment Control - Carrier Need Notification' );
INSERT INTO `email_templates`(`id`,`title`,`body`,`entityID`,`createdAt`,`updatedAt`,`subject`) VALUES ( '3', 'Customer Availability Notification', 'NEC - Customer Availability Notification!<br /><br />I have a trailer available.', '0', '2017-05-16 00:00:00', '2017-05-16 00:00:00', 'Nationwide Equipment Control - Customer Availability Notification' );
-- ---------------------------------------------------------


-- Dump data of "entities" ---------------------------------
INSERT INTO `entities`(`id`,`entityTypeID`,`name`,`comments`,`urls`,`status`,`logoURL`,`entityRating`,`assignedMemberID`,`createdAt`,`updatedAt`) VALUES ( '0', '0', 'Nationwide Equipment Control', NULL, NULL, 'Active', NULL, '0', '32', '2017-05-16 20:00:00', '2017-05-16 20:00:00' );
INSERT INTO `entities`(`id`,`entityTypeID`,`name`,`comments`,`urls`,`status`,`logoURL`,`entityRating`,`assignedMemberID`,`createdAt`,`updatedAt`) VALUES ( '29', '2', 'Mac Truck', NULL, NULL, 'Active', NULL, '0', '28', '2017-04-08 09:45:50', '2017-04-20 07:23:59' );
INSERT INTO `entities`(`id`,`entityTypeID`,`name`,`comments`,`urls`,`status`,`logoURL`,`entityRating`,`assignedMemberID`,`createdAt`,`updatedAt`) VALUES ( '30', '2', 'Big G Express', NULL, NULL, 'Active', NULL, '0', '29', '2017-04-08 09:59:04', '2017-05-18 19:53:25' );
INSERT INTO `entities`(`id`,`entityTypeID`,`name`,`comments`,`urls`,`status`,`logoURL`,`entityRating`,`assignedMemberID`,`createdAt`,`updatedAt`) VALUES ( '31', '1', 'Toyota', NULL, NULL, 'Active', NULL, '0', '30', '2017-04-20 07:43:00', '2017-04-20 07:52:40' );
INSERT INTO `entities`(`id`,`entityTypeID`,`name`,`comments`,`urls`,`status`,`logoURL`,`entityRating`,`assignedMemberID`,`createdAt`,`updatedAt`) VALUES ( '32', '2', 'Yellow', NULL, NULL, 'Active', NULL, '0', '31', '2017-04-20 08:45:45', '2017-04-20 08:45:45' );
INSERT INTO `entities`(`id`,`entityTypeID`,`name`,`comments`,`urls`,`status`,`logoURL`,`entityRating`,`assignedMemberID`,`createdAt`,`updatedAt`) VALUES ( '35', '1', 'Trailers-R-Us', NULL, NULL, 'Active', NULL, '0', '33', '2017-05-18 10:14:18', '0000-00-00 00:00:00' );
INSERT INTO `entities`(`id`,`entityTypeID`,`name`,`comments`,`urls`,`status`,`logoURL`,`entityRating`,`assignedMemberID`,`createdAt`,`updatedAt`) VALUES ( '36', '1', 'Milestone Corporation', NULL, NULL, 'Active', NULL, '0', '34', '2017-05-20 09:24:37', '0000-00-00 00:00:00' );
-- ---------------------------------------------------------


-- Dump data of "entity_types" -----------------------------
INSERT INTO `entity_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '0', 'NEC', '2017-04-06 08:00:00', '2017-04-06 08:00:00' );
INSERT INTO `entity_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '1', 'Customer', '2017-04-06 08:00:00', '2017-04-06 08:00:00' );
INSERT INTO `entity_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '2', 'Carrier', '2017-04-06 08:00:00', '2017-04-06 08:00:00' );
-- ---------------------------------------------------------


-- Dump data of "insurance_carriers" -----------------------
INSERT INTO `insurance_carriers`(`id`,`entityID`,`name`,`contactName`,`contactPhone`,`policyNumber`,`policyExpirationDate`,`status`,`createdAt`,`updatedAt`) VALUES ( '1', '29', 'State Farm', 'Joe Bryan', '222-333-4444', 'CZ445', '2018-12-31', 'Active', '2017-04-19 20:09:05', '2017-05-20 11:45:04' );
-- ---------------------------------------------------------


-- Dump data of "links" ------------------------------------
INSERT INTO `links`(`id`,`name`,`link`,`createdAt`,`updatedAt`,`entityID`,`status`) VALUES ( '1', 'Google Home Page', 'https://www.google.com', '2017-04-20 07:22:42', '2017-04-20 07:22:56', '29', 'Active' );
-- ---------------------------------------------------------


-- Dump data of "location_types" ---------------------------
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '1', '0', 'Main Office', 'Active', '2017-04-06 08:00:00', '2017-04-06 08:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '2', '0', 'Origination', 'Active', '2017-04-06 08:00:00', '2017-04-06 08:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '3', '0', 'Destination', 'Active', '2017-04-06 08:00:00', '2017-04-06 08:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '4', '0', 'My First Location Type', 'Active', '2017-04-21 07:23:32', '0000-00-00 00:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '5', '29', 'Billing', 'Active', '2017-04-24 18:58:33', '0000-00-00 00:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '6', '32', 'Origination', 'Active', '2017-05-04 11:35:48', '0000-00-00 00:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '7', '32', 'Destination', 'Active', '2017-05-04 11:35:54', '0000-00-00 00:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '8', '29', 'Maintenance Building (West Carrollton)', 'Active', '2017-05-04 11:36:22', '0000-00-00 00:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '9', '32', 'Customer Dock - Costco', 'Active', '2017-05-04 11:36:11', '0000-00-00 00:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '10', '35', 'Distribution Center', 'Active', '2017-05-18 10:44:48', '0000-00-00 00:00:00' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '11', '30', 'Facility', 'Active', '2017-05-18 18:55:30', '2017-05-18 19:30:27' );
INSERT INTO `location_types`(`id`,`entityID`,`name`,`status`,`createdAt`,`updatedAt`) VALUES ( '12', '30', 'Dropyard', 'Active', '2017-05-18 19:30:39', '0000-00-00 00:00:00' );
-- ---------------------------------------------------------


-- Dump data of "locations" --------------------------------
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '30', '29', '1', 'Headquarters', '33 State St.', '', 'Denver', 'CO', '67890', '0.000000', '0.000000', '', 'Active', '2017-04-08 09:45:50', '2017-04-20 07:23:59' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '31', '30', '1', 'Headquarters', '190 Hawkins Drive', '', 'Shelbyville', 'TN', '37160', '35.491611', '-86.433693', '', 'Active', '2017-04-08 09:59:04', '2017-05-18 19:53:25' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '32', '30', '11', 'Gladeville', '193 Aldi Blvd.', '', 'Mt. Juliet', 'TN', '37071', '36.066280', '-86.433456', '', 'Active', '2017-04-18 23:08:47', '2017-05-18 19:31:56' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '33', '31', '2', 'Dispatch Office', '6836 Corkwood Knl', '', 'Hamilton', 'OH', '45011', '39.381542', '-84.426895', '', 'Active', '2017-04-20 07:43:00', '2017-04-20 05:56:48' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '34', '31', '1', 'Head Office', '384 Inverness Pkwy', '', 'Englewood', 'CO', '80112', '39.563450', '-104.864799', '', 'Active', '2017-04-20 05:59:32', '2017-04-20 06:00:24' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '35', '32', '1', 'Headquarters', '6836 Corkwood Knl', '', 'Hamilton', 'OH', '45011', '0.000000', '0.000000', '', 'Active', '2017-04-20 08:45:45', '2017-04-20 08:45:45' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '36', '29', '5', 'Billing Office', '1845 Sherman St', '', 'Denver', 'CO', '80303', '39.745567', '-104.985245', '', 'Active', '2017-04-24 19:00:31', '2017-05-04 00:53:16' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '37', '29', '8', 'Maitenance Building', '5906 Norwell Dr.', '', 'West Carrollton', 'OH', '45449', '39.665989', '-84.229813', '', 'Active', '2017-05-04 11:31:59', '2017-05-04 11:36:33' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '38', '32', '6', 'Costo', '384 Inverness Pkwy', '', 'Englewood', 'CO', '80112', '39.563450', '-104.864799', '', 'Active', '2017-05-04 11:41:35', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '39', '0', '1', 'Headquarters', '1746 Cole Blvd.', 'Suite 300', 'Lakewood', 'CO', '80401', '39.738525', '-105.158623', '', 'Active', '2017-05-16 21:54:00', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '40', '35', '1', 'Headquarters', '1920 Knoll Dr.', '', 'Bellbrook', 'OH', '45305', '39.640495', '-84.097595', '', 'Active', '2017-05-18 10:14:18', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '45', '35', '2', 'Dayton', '', '', 'Dayton', 'OH', '45440', '39.672089', '-84.096008', '', 'Active', '2017-05-18 11:05:48', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '46', '30', '11', 'Deason Maitenance Facility', '137 Eady Rd.', '', 'Shelbyville', 'TN', '37160', '35.588184', '-86.443962', '', 'Active', '2017-05-18 19:33:58', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '47', '30', '11', 'East TN Facility', '5754 Fish Hatchery Rd.', '', 'Russellville', 'TN', '37860', '36.194874', '-83.175087', '', 'Active', '2017-05-18 19:34:58', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '48', '30', '11', 'Jeffersonville IN Facility', '4870 Keystone Blvd.', '', 'Jeffersonville', 'IN', '47130', '38.349472', '-85.710060', '', 'Active', '2017-05-18 19:35:49', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '49', '30', '12', 'Jacksonville FL Dropyard', '1710 N McDuff Ave', '', 'Jacksonville', 'FL', '32254', '30.343077', '-81.705635', '', 'Active', '2017-05-18 19:38:47', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '50', '30', '12', 'Zanesville OH Dropyard', '4194 W Pike', '', 'Zanesville', 'OH', '43701', '39.954655', '-82.074776', '', 'Active', '2017-05-18 19:41:24', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '51', '30', '12', 'Atlanta GA Dropyard', '4400 Bowman Industrial Ct', '', 'Atlanta', 'GA', '30288', '33.651295', '-84.325676', '', 'Active', '2017-05-18 19:43:04', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '52', '30', '12', 'Memphis TN Dropyard', '3522 Lamar Ave', '', 'Memphis', 'TN', '38118', '35.070969', '-89.945297', '', 'Active', '2017-05-18 19:44:19', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '53', '30', '12', 'Mechanicsburg PA Dropyard', '6380 Brackbill Blvd', '', 'Mechanicsburg', 'PA', '17055', '40.227234', '-77.004745', '', 'Active', '2017-05-18 19:45:43', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '54', '30', '12', 'Fort Mill SC Dropyard', '820 Tom Hall St.', '', 'Fort Mill', 'SC', '29715', '35.007061', '-80.927826', '', 'Active', '2017-05-18 19:49:13', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '55', '36', '1', 'Headquarters', '3050 West Clay St.', 'Suite 300', 'St. Charles', 'MO', '63301', '38.788052', '-90.536232', '', 'Active', '2017-05-20 09:24:37', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '56', '36', '2', 'Indianapolis', '', '', 'Indianapolis', 'IN', '46077', '39.983189', '-86.276802', '', 'Active', '2017-05-20 09:53:10', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '57', '36', '3', 'Springfield', '', '', 'Springfield', 'MO', '65619', '37.141586', '-93.399376', '', 'Active', '2017-05-20 09:53:11', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '58', '36', '2', 'Lafayette', '', '', 'Lafayette', 'IN', '47901', '40.417576', '-86.889763', '', 'Active', '2017-05-20 10:08:28', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '59', '36', '3', 'San Antonio', '', '', 'San Antonio', 'TX', '78006', '29.917368', '-98.704079', '', 'Active', '2017-05-20 10:08:28', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '60', '36', '3', 'Detroit', '', '', 'Detroit', 'MI', '48127', '42.344604', '-83.282707', '', 'Active', '2017-05-20 10:11:19', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '61', '36', '3', 'Milwaukee', '', '', 'Milwaukee', 'WI', '53172', '42.912121', '-87.864799', '', 'Active', '2017-05-20 10:14:25', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '62', '36', '2', 'Terra Haute', '', '', 'Terra Haute', 'IN', '47801', '39.470001', '-87.410004', '', 'Active', '2017-05-20 10:45:48', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '63', '36', '3', 'Houston', '', '', 'Houston', 'TX', '77001', '29.770000', '-95.370003', '', 'Active', '2017-05-20 10:45:48', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '64', '36', '3', 'Akron', '', '', 'Akron', 'OH', '44223', '41.174030', '-81.521240', '', 'Active', '2017-05-20 11:01:05', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '65', '36', '2', 'Memphis', '', '', 'Memphis', 'TN', '37501', '35.003517', '-89.937729', '', 'Active', '2017-05-20 11:03:30', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '66', '36', '3', 'Atlanta', '', '', 'Atlanta', 'GA', '30301', '33.760002', '-84.389999', '', 'Active', '2017-05-20 11:03:31', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '67', '36', '3', 'Chicago', '', '', 'Chicago', 'IL', '60007', '42.011360', '-88.002060', '', 'Active', '2017-05-20 11:05:12', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '68', '36', '2', 'Pageland', '', '', 'Pageland', 'SC', '29728', '34.773209', '-80.391731', '', 'Active', '2017-05-20 11:08:26', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '69', '36', '3', 'Baltimore', '', '', 'Baltimore', 'MD', '21201', '39.296337', '-76.621056', '', 'Active', '2017-05-20 11:08:27', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '70', '36', '2', 'Columbus', '', '', 'Columbus', 'OH', '43004', '40.014465', '-82.810196', '', 'Active', '2017-05-20 11:10:05', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '71', '36', '3', 'St. Louis', '', '', 'St. Louis', 'MO', '63101', '38.630539', '-90.192825', '', 'Active', '2017-05-20 11:10:06', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '72', '36', '2', 'Albany', '', '', 'Albany', 'NY', '12084', '42.700325', '-73.906586', '', 'Active', '2017-05-20 11:11:34', '0000-00-00 00:00:00' );
INSERT INTO `locations`(`id`,`entityID`,`locationTypeID`,`name`,`address1`,`address2`,`city`,`state`,`zip`,`latitude`,`longitude`,`timeZone`,`status`,`createdAt`,`updatedAt`) VALUES ( '73', '36', '3', 'York', '', '', 'York', 'PA', '17401', '39.959156', '-76.736542', '', 'Active', '2017-05-20 11:11:34', '0000-00-00 00:00:00' );
-- ---------------------------------------------------------


-- Dump data of "locations_contacts" -----------------------
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '36', '31', '29' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '30', '32' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '31', '29' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '37', '31', '29' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '30', '32' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '34', '35' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '32', '27', '30' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '27', '30' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '27', '30' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '27', '30' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '27', '30' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '27', '30' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '27', '30' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '27', '30' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '27', '30' );
INSERT INTO `locations_contacts`(`location_id`,`contact_id`,`entityID`) VALUES ( '0', '27', '30' );
-- ---------------------------------------------------------


-- Dump data of "members" ----------------------------------
INSERT INTO `members`(`id`,`userID`,`entityID`,`firstName`,`lastName`,`createdAt`,`updatedAt`) VALUES ( '28', '48', '29', 'Joe', 'Carrier', '2017-04-08 09:45:51', '2017-04-08 09:45:51' );
INSERT INTO `members`(`id`,`userID`,`entityID`,`firstName`,`lastName`,`createdAt`,`updatedAt`) VALUES ( '29', '49', '30', 'Otis', 'Birdsong', '2017-04-08 09:59:04', '2017-04-08 09:59:04' );
INSERT INTO `members`(`id`,`userID`,`entityID`,`firstName`,`lastName`,`createdAt`,`updatedAt`) VALUES ( '30', '50', '31', 'John', 'Opoku', '2017-04-20 07:43:01', '2017-04-20 07:43:01' );
INSERT INTO `members`(`id`,`userID`,`entityID`,`firstName`,`lastName`,`createdAt`,`updatedAt`) VALUES ( '31', '51', '32', 'John', 'Carrier', '2017-04-20 08:45:45', '2017-04-20 08:45:45' );
INSERT INTO `members`(`id`,`userID`,`entityID`,`firstName`,`lastName`,`createdAt`,`updatedAt`) VALUES ( '32', '52', '0', 'Troy', 'Eggers', '2017-05-16 21:54:01', '0000-00-00 00:00:00' );
INSERT INTO `members`(`id`,`userID`,`entityID`,`firstName`,`lastName`,`createdAt`,`updatedAt`) VALUES ( '33', '53', '35', 'Jay', 'Hawkins', '2017-05-18 10:14:19', '0000-00-00 00:00:00' );
INSERT INTO `members`(`id`,`userID`,`entityID`,`firstName`,`lastName`,`createdAt`,`updatedAt`) VALUES ( '34', '54', '36', 'Allison', 'Horn', '2017-05-20 09:24:37', '0000-00-00 00:00:00' );
-- ---------------------------------------------------------


-- Dump data of "object_type_data_point_values" ------------
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '1', '2', '12.0 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '2', '2', '12.5 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '3', '2', '13.0 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '4', '2', '13.5 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '5', '4', 'Yes', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '6', '4', 'No', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '7', '7', 'Roll', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '8', '7', 'Swing', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '9', '8', 'Yes', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '10', '8', 'No', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '11', '10', '90 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '12', '10', '95 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '13', '10', '100 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '14', '10', '105 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '15', '11', '10 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '16', '11', '11 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '17', '11', '12 ft.', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '18', '12', 'Yes', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '19', '12', 'No', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '20', '13', 'Yes', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '21', '13', 'No', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '22', '14', 'Yes', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '23', '14', 'No', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '24', '15', 'Yes', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '25', '15', 'No', '2017-05-04 13:00:00', '2017-05-04 13:00:00', 'Active' );
INSERT INTO `object_type_data_point_values`(`id`,`object_type_data_point_id`,`value`,`createdAt`,`updatedAt`,`status`) VALUES ( '26', '10', '53 ft.', '2017-05-20 08:00:00', '2017-05-20 08:00:00', 'Active' );
-- ---------------------------------------------------------


-- Dump data of "object_type_data_points" ------------------
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '2', '0', '1', 'height', 'Height', 'Active', '2017-04-28 15:41:28', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '4', '0', '1', 'dry_van', 'Dry Van', 'Active', '2017-04-28 15:46:16', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '7', '0', '1', 'door', 'Door', 'Active', '2017-04-28 15:47:38', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '8', '0', '1', 'railable', 'Railable', 'Active', '2017-04-28 15:47:56', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '10', '0', '1', 'length', 'Length', 'Active', '2017-04-28 15:49:04', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '11', '0', '1', 'width', 'Width', 'Active', '2017-04-28 15:49:22', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '12', '0', '1', 'reefer', 'Reefer', 'Active', '2017-04-28 15:49:44', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '13', '0', '1', 'air_ride', 'Air Ride', 'Active', '2017-04-28 15:50:11', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '14', '0', '1', 'floor', 'Floor', 'Active', '2017-05-04 13:20:31', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '15', '0', '1', 'carb', 'CARB', 'Active', '2017-05-04 13:20:41', '2017-05-04 13:20:41' );
INSERT INTO `object_type_data_points`(`id`,`entityID`,`objectTypeID`,`columnName`,`title`,`status`,`createdAt`,`updatedAt`) VALUES ( '16', '31', '1', 'test', 'Test', 'Active', '2017-05-18 11:56:08', '0000-00-00 00:00:00' );
-- ---------------------------------------------------------


-- Dump data of "object_types" -----------------------------
INSERT INTO `object_types`(`id`,`name`,`createdAt`,`updatedAt`) VALUES ( '1', 'Trailers', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
-- ---------------------------------------------------------


-- Dump data of "objects" ----------------------------------
-- ---------------------------------------------------------


-- Dump data of "regions" ----------------------------------
INSERT INTO `regions`(`id`,`name`,`geofencingCoordinates`,`createdAt`,`updatedAt`) VALUES ( '1', 'East', '"{\\"lat\\": \\"33.44}"', '0000-00-00 00:00:00', '0000-00-00 00:00:00' );
INSERT INTO `regions`(`id`,`name`,`geofencingCoordinates`,`createdAt`,`updatedAt`) VALUES ( '2', 'East', '"{\\"lat\\": \\"33.44\\"}"', '0000-00-00 00:00:00', '0000-00-00 00:00:00' );
-- ---------------------------------------------------------


-- Dump data of "requisition_quotes" -----------------------
-- ---------------------------------------------------------


-- Dump data of "requisition_tracking" ---------------------
-- ---------------------------------------------------------


-- Dump data of "requisitions" -----------------------------
-- ---------------------------------------------------------


-- Dump data of "states" -----------------------------------
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '1', 'CT', 'Connecticut', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '2', 'CO', 'Colorado', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '3', 'CA', 'California', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '4', 'AZ', 'Arizona', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '5', 'AR', 'Arkansas', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '6', 'AL', 'Alabama', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '7', 'AK', 'Alaska', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '8', 'DC', 'District of Columbia', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '9', 'DE', 'Delaware', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '10', 'FL', 'Florida', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '11', 'GA', 'Georgia', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '12', 'HI', 'Hawaii', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '13', 'IA', 'Iowa', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '14', 'ID', 'Idaho', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '15', 'IL', 'Illinois', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '16', 'IN', 'Indiana', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '17', 'KS', 'Kansas', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '18', 'KY', 'Kentucky', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '19', 'LA', 'Louisiana', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '20', 'MA', 'Massachusetts', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '21', 'MD', 'Maryland', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '22', 'ME', 'Maine', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '23', 'MI', 'Michigan', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '24', 'MN', 'Minnesota', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '25', 'MO', 'Missouri', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '26', 'MS', 'Mississippi', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '27', 'MT', 'Montana', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '28', 'NC', 'North Carolina', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '29', 'ND', 'North Dakota', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '30', 'NE', 'Nebraska', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '31', 'NH', 'New Hampshire', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '32', 'NJ', 'New Jersey', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '33', 'NM', 'New Mexico', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '34', 'NV', 'Nevada', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '35', 'NY', 'New York', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '36', 'OH', 'Ohio', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '37', 'OK', 'Oklahoma', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '38', 'OR', 'Oregon', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '39', 'PA', 'Pennsylvania', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '40', 'RI', 'Rhode Island', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '41', 'SC', 'South Carolina', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '42', 'SD', 'South Dakota', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '43', 'TN', 'Tennessee', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '44', 'TX', 'Texas', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '45', 'UT', 'Utah', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '46', 'VA', 'Virginia', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '47', 'VT', 'Vermont', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '48', 'WA', 'Washington', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '49', 'WI', 'Wisconsin', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '50', 'WV', 'West Virginia', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
INSERT INTO `states`(`id`,`abbreviation`,`name`,`createdAt`,`updatedAt`) VALUES ( '51', 'WY', 'Wyoming', '2017-03-22 12:15:00', '2017-03-22 12:15:00' );
-- ---------------------------------------------------------


-- Dump data of "users" ------------------------------------
INSERT INTO `users`(`id`,`username`,`password`,`status`,`createdAt`,`updatedAt`) VALUES ( '48', 'jay@qwiqfire.com', '$2y$10$vaXmqvsnGiWOqjnkzuSMn.rSQLcA5Ieon0UVzNgjrorWdCm3bWtpu', 'Active', '2017-04-08 09:45:51', '2017-04-08 09:46:04' );
INSERT INTO `users`(`id`,`username`,`password`,`status`,`createdAt`,`updatedAt`) VALUES ( '49', 'jhawkins@dynamasys.com', '$2y$10$uXw.TJRbUS7KVEgOztmIBeISRzqcYlgO4JKKhl.IqrG1Ct6FPuZXq', 'Active', '2017-04-08 09:59:04', '2017-04-08 09:59:19' );
INSERT INTO `users`(`id`,`username`,`password`,`status`,`createdAt`,`updatedAt`) VALUES ( '50', 'john.opoku@gmail.com', '$2y$10$ICJvTIzqlbgspGcFF7jxwengaktuF10fZTTNXlw.IrYN3H1pBvBUO', 'Active', '2017-04-20 07:43:01', '2017-04-20 07:44:49' );
INSERT INTO `users`(`id`,`username`,`password`,`status`,`createdAt`,`updatedAt`) VALUES ( '51', 'jopoku@dubtel.com', '$2y$10$yuDx.VI1FV0UAunrCBmpEOySILIAgE63xIDuDewkJNRQchD7f1jGW', 'Active', '2017-04-20 08:45:45', '2017-04-20 08:46:28' );
INSERT INTO `users`(`id`,`username`,`password`,`status`,`createdAt`,`updatedAt`) VALUES ( '52', 'troy@nationwide-equipment.com', '$2y$10$AG1ID3HlkFHfTQPBcR9uMOPJQy6pJ.WiRsBgJd0LdmYXEWZCAz9QC', 'Active', '2017-05-16 21:54:01', '2017-05-17 00:51:54' );
INSERT INTO `users`(`id`,`username`,`password`,`status`,`createdAt`,`updatedAt`) VALUES ( '53', 'jaycarl.hawkins@gmail.com', '$2y$10$WCWq8AQtfLcLwC7lQhFQO.KTH6TqXEL1Asi/aRQUJPGI9RYtH2/Hm', 'Active', '2017-05-18 10:14:19', '2017-05-18 10:14:28' );
INSERT INTO `users`(`id`,`username`,`password`,`status`,`createdAt`,`updatedAt`) VALUES ( '54', 'tenahawkins@yahoo.com', '$2y$10$SjrcydrWpl.W3ndrliJp.eDJ7BxU0Z4LrxFlKBAtMKEdwaJ5RmufO', 'Active', '2017-05-20 09:24:37', '0000-00-00 00:00:00' );
-- ---------------------------------------------------------


-- Dump data of "customer_needs" ---------------------------
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '2', '35', 'Dayton', 'OH', '45440', 'Bellbrook', 'OH', '45305', '-84.0960056', '39.6720868', '-84.0707681', '39.6356154', '[{"height": "13.5 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "90 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '5', '2017-05-18 11:05:48', '0000-00-00 00:00:00', '[{"33": "Troy Eggers"}]', NULL );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '3', '31', 'Englewood', 'CO', '80112', 'Hamilton', 'OH', '45011', '-104.8571368', '39.5835785', '-84.5117321', '39.4296413', '[{"height": "13.5 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "100 ft."}, {"width": "12 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '4', '2017-05-18 11:31:51', '2017-05-18 11:35:35', '[{"29": "John Opoku"}]', NULL );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '4', '36', 'Indianapolis', 'IN', '46077', 'Springfield', 'MO', '65619', '-86.2767995', '39.9831886', '-93.3993751', '37.1415852', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "90 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '4', '2017-05-20 09:53:11', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-01-26' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '5', '36', 'Lafayette', 'IN', '47901', 'San Antonio', 'TX', '78006', '-86.8897655', '40.4175762', '-98.704075', '29.9173682', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "90 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '4', '2017-05-20 10:08:29', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-03-17' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '6', '36', 'Lafayette', 'IN', '47901', 'Detroit', 'MI', '48127', '-86.8897655', '40.4175762', '-83.2827093', '42.3446054', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "90 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '26', '2017-05-20 10:11:19', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-03-17' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '7', '36', 'Indianapolis', 'IN', '46077', 'Milwaukee', 'WI', '53172', '-86.2767995', '39.9831886', '-87.8647961', '42.9121206', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "90 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '2', '2017-05-20 10:14:25', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-03-28' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '8', '36', 'Indianapolis', 'IN', '46077', 'San Antonio', 'TX', '78006', '-86.2767995', '39.9831886', '-98.704075', '29.9173682', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "90 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '20', '2017-05-20 10:22:43', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-04-13' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '9', '36', 'Terra Haute', 'IN', '47801', 'Houston', 'TX', '77001', '-87.41', '39.47', '-95.37', '29.77', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "53 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '8', '2017-05-20 10:45:49', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-04-14' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '10', '36', 'Indianapolis', 'IN', '46077', 'Akron', 'OH', '44223', '-86.2767995', '39.9831886', '-81.521241', '41.1740315', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "53 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '13', '2017-05-20 11:01:05', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-04-17' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '11', '36', 'Memphis', 'TN', '37501', 'Atlanta', 'GA', '30301', '-89.9377309', '35.0035156', '-84.3899963', '33.7600008', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "53 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '3', '2017-05-20 11:03:31', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-04-25' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '12', '36', 'Indianapolis', 'IN', '46077', 'Chicago', 'IL', '60007', '-86.2767995', '39.9831886', '-88.0020589', '42.0113617', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Swing"}, {"railable": "Yes"}, {"length": "53 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '18', '2017-05-20 11:05:13', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-04-26' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '13', '36', 'Indianapolis', 'IN', '46077', 'Chicago', 'IL', '60007', '-86.2767995', '39.9831886', '-88.0020589', '42.0113617', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Swing"}, {"railable": "Yes"}, {"length": "53 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '5', '2017-05-20 11:06:32', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-05-01' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '14', '36', 'Pageland', 'SC', '29728', 'Baltimore', 'MD', '21201', '-80.3917315', '34.7732102', '-76.6210539', '39.2963369', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Roll"}, {"railable": "Yes"}, {"length": "53 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '4', '2017-05-20 11:08:27', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-05-02' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '15', '36', 'Columbus', 'OH', '43004', 'St. Louis', 'MO', '63101', '-82.8101975', '40.0144647', '-90.1928216', '38.6305392', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Swing"}, {"railable": "Yes"}, {"length": "53 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '1', '2017-05-20 11:10:06', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-05-08' );
INSERT INTO `customer_needs`(`id`,`entityID`,`originationCity`,`originationState`,`originationZip`,`destinationCity`,`destinationState`,`destinationZip`,`originationLng`,`originationLat`,`destinationLng`,`destinationLat`,`needsDataPoints`,`status`,`qty`,`createdAt`,`updatedAt`,`contactEmails`,`availableDate`) VALUES ( '16', '36', 'Albany', 'NY', '12084', 'York', 'PA', '17401', '-73.9065883', '42.7003239', '-76.736538', '39.9591553', '[{"height": "12.0 ft."}, {"dry_van": "Yes"}, {"door": "Swing"}, {"railable": "Yes"}, {"length": "53 ft."}, {"width": "10 ft."}, {"reefer": "Yes"}, {"air_ride": "Yes"}, {"floor": "Yes"}, {"carb": "Yes"}]', 'Open', '2', '2017-05-20 11:11:42', '0000-00-00 00:00:00', '[{"44": "Allison Horn"}]', '2017-05-08' );
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID10" -------------------------
-- CREATE INDEX "index_entityID10" -----------------------------
CREATE INDEX `index_entityID10` USING BTREE ON `carrier_needs`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID1" --------------------------
-- CREATE INDEX "index_entityID1" ------------------------------
CREATE INDEX `index_entityID1` USING BTREE ON `contacts`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "lnk_contact_types_contacts" ---------------
-- CREATE INDEX "lnk_contact_types_contacts" -------------------
CREATE INDEX `lnk_contact_types_contacts` USING BTREE ON `contacts`( `contactTypeID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID2" --------------------------
-- CREATE INDEX "index_entityID2" ------------------------------
CREATE INDEX `index_entityID2` USING BTREE ON `insurance_carriers`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID3" --------------------------
-- CREATE INDEX "index_entityID3" ------------------------------
CREATE INDEX `index_entityID3` USING BTREE ON `links`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_name" -------------------------------
-- CREATE INDEX "index_name" -----------------------------------
CREATE INDEX `index_name` USING BTREE ON `links`( `name` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID" ---------------------------
-- CREATE INDEX "index_entityID" -------------------------------
CREATE INDEX `index_entityID` USING BTREE ON `locations`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "lnk_location_types_locations" -------------
-- CREATE INDEX "lnk_location_types_locations" -----------------
CREATE INDEX `lnk_location_types_locations` USING BTREE ON `locations`( `locationTypeID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID9" --------------------------
-- CREATE INDEX "index_entityID9" ------------------------------
CREATE INDEX `index_entityID9` USING BTREE ON `locations_contacts`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_location_id" ------------------------
-- CREATE INDEX "index_location_id" ----------------------------
CREATE INDEX `index_location_id` USING BTREE ON `locations_contacts`( `location_id` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID4" --------------------------
-- CREATE INDEX "index_entityID4" ------------------------------
CREATE INDEX `index_entityID4` USING BTREE ON `members`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_userID" -----------------------------
-- CREATE INDEX "index_userID" ---------------------------------
CREATE INDEX `index_userID` USING BTREE ON `members`( `userID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "lnk_object_type_data_points_object_type_data_point_values" 
-- CREATE INDEX "lnk_object_type_data_points_object_type_data_point_values" 
CREATE INDEX `lnk_object_type_data_points_object_type_data_point_values` USING BTREE ON `object_type_data_point_values`( `object_type_data_point_id` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "Index_1" ----------------------------------
-- CREATE INDEX "Index_1" --------------------------------------
CREATE INDEX `Index_1` USING BTREE ON `object_type_data_points`( `objectTypeID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID5" --------------------------
-- CREATE INDEX "index_entityID5" ------------------------------
CREATE INDEX `index_entityID5` USING BTREE ON `objects`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID7" --------------------------
-- CREATE INDEX "index_entityID7" ------------------------------
CREATE INDEX `index_entityID7` USING BTREE ON `requisition_quotes`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID8" --------------------------
-- CREATE INDEX "index_entityID8" ------------------------------
CREATE INDEX `index_entityID8` USING BTREE ON `requisition_tracking`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID6" --------------------------
-- CREATE INDEX "index_entityID6" ------------------------------
CREATE INDEX `index_entityID6` USING BTREE ON `requisitions`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_objectID" ---------------------------
-- CREATE INDEX "index_objectID" -------------------------------
CREATE INDEX `index_objectID` USING BTREE ON `requisitions`( `objectID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "lnk_entities_customer_needs" --------------
-- CREATE INDEX "lnk_entities_customer_needs" ------------------
CREATE INDEX `lnk_entities_customer_needs` USING BTREE ON `customer_needs`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_contact_types_contacts" ----------------
-- CREATE LINK "lnk_contact_types_contacts" --------------------
ALTER TABLE `contacts`
	ADD CONSTRAINT `lnk_contact_types_contacts` FOREIGN KEY ( `contactTypeID` )
	REFERENCES `contact_types`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_contacts" ---------------------
-- CREATE LINK "lnk_entities_contacts" -------------------------
ALTER TABLE `contacts`
	ADD CONSTRAINT `lnk_entities_contacts` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_locations" --------------------
-- CREATE LINK "lnk_entities_locations" ------------------------
ALTER TABLE `locations`
	ADD CONSTRAINT `lnk_entities_locations` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_location_types_locations" --------------
-- CREATE LINK "lnk_location_types_locations" ------------------
ALTER TABLE `locations`
	ADD CONSTRAINT `lnk_location_types_locations` FOREIGN KEY ( `locationTypeID` )
	REFERENCES `location_types`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_members" ----------------------
-- CREATE LINK "lnk_entities_members" --------------------------
ALTER TABLE `members`
	ADD CONSTRAINT `lnk_entities_members` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_users_members" -------------------------
-- CREATE LINK "lnk_users_members" -----------------------------
ALTER TABLE `members`
	ADD CONSTRAINT `lnk_users_members` FOREIGN KEY ( `userID` )
	REFERENCES `users`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_object_type_data_points_object_type_data_point_values" 
-- CREATE LINK "lnk_object_type_data_points_object_type_data_point_values" 
ALTER TABLE `object_type_data_point_values`
	ADD CONSTRAINT `lnk_object_type_data_points_object_type_data_point_values` FOREIGN KEY ( `object_type_data_point_id` )
	REFERENCES `object_type_data_points`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_object_types_object_type_data_points" --
-- CREATE LINK "lnk_object_types_object_type_data_points" ------
ALTER TABLE `object_type_data_points`
	ADD CONSTRAINT `lnk_object_types_object_type_data_points` FOREIGN KEY ( `objectTypeID` )
	REFERENCES `object_types`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_carrier_needs" ----------------
-- CREATE LINK "lnk_entities_carrier_needs" --------------------
ALTER TABLE `carrier_needs`
	ADD CONSTRAINT `lnk_entities_carrier_needs` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_customer_needs" ---------------
-- CREATE LINK "lnk_entities_customer_needs" -------------------
ALTER TABLE `customer_needs`
	ADD CONSTRAINT `lnk_entities_customer_needs` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- ---------------------------------------------------------


