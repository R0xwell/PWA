-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-11-2025 a las 19:49:44
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `refugio_animales`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apoyos`
--

CREATE TABLE `apoyos` (
  `idApoyo` bigint(20) NOT NULL,
  `idMascota` bigint(20) DEFAULT NULL,
  `idPadrino` bigint(20) NOT NULL,
  `monto` double NOT NULL,
  `causa` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `apoyos`
--

INSERT INTO `apoyos` (`idApoyo`, `idMascota`, `idPadrino`, `monto`, `causa`) VALUES
(18, 4, 1, 5000, 'Alimentacion'),
(19, 4, 1, 324, 'sad'),
(20, 4, 1, 55, 'dad'),
(21, 5, 1, 123124, 'sdf'),
(26, 4, 2, 2123, 'sdas'),
(27, 4, 2, 2123, 'sdas'),
(28, 4, 2, 2123, 'sdas'),
(29, 4, 2, 2123, 'Hola'),
(30, 4, 2, 500, 'me senti mal'),
(31, 4, 2, 12, 'me senti mal'),
(33, 9, 2, 12, 'me senti mal'),
(34, 9, 2, 120, 'bien'),
(35, 10, 1, 120, 'bien'),
(36, 10, 1, 120, 'soy ben 10'),
(37, 10, 1, 1212, 'xdxd'),
(39, 5, 1, 800, 'Me jodi'),
(40, 5, 2, 600, 'alimento');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` int(11) NOT NULL,
  `token` text NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `idUsuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dispositivos`
--

INSERT INTO `dispositivos` (`id`, `token`, `fecha_registro`, `idUsuario`) VALUES
(11, 'ewsKOUcUamlJHPYQ1yA5N-:APA91bHtUjcExailCp2J9QT9eIm5S_IdGAUmA79Y09xZPvpSBlvOPQ_d5n_Xv9dcMbsk2d7tgntjEhGBkDkuPB2GPKMqZjFYp97t8U6Mtz9Bkkhpi33tMdU', '2025-11-14 21:18:46', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mascotas`
--

CREATE TABLE `mascotas` (
  `idMascota` bigint(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `sexo` varchar(10) NOT NULL,
  `raza` varchar(100) DEFAULT NULL,
  `peso` double NOT NULL,
  `condiciones` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mascotas`
--

INSERT INTO `mascotas` (`idMascota`, `nombre`, `sexo`, `raza`, `peso`, `condiciones`) VALUES
(4, 'Bonnie', 'F', 'Conejo', 2.5, 'Saludable'),
(5, 'Mal', 'F', 'Gato', 6, 'Maldad'),
(7, 'Pez', 'M', 'Pez', 0.03, 'buena'),
(8, 'a', 'M', '2', 1, 'buena'),
(9, 'Entrega3', 'M', 'Tarea', 10, 'Mala'),
(10, 'Entrega2', 'M', 'Tareapasada', 9, 'Buena'),
(11, 'Daño', 'F', 'Pastor', 45, 'Buena'),
(12, 'Filo', 'F', 'Ave', 1, 'Se callo al volar'),
(13, 'mimi', 'F', 'perro', 16, 'buena'),
(14, 'Juano', 'M', 'Lagarto', 2, 'Grande'),
(15, 'Pepe', 'M', 'Perro', 15, 'Buena'),
(16, 'Carlo', 'M', 'Cerdo', 34, 'Chorizo'),
(17, 'a', 'M', 'a', 1, 'a'),
(19, 'Calcio', 'F', 'elemeto periodico', 12, '00101'),
(20, 'A', 'F', 'A', 4, 'Buena'),
(21, 'a', 'M', 'a', 112, 'aa'),
(22, 'a', 'M', 'a', 8464, 'aaaaa'),
(23, 'admin', 'F', 'can', 12, 'tiembla'),
(24, 'A', 'M', 'A', 4, 'A'),
(25, 'a', 'F', 'a', 1, 'aa'),
(26, 'a', 'F', 'a', 12, 'aa'),
(27, 'a', 'M', 'aa', 12, 'asas'),
(28, 'Aa', 'M', 'Aa', 48, 'Aad'),
(29, 'Xd', 'M', 'a', 12, 'sdad'),
(31, 'A', 'M', 'A', 47, 'Sdd'),
(34, 'Xd', 'M', 'Dd', 58, 'Qudu'),
(35, 'hamters', 'F', 'hum', 123, 'Mala'),
(36, 'Hola', 'F', 'perro', 12, 'Buena'),
(37, 'test', 'M', 'prueba', 12, 'Tarea');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `idNotificacion` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`idNotificacion`, `tipo`, `accion`, `mensaje`, `fecha`) VALUES
(1, 'Mascota', 'Agregado', 'Se agregó la mascota: Memo', '2025-10-14 16:24:41'),
(2, 'Mascota', 'Editado', 'Se actualizó la mascota: Memo (ID: 1)', '2025-10-14 16:24:48'),
(3, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Leo (ID 1)', '2025-10-14 16:25:11'),
(4, 'Apoyo', 'Agregado', 'Se agregó un nuevo apoyo: ID Apoyo 1, Monto 500, Causa: Opreacion', '2025-10-14 16:25:27'),
(5, 'Mascota', 'Agregado', 'Se agregó la mascota: Igli', '2025-10-14 16:26:52'),
(6, 'Padrino', 'Editado', 'Se actualizó el padrino: Leo (ID 1)', '2025-10-14 16:27:25'),
(7, 'Mascota', 'Agregado', 'Se agregó la mascota: Tiger', '2025-10-14 16:29:34'),
(8, 'Mascota', 'Editado', 'Se actualizó la mascota: Bonnie (ID: 4)', '2025-10-14 16:31:32'),
(9, 'Mascota', 'Editado', 'Se actualizó la mascota: Bonnie (ID: 4)', '2025-10-14 16:31:37'),
(10, 'Mascota', 'Agregado', 'Se agregó la mascota: Bonnie', '2025-10-14 16:34:43'),
(11, 'Mascota', 'Agregado', 'Se agregó la mascota: Mal', '2025-10-14 17:12:16'),
(12, 'Mascota', 'Agregado', 'Se agregó la mascota: Ligre', '2025-10-14 19:01:02'),
(13, 'Mascota', 'Eliminado', 'Se eliminó la mascota: Ligre (ID: 6)', '2025-10-14 19:03:09'),
(14, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Ros (ID 2)', '2025-10-14 19:13:40'),
(15, 'Padrino', 'Editado', 'Se actualizó el padrino: Rosendo (ID 2)', '2025-10-14 19:13:48'),
(16, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Mario (ID 3)', '2025-10-14 19:17:10'),
(17, 'Apoyo', 'Agregado', 'Se agregó un nuevo apoyo: ID Apoyo 2, Monto 700, Causa: Alimento', '2025-10-14 19:25:25'),
(18, 'Mascota', 'Agregado', 'Se agregó la mascota: Pez', '2025-10-15 17:31:30'),
(19, 'Mascota', 'Agregado', 'Se agregó la mascota: a', '2025-10-15 17:33:05'),
(20, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Domingo (ID 4)', '2025-10-15 17:34:56'),
(21, 'Mascota', 'Agregado', 'Se agregó la mascota: Entrega3', '2025-10-22 17:33:18'),
(22, 'Mascota', 'Agregado', 'Se agregó la mascota: Entrega2', '2025-10-22 17:37:12'),
(23, 'Mascota', 'Agregado', 'Se agregó la mascota: Daño', '2025-10-24 09:11:06'),
(24, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Julio (ID 5)', '2025-10-24 09:35:31'),
(25, 'Apoyo', 'Agregado', 'Se agregó un nuevo apoyo: ID Apoyo 3, Monto 900, Causa: Tratamiento', '2025-10-24 09:47:55'),
(26, 'Mascota', 'Agregado', 'Se agregó la mascota: Filo', '2025-10-24 10:07:44'),
(27, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Celeste (ID 6)', '2025-10-24 10:23:44'),
(28, 'Apoyo', 'Agregado', 'Se agregó un nuevo apoyo: ID Apoyo 4, Monto 400, Causa: Medicamento', '2025-10-24 10:47:59'),
(29, 'Apoyo', 'Eliminado', 'Se eliminó el apoyo: ID Apoyo 4, Monto 400, Causa: Medicamento', '2025-10-24 10:55:54'),
(30, 'Apoyo', 'Agregado', 'Se agregó un nuevo apoyo: ID Apoyo 5, Monto 400, Causa: Medicamento', '2025-10-24 10:57:55'),
(31, 'Apoyo', 'Agregado', 'Se agregó un nuevo apoyo: ID Apoyo 6, Monto 400, Causa: Medicamento', '2025-10-24 10:57:56'),
(32, 'Mascota', 'Agregado', 'Se agregó la mascota: mimi', '2025-10-24 11:10:17'),
(33, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Amanda (ID 7)', '2025-10-24 11:26:09'),
(34, 'Apoyo', 'Eliminado', 'Se eliminó el apoyo: ID Apoyo 6, Monto 400, Causa: Medicamento', '2025-10-24 11:59:42'),
(35, 'Apoyo', 'Agregado', 'Se agregó un nuevo apoyo: ID Apoyo 7, Monto 800, Causa: Alimento y vacuna', '2025-10-24 12:04:01'),
(36, 'Mascota', 'Agregado', 'Se agregó la mascota: Juano', '2025-10-24 12:24:26'),
(37, 'Mascota', 'Eliminado', 'Se eliminó la mascota: Memo (ID: 1)', '2025-10-29 17:17:24'),
(38, 'Mascota', 'Eliminado', 'Se eliminó la mascota: Igli (ID: 2)', '2025-10-29 17:17:26'),
(39, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: a (ID 8)', '2025-10-29 17:28:04'),
(40, 'Mascota', 'Agregado', 'Se agregó la mascota: Pepe', '2025-10-29 17:34:37'),
(41, 'Mascota', 'Agregado', 'Se agregó la mascota: Carlo', '2025-11-11 17:49:03'),
(42, 'Mascota', 'Agregado', 'Se agregó la mascota: a', '2025-11-11 17:52:50'),
(43, 'Mascota', 'Agregado', 'Se agregó la mascota: Memo2', '2025-11-11 17:56:41'),
(44, 'Mascota', 'Agregado', 'Se agregó la mascota: Calcio', '2025-11-11 20:04:06'),
(45, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: a (ID 9)', '2025-11-11 20:24:22'),
(46, 'Mascota', 'Agregado', 'Se agregó la mascota: A', '2025-11-11 20:52:59'),
(47, 'Mascota', 'Agregado', 'Se agregó la mascota: a', '2025-11-11 21:12:39'),
(48, 'Mascota', 'Agregado', 'Se agregó la mascota: a', '2025-11-11 21:16:22'),
(49, 'Mascota', 'Agregado', 'Se agregó la mascota: admin', '2025-11-12 17:21:43'),
(50, 'Mascota', 'Agregado', 'Se agregó la mascota: A', '2025-11-12 17:22:23'),
(51, 'Mascota', 'Agregado', 'Se agregó la mascota: a', '2025-11-12 17:40:04'),
(52, 'Mascota', 'Eliminado', 'Se eliminó la mascota: Tiger (ID: 3)', '2025-11-12 19:47:03'),
(53, 'Mascota', 'Agregado', 'Se agregó la mascota: a', '2025-11-13 19:41:21'),
(54, 'Mascota', 'Agregado', 'Se agregó la mascota: a', '2025-11-13 19:41:42'),
(55, 'Mascota', 'Agregado', 'Se agregó la mascota: Aa', '2025-11-13 19:44:52'),
(56, 'Mascota', 'Agregado', 'Se agregó la mascota: a', '2025-11-13 20:18:46'),
(57, 'Mascota', 'Agregado', 'Se agregó la mascota: Q', '2025-11-13 20:19:47'),
(58, 'Mascota', 'Eliminado', 'Se eliminó la mascota: Q (ID: 30)', '2025-11-13 20:20:50'),
(59, 'Mascota', 'Editado', 'Se actualizó la mascota: Xd (ID: 29)', '2025-11-13 20:21:01'),
(60, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: HEy (ID 10)', '2025-11-13 21:10:33'),
(61, 'Mascota', 'Agregado', 'Se agregó la mascota: A', '2025-11-13 21:11:52'),
(62, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Hay (ID 11)', '2025-11-13 21:12:09'),
(63, 'Padrino', 'Eliminado', 'Se eliminó el padrino: Hay (ID 11)', '2025-11-13 21:14:49'),
(64, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Hola (ID 12)', '2025-11-14 10:56:14'),
(65, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Hola (ID 13)', '2025-11-14 10:57:17'),
(66, 'Mascota', 'Agregado', 'Se agregó la mascota: a', '2025-11-14 12:48:57'),
(67, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: ads (ID 14)', '2025-11-14 12:54:35'),
(68, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: a (ID 15)', '2025-11-14 12:59:13'),
(69, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: as (ID 16)', '2025-11-14 12:59:38'),
(70, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: asd (ID 17)', '2025-11-14 13:02:48'),
(71, 'Mascota', 'Agregado', 'Se agregó la mascota: sd', '2025-11-14 13:03:15'),
(72, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: as (ID 18)', '2025-11-14 13:27:51'),
(73, 'Padrino', 'Eliminado', 'Se eliminó el padrino: as (ID: 18)', '2025-11-14 13:28:04'),
(74, 'Padrino', 'Editado', 'Se actualizó el padrino: Mailo (ID: 17)', '2025-11-14 13:28:11'),
(75, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: sxd (ID 19)', '2025-11-14 13:44:39'),
(76, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: q (ID 20)', '2025-11-14 14:01:19'),
(77, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 18). Monto 231 — Causa: mal.', '2025-11-14 14:01:46'),
(78, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 19). Monto 324 — Causa: sad.', '2025-11-14 14:13:08'),
(79, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 20). Monto 55 — Causa: dad.', '2025-11-14 14:14:05'),
(80, 'Mascota', 'Eliminado', 'Se eliminó la mascota: sd (ID: 33)', '2025-11-14 17:57:01'),
(81, 'Padrino', 'Eliminado', 'Se eliminó el padrino: q (ID: 20)', '2025-11-14 17:57:09'),
(82, 'Apoyo', 'Editado', 'Apoyo (ID 2) actualizado.', '2025-11-14 17:59:21'),
(83, 'Mascota', 'Eliminado', 'Se eliminó la mascota: a (ID: 32)', '2025-11-14 18:50:13'),
(84, 'Padrino', 'Eliminado', 'Se eliminó el padrino: sxd (ID: 19)', '2025-11-14 18:50:20'),
(85, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 21). Monto 1223 — Causa: sdf.', '2025-11-14 18:52:22'),
(86, 'Apoyo', 'Editado', 'Apoyo (ID 21) actualizado.', '2025-11-14 18:52:57'),
(87, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 23). Monto 21313 — Causa: Alimento.', '2025-11-14 19:03:55'),
(88, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 24). Monto 231 — Causa: mal.', '2025-11-14 19:04:27'),
(89, 'Apoyo', 'Editado', 'Apoyo actualizado (ID 24).', '2025-11-14 19:12:56'),
(90, 'Apoyo', 'Editado', 'Apoyo actualizado (ID 24).', '2025-11-14 19:13:07'),
(91, 'Apoyo', 'Eliminado', 'Apoyo eliminado (ID 24). Monto 231 — Causa: mal.', '2025-11-14 19:13:11'),
(92, 'Apoyo', 'Eliminado', 'Apoyo eliminado (ID 23). Monto 21313 — Causa: Alimento.', '2025-11-14 19:13:17'),
(93, 'Apoyo', 'Eliminado', 'Apoyo eliminado (ID 1). Monto 500 — Causa: Opreacion.', '2025-11-14 19:13:20'),
(94, 'Apoyo', 'Eliminado', 'Apoyo eliminado (ID 2). Monto 21313 — Causa: Alimento.', '2025-11-14 19:13:23'),
(95, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 26). Monto 2123 — Causa: sdas.', '2025-11-14 19:13:46'),
(96, 'Apoyo', 'Eliminado', 'Apoyo eliminado (ID 3). Monto 900 — Causa: Tratamiento.', '2025-11-14 19:18:31'),
(97, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 27). Monto 2123 — Causa: sdas.', '2025-11-14 19:18:44'),
(98, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 28). Monto 2123 — Causa: sdas.', '2025-11-14 19:19:04'),
(99, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 29). Monto 2123 — Causa: Hola.', '2025-11-14 20:11:21'),
(100, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 30). Monto 500 — Causa: me senti mal.', '2025-11-14 20:11:40'),
(101, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 31). Monto 12 — Causa: me senti mal.', '2025-11-14 20:11:55'),
(102, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 33). Monto 12 — Causa: me senti mal.', '2025-11-14 20:12:25'),
(103, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 34). Monto 120 — Causa: bien.', '2025-11-14 20:17:33'),
(104, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 35). Monto 120 — Causa: bien.', '2025-11-14 20:18:45'),
(105, 'Apoyo', 'Eliminado', 'Apoyo eliminado (ID 5). Monto 400 — Causa: Medicamento.', '2025-11-14 20:19:18'),
(106, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 36). Monto 120 — Causa: soy ben 10.', '2025-11-14 20:22:45'),
(107, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 37). Monto 1212 — Causa: xdxd.', '2025-11-14 20:23:35'),
(108, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 38). Monto 123 — Causa: xdxd.', '2025-11-14 20:27:53'),
(109, 'Apoyo', 'Editado', 'Apoyo actualizado (ID 38).', '2025-11-14 20:28:13'),
(110, 'Apoyo', 'Editado', 'Apoyo actualizado (ID 38).', '2025-11-14 20:28:23'),
(111, 'Apoyo', 'Editado', 'Apoyo actualizado (ID 38).', '2025-11-14 20:28:33'),
(112, 'Apoyo', 'Editado', 'Apoyo actualizado (ID 38).', '2025-11-14 20:28:44'),
(113, 'Apoyo', 'Eliminado', 'Apoyo eliminado (ID 38). Monto 123456 — Causa: Hola.', '2025-11-14 20:28:50'),
(114, 'Mascota', 'Agregado', 'Se agregó la mascota: Xd', '2025-11-14 20:30:32'),
(115, 'Mascota', 'Eliminado', 'Se eliminó la mascota: Memo2 (ID: 18)', '2025-11-14 20:30:56'),
(116, 'Padrino', 'Editado', 'Se actualizó el padrino: a (ID: 8)', '2025-11-14 20:31:24'),
(117, 'Padrino', 'Eliminado', 'Se eliminó el padrino: Mailo (ID: 17)', '2025-11-14 20:31:42'),
(118, 'Padrino', 'Agregado', 'Se agregó un nuevo padrino: Hala (ID 21)', '2025-11-14 20:32:03'),
(119, 'Apoyo', 'Eliminado', 'Apoyo eliminado (ID 7). Monto 800 — Causa: Alimento y vacuna.', '2025-11-14 20:32:18'),
(120, 'Apoyo', 'Editado', 'Apoyo actualizado (ID 18).', '2025-11-14 20:32:48'),
(121, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 39). Monto 800 — Causa: Me jodi.', '2025-11-14 20:33:16'),
(122, 'Mascota', 'Agregado', 'Se agregó la mascota: hamters', '2025-11-14 21:14:45'),
(123, 'Mascota', 'Agregado', 'Se agregó la mascota: Hola', '2025-11-14 21:16:40'),
(124, 'Mascota', 'Agregado', 'Se agregó la mascota: test', '2025-11-14 21:17:15'),
(125, 'Apoyo', 'Agregado', 'Nuevo apoyo (ID 40). Monto 600 — Causa: alimento.', '2025-11-14 21:20:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `padrinos`
--

CREATE TABLE `padrinos` (
  `idPadrino` bigint(20) NOT NULL,
  `nombrePadrino` varchar(100) NOT NULL,
  `sexo` varchar(10) NOT NULL,
  `telefono` decimal(10,0) NOT NULL,
  `correoElectronico` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `padrinos`
--

INSERT INTO `padrinos` (`idPadrino`, `nombrePadrino`, `sexo`, `telefono`, `correoElectronico`) VALUES
(1, 'Leo', 'M', 8621115622, 'leobqr@gmail.com'),
(2, 'Rosendo', 'M', 878, 'Rge@gmail.com'),
(3, 'Mario', 'M', 878, 'M@gmail.com'),
(4, 'Domingo', 'M', 862, 'dom@gmail.com'),
(5, 'Julio', 'M', 878, 'Julio@gmail.com'),
(6, 'Celeste', 'F', 862, 'celes@gamil.com'),
(7, 'Amanda', 'F', 878, 'aman@gmail.com'),
(8, 'a', 'F', 8644, 'meme@gmail.com'),
(9, 'a', 'M', 0, NULL),
(10, 'HEy', 'M', 123, 'as@gmail.com'),
(12, 'Hola', 'M', 0, 'hola@gmail.com'),
(13, 'Hola', 'M', 2132, 'hola@gmail.com'),
(14, 'ads', 'M', 211, 'dsad@gmail.com'),
(15, 'a', 'F', 0, NULL),
(16, 'as', 'M', 232, 'asds@gmail.com'),
(21, 'Hala', 'F', 86568, 'hola@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idUsuario` bigint(20) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(20) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `usuario`, `password`, `rol`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'admin');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `apoyos`
--
ALTER TABLE `apoyos`
  ADD PRIMARY KEY (`idApoyo`),
  ADD KEY `idMascota` (`idMascota`),
  ADD KEY `idPadrino` (`idPadrino`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD PRIMARY KEY (`idMascota`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`idNotificacion`);

--
-- Indices de la tabla `padrinos`
--
ALTER TABLE `padrinos`
  ADD PRIMARY KEY (`idPadrino`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `apoyos`
--
ALTER TABLE `apoyos`
  MODIFY `idApoyo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `idMascota` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `idNotificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT de la tabla `padrinos`
--
ALTER TABLE `padrinos`
  MODIFY `idPadrino` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `apoyos`
--
ALTER TABLE `apoyos`
  ADD CONSTRAINT `apoyos_ibfk_1` FOREIGN KEY (`idMascota`) REFERENCES `mascotas` (`idMascota`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `apoyos_ibfk_2` FOREIGN KEY (`idPadrino`) REFERENCES `padrinos` (`idPadrino`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
