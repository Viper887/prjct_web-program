-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Май 13 2026 г., 20:58
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
(19, 14, 'Mizin Vladyslav', '+380500635285', 'Нова Пошта: м. Дивізія, Пункт приймання-видачі (до 30 кг): вул. Дружби, 1/1 | Оплата: При отриманні', 12.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":1}]', 'new', '2026-05-08 09:53:32'),
(20, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №1: вул. Ветеринарна, 22 (заїзд з вул. Європейська | Оплата: При отриманні', 12.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":1}]', 'new', '2026-05-08 10:05:43'),
(21, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №1: вул. Ветеринарна, 22 (заїзд з вул. Європейська | Оплата: При отриманні', 36.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":3}]', 'new', '2026-05-09 09:25:24'),
(22, 18, 'ФІавфівфі', '+3800000000', 'Нова Пошта: м. Полтава, Відділення №24 (до 30 кг на одне місце): вул. Олександра Оксанченка, 48 | Оплата: При отриманні', 1000.00, '[{\"product_id\":13,\"title\":\"qwwer\",\"price\":\"1000.00\",\"quantity\":1}]', 'new', '2026-05-11 10:15:48'),
(23, 14, 'Mizin Vladyslav', '+3801234', 'Нова Пошта: м. Полтава, Відділення №15 (до 30 кг на одне місце): вул. Європейська, 94 | Оплата: При отриманні', 12.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":1}]', 'new', '2026-05-11 10:23:35'),
(24, 18, 'sedgher', '+380444444', 'Нова Пошта: м. Полтава, Відділення №13 (до 30 кг на одне місце): вул. Великотирнівська, 35/2 | Оплата: При отриманні', 60.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":5}]', 'new', '2026-05-11 11:23:20'),
(25, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №14 (до 30 кг на одне місце): вул. Симона Петлюри, 45 | Оплата: При отриманні', 48.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":4}]', 'new', '2026-05-12 10:52:26'),
(26, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №2 (до 200 кг): вул. Героїв ОУН, 26 | Оплата: При отриманні', 12.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":1}]', 'new', '2026-05-12 11:04:20'),
(27, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №9 (до 30 кг): вул. Гоголя, 20 | Оплата: При отриманні', 12.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":1}]', 'new', '2026-05-12 11:07:17'),
(28, 14, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №8 (до 30 кг на одне місце): бульв. Богдана Хмельницького, 21 | Оплата: При отриманні', 12.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":1}]', 'new', '2026-05-12 11:07:56'),
(29, 18, 'EuroPenetrator', '+380500635285', 'Нова Пошта: м. Полтава, Відділення №15 (до 30 кг на одне місце): вул. Європейська, 94 | Оплата: При отриманні', 12.00, '[{\"product_id\":8,\"title\":\"Сосисонка\",\"price\":\"12.00\",\"quantity\":1}]', 'new', '2026-05-13 18:54:38');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `seller_id`, `title`, `description`, `price`, `image_path`) VALUES
(8, 14, 'Сосисонка', NULL, 12.00, 'uploads/1777982579png-klev-club-f5k8-p-taksa-png-15.png'),
(13, 14, 'qwwer', NULL, 1000.00, 'uploads/1778494448_product.png'),
(14, 14, 'какащка', NULL, 10000000.00, 'uploads/1778495497_product.png');

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
(18, 'Relationship', 'vladyslav.mizin228@gmail.com', '$2y$10$SNLx3DSBH7yNLgwZHaihVOc9C9utg5vN35dNQqK8qwsiWKoJmq5Pa', 'buyer', NULL, 0),
(23, 'Курсує', 'Arizonchik337@gmail.com', '$2y$10$QfZW0EmtF/kz5HZMjbzP1.2xb/RbDXDlin2z63uOKJ03a9ZNXRbBO', 'buyer', NULL, 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
