-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Июл 27 2022 г., 19:22
-- Версия сервера: 10.4.20-MariaDB
-- Версия PHP: 8.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `cloudstudents`
--

-- --------------------------------------------------------

--
-- Структура таблицы `sharedfiles`
--

CREATE TABLE `sharedfiles` (
  `id` int(11) NOT NULL,
  `file_id` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `sharedfiles`
--

INSERT INTO `sharedfiles` (`id`, `file_id`, `user_id`, `count`) VALUES
(6, '1', '1', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `users_status` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `initial_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `user_email`, `user_password`, `users_status`, `age`, `first_name`, `initial_path`) VALUES
(2, 'ekasaitlim@gmail.com', '$2y$10$bymKNvyy6UguO8bK8Yp7IueK6IYK7sYfEBosw8O6Ez2TvobtEDfY.', 'user', 18, 'Aleksander', 'b9ca24eb5a424e34a7c8c6203cec0e42'),
(3, 'chelae1@mail.ru', '$2y$10$bIA6cnIyjOOT.yEaRa23feMtt3E2ouQ9QNJZKGhuqok/GG2.UbQq6', 'admin', 18, 'Aleksander', '6fb8e91a04ca164f74b7a5754824157c');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `sharedfiles`
--
ALTER TABLE `sharedfiles`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `sharedfiles`
--
ALTER TABLE `sharedfiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
