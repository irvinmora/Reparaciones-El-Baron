-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-03-2026 a las 21:05:45
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
-- Base de datos: `el_baron_reparaciones`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombres`, `apellidos`, `telefono`, `direccion`, `email`, `fecha_registro`) VALUES
(1, 'IRVIN ADONIS ', 'MORA PAREDES', '0995985655', 'FLORES', 'imorap@fafi.utb.edu.ec', '2026-03-18 20:48:27'),
(2, 'KEVIN RAUL', 'SISA LLUMITAXI', '0986694890', 'by pass', NULL, '2026-03-18 21:58:05'),
(3, 'QUIROZ VARGAS ', 'LEONELA ', '0987296574', 'puerta negra', NULL, '2026-03-19 13:16:48'),
(4, 'SUAREZ CEPEDA', 'BIANCA MAIRA', '0986694890', 'FLORES Y MEJIA', NULL, '2026-03-19 14:31:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 2, 1, 1, 45.00, 45.00),
(2, 3, 3, 1, 8.50, 8.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `categoria` varchar(50) DEFAULT 'General',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id`, `descripcion`, `monto`, `categoria`, `fecha`) VALUES
(1, 'ALQUILER DEL LOCAL  ', 25.00, 'Alquiler', '2026-03-19 05:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_reparacion`
--

CREATE TABLE `pagos_reparacion` (
  `id` int(11) NOT NULL,
  `reparacion_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo_pago` enum('abono','pago_completo') DEFAULT 'abono'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_reparacion`
--

INSERT INTO `pagos_reparacion` (`id`, `reparacion_id`, `monto`, `fecha_pago`, `tipo_pago`) VALUES
(1, 1, 8.00, '2026-03-18 20:50:10', 'abono'),
(2, 1, 2.00, '2026-03-18 21:56:29', 'pago_completo'),
(3, 2, 15.00, '2026-03-18 21:58:05', 'abono'),
(4, 2, 15.00, '2026-03-18 22:05:47', 'pago_completo'),
(5, 3, 10.00, '2026-03-18 22:50:00', 'abono'),
(6, 3, 5.00, '2026-03-18 23:16:42', 'pago_completo'),
(7, 4, 4.00, '2026-03-19 13:16:48', 'abono'),
(8, 5, 20.00, '2026-03-19 14:31:39', 'abono'),
(9, 5, 30.00, '2026-03-19 14:55:54', 'pago_completo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 5,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `precio`, `stock`, `stock_minimo`, `fecha_registro`) VALUES
(1, 'MOT001', 'Motor para licuadora', 'Motor universal 120V', 45.00, 9, 5, '2026-03-18 20:12:09'),
(2, 'RES002', 'Resistencia para microondas', 'Resistencia 800W', 25.00, 15, 5, '2026-03-18 20:12:09'),
(3, 'CAP003', 'Capacitor para ventilador', 'Capacitor 5uF', 8.50, 19, 5, '2026-03-18 20:12:09'),
(4, 'PLA004', 'Plancha para cabello', 'Plancha cerámica', 35.00, 8, 5, '2026-03-18 20:12:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reparaciones`
--

CREATE TABLE `reparaciones` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `producto` varchar(100) NOT NULL,
  `descripcion_problema` text DEFAULT NULL,
  `costo_total` decimal(10,2) DEFAULT NULL,
  `abono_inicial` decimal(10,2) DEFAULT 0.00,
  `saldo_pendiente` decimal(10,2) DEFAULT NULL,
  `estado` enum('pendiente','en_proceso','completado','entregado') DEFAULT 'pendiente',
  `fecha_ingreso` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reparaciones`
--

INSERT INTO `reparaciones` (`id`, `cliente_id`, `producto`, `descripcion_problema`, `costo_total`, `abono_inicial`, `saldo_pendiente`, `estado`, `fecha_ingreso`, `fecha_entrega`) VALUES
(1, 1, 'Licuadora', 'PERILLA', 10.00, 8.00, 0.00, 'completado', '2026-03-18 20:50:10', '2026-03-18 16:56:29'),
(2, 2, 'Licuadora', 'bobina', 30.00, 15.00, 0.00, 'completado', '2026-03-18 21:58:05', '2026-03-18 17:05:47'),
(3, 1, 'Plancha', 'CAMBIAR CABLE Y CAPACITOR', 15.00, 10.00, 0.00, 'completado', '2026-03-18 22:50:00', '2026-03-18 18:16:42'),
(4, 3, 'Ventilador', 'no le vale el capacitor ni el cable ', 8.00, 4.00, 4.00, 'pendiente', '2026-03-19 13:16:48', NULL),
(5, 4, 'Microondas', 'CAMBIAR EL TRANFORMADOR ', 50.00, 20.00, 0.00, 'completado', '2026-03-19 14:31:39', '2026-03-19 09:55:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `rol` enum('administrador','tecnico') DEFAULT 'tecnico'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `google_id`, `avatar`, `fecha_registro`, `rol`) VALUES
(1, 'IRVIN ADONIS MORA PAREDES', 'irvinadonismoraparedesqc@gmail.com', '$2y$10$0zeWrPZme5kpoIFMon8gwe7xdgXtfDgJ5LeCmVT3ge1pkLRof7ML2', NULL, NULL, '2026-03-18 20:27:06', 'administrador'),
(5, 'IRVIN ADONIS MORA PAREDES', 'imorap@fafi.utb.edu.ec', NULL, '101627553616898062523', 'https://lh3.googleusercontent.com/a/ACg8ocIp4XR5s815pUy45-8S6ya2oXRkrbfEiwoJf8eO9MOQMiTfL9Y=s96-c', '2026-03-19 19:14:51', 'tecnico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `cliente_id`, `total`, `fecha_venta`) VALUES
(1, 1, 0.00, '2026-03-18 22:39:53'),
(2, 1, 45.00, '2026-03-18 22:40:53'),
(3, 2, 8.50, '2026-03-18 22:48:08');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos_reparacion`
--
ALTER TABLE `pagos_reparacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reparacion_id` (`reparacion_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `reparaciones`
--
ALTER TABLE `reparaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pagos_reparacion`
--
ALTER TABLE `pagos_reparacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reparaciones`
--
ALTER TABLE `reparaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `pagos_reparacion`
--
ALTER TABLE `pagos_reparacion`
  ADD CONSTRAINT `pagos_reparacion_ibfk_1` FOREIGN KEY (`reparacion_id`) REFERENCES `reparaciones` (`id`);

--
-- Filtros para la tabla `reparaciones`
--
ALTER TABLE `reparaciones`
  ADD CONSTRAINT `reparaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
