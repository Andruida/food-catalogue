SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `deployment` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `deployment_user` (
  `id` int(11) UNSIGNED NOT NULL,
  `deployment_id` int(11) UNSIGNED DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `dishcombo` (
  `id` int(11) UNSIGNED NOT NULL,
  `main_course_id` int(11) UNSIGNED DEFAULT NULL,
  `side_dish_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `food` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(191) DEFAULT NULL,
  `foodtype_id` int(11) UNSIGNED DEFAULT NULL,
  `deployment_id` int(11) UNSIGNED DEFAULT NULL,
  `mealtype_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `foodtype` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `foodtype` (`id`, `name`) VALUES
(1, 'SOUP'),
(2, 'MAINCOURSE'),
(3, 'SIDEDISH'),
(4, 'DESSERT'),
(5, 'MAIN_COURSE');

CREATE TABLE `log` (
  `id` int(11) UNSIGNED NOT NULL,
  `date` date DEFAULT NULL,
  `soup_id` int(11) UNSIGNED DEFAULT NULL,
  `dishcombo_id` int(11) UNSIGNED DEFAULT NULL,
  `dessert_id` int(11) UNSIGNED DEFAULT NULL,
  `mealtype_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `mealtype` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `mealtype` (`id`, `name`) VALUES
(1, 'BREAKFAST'),
(2, 'LUNCH'),
(3, 'BREAKFAST+LUNCH'),
(4, 'DINNER'),
(5, 'BREAKFAST+DINNER'),
(6, 'LUNCH+DINNER'),
(7, 'BREAKFAST+LUNCH+DINNER');

CREATE TABLE `rating` (
  `id` int(11) UNSIGNED NOT NULL,
  `food_id` int(11) UNSIGNED DEFAULT NULL,
  `dishcombo_id` int(11) UNSIGNED DEFAULT NULL,
  `rating` decimal(10,2) DEFAULT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE `user` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(191) DEFAULT NULL,
  `name` varchar(191) DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  `last_deployment_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


ALTER TABLE `deployment`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `deployment_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_b6e63b31e2c95f8dde2061ab578ec260653344a8` (`deployment_id`,`user_id`),
  ADD KEY `index_foreignkey_deployment_user_deployment` (`deployment_id`),
  ADD KEY `index_foreignkey_deployment_user_user` (`user_id`);

ALTER TABLE `dishcombo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_main_course_id_side_dish_id` (`main_course_id`,`side_dish_id`),
  ADD KEY `index_foreignkey_dishcombo_main_course` (`main_course_id`),
  ADD KEY `index_foreignkey_dishcombo_side_dish` (`side_dish_id`);

ALTER TABLE `food`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_name_type_deployment` (`name`,`foodtype_id`,`deployment_id`),
  ADD KEY `index_foreignkey_food_type` (`foodtype_id`),
  ADD KEY `index_foreignkey_food_deployment` (`deployment_id`),
  ADD KEY `index_foreignkey_food_mealtype` (`mealtype_id`);

ALTER TABLE `foodtype`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_foreignkey_log_soup` (`soup_id`),
  ADD KEY `index_foreignkey_log_dessert` (`dessert_id`),
  ADD KEY `index_foreignkey_log_dishcombo` (`dishcombo_id`),
  ADD KEY `index_foreignkey_log_mealtype` (`mealtype_id`);

ALTER TABLE `mealtype`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_food_dishcombo_user` (`food_id`,`dishcombo_id`,`user_id`),
  ADD KEY `index_foreignkey_rating_dishcombo` (`dishcombo_id`),
  ADD KEY `index_foreignkey_rating_food` (`food_id`),
  ADD KEY `index_foreignkey_rating_user` (`user_id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `index_foreignkey_user_deployment` (`last_deployment_id`);


ALTER TABLE `deployment`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `deployment_user`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `dishcombo`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `food`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `foodtype`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `mealtype`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `rating`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `deployment_user`
  ADD CONSTRAINT `c_fk_deployment_user_deployment_id` FOREIGN KEY (`deployment_id`) REFERENCES `deployment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_deployment_user_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `dishcombo`
  ADD CONSTRAINT `c_fk_dishcombo_main_course_id` FOREIGN KEY (`main_course_id`) REFERENCES `food` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_dishcombo_side_dish_id` FOREIGN KEY (`side_dish_id`) REFERENCES `food` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `food`
  ADD CONSTRAINT `c_fk_food_deployment_id` FOREIGN KEY (`deployment_id`) REFERENCES `deployment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_food_mealtype_id` FOREIGN KEY (`mealtype_id`) REFERENCES `mealtype` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_food_type_id` FOREIGN KEY (`foodtype_id`) REFERENCES `foodtype` (`id`) ON UPDATE CASCADE;

ALTER TABLE `log`
  ADD CONSTRAINT `c_fk_log_dessert_id` FOREIGN KEY (`dessert_id`) REFERENCES `food` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_log_dishcombo_id` FOREIGN KEY (`dishcombo_id`) REFERENCES `dishcombo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_log_mealtype_id` FOREIGN KEY (`mealtype_id`) REFERENCES `mealtype` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_log_soup_id` FOREIGN KEY (`soup_id`) REFERENCES `food` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `rating`
  ADD CONSTRAINT `c_fk_rating_dishcombo_id` FOREIGN KEY (`dishcombo_id`) REFERENCES `dishcombo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_rating_food_id` FOREIGN KEY (`food_id`) REFERENCES `food` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `c_fk_rating_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `user`
  ADD CONSTRAINT `c_fk_user_deployment_id` FOREIGN KEY (`last_deployment_id`) REFERENCES `deployment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
