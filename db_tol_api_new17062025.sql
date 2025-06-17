/*
SQLyog Community v13.1.7 (64 bit)
MySQL - 5.7.27-0ubuntu0.16.04.1-log : Database - tol_api_jwt
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*Table structure for table `api_keys` */

DROP TABLE IF EXISTS `api_keys`;

CREATE TABLE `api_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100) NOT NULL,
  `key_value` varchar(255) NOT NULL,
  `permissions` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_value` (`key_value`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `api_keys` */

insert  into `api_keys`(`id`,`client_name`,`key_value`,`permissions`,`status`,`created_at`,`updated_at`) values 
(1,'Public Mobile App','d62de94c44d784372d258f5f7f044ca64aaa819911a223c546cb05e663eaeb84','[\"read_products\",\"read_categories\"]','active','2025-06-03 09:06:21','2025-06-03 09:06:21');

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`slug`,`created_at`,`updated_at`) values 
(1,'Elektronik','elektronik','2025-06-03 09:06:21','2025-06-03 09:06:21'),
(2,'Pakaian','pakaian_kaos','2025-06-03 09:06:21','2025-06-09 09:00:58');

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`version`,`class`,`group`,`namespace`,`time`,`batch`) values 
(1,'2025-06-01-083545','App\\Database\\Migrations\\CreateUsersTable','default','App',1748916346,1);

/*Table structure for table `payments` */

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(36) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_gateway_response` text,
  `payment_status` enum('pending','success','failed') DEFAULT 'pending',
  `amount_paid` varchar(20) NOT NULL,
  `paid_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `payments` */

insert  into `payments`(`id`,`transaction_id`,`payment_method`,`payment_gateway_response`,`payment_status`,`amount_paid`,`paid_at`,`created_at`,`updated_at`) values 
(3,'8','bank_transfer',NULL,'pending','15150000','2025-06-05 06:33:44','2025-06-05 06:33:44','2025-06-05 13:33:46'),
(4,'9','bank_transfer',NULL,'pending','15150000','2025-06-05 06:38:24','2025-06-05 06:38:24','2025-06-05 13:38:26'),
(5,'10','bank_transfer',NULL,'pending','15150000','2025-06-09 06:14:56','2025-06-09 06:14:56','2025-06-09 13:14:58');

/*Table structure for table `permissions` */

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama tampilan permission, e.g., Manage Users, View Products',
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Slug untuk kode, e.g., manage-users, view-products',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `permissions` */

insert  into `permissions`(`id`,`name`,`slug`,`description`,`created_at`,`updated_at`) values 
(1,'Manage Users','manage-users','CRUD semua user','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(2,'View Users','view-users','Melihat daftar user','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(3,'Manage Roles','manage-roles','CRUD roles dan assignment permission ke role','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(4,'Manage Permissions','manage-permissions','CRUD permissions','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(5,'Manage Products','manage-products','CRUD produk','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(6,'View Products','view-products','Melihat daftar produk (publik)','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(7,'Manage Categories','manage-categories','CRUD kategori','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(8,'View Categories','view-categories','Melihat daftar kategori (publik)','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(9,'Create Transaction','create-transaction','Membuat transaksi baru (client)','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(10,'View Own Transactions','view-own-transactions','Melihat transaksi milik sendiri (client)','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(11,'Manage All Transactions','manage-all-transactions','Melihat dan mengubah status semua transaksi (admin)','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(12,'Manage API Keys','manage-api-keys','CRUD API Keys untuk aplikasi eksternal','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(13,'View Own Profile','view-own-profile','Melihat profil sendiri','2025-06-05 02:00:06','2025-06-05 02:00:06');

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` text NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `products` */

insert  into `products`(`id`,`category_id`,`name`,`slug`,`description`,`price`,`stock`,`created_at`,`updated_at`) values 
(1,1,'Laptop Super Canggih','laptop_super_canggih','Laptop dengan spek dewa.',15000000.00,44,'2025-06-03 09:06:21','2025-06-09 06:14:56'),
(2,2,'Kaos Keren Polos','kaos_keren_polos','Kaos bahan katun adem.',150000.00,34,'2025-06-03 09:06:21','2025-06-09 06:14:56');

/*Table structure for table `role_permissions` */

DROP TABLE IF EXISTS `role_permissions`;

CREATE TABLE `role_permissions` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `role_permissions` */

insert  into `role_permissions`(`role_id`,`permission_id`) values 
(1,1),
(1,2),
(1,3),
(1,4),
(1,5),
(1,6),
(2,6),
(1,7),
(1,8),
(2,8),
(1,9),
(2,9),
(1,10),
(2,10),
(1,11),
(1,12),
(1,13),
(2,13);

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama peran, e.g., Super Admin, Client, Editor',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `roles` */

insert  into `roles`(`id`,`name`,`description`,`created_at`,`updated_at`) values 
(1,'Super Admin','Memiliki semua hak akses','2025-06-05 02:00:06','2025-06-05 02:00:06'),
(2,'Client','Pengguna terdaftar standar','2025-06-05 02:00:06','2025-06-05 02:00:06');

/*Table structure for table `transaction_details` */

DROP TABLE IF EXISTS `transaction_details`;

CREATE TABLE `transaction_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

/*Data for the table `transaction_details` */

insert  into `transaction_details`(`id`,`transaction_id`,`product_id`,`quantity`,`price_per_unit`,`subtotal`) values 
(15,8,1,1,15000000.00,15000000.00),
(16,8,2,1,150000.00,150000.00),
(17,9,1,1,15000000.00,15000000.00),
(18,9,2,1,150000.00,150000.00),
(19,10,1,1,15000000.00,15000000.00),
(20,10,2,1,150000.00,150000.00);

/*Table structure for table `transaction_history_log` */

DROP TABLE IF EXISTS `transaction_history_log`;

CREATE TABLE `transaction_history_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(32) NOT NULL,
  `user_id` int(15) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Data for the table `transaction_history_log` */

insert  into `transaction_history_log`(`id`,`transaction_id`,`user_id`,`action`,`details`,`created_at`) values 
(1,'3f24e9202f6a4624b5da0b20fc55cbcc',2,'Transaction Created','{\"items_count\":2}','2025-06-17 03:43:41'),
(2,'1a68a885ce7d4638bef82fd4783da26c',2,'Transaction Created','{\"items_count\":2}','2025-06-17 03:45:09'),
(3,'3076b390d8524b7a9d6602a594bbe005',2,'Transaction Created','{\"items_count\":2}','2025-06-17 06:05:16');

/*Table structure for table `transaction_pr_h_apis` */

DROP TABLE IF EXISTS `transaction_pr_h_apis`;

CREATE TABLE `transaction_pr_h_apis` (
  `id` varchar(32) NOT NULL,
  `no_po` varchar(30) NOT NULL,
  `customer_id` varchar(32) DEFAULT NULL,
  `nama_customer` varchar(40) DEFAULT NULL,
  `hanya_jasa` tinyint(1) DEFAULT '0',
  `jenis_lensa` varchar(2) DEFAULT NULL,
  `tgl_id` date DEFAULT '1900-01-01',
  `r_lensa` varchar(12) DEFAULT NULL,
  `r_nama_lensa` varchar(150) DEFAULT NULL,
  `r_spheris` varchar(6) DEFAULT NULL,
  `r_cylinder` varchar(6) DEFAULT NULL,
  `r_bcurve` varchar(10) DEFAULT NULL,
  `r_axis` varchar(5) DEFAULT NULL,
  `r_additional` varchar(5) DEFAULT NULL,
  `r_pd_far` float(3,1) DEFAULT NULL,
  `r_pd_near` float(3,1) DEFAULT NULL,
  `r_prisma` varchar(5) DEFAULT NULL,
  `r_base` varchar(4) DEFAULT NULL,
  `r_prisma2` varchar(5) DEFAULT NULL,
  `r_base2` varchar(4) DEFAULT NULL,
  `r_base_curve` varchar(10) DEFAULT NULL COMMENT 'Lookup Dari Master Group Lensa',
  `r_edge_thickness` decimal(5,2) DEFAULT NULL,
  `r_center_thickness` decimal(5,2) DEFAULT NULL,
  `r_qty` smallint(4) DEFAULT '0',
  `l_lensa` varchar(12) DEFAULT NULL,
  `l_nama_lensa` varchar(150) DEFAULT NULL,
  `l_spheris` varchar(6) DEFAULT NULL,
  `l_cylinder` varchar(6) DEFAULT NULL,
  `l_bcurve` varchar(10) DEFAULT NULL,
  `l_axis` varchar(5) DEFAULT NULL,
  `l_additional` varchar(5) DEFAULT NULL,
  `l_pd_far` float(3,1) DEFAULT NULL,
  `l_pd_near` float(3,1) DEFAULT NULL,
  `l_prisma` varchar(5) DEFAULT NULL,
  `l_base` varchar(4) DEFAULT NULL,
  `l_prisma2` varchar(5) DEFAULT NULL,
  `l_base2` varchar(4) DEFAULT NULL,
  `l_base_curve` varchar(10) DEFAULT NULL,
  `l_edge_thickness` decimal(5,2) DEFAULT NULL,
  `l_center_thickness` decimal(5,2) DEFAULT NULL,
  `l_qty` smallint(4) DEFAULT '0',
  `total_pdf` double(4,1) DEFAULT '0.0',
  `total_pdn` double(4,1) DEFAULT '0.0',
  `effectif_diameter` smallint(2) DEFAULT NULL,
  `lens_size` smallint(2) DEFAULT NULL,
  `bridge_size` smallint(2) DEFAULT NULL,
  `seg_height` smallint(2) DEFAULT NULL,
  `mbs` smallint(2) DEFAULT NULL,
  `vertical` decimal(5,2) DEFAULT NULL,
  `accessories` varchar(8) DEFAULT NULL,
  `spesial_instruksi` varchar(60) DEFAULT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `frame_status` varchar(20) DEFAULT NULL,
  `note` varchar(100) DEFAULT NULL,
  `model` smallint(6) DEFAULT NULL,
  `jenis_frame` varchar(25) DEFAULT NULL,
  `wa` int(11) DEFAULT '5',
  `pt` int(11) DEFAULT '9',
  `bvd` int(11) DEFAULT '12',
  `ffv` int(11) DEFAULT NULL,
  `rd` decimal(6,3) DEFAULT NULL,
  `max_id` int(4) DEFAULT '0',
  `v_code` int(4) DEFAULT NULL,
  `pe` varchar(5) DEFAULT NULL,
  `koridor` varchar(10) DEFAULT NULL COMMENT 'SHORT, LONG',
  `finish_diameter` int(11) DEFAULT NULL,
  `pic_input` varchar(9) DEFAULT NULL,
  `wkt_input` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Idx_NoRef` (`customer_id`,`no_po`),
  KEY `customer_id` (`customer_id`),
  KEY `no_ref` (`no_po`),
  KEY `tgl_pr` (`tgl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `transaction_pr_h_apis` */

insert  into `transaction_pr_h_apis`(`id`,`no_po`,`customer_id`,`nama_customer`,`hanya_jasa`,`jenis_lensa`,`tgl_id`,`r_lensa`,`r_nama_lensa`,`r_spheris`,`r_cylinder`,`r_bcurve`,`r_axis`,`r_additional`,`r_pd_far`,`r_pd_near`,`r_prisma`,`r_base`,`r_prisma2`,`r_base2`,`r_base_curve`,`r_edge_thickness`,`r_center_thickness`,`r_qty`,`l_lensa`,`l_nama_lensa`,`l_spheris`,`l_cylinder`,`l_bcurve`,`l_axis`,`l_additional`,`l_pd_far`,`l_pd_near`,`l_prisma`,`l_base`,`l_prisma2`,`l_base2`,`l_base_curve`,`l_edge_thickness`,`l_center_thickness`,`l_qty`,`total_pdf`,`total_pdn`,`effectif_diameter`,`lens_size`,`bridge_size`,`seg_height`,`mbs`,`vertical`,`accessories`,`spesial_instruksi`,`keterangan`,`frame_status`,`note`,`model`,`jenis_frame`,`wa`,`pt`,`bvd`,`ffv`,`rd`,`max_id`,`v_code`,`pe`,`koridor`,`finish_diameter`,`pic_input`,`wkt_input`,`updated_at`) values 
('1a68a885ce7d4638bef82fd4783da26c','INV/2025-06-1002','001002','EYE SOUL',1,'PG','2025-06-17','50001',NULL,'-0.50','-2.00','0','5','+1.50',0.0,0.0,'0','0','0','0','0',0.00,0.00,1,'91412',NULL,'-0.50','-2.00','0','5','+1.50',0.0,0.0,'0','0','0','0','0',0.00,0.00,1,4.0,10.0,4,5,4,5,12,3.00,'0','ini spesial instruksi','ini Keterangan','E','ini Data Note',2,'BOR',0,0,0,0,0.000,1001,0,'0','SHORT',0,'001002','2025-06-17 03:45:09','2025-06-17 03:45:09'),
('3076b390d8524b7a9d6602a594bbe005','INV/2025-06-1003','001002','EYE SOUL',1,'PG','2025-06-17','50001',NULL,'-0.50','-2.00','0','5','+1.50',0.0,0.0,'0','0','0','0','0',0.00,0.00,1,'91412',NULL,'-0.50','-2.00','0','5','+1.50',0.0,0.0,'0','0','0','0','0',0.00,0.00,1,4.0,10.0,4,5,4,5,12,3.00,'0','ini spesial instruksi','ini Keterangan','E','ini Data Note',2,'BOR',0,0,0,0,0.000,1001,0,'0','SHORT',0,'2','2025-06-17 06:05:16','2025-06-17 06:05:16'),
('3f24e9202f6a4624b5da0b20fc55cbcc','INV/2025-06-1001','001003','EYE SOUL',1,'PG','2025-06-17','50001',NULL,'-0.50','-2.00','0','5','+1.50',0.0,0.0,'0','0','0','0','0',0.00,0.00,1,'91412',NULL,'-0.50','-2.00','0','5','+1.50',0.0,0.0,'0','0','0','0','0',0.00,0.00,1,4.0,10.0,4,5,4,5,12,3.00,'0','ini spesial instruksi','ini Keterangan','E','ini Data Note',2,'BOR',0,0,0,0,0.000,1001,0,'0','SHORT',0,'001002','2025-06-17 03:43:41','2025-06-17 03:43:41');

/*Table structure for table `transaction_pr_jasa_d_apis` */

DROP TABLE IF EXISTS `transaction_pr_jasa_d_apis`;

CREATE TABLE `transaction_pr_jasa_d_apis` (
  `jasa_id` varchar(32) NOT NULL,
  `id` varchar(32) NOT NULL,
  `qty` smallint(4) DEFAULT '0',
  `wkt_input` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` date DEFAULT NULL,
  PRIMARY KEY (`jasa_id`,`id`),
  UNIQUE KEY `pr_h_id` (`id`,`jasa_id`),
  KEY `FK_trn_pr_jasa_d_trn_pr_h` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `transaction_pr_jasa_d_apis` */

insert  into `transaction_pr_jasa_d_apis`(`jasa_id`,`id`,`qty`,`wkt_input`,`updated_at`) values 
('0012','1a68a885ce7d4638bef82fd4783da26c',2,'2025-06-17 03:45:09','2025-06-17'),
('0012','3076b390d8524b7a9d6602a594bbe005',2,'2025-06-17 06:05:16','2025-06-17'),
('0012','3f24e9202f6a4624b5da0b20fc55cbcc',2,'2025-06-17 03:43:41','2025-06-17'),
('0014','1a68a885ce7d4638bef82fd4783da26c',2,'2025-06-17 03:45:09','2025-06-17'),
('0014','3076b390d8524b7a9d6602a594bbe005',2,'2025-06-17 06:05:16','2025-06-17'),
('0014','3f24e9202f6a4624b5da0b20fc55cbcc',2,'2025-06-17 03:43:41','2025-06-17');

/*Table structure for table `transactions` */

DROP TABLE IF EXISTS `transactions`;

CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `transaction_code` varchar(50) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','paid','failed','shipped','completed','cancelled') DEFAULT 'pending',
  `transaction_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_code` (`transaction_code`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

/*Data for the table `transactions` */

insert  into `transactions`(`id`,`user_id`,`transaction_code`,`total_amount`,`status`,`transaction_date`,`created_at`,`updated_at`) values 
(8,2,'INV/20250605/DBIFSR',15150000.00,'pending','2025-06-05 06:33:44','2025-06-05 13:33:46','2025-06-05 06:33:44'),
(9,2,'INV/20250605/IL4NEY',15150000.00,'pending','2025-06-05 06:38:24','2025-06-05 13:38:26','2025-06-05 06:38:24'),
(10,2,'INV/20250609/5ZEPIM',15150000.00,'pending','2025-06-09 06:14:56','2025-06-09 13:14:58','2025-06-09 06:14:56');

/*Table structure for table `trn_pr_h` */

DROP TABLE IF EXISTS `trn_pr_h`;

CREATE TABLE `trn_pr_h` (
  `pr_h_id` varchar(32) NOT NULL,
  `no_pr` varchar(15) NOT NULL,
  `tgl_pr` date DEFAULT '1900-01-01',
  `company_id` varchar(32) DEFAULT NULL,
  `customer_id` varchar(32) DEFAULT NULL,
  `end_customer_id` varchar(10) DEFAULT NULL,
  `customer_disc_h_id` varchar(32) DEFAULT NULL,
  `nama_customer` varchar(40) DEFAULT NULL,
  `salesman_id` varchar(32) DEFAULT NULL,
  `waktu_ready` datetime DEFAULT '1900-01-01 00:00:00',
  `kode_dokter` varchar(10) DEFAULT NULL,
  `nama_dokter` varchar(60) DEFAULT NULL,
  `hanya_jasa` tinyint(1) DEFAULT '0',
  `foc` tinyint(1) DEFAULT '0',
  `foc_khusus` tinyint(1) DEFAULT '0',
  `potong_komisi` tinyint(1) NOT NULL DEFAULT '1',
  `is_promo` tinyint(1) NOT NULL DEFAULT '0',
  `promo_h_id` int(11) DEFAULT NULL,
  `spk_id` varchar(32) DEFAULT NULL,
  `lensa_id` date DEFAULT '1900-01-01',
  `no_ref` varchar(30) NOT NULL,
  `no_ref_PR` varchar(30) DEFAULT NULL,
  `jenis_lensa` varchar(2) DEFAULT NULL,
  `r_lensa` varchar(12) DEFAULT NULL,
  `r_nama_lensa` varchar(150) DEFAULT NULL,
  `r_spheris` varchar(6) DEFAULT NULL,
  `r_cylinder` varchar(6) DEFAULT NULL,
  `r_bcurve` varchar(10) DEFAULT NULL,
  `r_lensa_8digit` varchar(8) DEFAULT NULL,
  `r_axis` varchar(5) DEFAULT NULL,
  `r_additional` varchar(5) DEFAULT NULL,
  `r_pd_far` float(3,1) DEFAULT NULL,
  `r_pd_near` float(3,1) DEFAULT NULL,
  `r_prisma` varchar(5) DEFAULT NULL,
  `r_base` varchar(4) DEFAULT NULL,
  `r_prisma_2` varchar(5) DEFAULT NULL,
  `r_base_2` varchar(4) DEFAULT NULL,
  `r_qty` smallint(4) DEFAULT '0',
  `r_harga` decimal(16,2) DEFAULT '0.00',
  `r_diskon_persen` decimal(6,3) DEFAULT '0.000',
  `r_diskon_total` decimal(12,2) DEFAULT '0.00',
  `r_total` decimal(12,2) DEFAULT '0.00',
  `l_lensa` varchar(12) DEFAULT NULL,
  `l_nama_lensa` varchar(150) DEFAULT NULL,
  `l_spheris` varchar(6) DEFAULT NULL,
  `l_cylinder` varchar(6) DEFAULT NULL,
  `l_bcurve` varchar(10) DEFAULT NULL,
  `l_lensa_8digit` varchar(8) DEFAULT NULL,
  `l_axis` varchar(5) DEFAULT NULL,
  `l_additional` varchar(5) DEFAULT NULL,
  `l_pd_far` float(3,1) DEFAULT NULL,
  `l_pd_near` float(3,1) DEFAULT NULL,
  `l_prisma` varchar(5) DEFAULT NULL,
  `l_base` varchar(4) DEFAULT NULL,
  `l_prisma_2` varchar(5) DEFAULT NULL,
  `l_base_2` varchar(4) DEFAULT NULL,
  `l_qty` smallint(4) DEFAULT '0',
  `l_harga` decimal(16,2) DEFAULT '0.00',
  `l_diskon_persen` decimal(6,3) DEFAULT '0.000',
  `l_diskon_total` decimal(12,2) DEFAULT '0.00',
  `l_total` decimal(12,2) DEFAULT '0.00',
  `effectif_diameter` smallint(2) DEFAULT NULL,
  `lens_size` smallint(2) DEFAULT NULL,
  `bridge_size` smallint(2) DEFAULT NULL,
  `seg_height` smallint(2) DEFAULT NULL,
  `mbs` smallint(2) DEFAULT NULL,
  `vertical` decimal(5,2) DEFAULT NULL,
  `accessories` varchar(8) DEFAULT NULL,
  `spesial_instruksi` varchar(60) DEFAULT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `frame_status` varchar(20) DEFAULT NULL,
  `kd_frame` char(14) DEFAULT NULL,
  `frame_name` varchar(60) DEFAULT NULL,
  `frame_condition` varchar(60) DEFAULT NULL,
  `notes_alamat_kirim` varchar(150) DEFAULT NULL,
  `nomor_case` varchar(10) DEFAULT NULL,
  `pic_case` varchar(20) DEFAULT NULL,
  `wkt_case` datetime DEFAULT NULL,
  `store_tujuan` varchar(6) DEFAULT NULL,
  `store_ambil` varchar(6) DEFAULT NULL,
  `note` varchar(100) DEFAULT NULL,
  `model` smallint(6) DEFAULT NULL,
  `jenis_frame` varchar(25) DEFAULT NULL,
  `total_akhir` decimal(12,2) DEFAULT '0.00',
  `wkt_kirim` datetime DEFAULT NULL,
  `is_ready` tinyint(1) DEFAULT '0',
  `is_stb` tinyint(1) DEFAULT '0',
  `is_case` tinyint(1) DEFAULT '0',
  `is_spk` tinyint(1) DEFAULT '0',
  `is_proses` tinyint(1) NOT NULL DEFAULT '0',
  `is_batal` tinyint(1) NOT NULL DEFAULT '0',
  `pic_batal` varchar(9) DEFAULT NULL,
  `wkt_batal` datetime DEFAULT NULL,
  `keterangan_batal` varchar(50) DEFAULT NULL,
  `is_urgent` tinyint(1) DEFAULT NULL,
  `pic_input` varchar(9) DEFAULT NULL,
  `wkt_input` datetime DEFAULT NULL,
  `nama_proses` varchar(50) DEFAULT 'PRS',
  `pic_proses` varchar(9) DEFAULT NULL,
  `wkt_proses` datetime DEFAULT NULL,
  `pic_edit` varchar(9) DEFAULT NULL,
  `wkt_edit` datetime DEFAULT NULL,
  `cetak_garansi_ke` int(11) DEFAULT '0',
  `pic_cetak_garansi` varchar(9) DEFAULT NULL,
  `wkt_cetak_garansi` datetime DEFAULT NULL,
  `pic_terima_garansi` varchar(9) DEFAULT NULL,
  `wkt_terima_garansi` datetime DEFAULT NULL,
  `wkt_print_garansi` datetime DEFAULT NULL,
  `is_bkb` tinyint(1) DEFAULT '0',
  `r_brg_id_bkb` varchar(32) DEFAULT NULL,
  `l_brg_id_bkb` varchar(32) DEFAULT NULL,
  `is_prd` tinyint(1) DEFAULT '0',
  `no_order_indent` varchar(15) DEFAULT NULL,
  `r_nama_lensa_bkb` varchar(150) DEFAULT NULL,
  `l_nama_lensa_bkb` varchar(150) DEFAULT NULL,
  `is_komplain` tinyint(1) NOT NULL DEFAULT '0',
  `wa` int(11) DEFAULT '5',
  `pt` int(11) DEFAULT '9',
  `bvd` int(11) DEFAULT '12',
  `ffv` int(11) DEFAULT NULL,
  `rd` decimal(6,3) DEFAULT NULL,
  `max_id` int(4) DEFAULT '0',
  `v_code` int(4) DEFAULT NULL,
  `pe` varchar(5) DEFAULT NULL,
  `total_pdf` double(4,1) DEFAULT '0.0',
  `total_pdn` double(4,1) DEFAULT '0.0',
  `jns_entry` varchar(3) DEFAULT NULL COMMENT 'DEI = Desktop Entry Independent, ORE = Order Taker Entry, TRF = Transfer NP, WOE = Web Online Entry',
  `koridor` varchar(10) DEFAULT NULL COMMENT 'SHORT, LONG',
  `r_base_curve` varchar(10) DEFAULT NULL COMMENT 'Lookup Dari Master Group Lensa',
  `l_base_curve` varchar(10) DEFAULT NULL,
  `is_cetak` tinyint(1) DEFAULT '0',
  `ekspedisi` varchar(20) DEFAULT NULL,
  `is_lunas` tinyint(1) DEFAULT '0',
  `no_np` varchar(16) DEFAULT NULL,
  `jenis_npp` varchar(10) DEFAULT NULL COMMENT 'REGULER, WARRANTY',
  `is_sample` tinyint(1) DEFAULT '0',
  `is_garansi` tinyint(1) DEFAULT '0',
  `is_rework` tinyint(1) DEFAULT '0',
  `jenis_prs` varchar(2) DEFAULT NULL,
  `kd_prod` varchar(30) DEFAULT NULL,
  `is_resize` tinyint(1) DEFAULT '0',
  `finish_diameter` int(11) DEFAULT NULL,
  `r_edge_thickness` decimal(5,2) DEFAULT NULL,
  `l_edge_thickness` decimal(5,2) DEFAULT NULL,
  `r_center_thickness` decimal(5,2) DEFAULT NULL,
  `l_center_thickness` decimal(5,2) DEFAULT NULL,
  `wkt_prosess` datetime DEFAULT NULL,
  PRIMARY KEY (`pr_h_id`),
  UNIQUE KEY `no_pr` (`no_pr`),
  UNIQUE KEY `Idx_NoRef` (`customer_id`,`no_ref`,`no_pr`),
  KEY `FK_trn_pr_h` (`company_id`),
  KEY `no_ref` (`no_ref`),
  KEY `no_np` (`no_np`),
  KEY `Index 7` (`spk_id`),
  KEY `tgl_pr` (`tgl_pr`,`no_pr`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `trn_pr_h` */

/*Table structure for table `user_roles` */

DROP TABLE IF EXISTS `user_roles`;

CREATE TABLE `user_roles` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `user_roles` */

insert  into `user_roles`(`user_id`,`role_id`) values 
(1,1),
(2,2);

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `kode_customer` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') NOT NULL DEFAULT 'client',
  `api_key` varchar(255) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

/*Data for the table `users` */

insert  into `users`(`id`,`kode_customer`,`name`,`email`,`password`,`role`,`api_key`,`reset_token`,`reset_expires`,`created_at`,`updated_at`) values 
(1,'0','Admin','admin@example.com','$2y$10$ZO4I/bJkX1afeR1/e2uCEusLUg3jDlaqn7DCEYSXwi8PhcTyeUFEO','admin',NULL,NULL,NULL,'2025-06-03 03:00:35','2025-06-03 03:00:35'),
(2,'001002','EYE SOUL','client@example.com','$2y$10$DflhQjqxr0L4PxPBdngURuOBd1YcqOE3OUkjD9MsD6VadIdG0pRT6','client',NULL,'zdMEhPRzRni0gxNXMrXp83VoZvjCwIMJQlc3idJI4tMmceegYm4uVMnS05r0','2025-06-03 05:51:10','2025-06-03 03:01:00','2025-06-03 04:51:10');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
