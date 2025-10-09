-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-10-2025 a las 02:02:33
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
(1, 1, 1, 1000, 'Pago de una operacion.'),
(2, NULL, 1, 500, 'Apoyo al refugio.');

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
(1, 'Chaparro', 'M', NULL, 10, 'Tiene un problema en el estomago, necesita 2 operaciones.'),
(2, 'Mugre', 'M', 'Bug dog', 15, 'Mala'),
(3, 'Mugre', 'M', 'Bug dog', 15, 'Mala');

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
(1, 'Jose Perez', 'M', 8621234567, 'jose.perez@gmail.com');

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
-- Indices de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  ADD PRIMARY KEY (`idMascota`);

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
  MODIFY `idApoyo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `mascotas`
--
ALTER TABLE `mascotas`
  MODIFY `idMascota` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `padrinos`
--
ALTER TABLE `padrinos`
  MODIFY `idPadrino` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
