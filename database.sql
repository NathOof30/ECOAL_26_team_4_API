CREATE TABLE `users` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `email` varchar(255) UNIQUE,
  `password` varchar(255),
  `avatar_url` varchar(255),
  `nationality` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (now()),
  `user_type` varchar(255) DEFAULT 'user' COMMENT 'admin, editor, user'
);

CREATE TABLE `collections` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255),
  `description` text,
  `user_id` integer
);

CREATE TABLE `items` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255),
  `description` text,
  `image_url` varchar(255),
  `status` boolean DEFAULT false,
  `created_at` timestamp DEFAULT (now()),
  `collection_id` integer,
  `category1_id` integer,
  `category2_id` integer
);

CREATE TABLE `category` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255)
);

CREATE TABLE `item_criteria` (
  `id_item` integer,
  `id_criteria` integer,
  `value` integer COMMENT '0: Low, 1: Medium, 2: High'
);

CREATE TABLE `criteria` (
  `id_criteria` integer PRIMARY KEY,
  `name` varchar(255)
);

ALTER TABLE `collections` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `items` ADD FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`);

ALTER TABLE `item_criteria` ADD FOREIGN KEY (`id_item`) REFERENCES `items` (`id`);

ALTER TABLE `item_criteria` ADD FOREIGN KEY (`id_criteria`) REFERENCES `criteria` (`id_criteria`);

ALTER TABLE `items` ADD FOREIGN KEY (`category1_id`) REFERENCES `category` (`id`);

ALTER TABLE `items` ADD FOREIGN KEY (`category2_id`) REFERENCES `category` (`id`);
