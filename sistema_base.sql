-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/11/2026 às 02:01
-- Versão do servidor: 10.11.16-MariaDB
-- Versão do PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_base`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `animais`
--

CREATE TABLE `animais` (
  `id` int(11) NOT NULL,
  `identificacao` varchar(100) NOT NULL,
  `especie` enum('Gado','Ovelha') NOT NULL DEFAULT 'Gado',
  `tipo` varchar(50) NOT NULL,
  `preco` decimal(10,2) DEFAULT 0.00,
  `status` enum('ativo','vendido','morto') DEFAULT 'ativo',
  `data_cadastro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `animais`
--

INSERT INTO `animais` (`id`, `identificacao`, `especie`, `tipo`, `preco`, `status`, `data_cadastro`) VALUES
(1, 'pietro / 21', 'Gado', 'bezerro', 0.00, '', '2026-11-05 14:33:58'),
(2, 'wagner / 10', 'Gado', 'bezerro', 0.00, 'ativo', '2026-11-05 14:35:32'),
(3, 'mimosa / 3', 'Gado', 'vaca', 0.00, '', '2026-11-05 14:43:38'),
(4, 'icaro / 1', 'Ovelha', 'Borrego', 220.00, 'ativo', '2026-11-05 17:06:07'),
(5, 'Lia / 11', 'Ovelha', 'Ovelha', 600.00, 'ativo', '2026-11-05 17:06:53'),
(6, 'toro / 2', 'Gado', 'Boi', 3000.00, 'ativo', '2026-11-05 17:07:25'),
(7, 'cabeça / 3', 'Ovelha', 'Carneiro', 800.00, 'ativo', '2026-11-05 17:08:49'),
(8, 'WAGNER / 67', 'Ovelha', 'BORREGO', 67.00, 'ativo', '2026-11-05 17:15:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id` int(11) NOT NULL,
  `animal_id` int(11) DEFAULT NULL,
  `tipo_movimentacao` enum('nascimento','venda','morte','cadastro_inicial') DEFAULT NULL,
  `valor_venda` decimal(10,2) DEFAULT NULL,
  `data_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentacoes`
--

INSERT INTO `movimentacoes` (`id`, `animal_id`, `tipo_movimentacao`, `valor_venda`, `data_registro`) VALUES
(1, 1, 'cadastro_inicial', NULL, '2026-11-05 14:33:58'),
(2, 2, 'nascimento', NULL, '2026-11-05 14:35:32'),
(3, 3, 'cadastro_inicial', NULL, '2026-11-05 14:43:38'),
(4, 3, 'morte', NULL, '2026-11-05 14:51:31'),
(5, 1, 'venda', NULL, '2026-11-05 14:59:19'),
(6, 4, 'nascimento', NULL, '2026-11-05 17:06:07'),
(7, 5, 'cadastro_inicial', NULL, '2026-11-05 17:06:53'),
(8, 6, 'cadastro_inicial', NULL, '2026-11-05 17:07:25'),
(9, 7, 'cadastro_inicial', NULL, '2026-11-05 17:08:49'),
(10, 8, 'nascimento', NULL, '2026-11-05 17:15:54');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `animais`
--
ALTER TABLE `animais`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `animal_id` (`animal_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `animais`
--
ALTER TABLE `animais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animais` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
