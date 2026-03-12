CREATE TABLE `users` (
  `id_user` integer PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `email` varchar(255) UNIQUE,
  `password` varchar(255),
  `avatar_url` varchar(255),
  `nationality` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (now()),
  `user_type` varchar(255) DEFAULT 'user' COMMENT 'admin, editor, user'
);

CREATE TABLE `category` (
  `id_category` integer PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255)
);

CREATE TABLE `criteria` (
  `id_criteria` integer PRIMARY KEY,
  `name` varchar(255)
);

CREATE TABLE `collections` (
  `id_collections` integer PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255),
  `description` text,
  `user_id` integer,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`)
);

CREATE TABLE `items` (
  `id_items` integer PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255),
  `description` text,
  `image_url` varchar(255),
  `status` boolean DEFAULT false,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `collections_items` (
  `id_collection` integer NOT NULL,
  `id_item` integer NOT NULL,
  PRIMARY KEY (`id_item`),
  UNIQUE KEY `collections_items_collection_item_unique` (`id_collection`, `id_item`),
  FOREIGN KEY (`id_collection`) REFERENCES `collections` (`id_collections`),
  FOREIGN KEY (`id_item`) REFERENCES `items` (`id_items`)
);

CREATE TABLE `items_categories` (
  `id_item` integer NOT NULL,
  `id_category` integer NOT NULL,
  PRIMARY KEY (`id_item`, `id_category`),
  FOREIGN KEY (`id_item`) REFERENCES `items` (`id_items`),
  FOREIGN KEY (`id_category`) REFERENCES `category` (`id_category`)
);

CREATE TABLE `item_criteria` (
  `id_item` integer,
  `id_criteria` integer,
  `value` integer COMMENT '0: Low, 1: Medium, 2: High',
  FOREIGN KEY (`id_item`) REFERENCES `items` (`id_items`),
  FOREIGN KEY (`id_criteria`) REFERENCES `criteria` (`id_criteria`)
);
