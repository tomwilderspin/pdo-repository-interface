CREATE DATABASE IF NOT EXISTS `store` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


-- tables --

CREATE TABLE IF NOT EXISTS `store`.`products` (
`id` INT NOT NULL AUTO_INCREMENT,
`product_sku` INT NOT NULL,
`name` VARCHAR(150) NOT NULL,
`price` VARCHAR(10) NOT NULL,
`description` INT NOT NULL,
`image_reference` VARCHAR(2000),
`date_added` INT NOT NULL,
PRIMARY KEY (`id`),
UNIQUE INDEX `product_sku` (`product_sku` ASC),
INDEX `date_added` (`date_added` ASC));