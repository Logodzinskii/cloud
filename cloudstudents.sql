-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Авг 02 2022 г., 20:47
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
(24, 'chelae1@mail.ru', '$2y$10$OUh9EJPvu/JfzvIez1dJrei3FNtpI20kMxGuFE2ky1ITMQncHXL86', 'admin', 18, 'Aleksander', 'ab20568ce97e06b9abcda5643d6389a9');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
