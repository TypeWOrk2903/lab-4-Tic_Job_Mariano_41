-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 12/05/2026 às 06:20
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `webmovies`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tmdb_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `tmdb_id`, `title`, `poster_path`, `created_at`) VALUES
(3, 3, 27205, 'Interstellar', '/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', '2026-05-11 21:45:33'),
(5, 3, 550, 'Fight Club', '/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg', '2026-05-11 21:46:47'),
(6, 3, 269149, 'Zootopia: Essa Cidade é o Bicho', '/ncqVGCX9P3EKQfsOXiB3XxQ0Dip.jpg', '2026-05-12 04:07:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `generos`
--

CREATE TABLE `generos` (
  `id` int(10) UNSIGNED NOT NULL,
  `tmdb_id` int(11) NOT NULL,
  `name_pt` varchar(80) NOT NULL,
  `name_en` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `generos`
--

INSERT INTO `generos` (`id`, `tmdb_id`, `name_pt`, `name_en`, `slug`, `created_at`) VALUES
(1, 28, 'Ação', 'Action', 'acao', '2026-05-10 08:23:10'),
(2, 12, 'Aventura', 'Adventure', 'aventura', '2026-05-10 08:23:10'),
(3, 16, 'Animação', 'Animation', 'animacao', '2026-05-10 08:23:10'),
(4, 35, 'Comédia', 'Comedy', 'comedia', '2026-05-10 08:23:10'),
(5, 80, 'Crime', 'Crime', 'crime', '2026-05-10 08:23:10'),
(6, 99, 'Documentário', 'Documentary', 'documentario', '2026-05-10 08:23:10'),
(7, 18, 'Drama', 'Drama', 'drama', '2026-05-10 08:23:10'),
(8, 10751, 'Família', 'Family', 'familia', '2026-05-10 08:23:10'),
(9, 14, 'Fantasia', 'Fantasy', 'fantasia', '2026-05-10 08:23:10'),
(10, 36, 'História', 'History', 'historia', '2026-05-10 08:23:10'),
(11, 10402, 'Música', 'Music', 'musica', '2026-05-10 08:23:10'),
(12, 9648, 'Mistério', 'Mystery', 'misterio', '2026-05-10 08:23:10'),
(13, 10749, 'Romance', 'Romance', 'romance', '2026-05-10 08:23:10'),
(14, 878, 'Ficção Científica', 'Science Fiction', 'ficcao-cientifica', '2026-05-10 08:23:10'),
(15, 53, 'Thriller', 'Thriller', 'thriller', '2026-05-10 08:23:10'),
(16, 10752, 'Guerra', 'War', 'guerra', '2026-05-10 08:23:10'),
(17, 37, 'Faroeste', 'Western', 'faroeste', '2026-05-10 08:23:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tmdb_id` int(11) NOT NULL,
  `rating` decimal(3,1) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `tmdb_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 550, 9.0, NULL, '2026-05-11 21:39:21'),
(2, 3, 27205, 8.0, NULL, '2026-05-11 21:45:58'),
(6, 3, 269149, 10.0, NULL, '2026-05-12 04:07:16');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 7 day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_estonian_ci NOT NULL DEFAULT 'Not NULL',
  `email` varchar(180) NOT NULL DEFAULT 'Not NULL',
  `password` varchar(255) NOT NULL DEFAULT 'Not Null',
  `forget` varchar(64) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `forget`, `avatar`, `created_at`) VALUES
(1, 'Job Mariano', 'jobeiras23@gmail.com', '$2y$12$6cSBFW637goqJ0QdxgAVhOlXYOdAI5g/b/7KHSQUBQdLN5XmRcRkW', NULL, NULL, '2026-05-10 06:57:36'),
(2, 'Jobeiras Mariano', 'jobmariano26@gmail.com', '$2y$12$.ReWiQyNP5TM9OEhiOxR3OdzJfR5pdXrPlbqKHnQM9J8GKDNpBu.y', NULL, 'uploads/avatars/avatar_2_1778402228.jpg', '2026-05-10 08:04:29'),
(3, 'Teste WebMovies', 'teste@webmovies.dev', '$2y$10$0Q/92uXw24E1rnJZ84Bci.UB6lBKwicKsjH6h7psV7Yq08kOytw/m', NULL, NULL, '2026-05-11 21:39:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_genres`
--

CREATE TABLE `user_genres` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `genre_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `user_genres`
--

INSERT INTO `user_genres` (`id`, `user_id`, `genre_id`, `created_at`) VALUES
(5, 2, 1, '2026-05-11 20:27:49'),
(6, 2, 3, '2026-05-11 20:27:49'),
(7, 2, 4, '2026-05-11 20:27:49'),
(8, 2, 8, '2026-05-11 20:27:49'),
(15, 3, 17, '2026-05-11 21:53:16'),
(16, 3, 9, '2026-05-11 21:53:16'),
(17, 3, 8, '2026-05-11 21:53:16');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_movie` (`user_id`,`tmdb_id`);

--
-- Índices de tabela `generos`
--
ALTER TABLE `generos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tmdb_id` (`tmdb_id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_rating` (`user_id`,`tmdb_id`);

--
-- Índices de tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- Índices de tabela `user_genres`
--
ALTER TABLE `user_genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_genre` (`user_id`,`genre_id`),
  ADD KEY `fk_ug_genre` (`genre_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `generos`
--
ALTER TABLE `generos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `user_genres`
--
ALTER TABLE `user_genres`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_user_favorites` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `fk_user_ratings` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `user_genres`
--
ALTER TABLE `user_genres`
  ADD CONSTRAINT `fk_ug_genre` FOREIGN KEY (`genre_id`) REFERENCES `generos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ug_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
