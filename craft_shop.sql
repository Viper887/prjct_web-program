-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Май 14 2026 г., 23:29
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `craft_shop`
--
CREATE DATABASE IF NOT EXISTS `craft_shop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `craft_shop`;

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `items_json` text DEFAULT NULL,
  `status` enum('new','processing','completed') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `phone`, `address`, `total_price`, `items_json`, `status`, `created_at`) VALUES
(43, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №1: вул. Ветеринарна, 22 (заїзд з вул. Європейська | Оплата: При отриманні', 213.00, '[{\"product_id\":25,\"title\":\"213\",\"price\":\"213.00\",\"quantity\":1}]', 'new', '2026-05-14 21:21:43'),
(44, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №1: вул. Ветеринарна, 22 (заїзд з вул. Європейська | Оплата: Карта', 696969.00, '[{\"product_id\":24,\"title\":\"Бурмалда\",\"price\":\"696969.00\",\"quantity\":1}]', 'new', '2026-05-14 21:22:14'),
(45, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №1: вул. Ветеринарна, 22 (заїзд з вул. Європейська | Оплата: При отриманні', 1000000.00, '[{\"product_id\":23,\"title\":\"12345678901234567890123456789012345678901234567890\",\"price\":\"1000000.00\",\"quantity\":1}]', 'new', '2026-05-14 21:24:21'),
(46, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полапи, Пункт приймання-видачі (до 30 кг): вул. Незалежності, 194 | Оплата: При отриманні', 1696969.00, '[{\"product_id\":23,\"title\":\"12345678901234567890123456789012345678901234567890\",\"price\":\"1000000.00\",\"quantity\":1},{\"product_id\":24,\"title\":\"Бурмалда\",\"price\":\"696969.00\",\"quantity\":1}]', 'new', '2026-05-14 21:24:59'),
(47, 14, 'EuroPenetrator', '+380500635285', 'Кур\'єр: м. 1234, вул. 234, буд. 234, кв. 3 | Оплата: При отриманні', 696969.00, '[{\"product_id\":24,\"title\":\"Бурмалда\",\"price\":\"696969.00\",\"quantity\":1}]', 'new', '2026-05-14 21:27:12');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `google_file_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `seller_id`, `title`, `description`, `weight`, `price`, `image_path`, `google_file_id`) VALUES
(21, 14, 'Сосисонка', NULL, NULL, 1000000.00, 'uploads/1778758905_product.png', NULL),
(24, 14, 'Бурмалда', NULL, NULL, 696969.00, 'uploads/1778759319_product.png', NULL),
(25, 14, '213', 'sdgfd', '213', 213.00, 'uploads/1778791846_product.png', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('buyer','seller') DEFAULT NULL,
  `verification_code` varchar(6) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `verification_code`, `is_verified`) VALUES
(14, 'EuroPenetrator', 'vladyslav.mizin@gmail.com', '$2y$10$hTPZnW1BnDbapGKlp5.3jucJAIdC8cMEyinbUo0oWo8uK4s4i3za.', 'seller', NULL, 0),
(17, 'Володіє', 'kakahakona@gmail.com', '$2y$10$own0.qSYbDfdMn6qdaPmyObu2qr.otVIXvqwRZFyD81YkedltnsN2', 'seller', NULL, 0),
(18, 'Relationship', 'vladyslav.mizin228@gmail.com', '$2y$10$SNLx3DSBH7yNLgwZHaihVOc9C9utg5vN35dNQqK8qwsiWKoJmq5Pa', 'buyer', NULL, 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`user_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
