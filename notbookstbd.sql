-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-06-2026 a las 18:08:43
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
-- Base de datos: `notbookstbd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id_materia` int(11) NOT NULL,
  `nombre_materia` varchar(255) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `id_usuario`, `color`, `link`) VALUES
(33, 'GERENCIA EN PROYECTOS DE INGENIERIA / PRIMER BLOQUE', 9, '#f59e0b', 'https://cdigital.cun.edu.co/course/view.php?id=108717');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id_tarea` int(11) NOT NULL,
  `id_materia` int(11) DEFAULT NULL,
  `nombre_tarea` varchar(255) NOT NULL,
  `descripcion_tarea` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` date DEFAULT NULL,
  `hora_cierre` time NOT NULL DEFAULT '23:59:00',
  `tipo_tarea` enum('tarea','quiz','parcial','examen_final','proyecto','otro') NOT NULL DEFAULT 'tarea',
  `color` varchar(7) DEFAULT NULL,
  `estado` enum('pendiente','en_progreso','completada','cancelada') DEFAULT 'pendiente',
  `link` varchar(500) DEFAULT NULL,
  `notificado_24h` tinyint(1) DEFAULT 0,
  `notificado_1h` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id_tarea`, `id_materia`, `nombre_tarea`, `descripcion_tarea`, `fecha_creacion`, `fecha_cierre`, `hora_cierre`, `tipo_tarea`, `color`, `estado`, `link`, `notificado_24h`, `notificado_1h`) VALUES
(22, 33, 'PARCIAL 1', '¡Excelente! En la captura se', '2026-06-02 14:35:44', '2026-06-03', '13:59:00', 'examen_final', '#4af0c8', 'completada', NULL, 0, 0),
(23, 33, 'PARCIAL 1 aaa', 'URGENTE', '2026-06-02 15:00:27', '2026-06-02', '23:59:00', 'examen_final', '#63b3ed', 'completada', NULL, 1, 0),
(26, 33, 'PARCIAL 1 aaa', 'PARCIAL 1', '2026-06-03 14:35:17', '2026-06-03', '23:59:00', 'proyecto', '#f87171', 'pendiente', NULL, 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `usuario` varchar(150) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `contraseña` varchar(150) NOT NULL,
  `imagen` varchar(90) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `usuario`, `correo`, `contraseña`, `imagen`) VALUES
(7, 'Luis Miguel Mosquera Cuesta', 'LUISM23', 'lumilui3s2018@gmail.com', '1040Lumi', '../asset/img/user/Foto perfil.png'),
(8, 'LUIS MIGUEL', 'LUIS20', 'lumiluis2018@gmail.com', '$2y$10$HV2u1Wo5MtHQ0xLC3Qz/OOQoZrCz.1inZhqOJA1omI5A6tPokm55a', '../asset/img/user/avatar_8_1780284118.jpg'),
(9, 'Luis Mosquera', 'luismm', 'luis.mosqueraccucue@cun.edu.co', '$2y$10$k4iX/LSnvEOBby2W2LSRwube2TkG587563ygsYTUgkZ8P1NLeff52', '../asset/img/user/G_NKSxNaIAMVWmI.jpg');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id_tarea`),
  ADD KEY `id_materia` (`id_materia`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id_tarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `materias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
