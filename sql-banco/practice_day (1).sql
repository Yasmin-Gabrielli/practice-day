-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 18/11/2026 às 19:57
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `practice_day`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `anexos_tarefa`
--

CREATE TABLE `anexos_tarefa` (
  `id` char(36) NOT NULL,
  `tarefa_id` char(36) NOT NULL,
  `arquivo_id` char(36) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `anotacoes_leitura`
--

CREATE TABLE `anotacoes_leitura` (
  `id` char(36) NOT NULL,
  `destaque_id` char(36) NOT NULL,
  `conteudo` text NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `arquivos`
--

CREATE TABLE `arquivos` (
  `id` char(36) NOT NULL,
  `pasta_id` char(36) DEFAULT NULL,
  `disciplina_id` char(36) DEFAULT NULL,
  `usuario_id` char(36) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `extensao` varchar(15) DEFAULT NULL,
  `tipo_mime` varchar(120) DEFAULT NULL,
  `tamanho_bytes` bigint(20) DEFAULT NULL,
  `caminho_armazenamento` text NOT NULL,
  `favorito` tinyint(1) DEFAULT 0,
  `excluido` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `versao_sincronizacao` int(11) DEFAULT 1,
  `status_sincronizacao` enum('pendente','sincronizado','conflito') DEFAULT 'pendente',
  `origem` varchar(20) NOT NULL DEFAULT 'upload'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `arquivos`
--

INSERT INTO `arquivos` (`id`, `pasta_id`, `disciplina_id`, `usuario_id`, `nome_arquivo`, `nome_original`, `extensao`, `tipo_mime`, `tamanho_bytes`, `caminho_armazenamento`, `favorito`, `excluido`, `criado_em`, `atualizado_em`, `versao_sincronizacao`, `status_sincronizacao`, `origem`) VALUES
('1929d6cc-0185-41e9-8c51-b244a7e8a47a', NULL, '8618dbbb-106e-4366-a8f3-b093ae8bbd88', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'a14686fd-2d42-4adf-bce5-0f440ad8d910.jpg', 'Digitalização Hugo & Yas - página 1.jpg', 'jpg', 'image/jpeg', 138663, 'scanner/faccc05a-abd7-4514-8b11-fc0ce7833b48/a14686fd-2d42-4adf-bce5-0f440ad8d910.jpg', 0, 0, '2026-11-06 17:10:46', '2026-11-06 17:10:46', 1, 'pendente', 'camera'),
('5143a4ca-2412-4b42-8241-a02cd76f5bb1', 'cc51a531-91d9-404a-bda6-d8c3dc06e0ce', 'c4e9d041-074b-43cb-adae-849064ff7e14', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', '5143a4ca-2412-4b42-8241-a02cd76f5bb1.pdf', 'Projeto integrador final- Yasmin Gabrielli.pdf', 'pdf', 'application/pdf', 27640, 'uploads/d3de82d5-ac8c-4d12-b351-5fe32e770a10/5143a4ca-2412-4b42-8241-a02cd76f5bb1.pdf', 0, 0, '2026-10-21 18:23:50', '2026-10-21 18:23:50', 1, 'pendente', 'upload'),
('8cc5587b-2e49-4d35-b223-d62e7a1c0cbc', 'c2f614c4-afbc-4142-a959-f46b05988320', 'e0468669-f80a-4635-a6fc-dcf2de924849', '837d2a76-7029-46d7-aea9-0885621d04d8', '8cc5587b-2e49-4d35-b223-d62e7a1c0cbc.jpg', 'dante.jpg', 'jpg', 'image/jpeg', 152100, 'uploads/837d2a76-7029-46d7-aea9-0885621d04d8/8cc5587b-2e49-4d35-b223-d62e7a1c0cbc.jpg', 1, 0, '2026-10-16 16:27:18', '2026-10-16 16:28:08', 1, 'pendente', 'upload'),
('aa79da72-c28c-4f69-9445-ec1223e2d6bd', NULL, 'c4e9d041-074b-43cb-adae-849064ff7e14', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'aa79da72-c28c-4f69-9445-ec1223e2d6bd.png', 'print-pwa-instalado.png', 'png', 'image/png', 48042, 'uploads/d3de82d5-ac8c-4d12-b351-5fe32e770a10/aa79da72-c28c-4f69-9445-ec1223e2d6bd.png', 0, 0, '2026-10-23 13:08:48', '2026-10-23 13:08:48', 1, 'pendente', 'upload'),
('b2fe3940-926e-410a-9242-162575172e83', 'f512904e-b9f6-4eb4-89a4-d275d5e3a1f9', NULL, 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'b2fe3940-926e-410a-9242-162575172e83.epub', 'O principe cruel.epub', 'epub', 'application/zip', 6276181, 'uploads/b20f781a-a576-4b91-8f8e-bf9a1446131e/b2fe3940-926e-410a-9242-162575172e83.epub', 0, 0, '2026-11-18 11:59:04', '2026-11-18 11:59:21', 1, 'pendente', 'upload'),
('bd40163b-2c06-4b78-bf5b-edc39108f951', NULL, NULL, 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'bd40163b-2c06-4b78-bf5b-edc39108f951.jpg', '1428234.jpg', 'jpg', 'image/jpeg', 80330, 'uploads/d3de82d5-ac8c-4d12-b351-5fe32e770a10/bd40163b-2c06-4b78-bf5b-edc39108f951.jpg', 0, 0, '2026-10-23 16:52:44', '2026-10-23 16:52:44', 1, 'pendente', 'upload'),
('c54bb1e2-6c75-44a5-b12f-7d085bfd8cb2', 'bd394006-b365-433d-8545-b03e7c6c5177', NULL, 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'c54bb1e2-6c75-44a5-b12f-7d085bfd8cb2.epub', 'Livro.epub', 'epub', 'application/zip', 6276181, 'uploads/d3de82d5-ac8c-4d12-b351-5fe32e770a10/c54bb1e2-6c75-44a5-b12f-7d085bfd8cb2.epub', 0, 0, '2026-10-23 13:36:50', '2026-10-23 13:37:08', 1, 'pendente', 'upload'),
('dd766835-4aca-4ddf-8df9-936ae0f20420', 'f512904e-b9f6-4eb4-89a4-d275d5e3a1f9', NULL, 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'dd766835-4aca-4ddf-8df9-936ae0f20420.epub', 'Desenfreados.epub', 'epub', 'application/epub+zip', 5573996, 'uploads/b20f781a-a576-4b91-8f8e-bf9a1446131e/dd766835-4aca-4ddf-8df9-936ae0f20420.epub', 0, 0, '2026-11-06 17:05:40', '2026-11-18 11:08:47', 1, 'pendente', 'upload'),
('e2680375-bd4b-4f21-8060-c47c825c401e', 'f512904e-b9f6-4eb4-89a4-d275d5e3a1f9', NULL, 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'e2680375-bd4b-4f21-8060-c47c825c401e.epub', 'Shadow Hunters livro.epub', 'epub', 'application/epub+zip', 363082, 'uploads/b20f781a-a576-4b91-8f8e-bf9a1446131e/e2680375-bd4b-4f21-8060-c47c825c401e.epub', 0, 0, '2026-11-18 11:09:52', '2026-11-18 11:53:20', 1, 'pendente', 'upload'),
('e4f73990-25f2-4af8-bbbb-fd93e75e7a80', NULL, NULL, 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'f4ab4c3d-a52f-4b5c-9a25-dd300e38ffc2.jpg', 'Digitalização Foto Huguin - página 1.jpg', 'jpg', 'image/jpeg', 103926, 'scanner/ec2bf951-3b07-4033-b9e8-fa306136b49b/f4ab4c3d-a52f-4b5c-9a25-dd300e38ffc2.jpg', 0, 0, '2026-11-04 11:10:55', '2026-11-04 11:10:55', 1, 'pendente', 'camera');

-- --------------------------------------------------------

--
-- Estrutura para tabela `arquivos_favoritos`
--

CREATE TABLE `arquivos_favoritos` (
  `usuario_id` char(36) NOT NULL,
  `arquivo_id` char(36) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `arquivos_favoritos`
--

INSERT INTO `arquivos_favoritos` (`usuario_id`, `arquivo_id`, `criado_em`) VALUES
('837d2a76-7029-46d7-aea9-0885621d04d8', '8cc5587b-2e49-4d35-b223-d62e7a1c0cbc', '2026-10-16 16:28:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `biblioteca`
--

CREATE TABLE `biblioteca` (
  `id` char(36) NOT NULL,
  `arquivo_id` char(36) NOT NULL,
  `pagina_atual` int(11) DEFAULT 1,
  `total_paginas` int(11) NOT NULL,
  `progresso_porcentagem` decimal(5,2) DEFAULT 0.00,
  `cfi` text DEFAULT NULL,
  `posicao_scroll` int(11) NOT NULL DEFAULT 0,
  `zoom` decimal(5,2) NOT NULL DEFAULT 1.00,
  `ultimo_acesso` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `biblioteca`
--

INSERT INTO `biblioteca` (`id`, `arquivo_id`, `pagina_atual`, `total_paginas`, `progresso_porcentagem`, `ultimo_acesso`) VALUES
('16c7c433-4f55-4dd6-bbd7-f914e702e87e', 'e2680375-bd4b-4f21-8060-c47c825c401e', 1, 845, 1.10, '2026-11-18 11:54:56'),
('6df9b34e-4fad-484f-8a5b-ed7593bc0976', 'b2fe3940-926e-410a-9242-162575172e83', 1, 741, 0.40, '2026-11-18 12:02:15'),
('b892980b-1cca-4c49-8620-6c2823a5f812', 'dd766835-4aca-4ddf-8df9-936ae0f20420', 10, 2023, 9.70, '2026-11-18 18:56:35'),
('c590d325-670f-4e86-a10e-3d68adf53084', 'c54bb1e2-6c75-44a5-b12f-7d085bfd8cb2', 1, 741, 0.40, '2026-11-04 11:03:11'),
('e120e6ac-22be-427c-ac95-12a05429df03', '5143a4ca-2412-4b42-8241-a02cd76f5bb1', 1, 1, 100.00, '2026-11-04 11:03:24');

-- --------------------------------------------------------

--
-- Estrutura para tabela `blocos_nota`
--

CREATE TABLE `blocos_nota` (
  `id` char(36) NOT NULL,
  `nota_id` char(36) NOT NULL,
  `tipo_bloco` enum('texto','codigo','imagem','lista','equacao') NOT NULL,
  `conteudo` longtext NOT NULL,
  `ordem` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `blocos_nota`
--

INSERT INTO `blocos_nota` (`id`, `nota_id`, `tipo_bloco`, `conteudo`, `ordem`, `criado_em`) VALUES
('1ad246d0-b6e8-4d9c-871a-57f2fff02533', 'cba3a34d-e323-4962-a634-9e544df4a48d', 'lista', 'Eita\r\nPode não\r\nOxe', 1, '2026-10-23 13:47:55'),
('2ddc6d17-014a-42d9-8444-45f5afc430ea', '60e4166d-4517-4ca5-891b-b85d5147ee33', 'lista', 'Requisitos funcionais\r\nRequisitos não funcionais\r\nRegras de negocio', 1, '2026-11-04 18:10:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `nome` varchar(80) NOT NULL,
  `cor` varchar(20) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes_usuario`
--

CREATE TABLE `configuracoes_usuario` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `modo_escuro` tinyint(1) DEFAULT 0,
  `cor_primaria` varchar(20) DEFAULT '#000000',
  `cor_secundaria` varchar(20) DEFAULT '#FFFFFF',
  `papel_parede` text DEFAULT NULL,
  `tamanho_fonte` varchar(20) DEFAULT 'médio',
  `animacoes_ativas` tinyint(1) DEFAULT 1,
  `idioma` varchar(20) DEFAULT 'pt-BR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracoes_usuario`
--

INSERT INTO `configuracoes_usuario` (`id`, `usuario_id`, `modo_escuro`, `cor_primaria`, `cor_secundaria`, `papel_parede`, `tamanho_fonte`, `animacoes_ativas`, `idioma`) VALUES
('114cffbf-85a8-44a4-a11b-069b47661d09', 'af90ddc4-217f-4556-87b9-aec1bad3fcbc', 0, '#2563eb', '#ffffff', NULL, 'médio', 1, 'pt-BR'),
('2114899c-10f9-42f0-beb0-a56f58634eb6', 'b7d6feaa-3562-41b4-86c4-840aaef3eb6e', 0, '#2563eb', '#ffffff', NULL, 'médio', 1, 'pt-BR'),
('6c906e1e-58c0-424f-a9b5-fe8bcd8e7799', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 1, '#7d9c8e', '#0a0b0d', '/2025/practice-day/public/perfil/wallpaper', 'médio', 1, 'pt-BR'),
('9eabc6a1-7ba5-4fbc-bef2-cf7dcab2facb', '837d2a76-7029-46d7-aea9-0885621d04d8', 0, '#486093', '#c4acdc', NULL, 'grande', 1, 'pt-BR'),
('aae3df05-d044-4d7f-ba4c-73aa8b3f39ae', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 0, '#f28197', '#f8cfdc', '/practice-day/public/perfil/wallpaper', 'grande', 1, 'pt-BR');

-- --------------------------------------------------------

--
-- Estrutura para tabela `conteudos_ocr`
--

CREATE TABLE `conteudos_ocr` (
  `id` char(36) NOT NULL,
  `arquivo_id` char(36) DEFAULT NULL,
  `pagina_digitalizacao_id` char(36) DEFAULT NULL,
  `texto_extraido` longtext DEFAULT NULL,
  `idioma` varchar(20) DEFAULT NULL,
  `precisao` decimal(5,2) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `destaques_leitura`
--

CREATE TABLE `destaques_leitura` (
  `id` char(36) NOT NULL,
  `biblioteca_id` char(36) NOT NULL,
  `numero_pagina` int(11) NOT NULL,
  `texto_selecionado` text NOT NULL,
  `cor` varchar(20) DEFAULT '#FFFF00',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `digitalizacoes`
--

CREATE TABLE `digitalizacoes` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `disciplina_id` char(36) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `total_paginas` int(11) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `digitalizacoes`
--

INSERT INTO `digitalizacoes` (`id`, `usuario_id`, `disciplina_id`, `titulo`, `total_paginas`, `criado_em`) VALUES
('e6639395-a4d2-4853-9863-7e6f8c8fec82', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', NULL, 'eita', 1, '2026-11-18 11:55:37'),
('ec2bf951-3b07-4033-b9e8-fa306136b49b', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', NULL, 'Foto Huguin', 1, '2026-11-04 11:10:55'),
('faccc05a-abd7-4514-8b11-fc0ce7833b48', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', '8618dbbb-106e-4366-a8f3-b093ae8bbd88', 'Hugo & Yas', 1, '2026-11-06 17:10:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `disciplinas`
--

CREATE TABLE `disciplinas` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cor` varchar(20) DEFAULT NULL,
  `icone` varchar(100) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `disciplinas`
--

INSERT INTO `disciplinas` (`id`, `usuario_id`, `nome`, `cor`, `icone`, `criado_em`, `atualizado_em`) VALUES
('0cd4b7e9-ca1e-49ff-a039-eaa660acf956', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'Matematica', '#6650b4', 'bi-calculator', '2026-10-21 18:17:07', '2026-10-21 18:17:07'),
('762734a7-5db1-4d92-a849-ce4381c464e7', '837d2a76-7029-46d7-aea9-0885621d04d8', 'Matemática', '#7c4d89', 'bi-calculator', '2026-10-16 14:24:48', '2026-10-16 14:24:48'),
('7ce2fd63-7005-4cdd-9101-2265965545ba', '837d2a76-7029-46d7-aea9-0885621d04d8', 'Física', '#4077a0', 'bi-lightning-charge', '2026-10-16 14:25:57', '2026-10-16 14:25:57'),
('8618dbbb-106e-4366-a8f3-b093ae8bbd88', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'DS', '#8396be', 'bi-code-slash', '2026-11-06 16:56:29', '2026-11-06 16:56:29'),
('c4e9d041-074b-43cb-adae-849064ff7e14', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'DS', '#43464c', 'bi-code-slash', '2026-10-21 18:22:07', '2026-10-21 18:22:07'),
('e0468669-f80a-4635-a6fc-dcf2de924849', '837d2a76-7029-46d7-aea9-0885621d04d8', 'DS', '#747881', 'bi-code-slash', '2026-10-16 14:24:00', '2026-10-16 14:24:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos_calendario`
--

CREATE TABLE `eventos_calendario` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `disciplina_id` char(36) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `tipo` enum('prova','trabalho','evento','lembrete') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `eventos_calendario`
--

INSERT INTO `eventos_calendario` (`id`, `usuario_id`, `disciplina_id`, `titulo`, `descricao`, `data_inicio`, `data_fim`, `tipo`) VALUES
('06c170b2-92ce-4f5e-8994-ac26c734358a', '837d2a76-7029-46d7-aea9-0885621d04d8', NULL, 'Vestibular UNOESTE', 'Vestibular UNOESTE, ADS', '2026-10-18 09:00:00', '2026-10-18 12:00:00', 'evento'),
('1f3f17e7-7b79-493d-90f5-275f30ab32ab', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'c4e9d041-074b-43cb-adae-849064ff7e14', 'Enem', 'Prova do Enem', '2026-11-08 13:30:00', '2026-11-08 19:00:00', 'prova'),
('4f964167-8664-4068-926c-8930d3bb37da', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', NULL, 'Natal', NULL, '2026-12-25 00:00:00', '2026-12-26 00:00:00', 'evento'),
('6503fe19-c457-4c83-821a-2b90daa4b466', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', NULL, 'Enem', 'Exame Nacional do Ensino Médio', '2026-11-08 13:30:00', '2026-11-08 19:00:00', 'evento');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fila_sincronizacao`
--

CREATE TABLE `fila_sincronizacao` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `entidade` varchar(100) NOT NULL,
  `entidade_id` char(36) NOT NULL,
  `operacao` enum('insercao','atualizacao','exclusao') NOT NULL,
  `dados_json` longtext DEFAULT NULL,
  `processado` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_arquivo`
--

CREATE TABLE `historico_arquivo` (
  `id` char(36) NOT NULL,
  `arquivo_id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `acao` enum('criado','modificado','renomeado','movido','restaurado') NOT NULL,
  `detalhes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `historico_arquivo`
--

INSERT INTO `historico_arquivo` (`id`, `arquivo_id`, `usuario_id`, `acao`, `detalhes`, `criado_em`) VALUES
('12881900-70e2-4a75-a059-07257bb4d13d', 'c54bb1e2-6c75-44a5-b12f-7d085bfd8cb2', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'renomeado', 'Nome alterado de \"6a7bcf8b37995-1786498955.epub\" para \"Livro.epub\"', '2026-10-23 13:37:08'),
('3347b852-fcac-477b-8e7f-0796ae70bfdf', 'c54bb1e2-6c75-44a5-b12f-7d085bfd8cb2', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'criado', 'Upload inicial do arquivo (6a7bcf8b37995-1786498955.epub · 6.129,1 KB)', '2026-10-23 13:36:50'),
('3e0fe9e4-2496-4fd1-9f08-d62213824eb2', 'bd40163b-2c06-4b78-bf5b-edc39108f951', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'criado', 'Upload inicial do arquivo (1428234.jpg · 78,4 KB)', '2026-10-23 16:52:44'),
('4b0d208f-8217-43ac-b59d-777509c09350', 'dd766835-4aca-4ddf-8df9-936ae0f20420', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'renomeado', 'Nome alterado de \"6a7a641ee6578-1786405918.epub\" para \"Desenfreados.epub\"', '2026-11-06 17:06:12'),
('52a9e601-e087-45d2-8f4b-87583b17cd1a', 'b2fe3940-926e-410a-9242-162575172e83', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'criado', 'Upload inicial do arquivo (6a7bcf8b37995-1786498955.epub · 6.129,1 KB)', '2026-11-18 11:59:04'),
('538497f6-2bcb-4c8f-9ec7-b8b3d323e181', 'dd766835-4aca-4ddf-8df9-936ae0f20420', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'criado', 'Upload inicial do arquivo (6a7a641ee6578-1786405918.epub · 5.443,4 KB)', '2026-11-06 17:05:40'),
('905c8fd6-6232-4aa2-a339-48010afa1588', 'e2680375-bd4b-4f21-8060-c47c825c401e', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'renomeado', 'Nome alterado de \"69deda76a1e6d-1776212598.epub\" para \"Shadow Hunters livro.epub\"', '2026-11-18 11:53:20'),
('932b307b-c7e3-4e99-bd95-69c749063bb4', '5143a4ca-2412-4b42-8241-a02cd76f5bb1', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'criado', 'Upload inicial do arquivo (Projeto integrador final- Yasmin Gabrielli.pdf · 27,0 KB)', '2026-10-21 18:23:50'),
('abb69ec6-3ad3-4480-9001-028bb514de23', 'aa79da72-c28c-4f69-9445-ec1223e2d6bd', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'criado', 'Upload inicial do arquivo (print-pwa-instalado.png · 46,9 KB)', '2026-10-23 13:08:48'),
('b4704994-57b4-488a-bd67-09908966d9b2', 'b2fe3940-926e-410a-9242-162575172e83', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'renomeado', 'Nome alterado de \"6a7bcf8b37995-1786498955.epub\" para \"O principe cruel.epub\"', '2026-11-18 11:59:21'),
('c0d0f758-d892-4607-9dea-f11c0e9b9e00', 'dd766835-4aca-4ddf-8df9-936ae0f20420', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'movido', 'Arquivo movido para a pasta: Livros', '2026-11-18 11:08:47'),
('cff5960e-7092-40f0-a85d-48ecf8d42ae4', 'e2680375-bd4b-4f21-8060-c47c825c401e', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'criado', 'Upload inicial do arquivo (69deda76a1e6d-1776212598.epub · 354,6 KB)', '2026-11-18 11:09:52'),
('ed8288fd-5d01-4bcc-9a49-ca03c49b9ebf', '8cc5587b-2e49-4d35-b223-d62e7a1c0cbc', '837d2a76-7029-46d7-aea9-0885621d04d8', 'criado', 'Upload inicial do arquivo (dante.jpg · 148,5 KB)', '2026-10-16 16:27:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_sincronizacao`
--

CREATE TABLE `historico_sincronizacao` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `sincronizado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sucesso','falha','conflito_resolvido') NOT NULL,
  `detalhes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `lembretes`
--

CREATE TABLE `lembretes` (
  `id` char(36) NOT NULL,
  `evento_id` char(36) DEFAULT NULL,
  `tarefa_id` char(36) DEFAULT NULL,
  `data_hora_lembrete` datetime NOT NULL,
  `disparado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lembretes`
--

INSERT INTO `lembretes` (`id`, `evento_id`, `tarefa_id`, `data_hora_lembrete`, `disparado`) VALUES
('15caf627-4eea-4b17-8bc9-0ec2eb589556', '06c170b2-92ce-4f5e-8994-ac26c734358a', NULL, '2026-10-16 11:34:00', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `listas_checagem_tarefa`
--

CREATE TABLE `listas_checagem_tarefa` (
  `id` char(36) NOT NULL,
  `tarefa_id` char(36) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `concluido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `listas_checagem_tarefa`
--

INSERT INTO `listas_checagem_tarefa` (`id`, `tarefa_id`, `descricao`, `concluido`) VALUES
('0dc94fd5-2117-4149-8941-7bfac2af6d21', 'cfa41649-a669-49e3-9cec-c505cef9248d', 'Som no Pomodoro', 0),
('0fec2ab8-bcf4-487c-b636-2d1d83c84bee', '5aa2f58b-4845-41b6-bcc1-b60ecd88b223', 'Montar leds', 1),
('5e4b1b72-2311-43a3-bf3c-06cd12c033d8', '5aa2f58b-4845-41b6-bcc1-b60ecd88b223', 'Montar LCD', 0),
('6a1cf9b8-c6fc-4a23-8c9d-b5364a16284f', 'cfa41649-a669-49e3-9cec-c505cef9248d', 'Arrumar reponsividade', 0),
('8dd68c23-ede2-4cf3-bcf9-8562e168f560', '5aa2f58b-4845-41b6-bcc1-b60ecd88b223', 'Testar Projeto', 0),
('b4ebb7f5-cd13-4e7d-803b-68444bff1cfe', 'cfa41649-a669-49e3-9cec-c505cef9248d', 'Mexer no nav', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `lixeira_arquivos`
--

CREATE TABLE `lixeira_arquivos` (
  `id` char(36) NOT NULL,
  `arquivo_id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `excluido_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `expira_em` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `marcadores_livro`
--

CREATE TABLE `marcadores_livro` (
  `id` char(36) NOT NULL,
  `biblioteca_id` char(36) NOT NULL,
  `numero_pagina` int(11) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `marcadores_livro`
--

INSERT INTO `marcadores_livro` (`id`, `biblioteca_id`, `numero_pagina`, `titulo`, `criado_em`) VALUES
('09736099-cdb3-4114-bcd3-533c0c70a94b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, '__cfi__epubcfi(/6/34!/4/4[id__798_136_]/1:0)', '2026-11-06 17:05:56'),
('84545c58-8529-40fd-8c70-88b32bb4dd28', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 0, '__cfi__epubcfi(/6/14!/4/8/2/1:0)', '2026-11-18 12:00:21'),
('ddb4123f-4626-4af6-ada4-15162aca4e7d', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, '__cfi__epubcfi(/6/18!/4/2[calibre_pb_6]/1:0)', '2026-11-18 11:09:59'),
('e193cef7-bcd8-421e-b899-91f0a92835a1', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, '__cfi__epubcfi(/6/14!/4/8/2/1:0)', '2026-10-23 13:37:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notas`
--

CREATE TABLE `notas` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `disciplina_id` char(36) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` longtext DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `notas`
--

INSERT INTO `notas` (`id`, `usuario_id`, `disciplina_id`, `titulo`, `conteudo`, `criado_em`, `atualizado_em`) VALUES
('30ae59b7-409f-4c30-9873-5fdc0260d29e', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', NULL, 'Prompt para melhorar o site', 'Quero que você melhore completamente as paginas de leitura de qualquer arquivo, deve ser completamente acessivel, facil de ler os arquivos, quando for epub ou pdf, deve dar para ler em tela cheia, de deslizar ou de passar as paginas com as setas do teclado, ou pelo celular, passando com o dedo para o lado ou para os lados dependendo do modo . deixe o modo de leitura o mais moderno e acessivel possivel, de modo que torne até a leitura de arquivos confortavel dentro do site e quero que detecte uma capa do livro automaticamente, seja ele pdf ou epub, quero sempre pré-vizualizações . quero que melhore tambem a experiencia da camera, faça com que a camera tenha melhor qualidade nas fotos, melhore a hora da captura e arrume o lado da camera, estamos no brasil, estado de são paulo, na pagina da camera, deve aparecer uma pré-vizualização das imagens tiradas antes ao invés de um card escrito.', '2026-11-06 18:40:18', '2026-11-06 18:40:18'),
('60e4166d-4517-4ca5-891b-b85d5147ee33', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', NULL, 'Resumo do dia', 'Hoje tivemos que fazer revisões do projeto, fazer uma atividade pratica de levantamento de requisitos.', '2026-11-04 18:10:48', '2026-11-04 18:10:48'),
('cba3a34d-e323-4962-a634-9e544df4a48d', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', NULL, 'Resumo do dia', 'Hoje foi feito ajustes no projeto da feira de ciencias para que amanha seja apresentado', '2026-10-21 18:44:15', '2026-10-21 18:44:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `paginas_digitalizacao`
--

CREATE TABLE `paginas_digitalizacao` (
  `id` char(36) NOT NULL,
  `digitalizacao_id` char(36) NOT NULL,
  `numero_pagina` int(11) NOT NULL,
  `caminho_imagem` text NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `paginas_digitalizacao`
--

INSERT INTO `paginas_digitalizacao` (`id`, `digitalizacao_id`, `numero_pagina`, `caminho_imagem`, `criado_em`) VALUES
('177a97b1-069a-4afe-b894-228f1465b9f9', 'e6639395-a4d2-4853-9863-7e6f8c8fec82', 1, 'scanner/e6639395-a4d2-4853-9863-7e6f8c8fec82/177a97b1-069a-4afe-b894-228f1465b9f9.jpg', '2026-11-18 11:55:37'),
('a14686fd-2d42-4adf-bce5-0f440ad8d910', 'faccc05a-abd7-4514-8b11-fc0ce7833b48', 1, 'scanner/faccc05a-abd7-4514-8b11-fc0ce7833b48/a14686fd-2d42-4adf-bce5-0f440ad8d910.jpg', '2026-11-06 17:10:46'),
('f4ab4c3d-a52f-4b5c-9a25-dd300e38ffc2', 'ec2bf951-3b07-4033-b9e8-fa306136b49b', 1, 'scanner/ec2bf951-3b07-4033-b9e8-fa306136b49b/f4ab4c3d-a52f-4b5c-9a25-dd300e38ffc2.jpg', '2026-11-04 11:10:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pastas`
--

CREATE TABLE `pastas` (
  `id` char(36) NOT NULL,
  `disciplina_id` char(36) DEFAULT NULL,
  `pasta_pai_id` char(36) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `pastas`
--

INSERT INTO `pastas` (`id`, `disciplina_id`, `pasta_pai_id`, `nome`, `criado_em`) VALUES
('bd394006-b365-433d-8545-b03e7c6c5177', NULL, NULL, 'Epub\'s', '2026-10-23 11:36:00'),
('c2f614c4-afbc-4142-a959-f46b05988320', 'e0468669-f80a-4635-a6fc-dcf2de924849', NULL, 'batata', '2026-10-16 16:27:00'),
('cc51a531-91d9-404a-bda6-d8c3dc06e0ce', NULL, NULL, 'Pdf\'s', '2026-10-21 18:22:58'),
('f512904e-b9f6-4eb4-89a4-d275d5e3a1f9', NULL, NULL, 'Livros', '2026-11-18 11:08:28');

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissoes_pasta`
--

CREATE TABLE `permissoes_pasta` (
  `id` char(36) NOT NULL,
  `pasta_id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `tipo_permissao` enum('leitura','escrita','administrador') DEFAULT 'leitura',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `permissoes_pasta`
--

INSERT INTO `permissoes_pasta` (`id`, `pasta_id`, `usuario_id`, `tipo_permissao`, `criado_em`) VALUES
('5dca31e6-58dd-40ea-a07f-df8c016dcf9a', 'c2f614c4-afbc-4142-a959-f46b05988320', '837d2a76-7029-46d7-aea9-0885621d04d8', 'administrador', '2026-10-16 16:27:00'),
('65129fc4-931c-4b3a-886d-9ed2e60f2649', 'cc51a531-91d9-404a-bda6-d8c3dc06e0ce', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'administrador', '2026-10-21 18:22:58'),
('6721ab38-6264-42ea-87b9-a7e7511a49fe', 'f512904e-b9f6-4eb4-89a4-d275d5e3a1f9', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'administrador', '2026-11-18 11:08:28'),
('89bee632-7ba2-4a7d-89fd-b99ade0d3f82', 'bd394006-b365-433d-8545-b03e7c6c5177', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'administrador', '2026-10-23 11:36:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `posicoes_widget`
--

CREATE TABLE `posicoes_widget` (
  `id` char(36) NOT NULL,
  `widget_id` char(36) NOT NULL,
  `posicao_x` int(11) NOT NULL,
  `posicao_y` int(11) NOT NULL,
  `largura` int(11) NOT NULL,
  `altura` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_leitura`
--

CREATE TABLE `progresso_leitura` (
  `id` char(36) NOT NULL,
  `biblioteca_id` char(36) NOT NULL,
  `tempo_leitura_segundos` int(11) DEFAULT 0,
  `paginas_lidas_sessao` int(11) DEFAULT 0,
  `registrado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `progresso_leitura`
--

INSERT INTO `progresso_leitura` (`id`, `biblioteca_id`, `tempo_leitura_segundos`, `paginas_lidas_sessao`, `registrado_em`) VALUES
('00a742fb-edde-445b-be2a-c440b3ee8907', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:46:25'),
('017b7470-44ca-43d1-b367-2eea50c77b92', 'e120e6ac-22be-427c-ac95-12a05429df03', 4, 0, '2026-11-04 11:03:24'),
('019b2788-ac10-4fa2-97ca-b9e21f71b6f0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:42:25'),
('01a9bff8-3585-4aef-82d5-79d47dcd61be', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:40:24'),
('0285bd55-7fa7-48b1-98bd-63a7d1f78fae', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:56:13'),
('02874dc5-d964-4ac6-9958-14e22d2187dc', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 18:21:56'),
('030b94c6-a500-4dec-baf2-593c0c1b7340', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:06'),
('0387684b-eef7-4308-986f-6263597bba69', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:57'),
('0398fb11-f888-4da6-b228-d081c24e28aa', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:34:05'),
('04222ca0-a77c-4744-b05f-d344f400167e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:36:25'),
('04382ea1-d03e-45d1-9110-fadc983a8edf', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:10:09'),
('04707395-e078-4c17-93d3-9dd422545c9f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:24:25'),
('04b0c4b3-ac18-4ad4-a0ce-6e0f0a248a4e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:43:52'),
('04bb371b-35e6-46cc-9b55-e7056a638126', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:22'),
('04db85bb-a8c4-49b7-a79b-e6ce1e47a300', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:54:06'),
('050181ff-0659-48b6-b25f-f0dd45819f78', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:43'),
('0516f957-12fd-475a-951f-12c6d589ff9f', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:28'),
('056dbbe7-7773-4873-b59c-0ac0ce8210c9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:07:45'),
('05da1112-1dc6-4fcb-9a4c-f05a5eb9bdb3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:10:05'),
('0606cd1c-ebc8-4044-a5ea-c97f21c0f804', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:56:05'),
('0636bee1-1300-45b7-a5c5-520b52a46c76', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:37:43'),
('070bdfed-2846-44aa-a6b6-94720494bc08', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:10:25'),
('07d44ffa-cd4c-4968-93d7-a30db046c1a1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:50'),
('0892f8ef-a906-4720-8eed-aa9de74b41b8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:55:05'),
('090157bc-b58f-42d1-adf4-138f39e8074c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:32:35'),
('09c34124-02f4-496e-a0c9-1ab0f196b9c7', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:48:49'),
('09d0b705-fbd9-4d9b-a643-8eaaa61ea211', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:56'),
('09e1d77d-4289-4032-83ae-bc296f3adad7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:42:05'),
('0c24ac6d-d158-49d2-9928-0a2ddf4ac0e4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:20:05'),
('0c4ee3a0-7bf0-4711-9c8a-748dd0a89601', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:24:05'),
('0c5215f8-85c0-48ee-add8-eaef503571fa', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:13:45'),
('0ce75c77-a568-4e49-bbd5-93067ad53147', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:54:05'),
('0d76a528-fcaf-4998-9175-f4469f9ca468', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:42:32'),
('0de7b715-1872-4d1b-a795-a38458cf1ee2', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:53:51'),
('0df4ea43-ebe9-4358-b211-d3fe8f8eecb0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:01:34'),
('0f4b7caf-d4c9-4335-b1f5-9dcbfa964bd0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 75, 0, '2026-11-18 11:52:50'),
('0f9454c0-255c-4eff-a6d4-f02d7caa126c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:50:15'),
('101cf1b1-88c9-4034-963a-04aecbff5792', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:58'),
('10fe5ec6-3092-4d86-a547-0d498eaed803', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:44:05'),
('11712a0b-b188-4c70-96cc-7bdd5fc4aa22', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:39:25'),
('1237e9f7-9291-4e49-a6ef-d4b64cc8db14', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:52:25'),
('123f98f0-b36b-4be1-b362-d12e6bdd93dd', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:52:05'),
('127ffd0d-4f64-4b3d-b44b-28b596ab54d9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:28:45'),
('12c38d3d-3615-4d78-bc58-070f79fc1b51', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:41:03'),
('1322f40d-389b-4fdc-a95c-c41b9ee7cee6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:40:25'),
('14c20a14-92dd-4f0f-bdd3-c4cdcaa0ef81', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:47:25'),
('14e6eb5c-bacb-43d6-8ea5-1260522c7a66', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:38:43'),
('156b6481-29c1-419e-b489-2e31cf3ece07', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:41:25'),
('15abf1d8-a41e-4ac0-9c4a-d32cc20399fd', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:39:12'),
('15b267d5-6693-4e10-80ff-bf851cb7c43c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:32'),
('15b49ab1-cfd0-4e8e-ae45-b6e5c00067d4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 120, 1, '2026-11-18 11:29:57'),
('15f813ab-6e72-47e6-8dba-f397e2595179', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:41:25'),
('164a94d4-230e-4327-af1d-2c984134c3c2', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:15'),
('16fafe24-7d11-4ed4-a808-5e195d4fd4d2', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:40'),
('174ffd93-1875-4c21-9e43-adb5f159c188', 'b892980b-1cca-4c49-8620-6c2823a5f812', 47, 1, '2026-11-06 18:13:05'),
('17bdc49e-7fb3-402f-acd9-dfb41a2f9efc', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:46:21'),
('184c7b35-beaa-4a16-b459-d5501359f9ee', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:40'),
('18aa7b00-1045-437a-a163-0739b1ed0027', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:10:45'),
('18c159f6-78f6-411a-a9aa-9718c1c6ce5d', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:26'),
('193a5901-c216-47ba-b6f1-5650148a2afa', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:13:00'),
('19dcdd6e-833e-4830-b408-2a564a623cc4', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:24'),
('1a5aca8e-62a8-498d-a18b-394c656b4dd8', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:32'),
('1b0600ba-2b38-48ec-bf83-dfd6885987c3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:34:46'),
('1bd8ded2-968e-43b4-b5bb-9199800825e1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:11:45'),
('1bde2e61-dc78-456a-beb5-6448ba5b45c5', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:35:05'),
('1c19787d-8845-4bb7-be74-5da5a069a74d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:31:35'),
('1c2f2ec4-e201-4f9f-931b-9bc6abc57374', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:39:50'),
('1ca8702b-896e-4026-8493-aef73489df36', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 18:22:24'),
('1ce61712-01b2-48ed-ac7b-886cbd2e53a6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:37:05'),
('1d02369e-8749-46a2-912a-d035ce5c5fb2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:50:05'),
('1d33da99-f1b8-48cf-a4f8-d81c46f5e936', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:47'),
('1d5564b2-0e4e-4eeb-8ed6-c674f12dad59', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:10:08'),
('1d5ccbea-6f43-4058-967a-132ff102cd7d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:54:51'),
('1d76dc3b-2aa6-4254-b0f2-a5fb580ae0a4', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:01:07'),
('1dbd0ac5-27ef-4ec3-86f6-e9af77d92009', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:14:45'),
('1f52c1c5-a8a4-4fe7-847e-1a7f587448a3', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:15'),
('1f71a708-528e-40e2-9877-428a63097c19', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:46'),
('1f97a3e2-d252-42ed-9f68-bb1d5afc2805', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:39:02'),
('1faabe43-0353-48dc-8ea0-668f3d7aa3e3', 'c590d325-670f-4e86-a10e-3d68adf53084', 7, 1, '2026-11-04 11:03:11'),
('21286189-1b7d-4c41-bbff-b9571cd78c5f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:51:05'),
('213d340e-6c1e-4caa-ac28-94bd21ef6f4e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:48:02'),
('213f16de-7d00-4121-b2fc-6715687c98f4', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:40'),
('213fce1f-903b-411c-98f6-a87e0ce8dcb3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:36'),
('21cdbc28-5e0a-4678-a106-698dbce4eea3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:36:09'),
('22082f67-eebd-4060-8058-72a02c9250e8', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 16:27:51'),
('2235f325-379d-42be-9c19-ba4853bafa77', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:29'),
('223e647b-caaf-4a5f-9178-bf5de8385a69', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:36'),
('2277d3ee-9127-47ff-8f9d-a542681cffab', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:32:39'),
('229e3773-fe97-4029-9425-3703d172cc7f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:41:12'),
('22b70bea-97e5-404e-b201-415f4a5c4793', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:30'),
('22e03180-825c-4922-a06f-2966a36b506f', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:43'),
('230be4a4-6499-40e5-b402-b9efa749a0b8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:19:28'),
('23304736-80a1-4e76-9fbf-44490b390140', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:47:05'),
('24513b78-82e8-40f2-8f5a-755e7449086b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:46:05'),
('25225917-424a-4827-a4b3-4a3919a61f57', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('25415e67-03e6-44b9-9f4b-87a554ea4393', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:03:05'),
('263b2190-9ecd-4aea-942c-2d7e5f6a7098', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:02:25'),
('263dfd94-93d7-4abb-ba76-4d9b9fb674cd', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:31'),
('26ce636c-8261-4eca-a185-ae8457b7ae42', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:40:55'),
('27069ef0-723c-4325-9842-afa30fd294d5', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:38'),
('274a77c4-f7d6-462c-b69e-850b571857c1', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:27'),
('2775db1b-3708-4686-b1d0-3c6906caa363', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:06'),
('27d170f2-e58e-4bea-8b7f-ed1e1bab572a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:45:45'),
('27fedefa-1bb5-4002-87c1-73a89cacd29f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:31:56'),
('283cc6a1-b162-4256-9dcd-219fc2f5ffa5', 'b892980b-1cca-4c49-8620-6c2823a5f812', 59, 1, '2026-11-06 17:07:22'),
('2855e568-8937-4d3f-aaf5-9aefa482e851', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:44:08'),
('287071ad-8d20-444a-a27e-08d50f73a089', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:39:00'),
('28898ee8-a260-4246-8874-dbb11ca88bdd', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:33:15'),
('2a0da6f7-b5d5-4c4e-800c-0cefb504bc9e', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 20, 0, '2026-11-18 11:54:28'),
('2b7d04f5-0fb4-41b0-a168-fb69e47c8031', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:09:25'),
('2bd403f6-13a7-403f-a568-c3a68cf049e1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:33:55'),
('2c5ab31e-027a-455f-a5f7-4a01ebd85bbe', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:36:12'),
('2c99bbaa-d573-476c-8283-5b254f7b61ef', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:40:24'),
('2dc4fb7e-ef72-44d5-ad4b-2fb42c3e62f5', 'c590d325-670f-4e86-a10e-3d68adf53084', 27, 1, '2026-10-23 13:48:56'),
('2de564c9-2f08-4c75-b2ea-87af27afca5b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:30:04'),
('2e28967b-e2b1-4e9a-8d26-9af2aea8c554', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:30:05'),
('2e3ae1e1-a156-4987-a14e-00b86cdece0a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:17:25'),
('2ea26180-12b2-4f90-8b4e-4c3c6967c83f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:38:25'),
('2eca68ab-5c57-40db-9dec-64c697e4cb87', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:15:45'),
('2f3006ab-1784-486d-8b5c-af88036eb5a2', 'e120e6ac-22be-427c-ac95-12a05429df03', 0, 1, '2026-10-21 18:23:53'),
('3011cb57-5d5c-4c8c-a54f-326fd55e5517', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:56'),
('301f4339-6383-4d28-8a61-fff397ee0a50', 'b892980b-1cca-4c49-8620-6c2823a5f812', 22, 0, '2026-11-18 18:01:36'),
('30f06cd6-b5b1-4075-9df5-7e54d86904c7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 14, 1, '2026-11-18 11:45:49'),
('3204768e-4d84-472c-9617-718dfa93b0f9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:45:44'),
('32275fa3-851f-42e4-a9cc-fcec5025ea68', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:31:06'),
('323b9c24-cb96-4318-b9b1-5b082e741f40', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:26:45'),
('3280e76b-9242-4e82-9dd7-abded67f1a4f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:53:45'),
('32d41cb3-2909-4a0f-a9c4-9973a041c85e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:52'),
('33cbb822-6f5e-4fe5-a492-1f299f52c886', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:39:28'),
('34407dc7-bfa8-4501-ae4d-8dba0298f6c5', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:32:55'),
('345cb35d-4ef8-4e2c-b38a-3c95749c2d5d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:22:42'),
('34b027de-3911-449d-a3a4-735b6665bf53', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:47'),
('3503bb5a-4dc1-4b90-91e5-83ae83963000', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:39:42'),
('357c8585-ff9e-4660-b6b5-d0337a9a4339', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:25:25'),
('35ba11a4-b4b5-4ba2-b670-8770ebdf681b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:33:50'),
('35d93fe7-477c-4e83-8ef1-dd815b7e12ee', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:49:45'),
('35f048e9-2a8f-4c83-bd63-a98a3f70a596', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:30:43'),
('36785151-ea76-4ffe-af85-71a31bc2831e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:19:25'),
('367e2ffa-e86f-40d1-8f84-acf2c7528083', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('36a28d07-85d9-446a-970c-0104c95bb99c', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:48:46'),
('37cbff09-a6cb-44cf-8843-bca05c46b00f', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:40:05'),
('3814a866-9d69-4143-96b3-2846f22486fc', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 20, 0, '2026-11-18 11:54:08'),
('382bfe95-08ef-46f3-b233-4dafe89cefb1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:54:27'),
('387e467f-70e3-4aa8-a3b9-ba826640cb67', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:09:45'),
('38e52816-b47c-45e5-92fd-dced4f598c01', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:34:58'),
('3936862c-11ea-4639-adf3-859d3078c5d6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:35:46'),
('399c260a-8b74-4d63-90aa-9bade7aea78c', 'c590d325-670f-4e86-a10e-3d68adf53084', 34, 1, '2026-10-23 14:07:37'),
('39cabe4e-b91a-4888-a20e-f7659d1eff89', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:24'),
('39f4f4a2-e262-4154-aa55-f10b39393a6a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:33'),
('3a41f0ed-e33c-44f6-9449-dab3a45ab8a2', 'e120e6ac-22be-427c-ac95-12a05429df03', 5, 0, '2026-10-23 11:07:01'),
('3af338d8-2dfb-4f37-9d70-4028c9f6f1c9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:12:25'),
('3b371591-3c36-43d3-aa10-f81e080285d7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('3b49e23e-aebc-48b3-934c-12d42cffd167', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:51:47'),
('3b524e95-1f65-4f37-97f4-0cbc63533f40', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:39'),
('3bb413dd-4473-43ea-a7f0-b279e930e839', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:38:45'),
('3bf1cc81-5abf-475c-8f80-21094814e79c', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:40:40'),
('3c032e50-7dfe-40d4-ac5d-a8aee7258f11', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:06'),
('3c3a31e6-d907-43f7-9cf3-d859cfb33ea3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:47'),
('3c5b016d-617d-4de3-a9f4-e5e31d1fc355', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:39'),
('3c82ca49-d5f3-457a-b2df-5a4097697466', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('3cd72230-b850-4f32-a6b9-60300625e0ae', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:53:33'),
('3dd379a2-9208-453a-9816-c1ca51c248c1', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:37'),
('3eb9c4e0-8af8-4051-9c01-4b02d55cf8e3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 11:52:35'),
('3ef73156-b698-4b20-bc98-4c988cc89627', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:54:35'),
('3f2005b3-42a6-4855-a8f8-991c5e5f9d04', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 20, 0, '2026-11-18 12:02:15'),
('3f2cd350-9db8-46eb-91e9-74fb62d00c32', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:35:42'),
('3f4cdb06-0745-4f00-8981-4879a987e350', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:54:25'),
('3f5ccf0d-a633-455e-b967-5e328cd6d8f3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:00'),
('3f76191a-31af-439d-af21-905f4d852f0e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:29:12'),
('3fec48e1-fcd8-4cfa-a00f-04557b0c8532', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 0, 1, '2026-11-18 12:01:34'),
('408055e5-c699-42fb-9603-a5d3a59a52a8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:05'),
('4102ef53-fd95-404d-ac97-fe419208b10e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('412ca897-735e-437b-b582-b783dbbfb701', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:36:12'),
('41739b76-f1d0-46f6-9c49-15e989e7cb01', 'c590d325-670f-4e86-a10e-3d68adf53084', 21, 1, '2026-10-23 16:28:03'),
('425c36fc-f5c3-40c7-98cf-143f8cd1ec6c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 11:51:55'),
('426a3103-d7f5-4ba8-ba1f-8c42f8db1c1e', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:17'),
('43100841-680d-405d-9de3-570664bed53f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:52:26'),
('432e2f79-8025-4654-8d83-313fb478d217', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:32:15'),
('4335e728-0e7d-46f8-b21e-c436a6b4d782', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:38:42'),
('4362e74e-3a5d-49ce-86bb-cdd52fa4a833', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:40:23'),
('437e682d-2ea7-4c2b-ba83-9fde1b28ac54', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:06:25'),
('439af67a-be93-4586-ac59-cb85281c4db1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:51'),
('43e45b3d-6076-433f-9cbc-054697f9e6e8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:22:45'),
('448e9453-db31-452e-a975-8fea11ce12f8', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:42:25'),
('450526eb-1d06-4c72-b725-8ded54c7d5d2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:40:50'),
('455edff5-7377-4074-ae0f-d8c52b8903dc', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:45'),
('46777c0b-8bbe-40d7-9b5b-f2a74bb2450c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:32:05'),
('468ff770-c7a9-4416-9972-ec75c5dd3bd8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:56'),
('46a533fb-0952-4886-bcfb-4610b67c04eb', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 20, 0, '2026-11-18 12:00:35'),
('46c6cd16-018c-4f95-b235-6f066eec5343', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:48:48'),
('46debd43-c606-419d-bcf1-48c236962d13', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:41:45'),
('46e9282a-27ff-4387-81ac-98004abdca80', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:35:53'),
('484bebbe-c31c-4e2f-abb3-7889be3204ab', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:54:20'),
('4a4c9473-f9d3-4cff-950f-cd2f33fd12f3', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:39'),
('4a4d37ff-666a-4a45-b1c3-35b1808982ac', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('4a565f11-cf17-4f52-94c8-010f14991cd4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:16'),
('4a807586-cb5f-4bf5-b65a-a243e8f4bb4d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:23:05'),
('4b39bcc6-2a8f-4838-9cba-6dd4cde55ebf', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:55:07'),
('4b444c78-9fc8-456e-8d8e-fc93e5b0ce12', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:42:28'),
('4bc9254a-3864-4b9a-9aa0-b66881e003ce', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:34:54'),
('4c2c895a-fddf-448f-9e85-f46ca0dd0eb4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:36:32'),
('4c912b17-73d5-44c8-9bff-89325967b73d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:19:05'),
('4d0ff29b-d9a5-4994-b1f0-3c348a3fe25a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:16:45'),
('4d2616fb-1e21-4594-a5e9-264e8d2c293c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:34:15'),
('4d6de744-a47c-4bcb-9087-d6305438852f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:34:25'),
('4dc4095a-78b3-41ce-93ad-c7528cfc0aaf', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:38'),
('4dd8509b-d8c9-4070-bc4e-66996d1fc45b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:57:27'),
('4df108bb-a906-4efd-9b15-a236c1888a6a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:06'),
('4e9f8bca-df34-4643-a96e-9745c3a50eee', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:40:48'),
('4ebfb457-acea-4655-b7ce-7f741049009b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:33:35'),
('4eeeb876-25f8-4eb9-9c09-e157469e5a9a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:15:05'),
('4f03a5a9-9e4f-4504-8d7c-9bd52b36c0ae', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:40:12'),
('4f7ae145-2d72-4c3a-87dd-0a00c1156369', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:56'),
('5014ceef-1158-4f6e-8423-6afd2761b88c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:44:12'),
('50330261-fea2-4689-88ee-0944c32bc687', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:17:45'),
('504513e9-8d50-4185-b186-54d2cc135c6b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:22:05'),
('505f5292-6b9d-489d-860f-b423b39941a3', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:49'),
('50ba144a-cc83-4f51-8197-75a7036fdaa4', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:41:10'),
('5112435c-8ec7-4f9f-b584-450962a71e74', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:45:31'),
('5139e1dc-0e50-4c1e-827f-2b21c37d8cc9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:49:25'),
('5158a02e-0102-4e33-acd7-75b83f9a7c64', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:50:46'),
('5167562b-bffe-4408-b93d-08cec85bf092', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:35:15'),
('516df035-f735-4efa-a370-f7ae54dc9fa2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:20:51'),
('5216443c-b745-4152-ac19-5923bcb81de6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:22:50'),
('524687ca-c650-412f-b590-b3ba3536c114', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:25:05'),
('52ed6866-8ed9-44a6-b09a-1f8c8a15c76c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:44:25'),
('5342f1c4-3c3f-43e5-a311-a04c5f0200df', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 0, 1, '2026-11-18 12:00:21'),
('53ae989a-4276-4465-bf6c-f908bea5b56d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:56:21'),
('53cb0436-a328-4f5d-99d4-d41dbbe2b48a', 'c590d325-670f-4e86-a10e-3d68adf53084', 10, 1, '2026-10-23 13:37:23'),
('5445b8d6-034a-42fc-839c-d0e0c863c346', 'b892980b-1cca-4c49-8620-6c2823a5f812', 120, 1, '2026-11-18 11:45:32'),
('54483042-529b-4d99-a134-394790abc0c9', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:07'),
('545fcefd-9519-493b-a1b3-9ad4b920fa53', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:53'),
('5476b687-6224-47a9-8a93-4e09f44ea1c0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:54:34'),
('54a506e4-9faf-46e5-8ecd-43230b8bfeed', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:23'),
('54ac548c-a43b-461f-b891-f17b60c570d6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:31:05'),
('54eaf889-acf8-4e95-8dc1-892680674527', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:54:17'),
('550b22b8-251a-42f6-9b17-00867d3d8b2d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:06:54'),
('55594d39-feda-4b61-a39f-7b39947272ba', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:35:56'),
('555fb661-8c55-43bd-a224-a932bca7bd3f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:51:59'),
('55ed1582-8b28-4613-b370-906998cc2ac7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:42:52'),
('56606ff4-1aa3-447e-9de8-0aa89d80ad04', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 20, 0, '2026-11-18 11:54:48'),
('57979080-d1fb-49af-a239-1e07c9e149e5', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:53:51'),
('580eed8e-4dd6-4839-8899-fbce885fbab0', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:44'),
('582d5ce9-35c4-4c25-a03c-e067a5e05c31', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:40:23'),
('58ceb3ad-02b9-4218-aeec-31702d141712', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:39:05'),
('58df23cc-cc6b-4808-9baa-62d6fbd2921a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:43:45'),
('5939491a-cfea-4564-980d-c677141165d6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:12:45'),
('59a53ac2-bc7e-44bb-8010-3299f220afa6', 'c590d325-670f-4e86-a10e-3d68adf53084', 72, 1, '2026-10-23 13:39:49'),
('5a01139f-cdbb-4db4-adb5-4b5a4a65eade', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('5b62b230-4ff2-4182-baa6-049fe4de8f95', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:01:24'),
('5c87f6fc-63ef-463a-ac91-a8b2e8984e1f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 180, 0, '2026-11-18 16:35:27'),
('5cdcd175-eb69-48b7-9b0a-fcd8f5921f1f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:30:20'),
('5cebcfb1-92b7-42f7-ac8d-5ed07af2fdef', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:18:45'),
('5d4e5eb9-a81b-41a3-8b2b-1ac4f682f249', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:12'),
('5d533caf-c2e9-4874-9291-86907bc81cba', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:41:32'),
('5d5630bb-8795-43e6-b895-4977e787dc2a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:57'),
('5e7c7392-059d-4a08-a785-694a033756cb', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:42:50'),
('5e8724d6-65af-42c3-b6d3-82acbf88a810', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:36:32'),
('5f5b8bc5-d9b3-4101-9c79-d312c8ad563e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:02:05'),
('5f6a094e-a493-451b-b40e-cdfbd58ad56a', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 18, 1, '2026-11-18 11:10:13'),
('5fdffbb9-8c79-4744-b448-57041052425f', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:54'),
('5fe74f8b-8bb1-4c84-b2ce-10fc1a1b4215', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:40:54'),
('5ff6b811-54f3-4287-911e-7c7f04fe8db4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:26:25'),
('60fe6782-da52-4b45-8fa1-097ea5550dd4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:02:20'),
('612eae38-4b00-4b23-8077-d32b2a1afbf3', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 13, 1, '2026-11-18 11:46:27'),
('61768cb6-aa65-4543-aebd-87fff5613434', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:34'),
('6261eaa7-06dd-42c6-aa2f-d1cd0638de45', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:02:45'),
('62a84a84-c892-4689-8154-6f61b85a7d99', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 0, 1, '2026-11-18 12:01:21'),
('636daa86-67e0-4378-bd10-b2d8fd010ce2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:42:29'),
('63ea232c-bbb4-4f25-b5a3-f8de1eb167ec', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:32:50'),
('64d42a93-9012-4c45-a1e2-7ac39f2b764a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:44'),
('65662ce4-033d-4b2f-9cea-cd70ee0748e9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:55:25'),
('6582ce52-ea25-4f63-b026-6771c80b54d5', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:41'),
('65dccd7f-741a-4e84-8237-a14e18657742', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:46:26'),
('662d9c37-6ef4-4c80-b914-6483c71711b0', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:23'),
('6643881d-72e7-49b2-a973-763a6679c8a1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:25'),
('66573da6-51d8-4705-9bcb-41d4b4fb5a50', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:59:27'),
('66755715-e8ab-46e6-8c08-9453a981897b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:52:36'),
('66de1e14-dda0-4487-aa7b-af3b7000c9f9', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:43:25'),
('66e9ea41-c9e2-417b-9a98-12f1ba2ae355', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:30:24'),
('6707523d-1548-41ca-b109-ff72c5f8883e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:36:05'),
('6827c3b5-2836-4b7d-99d3-a0922345f9ad', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:42'),
('68b80919-d775-4c9a-8f58-cff9c2edd330', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:54:14'),
('68fd9552-1a26-4913-8708-76ab6814a486', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:30:55'),
('697f30b0-fdd5-4fb5-bb77-80e83ba03215', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:40'),
('6a226216-594f-40ce-917f-fe863c538f4e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:32:48'),
('6a32217b-d211-4652-8b1b-4b6cc9a00808', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:43:05'),
('6aac1fad-93b1-4b95-a045-69100ad6d411', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:53:05'),
('6b2dd5cf-9fb1-4e2f-873c-2518b3b6e115', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:52:37'),
('6b5d7410-9e9b-4acd-9202-319a0d74550a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('6b6b1c20-0bdf-45ce-862a-71d769483b43', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:37:41'),
('6b914140-914a-43e8-b215-abca66232480', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:47'),
('6bd7245c-8174-4ff7-af3f-03ccd3bf04f0', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:46'),
('6c2a45ef-cd58-4891-998b-2ddcf10e6246', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:05:05'),
('6c67aeea-5952-488d-a7f9-3285e75f47d2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:34:37'),
('6cbfa262-db89-4cff-a709-5e7d71c3f5da', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:13:19'),
('6ccb0e35-dda0-42d4-b6cb-07dc8cb69741', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:32:44'),
('6d102ae7-08ae-414d-8231-5ecf6b5d9b8c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:54'),
('6d330a87-df66-46e6-9ea2-01abdbfd9b21', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:08:25'),
('6d610b97-0718-411b-852d-a8796f679f2b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:30'),
('6d8eed64-2ea3-4e32-b87f-c0e28b6bad5b', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:34'),
('6ecb6afc-925e-4db0-907f-382fea2d50ab', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:12:05'),
('6f297ad0-74c3-4995-85db-44b3daa88a9a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:29'),
('6fb17f97-de46-436e-91d3-35ee9a95c161', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:52:48'),
('6fb6fdd7-48a2-4556-9f1f-32dbabb2a7d9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:31:35'),
('6fe05bb5-6e79-454f-85ee-1512c256caff', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:22:31'),
('7010897f-c3a0-4631-8f79-85ab7d7b24e3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:36:50'),
('71ba2ab8-138c-4db8-bc67-a1d8d43f6eeb', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:28'),
('71fa356c-81fe-4508-9663-0d426f02f2e2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:04:45'),
('72ca7af9-6f57-4f7c-b8ac-70d341747658', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:28:25'),
('73300c1f-67cb-4d17-8605-1c69a87c5d7a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:24:47'),
('742c11af-1861-4a77-902f-2a1ce106e820', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:48:05'),
('756095f9-6240-4d79-b599-3463821a92d3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:20:29'),
('75c77d95-e581-44e8-8504-b614978be6f6', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:46:20'),
('75e0466b-68a1-42b6-b989-a1d33c7655c0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:22:51'),
('7614aed5-75db-40f0-bb7c-d1a1279222f8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:33:45'),
('7621bf47-d0c8-4261-82b6-0c625609f5ff', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:06:46'),
('76939d75-2874-494d-acb3-b0d303e18853', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:43:32'),
('76f853bd-820a-4ce0-bd17-8a3eba9e8a92', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:04:25'),
('770b4f1c-a15e-4238-98eb-ffd35ae87f10', 'e120e6ac-22be-427c-ac95-12a05429df03', 0, 1, '2026-10-23 11:06:56'),
('77312685-44b4-44f9-82b7-4c8388e45656', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:48:45'),
('77434c63-796c-4ee5-aa64-0e5ab4801358', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:01:05'),
('7755c025-f82b-4ef9-ac56-a15d2655fc40', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('7780d407-649f-40fc-8dd1-27353313c94c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:40:32'),
('77c67311-b027-488d-9937-c8f00768b6b3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:38:47'),
('78144cb6-8e4e-4855-9e6f-1703f640886d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:54:21'),
('781ed4cf-84bd-4304-a8eb-47b6124179b2', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:43:55'),
('787c0c7b-0868-4b05-bad3-ed71e6f5bb70', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('78db8a85-7fd6-41b3-a105-8172d649ca41', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:44:44'),
('78e1e167-af5a-4c55-bff0-f356d1275b6c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:46'),
('791524b1-fc61-46a6-bb46-2ca16a34781e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:43'),
('7959d459-3092-4e9d-ac7e-e5344b3ff96d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:36:05'),
('7a287ab2-8594-4c75-8c7f-3d5f5fe99e1e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:50'),
('7b07b9a6-a7a3-4a2f-8c7c-0fb56c66ef0f', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:39'),
('7b106bcd-001c-4e73-932c-2435aef2a6db', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:52:00'),
('7b2e2da7-6135-4e0e-828f-1876c47c2f5b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:07:05'),
('7b35e7f9-0053-49ca-a5d7-d4df66e3c74a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:48'),
('7b5784a0-5074-49e2-83b7-415333e3c4e3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 10:45:36'),
('7b698986-848a-45a7-8e44-4df582b732af', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:38:52'),
('7bd1ae9c-6a13-4dac-8be8-c02869160e38', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:52:45'),
('7bf159f6-67e9-4e25-a134-55408c963dbf', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:19'),
('7c753a82-aa09-4452-bc4a-b94864de78c6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:54:03'),
('7cf2f40b-3081-416f-9fb6-7f6006569b4c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('7d9827ae-0598-40e9-a29a-6ea3b82e3ad1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:27:25'),
('7da669ca-e065-42bd-8066-f45a97326e03', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:09:59'),
('7e29df94-01ac-40cf-8102-34b7f6eb30f7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:05:25'),
('7e4bd5ba-2311-4ae1-b85b-5a35e74dcf5c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:35'),
('7e6a9cf0-5137-46b8-8b44-88feaaa677ee', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:34:50'),
('7ea785d3-0946-4192-9991-2619c41eef8f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 33, 1, '2026-11-18 11:51:34'),
('7ef872a9-6e86-4d40-8fd9-cc4e26f2800a', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:46:18'),
('7f2c2659-a2d2-41f1-bb26-85dd5d45a426', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:39'),
('7f7b09f7-b2e3-43db-852c-c4b83c86d121', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:07:25'),
('80623bfe-c89f-4294-80a0-f793a9115d9d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:57'),
('80832376-d06f-4fd1-ad7b-3d4d0c9a2225', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 16:28:01'),
('80b127ad-99ff-443e-b0bc-f8b6ec960000', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:39:52'),
('80d00a1f-6904-4f78-b863-e9883f4e4fca', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:23:45'),
('815e4d01-766c-4513-b1d3-f7cb89c58397', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('837b6be4-2e00-41c2-95ee-e8d49ceb561a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:08:20'),
('83b1233e-9b6b-4daa-8ce6-b3d0e599367b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('83d42083-8803-48a9-b74f-d089726ab0df', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:45:05'),
('842e17fb-58c5-4616-b79f-88fac4a9ba88', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:38:42'),
('842e5b0c-3699-4152-82ef-2f066e86e09b', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:37:42'),
('848c126d-23ec-4a4c-b319-a7809444b7a2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:24:45'),
('849b6535-5c34-4be1-a62d-346860cbbf62', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:29:05'),
('8518b96c-e66f-47e9-8904-2d7e924d1810', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:31:45'),
('857fb1fe-72a4-40a6-902e-e32341f74f86', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:36'),
('85fa02ec-6241-4eec-bf79-76fcd5679826', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:01:35'),
('8688b875-ad5e-4bd8-ad23-394aee19aadd', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 20, 0, '2026-11-18 12:01:35'),
('86bc63ef-828e-4f05-850f-5bc75360d0e0', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:38'),
('87178e4e-e59a-4e30-9bf3-90bb9563c749', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 18:12:55'),
('8748ec6b-7174-4659-b76f-d3b7ea55a4ce', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:11:17'),
('876c30ae-464f-4a86-b39b-b33af4627502', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:33'),
('877d0ac1-f2ba-4461-8157-fcb9537c10fe', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:51:53'),
('884535cf-af37-40e3-8a96-54a68899dc11', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:06'),
('884e166a-88c2-4921-9b52-73460068d700', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:42:12'),
('89412553-40c6-4554-a99e-54e7370d58e1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:40:05'),
('8988bc8a-e1b9-479b-ae1e-b66974259d95', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:42'),
('89bb7127-d9f3-44ba-a007-c1cf7596de23', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:01:07'),
('8a14e108-e20f-4044-9059-442391062e8a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:01:35'),
('8a47f68c-1641-4d93-9c5f-b37149f2e207', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:37:20'),
('8b42ca77-343d-42cd-a247-8c0f31a623b6', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 16:27:58'),
('8b7574fb-25f3-4f63-b040-c237c1e1fa3e', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:38:58'),
('8bf61a39-f028-4347-ba80-5295a02e864e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:58'),
('8c57b8ea-61f4-472d-b557-7976dde173d6', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:38'),
('8c7a87f1-8a50-4a61-aba0-0a1eed06a12d', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:40:24'),
('8cb05b0f-2a2c-4c07-af5e-bbf10ac39b09', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:36'),
('8ced4f36-278a-4951-8547-d2a8904a1407', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 0, 1, '2026-11-18 12:00:29'),
('8cfd0b23-e937-459e-a79b-8ad61db64d98', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:40:52'),
('8d519f86-37b7-4988-938e-f5ccbb25c4b3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:21:25'),
('8d656fb7-224b-453a-9945-6fe5a81b4b2b', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:34'),
('8db97cc7-8cde-46c2-8eb3-c8b089a225cd', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:37:20'),
('8e6977ba-6c72-4d7b-89b0-fdb3690dd25c', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:37'),
('8ef8d39d-4740-4614-94d0-565f1d0b635b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:05:56'),
('8f61133d-e59e-4296-b4b8-87ac3c210127', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('90347a15-29dc-4a10-a74e-a00993d03afe', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:22:25'),
('90b98737-8e46-4f84-9cc3-a494c3ccd00e', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:23'),
('90ea053d-ca7f-407f-8eb0-32ff9915f9d4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:53:35'),
('91544f54-1b11-4f7b-b113-ae0a8ca43c34', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:38'),
('915d7dd1-ba41-4a73-81c7-a292ec6e95fb', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:01:56'),
('9177f7e6-a491-444e-91a9-b448c70ba967', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:17:43'),
('91e865a1-86cf-471e-b5b4-6e5069814d1b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:36:53'),
('92cf32ed-a844-477e-8347-d703fdc1907d', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:43:46'),
('933c4fa1-1b5e-465f-9b1b-a1964f19aa32', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:20:25'),
('933ec8d0-13d4-4645-a07b-a05988369248', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:32:23'),
('93cd204f-7092-4751-a2e2-18dd8e597f12', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:52:33'),
('94279cce-565d-4ed9-94f8-d22ae11a184e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:09:44'),
('9574ae93-c1b0-4c09-945a-92428caa1934', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:37:18'),
('95b7966f-5930-4d28-8595-2b32bbdc9ee0', 'e120e6ac-22be-427c-ac95-12a05429df03', 0, 1, '2026-11-04 11:03:20'),
('95e0f1a2-0f6a-4f11-8e4a-d9964bf624d2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:35:45'),
('9622dc26-c0a8-42ff-a716-9ead49ac50a0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:19:45'),
('9656e94e-1de1-4cdf-bf56-efa90c7810c7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:31:20'),
('9717023c-53cd-44c0-b180-bde9dd8abc88', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:31:25'),
('971aad5a-9542-4325-ac7e-3d6200fcb353', 'b892980b-1cca-4c49-8620-6c2823a5f812', 10, 1, '2026-11-06 17:05:59'),
('97577c23-437c-44a0-b5ae-d42100e08749', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 10:45:31'),
('975b0c2e-79c0-4540-a3f6-db995f8b19c0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:51'),
('97ab0146-1a24-4e5a-ac28-e3bd5e68aff1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:34:40'),
('982b3fed-2b46-439b-95e8-f2e53ecd3bf7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:52'),
('982e1fda-f083-46d2-b05c-eb1b02a7db16', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:40:09'),
('985ff314-d5ee-4469-97b2-b8ceafbf26f1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:35:52'),
('98d0ab95-0f30-4e49-91ac-cddffdb9f025', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:50'),
('98d3de0c-056a-437f-8a62-8ab3ec6c904f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:58:27'),
('99565150-2323-4544-8312-17a349bfca54', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:33:05'),
('9a10622e-364b-4071-be8b-eb447d8cd639', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:53:44'),
('9a7fd6e7-ca17-4d3d-a547-717a6f237a8c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:11:05'),
('9ba56edb-6720-4e14-ab52-c712df1550c0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:34:55'),
('9bb68d2c-0d22-4f8c-9252-2afe88e824e4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:52'),
('9bcef168-124d-4fb3-b076-744bafb7b759', 'c590d325-670f-4e86-a10e-3d68adf53084', 30, 1, '2026-10-23 14:01:30'),
('9be7689b-e5bb-42aa-8d28-7223e9fb7bb0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:22:51'),
('9bf790c8-dc37-42ac-b041-f7831a0dc35b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:49'),
('9c349948-d665-46a9-9741-2153a414bc75', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:49'),
('9cab21c3-7ff9-4467-9e8b-86878f7c75f6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:39:04'),
('9df3794d-4546-4e1d-94cc-fc5b92db52da', 'b892980b-1cca-4c49-8620-6c2823a5f812', 120, 1, '2026-11-06 17:59:57'),
('9e039668-9065-4e8c-8aa4-20047e6d388b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:13:05'),
('9ecbeb81-a896-4cae-956e-bccf71297445', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:54:15'),
('9edbef6e-8d90-4ecb-a91e-c427ad5d448d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 11:52:15'),
('9f122449-7895-4501-86f6-62f534d2c8d0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:36:01'),
('9f6db47f-42cd-4a8e-9fca-4fe5d4b252dc', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:27:45'),
('a004c169-bc9a-4130-bc00-99a953b3dc36', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:43:12'),
('a052a69b-c327-497d-8a24-a9ae50ceda3e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:16:05'),
('a0a07f1a-d2ca-46c4-9577-b25ab790d1b2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('a1b32b54-1d5e-463c-aec3-ac35857d2866', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:01:06'),
('a1c83dfd-49a9-479c-82b9-f241b6d003c1', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:36'),
('a21db20c-6b23-4aa1-81cf-3b2cc78e50c6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:06'),
('a321a0d1-498f-4e11-87df-add4071e0a82', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:01:13'),
('a32d381e-8c42-4213-a1a6-dd8575d903b4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:08:45'),
('a357d58d-a59c-4479-93f3-b116a48f9cc7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('a35f2a85-8cb4-48c8-8885-8f69e97e4985', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:41'),
('a3b25fb2-994d-4c0a-9994-be0a530220d6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:49:39'),
('a3dc512c-3ab3-425b-96d7-000ea9938aa6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:09:05'),
('a4137bc7-53c9-4b59-842b-dff9fbe0a46b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:59:55'),
('a445965e-67c6-43df-9eab-453efe9ef525', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:38:44'),
('a49e4183-eb88-47bd-abdb-89f2ce49933e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:54:47'),
('a4dd3b0a-9aed-4262-99a3-03ba8259a874', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:15:25'),
('a5876c79-1e5a-4d91-a8b7-7d5e2664d537', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:29:45'),
('a596ebdb-5b91-4457-bc04-395a9255aa3a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:36:45');
INSERT INTO `progresso_leitura` (`id`, `biblioteca_id`, `tempo_leitura_segundos`, `paginas_lidas_sessao`, `registrado_em`) VALUES
('a5efe104-6242-4d8c-b4e5-d6ebc27328fe', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:21:45'),
('a62a3b3e-e1ad-4249-a46e-581590d593d5', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:37'),
('a79bdf6f-9119-43fb-aa2e-820526b23bfd', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:46:45'),
('a7fd903c-7cd2-4a94-960d-f3d4dc35cae4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:20:45'),
('a8157a0f-0f48-4574-87b1-c2436d03df9c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:31:32'),
('a8927b4a-c9d4-4ae8-86d5-db8ed15be677', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:52:46'),
('a9501989-da55-4e4c-8aaa-2811ed5e3825', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:31:05'),
('a968dbe1-8acd-41d7-96b0-94b92f490530', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:51:45'),
('a976c0ba-53a8-4c03-b458-51d1ca21d4c7', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:33'),
('a9ae6f72-d6c5-41f6-907d-e9c9a2053485', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:32:25'),
('aa18c164-e5f8-48e9-89aa-ae92bc0817c8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:38:50'),
('aa48f726-86ad-4519-87b8-d0bf36a2dfe6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:25'),
('aa5ffa6c-cb5b-4a73-8b00-0fe8856be1c5', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:48:50'),
('aa686c5c-0aab-4088-80e9-d5d8efc8c455', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:04'),
('aabda5b1-24e9-441a-a4fa-e652e5bee192', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:38'),
('ab24c4dd-4ca1-46b8-bc06-7e7222b746c7', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 0, 1, '2026-11-18 12:01:25'),
('ab578d54-e6c8-4515-9b4f-9b6c220111cd', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 20, 0, '2026-11-18 11:53:48'),
('ab5958ff-d6dd-4cec-9a59-9338f8fe7aba', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:50:18'),
('ab6958ad-7d8e-4b47-821b-3658e7c25bce', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:28:05'),
('abdb847d-10e2-4a18-b752-db982a355f7b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:32:46'),
('abe7f023-a6ee-4548-b20f-63f0cb7eb2bb', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:37'),
('abed5931-a514-4f85-b19e-d31baea11d40', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:15'),
('ac9abd88-6c1d-4e8e-aa3d-68028e682bce', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:31:03'),
('aca33b01-2f4d-47e5-b768-59db95743fdd', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:21:05'),
('acc36982-e413-469b-9789-af44f88683e2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:35:06'),
('acc7055f-8396-4637-99f0-e34cd1fb4a3e', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:39'),
('accaffee-7ee9-43f4-8692-3437ea606b9d', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:41'),
('acf59bda-07b6-497d-8c95-00b2c926bcd7', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:23'),
('acf7d3cc-d4d9-43a9-9721-fd7d6b8074f2', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-11-04 11:03:08'),
('ad48cb2d-f48e-4fbc-8ac4-8dd0340ad9fe', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:37:28'),
('ae14b4fb-19c1-42c1-af0d-e53ca6c3ac31', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:37'),
('ae575946-adc1-48e1-8000-1c0c727c3551', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:26'),
('aec77f5b-cc92-409b-b532-71293130b451', 'b892980b-1cca-4c49-8620-6c2823a5f812', 180, 0, '2026-11-18 18:56:35'),
('af76f475-2fa2-4211-b26f-1c289c011a00', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:05:39'),
('afa210ff-56fc-415b-bd24-b05f22a56107', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('aff80601-6182-4c1b-8fb0-16904c0caf19', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:05:45'),
('b02716db-a215-401f-bb1a-7c04597785e9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:39:32'),
('b0907336-67ea-4fe7-a80e-9dffd6962405', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:40:24'),
('b1361b48-5ced-40a3-993d-5f0b7973c59d', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:39'),
('b153785a-5ff6-4cb0-8497-6d311e648687', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:35:00'),
('b16cbfbe-a25d-463c-9c56-d53939306795', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:51:44'),
('b2019547-f7ea-4176-8666-ec9baba3630b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:40:45'),
('b2192f30-ec10-4680-8d58-d59c8894465d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:48:25'),
('b27624b3-f796-43aa-b323-b94903558c39', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:45'),
('b27f891e-4e9b-4339-821a-d3739f22c641', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:14:25'),
('b2f4f310-9eff-4a8d-ae5a-d916de84be85', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:17'),
('b4196841-6d6a-49a7-8d51-24f1e927be56', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:25:45'),
('b4bbd35b-18f6-4724-9e79-c79b0f6074d4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:01:54'),
('b5d69786-f6f8-4af0-b3a8-d48f98eb7e10', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 18:22:09'),
('b5f2751e-913b-4783-96c0-8efc523283a8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:48'),
('b639b0d7-ec13-4dc9-9cf1-4a6022b9583e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:21:39'),
('b6bfa8a1-449d-43d9-8e42-be386e7ee6c7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:54:13'),
('b6d7f6a0-57c8-4812-9cd6-def907b9049f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:58'),
('b7b4d3e5-56e4-400c-ba16-da295e064c45', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:48:49'),
('b7baae4b-ecd6-4c8c-9cc9-94e45a677c3b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:03'),
('b7e49715-f2ac-4b35-9075-219fa5bb8b69', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('b8479b82-4c26-4b48-af0e-65b5ff2fa92f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:30:50'),
('b8795716-8aec-433f-a805-15a0c03f1cc9', 'c590d325-670f-4e86-a10e-3d68adf53084', 120, 1, '2026-10-23 13:44:42'),
('b91f9b12-8b97-433c-9722-df23e1fcfd88', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:51:24'),
('b9e8c54a-4e32-47b5-aaad-337a80f5c569', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:31:50'),
('bab4a70c-5c51-4099-ad84-6da72f17b8cd', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:13:25'),
('bae47834-f910-4eea-a52a-c032962fa08d', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:39:13'),
('bb425f07-9099-4055-a39a-893582a3f467', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:30:32'),
('bc24c5e5-2396-4930-a04a-2e323fed77ca', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:10'),
('bcce4852-81ab-404a-8015-95ec8f82c85a', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 88, 0, '2026-11-18 11:54:56'),
('bcd1f17c-113e-409f-842d-953566385863', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 16:27:46'),
('bce6efa5-5b6a-4be0-a5fa-2073442a03ed', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:01:29'),
('bcf31c70-fae4-4d09-8712-9df8276a814d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:35:50'),
('bd24e674-dedc-4cf0-afc7-f37604aa290a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:34:45'),
('be3e1284-a237-4649-9a22-226aba131d21', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:23:25'),
('be3fd8f3-66be-45f3-ae09-ad7859bdf9b6', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:29:25'),
('be8c25de-f11f-4057-8556-18565ba149a0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:55:36'),
('bf6a137b-4458-4ce4-a291-5aaffd72d03e', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 14:07:23'),
('bf7680f4-4bf8-42b4-97c8-3eb39e3d07e2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:15:48'),
('c00dbdac-fa96-4bf0-ae76-9676d66e2d61', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:34:35'),
('c01837d5-9c26-460b-a67b-8b96e00fb18b', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:29'),
('c04d5111-5362-4596-bc42-2e165b3b6113', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:07:16'),
('c26855a4-c730-400b-b900-d76247775dd5', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:02:11'),
('c3cc3e1b-39e8-4e3e-9e0a-d8feac22768d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 10:45:22'),
('c3d576d9-048f-4af0-9f00-f17597ef606a', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:10:11'),
('c3f234ec-20b2-4c6a-9b4f-1d9183230125', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:35:25'),
('c4142ca6-ef35-49f2-afd8-d4cada1a4b17', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:53'),
('c416cba0-6b05-40b2-8f2f-703216e6d7d4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:31:15'),
('c41e53e2-e67f-496b-9150-cd0c3ad6973c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:07'),
('c50bb903-d844-422c-906e-bee058f88950', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:32:45'),
('c59d22a9-3abe-4f95-8b4a-e1c43d2d413d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:18:05'),
('c5a7f058-b5a4-443a-9b81-b608a9b8b677', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:54:45'),
('c683bc44-3b9c-438b-a171-c1deb7851ebd', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:30:35'),
('c6cb5c58-89c6-4b53-a596-9f3837e909ab', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:55:51'),
('c6d46265-0852-42d4-9ec5-35aa46217cfb', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:43:50'),
('c7d65624-7aac-47f6-8bd7-aa099ce47182', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:34:17'),
('c7ed8968-f5f3-4be7-8b87-305c20d66662', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:44:50'),
('c88004c2-fe81-4f42-9183-eb8698b13125', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:56:31'),
('c8e8c854-82d3-460e-8576-234f468f0601', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:17:05'),
('c9b4d374-5878-44be-a008-279aa368e51e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:30:25'),
('ca23ffc4-1bea-4c5b-bda9-1738931d9368', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:44'),
('ca8923fb-31dc-4473-8a41-12cbdf75b780', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:18'),
('cb12f99d-7ff7-40f5-b24f-f5a69790aa13', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:53'),
('cb437a3d-f59b-4d97-9808-baa23c4597d6', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:40:22'),
('cb939521-e719-43cc-976f-ea87593407d4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:27:05'),
('cbaf27ff-955d-42f6-8d6a-41e753c63ca7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:21'),
('cbb89d3e-3d4c-4e6e-90ed-fb0739cd587c', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:44:25'),
('cbd79af4-4914-4711-9bae-39f2dc47db8b', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:29'),
('cc62014d-ec44-4cf7-9613-bc14680e22c9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:06:45'),
('cc74b1b0-61f4-4467-b221-70aac2591d57', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:10:07'),
('cd90f4f7-abd6-408d-9372-69c983f0bc21', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:38'),
('ce5b577e-ab40-4fef-9348-63e2d974689c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:41:05'),
('ceb7ef20-0012-435a-a4fb-53d7f7668c04', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:08:05'),
('cee3bce4-b9a2-4703-bb1f-8e4276006e4a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:56:06'),
('cf222e16-d997-461e-95d8-fefb66d1fd4d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:53:27'),
('cfb710c8-6857-4392-9021-88f5e0fad714', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 14:01:20'),
('cfcbe4f3-00ba-4142-a940-bf6b86bff416', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:52:06'),
('d01eee67-e28b-45ef-b694-d08f71e45770', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:08'),
('d124ce2c-c5bb-4d59-8e7f-f3dbe75a2f39', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:55:00'),
('d12cc84a-1fe0-46ef-b7b3-5291c76e001b', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:12'),
('d1cdbfca-b13a-4cb4-b95b-74f9f48affbd', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 20, 0, '2026-11-18 12:01:55'),
('d252d09d-b926-48a9-8ca4-4b53113adabd', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:56'),
('d2c538e0-da5b-4913-8cbd-9e48583517b1', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:10:10'),
('d2cb34bf-e196-404a-836c-7a4e72d23cc3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:30:35'),
('d2ed3c9c-fe79-4aa3-847a-75a8ea43555a', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:35:11'),
('d33be6bd-68f3-4090-ab6f-877f008dbb71', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:34'),
('d5292f35-daf0-4e3c-8191-79c9d6ee94a1', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:01:08'),
('d55235ce-b4f5-465d-9d91-794cb86e5d34', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:44'),
('d5731d35-6f38-4323-84c8-04cfc1233a1e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 18:22:39'),
('d59abda1-75b1-42fb-89ec-e941df2d5157', 'b892980b-1cca-4c49-8620-6c2823a5f812', 92, 1, '2026-11-06 18:23:03'),
('d65097c7-015a-4d13-bcb9-e2fd20775d91', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:26:05'),
('d65e33a5-b395-4453-a206-c4eb092c6c82', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:41:50'),
('d6cba725-54ce-498f-8c19-25a209038702', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:06'),
('d6dccfa8-9ce6-40b5-aa43-0cb93e162630', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:00'),
('d8c35b0b-409d-44b6-8e14-fe87d9802757', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:29:53'),
('d90e2882-54a8-4381-9fce-261f1701f7b8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:44:52'),
('d9a451c7-77ad-4516-9ca0-6ed4b50061b1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:47'),
('da151676-9d68-4ac2-86b3-4de2e78a2251', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:51:25'),
('da75b395-268c-4da7-84ec-df151ac7becc', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:21'),
('dc531f1a-c3af-44e3-a206-41bb1875e421', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 20, 0, '2026-11-18 12:01:15'),
('dc5a804c-6d2b-4d17-afbe-0cde621b5870', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:38:05'),
('dcbfb330-d96d-4b9e-bba0-53a387809731', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 18:22:54'),
('ddd93f58-b7ad-4f60-aa7d-5e83c4d69851', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:41'),
('de7690e6-2bd2-4222-9d94-5d03be09cd40', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:38:50'),
('dec4f2c2-71c7-4010-9b64-f9f81ed5f830', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:44:45'),
('df03c78f-9fcd-44a3-b05a-a71f5f20c830', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:10:09'),
('df0a75d6-b700-4c5e-95d7-3994ac53163d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:47:45'),
('df4bce97-dad8-4acf-8a63-062f3d97e5c7', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:39'),
('dfdc0518-5f93-44ee-b722-ebed92dc8383', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:37:19'),
('dfecbd6b-0e75-4a27-b3fc-46bb7049a78b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 18:12:40'),
('dffabba0-d21e-4d9b-a808-b8032782ddfb', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:35:03'),
('e1eb7107-2ae1-4ea6-854a-b19835a66a05', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:11:25'),
('e249956f-4f53-4ed7-a445-44c07c0f9fb4', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:37'),
('e35fc272-986b-421b-97eb-796b50783cf2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 10:45:37'),
('e3ef07c3-eda8-456e-ad9f-15f7045ac7f6', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:38'),
('e4d6af7d-0e61-420d-abf2-94b77b59eaed', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:47'),
('e5424789-a018-4c18-a08d-7ac9a8f08e8e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:39:45'),
('e594a510-bae8-447a-9d0e-b6c53464b356', 'b892980b-1cca-4c49-8620-6c2823a5f812', 32, 1, '2026-11-18 10:45:47'),
('e5dbdd09-ed44-4215-9386-5c0592484502', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:55:45'),
('e67625b2-10a0-46b7-a81c-9683c9318e65', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:45:25'),
('e6802b96-12af-4243-9c8a-e73d26a01870', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:40:06'),
('e71c24a2-cebc-4bf9-b886-ec6fc45039e8', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:03:45'),
('e74b8169-a9d9-4725-999f-4241cb190927', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:36'),
('e7d06390-a853-42f1-b76c-86c819ca38ae', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:04:05'),
('e9cde736-efc7-47a3-8e47-87bba14a22b0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:37:25'),
('e9e8e6f9-4707-431f-8590-cb5315b03bea', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:49:05'),
('e9ebe1d2-bd52-4409-9b7c-366e2a825ef5', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 0, 1, '2026-11-18 12:00:34'),
('ea90b0dd-db96-4310-a0c6-000a73fdc380', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:40:23'),
('ead63efb-e921-424c-af8d-f39f49273242', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:43:25'),
('eaf0069d-8ac4-433d-9eec-3956e8b29138', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:16'),
('ebf2eab0-23a7-486f-87a6-1579ff78be17', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:56:25'),
('ec64d803-28d9-42a3-8f27-eb21e20640ec', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:31:55'),
('eda56b69-fc4e-4a89-8ec5-446e7871a10b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:50:45'),
('edb8f2ae-538f-402b-a239-c36d9f74c1ff', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:42:45'),
('edc47f4d-81c3-4d90-9383-7b57b7f6093c', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:37'),
('edd2709c-0ec9-40b1-8113-7c6cb7ee44f0', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:16:25'),
('edeb0948-4f4c-4b18-b712-5b477dbd5b2f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:08'),
('ee9ea0ba-501b-4ad8-aa05-20da9669638f', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:29'),
('eea4eee3-fc92-4674-9d61-6ff8812a31c3', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 0, 1, '2026-11-18 12:00:37'),
('efb1279e-6695-4d57-adf3-cb14876830f1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:37:33'),
('f0310d08-0dfe-4130-8bff-57b953a3b33c', 'e120e6ac-22be-427c-ac95-12a05429df03', 9, 0, '2026-10-21 18:24:02'),
('f04117c6-64b6-45f6-b3b1-e7514e9a10e9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:57'),
('f049eb8a-d197-4420-ac1e-ecdd533e19e2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:44:32'),
('f0931dea-d22b-4ca0-89b1-17541fec0f3b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:48'),
('f09849c4-9de6-43e3-bd95-e73a2b0c229f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:06'),
('f09b06fb-9938-457d-93fe-30de26a8cb93', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:54:43'),
('f0a50849-81a9-4d79-8422-820941a128f1', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:57'),
('f11cf0af-954e-4b96-91e3-a2ffe263fd38', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:38'),
('f142164c-f912-4cc7-8d25-f9e82f5536ad', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 18:12:47'),
('f1a67ee6-765a-41f5-a330-643e4013841c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:45'),
('f1f8aff7-72d4-41df-b262-e69840c0f6ee', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:53:25'),
('f2ee84ec-4448-4265-ad3a-407d7de2006c', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:18:25'),
('f34f2e6a-32b1-43a5-b950-a1339b31280f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:14:05'),
('f3986011-5885-4851-80c1-f07d2b2250c2', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:41:52'),
('f3db73c2-f135-439c-84f3-0db709023c63', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:07:01'),
('f4787992-7986-4193-8b98-036eabf1e1bd', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:50:25'),
('f4a80640-f605-4c57-8474-9bcc6ac88ffb', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:38:45'),
('f568fb41-41b0-4954-bc98-7e7c096469af', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:06:05'),
('f5e1da6d-8e93-477e-8d06-702d1e5f66bb', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:05'),
('f5f43529-6fae-4342-88a4-ea96c19f28d7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:21:33'),
('f5fbfd4d-1b1c-4f34-9af0-fe2ed78fdb0e', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:23'),
('f6245224-c7a7-4ab1-84f5-d839c27a3331', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-18 11:37:50'),
('f624a42b-5990-41ff-91f0-98287d9139aa', '16c7c433-4f55-4dd6-bbd7-f914e702e87e', 0, 1, '2026-11-18 11:53:58'),
('f63b33cc-a506-4884-b8c3-e381e6cc3cc7', '6df9b34e-4fad-484f-8a5b-ed7593bc0976', 20, 0, '2026-11-18 12:00:55'),
('f6825362-8305-406e-8e87-808de7a60440', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:44:39'),
('f71a7435-7c16-46cc-b38e-faac71dadd01', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:35:15'),
('f7537297-a79d-4a92-a043-8c6cdc873582', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 14:07:23'),
('f7600543-c277-47f2-b8e7-0166ddd4bd50', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:37:45'),
('f859c727-36f1-402d-b495-766684155d1b', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:41'),
('f9b24d62-ca42-4b53-a0a4-61e6731097d3', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:56:36'),
('f9e7d33e-fcd1-4d61-9e45-19088b5ba2e5', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:33:25'),
('fa4341e1-784c-4fce-b98d-860ae1f9ea5a', 'c590d325-670f-4e86-a10e-3d68adf53084', 15, 0, '2026-10-23 13:44:09'),
('fa706d95-aba9-4d59-8612-bb251ea3e8c9', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:30:45'),
('fabf096a-1125-4181-a819-c06097e53c18', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:44:33'),
('faf23977-b211-49d6-8b41-a46d171df41b', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:03:59'),
('fbbe6eff-9dd5-48ca-a161-18247ae25ea7', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:40:24'),
('fbc0c022-fc3c-479e-91a6-95c70a0dd8c7', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:49'),
('fbe8c935-dd7a-427d-97bd-eeb9b6757155', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:37:39'),
('fc0294e9-af63-4564-936c-6b3eaa2cec42', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 11:51:09'),
('fcc5998d-76fa-4f70-beb2-abf64c698eba', 'c590d325-670f-4e86-a10e-3d68adf53084', 24, 1, '2026-10-23 13:37:47'),
('fcd98882-a3db-495d-895a-d56438562d2d', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 16:37:13'),
('fd0c41d4-3c3a-457d-b6e8-8bbde2104076', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:54:45'),
('fe13e5cb-aa3c-49d2-8af1-b9663b1b4b73', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:07:08'),
('fe5c08fa-44f2-4d40-8895-782d4d6d0d8e', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-06 17:06:31'),
('fe813fa9-6e99-4891-b19b-682c92518228', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 16:34:55'),
('fedd8a1d-70e0-4b86-93dc-70e63160d49d', 'c590d325-670f-4e86-a10e-3d68adf53084', 0, 1, '2026-10-23 13:39:45'),
('ff3bb59a-448f-4407-9b00-9723246d4b56', 'b892980b-1cca-4c49-8620-6c2823a5f812', 0, 1, '2026-11-18 18:27:17'),
('ff669024-ef3a-4f47-80f1-175bda843361', 'b892980b-1cca-4c49-8620-6c2823a5f812', 20, 0, '2026-11-18 18:03:25'),
('ffbac890-0597-4a8b-be74-5fa851d8977f', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:55:06'),
('ffeb2a9e-faf5-4511-83b2-d6560559e4af', 'b892980b-1cca-4c49-8620-6c2823a5f812', 15, 0, '2026-11-06 17:55:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `redefinicoes_senha`
--

CREATE TABLE `redefinicoes_senha` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `token` varchar(255) NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `expira_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `registros_auditoria`
--

CREATE TABLE `registros_auditoria` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `entidade` varchar(120) NOT NULL,
  `entidade_id` char(36) DEFAULT NULL,
  `endereco_ip` varchar(100) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `registros_produtividade`
--

CREATE TABLE `registros_produtividade` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `data_registro` date NOT NULL,
  `tarefas_concluidas` int(11) DEFAULT 0,
  `tempo_estudo_minutos` int(11) DEFAULT 0,
  `pontuacao_produtividade` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `registros_produtividade`
--

INSERT INTO `registros_produtividade` (`id`, `usuario_id`, `data_registro`, `tarefas_concluidas`, `tempo_estudo_minutos`, `pontuacao_produtividade`) VALUES
('21a6c5a8-6c7d-4961-ad23-9e873dc9772e', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', '2026-11-06', 0, 51, 0),
('408303a6-468f-4f94-aaaf-9c2d6a24fac2', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', '2026-10-23', 0, 100, 0),
('43a3aabe-e68a-4239-b8da-0bd801f34c5f', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', '2026-11-18', 0, 25, 0),
('5da4ec81-ca94-4922-9efd-ea32b0a46aba', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', '2026-11-04', 0, 25, 0),
('624b56bb-b262-4fc4-be70-5f25067ec534', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', '2026-11-04', 0, 25, 0),
('8d0c54e2-d0e5-411d-8ba7-d6eedc86ea0c', '837d2a76-7029-46d7-aea9-0885621d04d8', '2026-10-16', 0, 51, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessoes_estudo`
--

CREATE TABLE `sessoes_estudo` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `disciplina_id` char(36) DEFAULT NULL,
  `inicio_sessao` datetime NOT NULL,
  `fim_sessao` datetime DEFAULT NULL,
  `duracao_minutos` int(11) DEFAULT NULL,
  `tipo_tecnica` enum('pomodoro','livre','cronometro') DEFAULT 'livre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `sessoes_estudo`
--

INSERT INTO `sessoes_estudo` (`id`, `usuario_id`, `disciplina_id`, `inicio_sessao`, `fim_sessao`, `duracao_minutos`, `tipo_tecnica`) VALUES
('0c9a627d-915b-4f60-a494-f1b2262538bd', '837d2a76-7029-46d7-aea9-0885621d04d8', 'e0468669-f80a-4635-a6fc-dcf2de924849', '2026-10-16 12:05:06', '2026-10-16 12:30:45', 26, 'pomodoro'),
('3e163d85-930a-4882-8b2f-4530fd320606', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', NULL, '2026-10-23 13:48:02', '2026-10-23 14:13:25', 25, 'pomodoro'),
('59cb5262-af25-48c3-adc2-fdc08d057477', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', NULL, '2026-10-23 10:06:32', '2026-10-23 10:31:33', 25, 'pomodoro'),
('8408f32c-8057-4b11-9a08-38c44978db7c', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', NULL, '2026-11-04 14:24:10', '2026-11-04 14:49:11', 25, 'pomodoro'),
('a17fecf7-97ac-4d24-a018-73d219410769', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', '8618dbbb-106e-4366-a8f3-b093ae8bbd88', '2026-11-06 14:43:47', '2026-11-06 15:09:27', 26, 'pomodoro'),
('a4f94fb9-2f58-4067-8027-fa34fcda0615', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', '8618dbbb-106e-4366-a8f3-b093ae8bbd88', '2026-11-06 14:11:51', '2026-11-06 14:36:52', 25, 'pomodoro'),
('b515e45e-f94b-424e-bae8-2d7f888aef50', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', NULL, '2026-10-23 08:00:56', '2026-10-23 08:26:25', 25, 'pomodoro'),
('d3ed3dad-36d3-4751-8404-26dc7095c1de', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', NULL, '2026-11-18 07:48:03', '2026-11-18 08:13:03', 25, 'pomodoro'),
('d60b00e0-2fc4-46de-9e8a-3852c2fc4a4f', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', NULL, '2026-11-04 13:42:25', '2026-11-04 14:07:25', 25, 'pomodoro'),
('e74896f7-a58a-4bb2-84e9-b0b586a66133', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', NULL, '2026-10-23 09:26:27', '2026-10-23 09:51:31', 25, 'pomodoro'),
('fb18934c-e8a8-45cf-a8a0-3eaeb481f474', '837d2a76-7029-46d7-aea9-0885621d04d8', 'e0468669-f80a-4635-a6fc-dcf2de924849', '2026-10-16 13:21:36', '2026-10-16 13:46:45', 25, 'pomodoro');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessoes_usuario`
--

CREATE TABLE `sessoes_usuario` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `token_sessao` varchar(255) NOT NULL,
  `ip_origem` varchar(100) DEFAULT NULL,
  `agente_usuario` text DEFAULT NULL,
  `expira_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `sessoes_usuario`
--

INSERT INTO `sessoes_usuario` (`id`, `usuario_id`, `token_sessao`, `ip_origem`, `agente_usuario`, `expira_em`, `criado_em`) VALUES
('0cb9adb1-a557-4c87-b510-6af37187af14', '837d2a76-7029-46d7-aea9-0885621d04d8', '9679640a7c8bf7a5705db1d9a52efdda3cc89601005cde4078090fbff1917ec3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:21:01', '2026-10-16 18:21:01'),
('18b29b5e-08a9-4eba-875b-4955d0aae311', '837d2a76-7029-46d7-aea9-0885621d04d8', '194a356a597147dfd656c6219f8b3ee5cb0b6d547ccbca42f5dca26237ca4ae1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:19:11', '2026-10-16 18:19:11'),
('217e7a35-af71-44f4-b8ec-dbae1db5e159', '837d2a76-7029-46d7-aea9-0885621d04d8', '04eff51aa1236209815f6e6b67a810e34c7b8a5ffc4a0b2fac3005bfbd088aa0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-22 02:46:03', '2026-10-21 18:46:03'),
('315ca066-88c5-403a-aeb7-a6327c4ba267', '837d2a76-7029-46d7-aea9-0885621d04d8', '0e5e11fa4204d241a0eb22d7314857d30ebf033f29ca710c100ec506bd51ab65', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-22 02:45:48', '2026-10-21 18:45:48'),
('33515d82-cbf2-4f55-97ca-335a43e77652', '837d2a76-7029-46d7-aea9-0885621d04d8', '6b5d686bcb9d22183c5ededa5b885ac0147af0e0ccdd9c46f823fd3b91f575d3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:20:04', '2026-10-16 18:20:04'),
('451fa712-a1bc-4ad3-8f8a-b0f72403d31e', '837d2a76-7029-46d7-aea9-0885621d04d8', '0c968d44633a4e1d4e40e71578ca81684ec2190082711da0313420e8f046c9e9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:19:50', '2026-10-16 18:19:50'),
('4638415e-4af6-4cd6-8bd3-20ea99bb35b0', 'af90ddc4-217f-4556-87b9-aec1bad3fcbc', 'f905078b38be3f59710e4ef1785cfe613849f9a777cdb3658c132417ea904937', '10.141.117.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-24 00:36:42', '2026-10-23 16:36:42'),
('48362004-9cd5-4923-8491-43345bb34ebd', '837d2a76-7029-46d7-aea9-0885621d04d8', '1a51e219c48a394f05c7fd7c39354eaef773c861331f834f2c23747c777e8629', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:19:23', '2026-10-16 18:19:23'),
('566a3a88-766c-4dae-9e79-aca292260c6c', '837d2a76-7029-46d7-aea9-0885621d04d8', '46244d786f415a62f9e734ff16b7a1b0a71252016af168dc1e7681a649adc5e0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:20:56', '2026-10-16 18:20:56'),
('59281cb2-2d45-403e-9a65-a8773d5d67c1', '837d2a76-7029-46d7-aea9-0885621d04d8', 'd3ead35db1930763bf23ae72a09faed49ce085fb104934fb245b316a73b54e9a', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:19:32', '2026-10-16 18:19:32'),
('7a68d6a2-7204-4b65-a6b7-a758b8fb467f', '837d2a76-7029-46d7-aea9-0885621d04d8', '61b2264eebe467275970fcf26d6d928910289269892d7869994089594a07b264', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:20:17', '2026-10-16 18:20:17'),
('8326dbf5-068a-4bff-b102-2b2fcf80bce0', '837d2a76-7029-46d7-aea9-0885621d04d8', '2827f0733b6c552079c1e11291124323df6c582bec7a36b6c7ef436b22a0f2d0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:19:44', '2026-10-16 18:19:44'),
('8468cb9e-a66a-48a8-8c96-92ac5e465825', '837d2a76-7029-46d7-aea9-0885621d04d8', '71ca162f891cc53803f6fcdad6817ceddde6d437cb6ab69a6c29e043faffad0a', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:19:54', '2026-10-16 18:19:54'),
('8578a91b-6b63-4d2c-9d48-3e1d59e727c6', 'b7d6feaa-3562-41b4-86c4-840aaef3eb6e', '0ee9b7027dea3f41b29d9e7f6410fd17f7e375a94be7721e2b6e2fdb459a7521', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/156.0.0.0 Safari/537.36', '2026-11-07 01:14:48', '2026-11-06 17:14:48'),
('9f7e0647-7846-4474-b45b-5d3a17475e9a', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'f8fb76d1a1829c28d1e49ed4808c1c6e641e8a72ac0f4992013644c2a61ab0f5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-18 19:50:36', '2026-11-18 11:50:36'),
('b20f781a-017eae43', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', '819d1fd6b21515aef38ca6e86544aba634d7c4dce61552a559c8fe3c9ad77030', '127.0.0.1', 'smoke-test', '2026-11-18 12:50:00', '2026-11-18 11:50:00'),
('bc72cf3d-8a3e-4492-86f8-559187edbae4', '837d2a76-7029-46d7-aea9-0885621d04d8', '9992d80ef03bf366da0bc52df76882263e63db46b77a68a169f5ef39fa6b5684', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:19:29', '2026-10-16 18:19:29'),
('d3de82d5-c1e6d2fd', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', '18ea59875ce9c9acf01eeef9cffe853af8241abab69ebc7bd39a919f0e34bbd0', '127.0.0.1', 'smoke-test', '2026-11-18 12:49:13', '2026-11-18 11:49:13'),
('e7ec9e58-2f24-4267-bd58-f96def0f0009', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', '63bcd5a83cd916baea649d54efc3c745fe67465a6eb93285d71a75292dec2771', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-19 00:29:37', '2026-11-18 16:29:37'),
('ff1a9e80-6b6d-4c71-b3d4-a52f52c1ecf5', '837d2a76-7029-46d7-aea9-0885621d04d8', 'ede642d7f14ab42b9199d74b563732c5685b8aacd473d854be3c45d79a5bfe2d', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-17 02:21:33', '2026-10-16 18:21:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tags`
--

CREATE TABLE `tags` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `nome` varchar(80) NOT NULL,
  `cor` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tags`
--

INSERT INTO `tags` (`id`, `usuario_id`, `nome`, `cor`) VALUES
('645a6db2-6a14-4a5f-b494-9e9f4cbec301', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'Importante', '#eb2424'),
('b672146f-0d4c-4318-ad05-f1548fb50a08', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'Livro', '#0d2863');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tags_arquivo`
--

CREATE TABLE `tags_arquivo` (
  `arquivo_id` char(36) NOT NULL,
  `tag_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tags_arquivo`
--

INSERT INTO `tags_arquivo` (`arquivo_id`, `tag_id`) VALUES
('5143a4ca-2412-4b42-8241-a02cd76f5bb1', '645a6db2-6a14-4a5f-b494-9e9f4cbec301'),
('b2fe3940-926e-410a-9242-162575172e83', 'b672146f-0d4c-4318-ad05-f1548fb50a08'),
('dd766835-4aca-4ddf-8df9-936ae0f20420', 'b672146f-0d4c-4318-ad05-f1548fb50a08'),
('e2680375-bd4b-4f21-8060-c47c825c401e', 'b672146f-0d4c-4318-ad05-f1548fb50a08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `disciplina_id` char(36) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `prioridade` enum('baixa','media','alta') DEFAULT 'media',
  `status` enum('a_fazer','em_andamento','concluido') DEFAULT 'a_fazer',
  `data_vencimento` datetime DEFAULT NULL,
  `recorrente` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tarefas`
--

INSERT INTO `tarefas` (`id`, `usuario_id`, `disciplina_id`, `titulo`, `descricao`, `prioridade`, `status`, `data_vencimento`, `recorrente`, `criado_em`) VALUES
('0e4f6dff-3229-4511-8357-85945a372135', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', NULL, 'Eita', NULL, 'alta', 'concluido', '2026-11-04 16:00:00', 0, '2026-11-04 17:40:02'),
('5aa2f58b-4845-41b6-bcc1-b60ecd88b223', '837d2a76-7029-46d7-aea9-0885621d04d8', '7ce2fd63-7005-4cdd-9101-2265965545ba', 'Projeto - Feira de Ciências', 'Projeto da feira de Ciências, Balada Tecnologica, Jogo de Botões, Site.', 'alta', 'em_andamento', '2026-10-22 12:20:00', 0, '2026-10-16 14:28:05'),
('c14af73f-ed78-48ba-a399-ed002ba0a8a1', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', NULL, 'Feira de ciencias', NULL, 'alta', 'concluido', '2026-10-22 12:20:00', 0, '2026-10-21 18:42:53'),
('cfa41649-a669-49e3-9cec-c505cef9248d', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'c4e9d041-074b-43cb-adae-849064ff7e14', 'Mexer no site', NULL, 'media', 'concluido', '2026-10-23 08:30:00', 0, '2026-10-23 11:09:46'),
('d224930c-5c36-42f9-85c5-ba9aedd442b6', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', '8618dbbb-106e-4366-a8f3-b093ae8bbd88', 'Eita 2', NULL, 'alta', 'em_andamento', '2026-11-18 16:00:00', 0, '2026-11-18 10:46:41');

-- --------------------------------------------------------

--
-- Estrutura para tabela `temas_usuario`
--

CREATE TABLE `temas_usuario` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `nome_tema` varchar(80) NOT NULL,
  `propriedades_json` longtext NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `temas_usuario`
--

INSERT INTO `temas_usuario` (`id`, `usuario_id`, `nome_tema`, `propriedades_json`, `criado_em`) VALUES
('044184b3-b8de-4463-a397-52ee5571eae0', '837d2a76-7029-46d7-aea9-0885621d04d8', 'preferencias', '{\"notificacoes_lembretes_tarefas\":true,\"notificacoes_eventos_calendario\":true}', '2026-10-16 14:22:26'),
('1de71f71-20ef-45dd-bdf3-9ddc3202a99e', 'af90ddc4-217f-4556-87b9-aec1bad3fcbc', 'preferencias', '{\"notificacoes_lembretes_tarefas\":true,\"notificacoes_eventos_calendario\":true}', '2026-10-23 16:36:26'),
('26d99677-b7d7-492d-83e3-0fe90ea17102', 'b7d6feaa-3562-41b4-86c4-840aaef3eb6e', 'preferencias', '{\"notificacoes_lembretes_tarefas\":true,\"notificacoes_eventos_calendario\":true}', '2026-11-06 17:14:44'),
('77b6bb7e-2b9c-4145-bf04-ff84192c689c', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'notificacoes_visualizadas', '{\"visualizadas\":{\"tarefa_vencida:cfa41649-a669-49e3-9cec-c505cef9248d\":\"2026-10-23 09:25:42\"}}', '2026-10-23 12:25:42'),
('83273230-c105-4d35-abff-8dc9765fe057', '837d2a76-7029-46d7-aea9-0885621d04d8', 'notificacoes_visualizadas', '{\"visualizadas\":{\"lembrete:15caf627-4eea-4b17-8bc9-0ec2eb589556\":\"2026-10-16 13:33:14\",\"evento:06c170b2-92ce-4f5e-8994-ac26c734358a\":\"2026-10-16 13:33:19\",\"tarefa_proxima:5aa2f58b-4845-41b6-bcc1-b60ecd88b223\":\"2026-10-16 13:33:21\"}}', '2026-10-16 16:33:14'),
('9fe10d9b-4233-48b4-9300-8f53bd3edc37', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'preferencias', '{\"notificacoes_lembretes_tarefas\":true,\"notificacoes_eventos_calendario\":true}', '2026-11-04 17:11:37'),
('b1d03d2e-44c1-44f9-b1b9-3bc13dbcb9d5', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'preferencias', '{\"notificacoes_lembretes_tarefas\":true,\"notificacoes_eventos_calendario\":true}', '2026-10-16 18:23:20'),
('b9d93443-3ccd-429d-ba5a-8da5d2003e5f', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'notificacoes_visualizadas', '{\"visualizadas\":{\"tarefa_proxima:0e4f6dff-3229-4511-8357-85945a372135\":\"2026-11-04 15:11:27\",\"tarefa_vencida:0e4f6dff-3229-4511-8357-85945a372135\":\"2026-11-06 13:32:19\",\"evento:6503fe19-c457-4c83-821a-2b90daa4b466\":\"2026-11-06 15:10:37\",\"tarefa_proxima:d224930c-5c36-42f9-85c5-ba9aedd442b6\":\"2026-11-18 07:49:36\"}}', '2026-11-04 18:11:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tentativas_login`
--

CREATE TABLE `tentativas_login` (
  `id` char(36) NOT NULL,
  `email` varchar(180) NOT NULL,
  `endereco_ip` varchar(100) NOT NULL,
  `sucesso` tinyint(1) NOT NULL,
  `tentativa_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tentativas_login`
--

INSERT INTO `tentativas_login` (`id`, `email`, `endereco_ip`, `sucesso`, `tentativa_em`) VALUES
('0090a1e7-d4e5-4bd1-adbf-24f2e73cc471', 'teste.automatico.1792165560@example.com', '::1', 1, '2026-10-16 15:49:36'),
('0182c294-e58c-4893-8f3b-fd72950ffab8', 'user@teste.com', '::1', 1, '2026-10-21 16:48:04'),
('03bc2f5e-58d0-4929-9e59-7a81ec158b98', 'teste2.d49d95@example.com', '::1', 0, '2026-10-23 13:33:33'),
('03d8ec06-32b5-4bd7-9842-77e1c8d0043f', 'user@gmail.com', '::1', 0, '2026-11-06 16:31:39'),
('04561fda-7413-4536-9b14-6edbb84ac268', 'user@teste.com', '::1', 1, '2026-11-04 16:42:04'),
('05bc82e2-9de7-419f-8dc4-a3cd1790b023', 'user@gmail.com', '::1', 0, '2026-11-18 10:41:14'),
('0849427e-4479-4f6a-8ca8-4c5f8f53e795', 'user2@teste.com', '::1', 1, '2026-11-04 18:53:35'),
('08965c1d-3dca-4eca-9baf-a192678ce16d', 'user2@teste.com', '::1', 1, '2026-11-18 10:43:27'),
('08a1b314-aa18-4808-9060-55e11d7e792c', 'user@teste.com', '::1', 1, '2026-11-04 10:42:57'),
('08e29b83-57bd-4bf7-8c74-e665ffb0e8ec', 'yasmin@gmail.com', '::1', 0, '2026-11-18 10:41:43'),
('0d84b478-f2cd-40e0-ae05-58d8bef496a0', 'yas@gmail.com', '::1', 1, '2026-10-16 18:19:44'),
('0eb57c01-545d-4b9d-9bac-f5dc10ddb49f', 'teste.automatico.1792165997@example.com', '::1', 1, '2026-10-16 15:53:18'),
('10e1f3d8-2eb1-4f70-84eb-4e38a5a187f1', 'user@teste.com', '::1', 1, '2026-11-18 10:41:23'),
('11c7d1f4-2cc3-43a1-99f8-eb1fa63a42b7', 'teste.automatico.1792165525@example.com', '::1', 1, '2026-10-16 15:45:25'),
('14073b1a-138f-47f4-8f6e-fc5ad72230e5', 'teste7.a32ba8@example.com', '::1', 1, '2026-10-23 14:11:09'),
('14da1a8c-f7a0-4987-abcd-47e8fca8ffd3', 'yas@gmail.com', '::1', 1, '2026-10-16 18:19:11'),
('164a22ae-cc18-4512-bea6-26cd51407241', 'teste18.dda895@example.com', '::1', 1, '2026-10-23 16:30:20'),
('17a78ce7-6f55-4311-8d40-9d7f32aba556', 'teste17.70e0ca@example.com', '::1', 1, '2026-10-23 15:15:23'),
('1932927a-96ed-48a6-adc7-521f82d9059f', 'user@teste.com', '::1', 1, '2026-10-21 18:45:29'),
('1937e0c4-b78e-4041-b34a-e847367f5117', 'user@teste.com', '::1', 1, '2026-11-04 14:15:22'),
('1dc85761-b7f5-45fc-8ee5-a4dad3da380d', 'teste10.e17b38@example.com', '::1', 1, '2026-10-23 14:47:34'),
('1de8622c-ca46-4dd2-9837-9322fcf1702a', 'teste.automatico.1792165997@example.com', '::1', 1, '2026-10-16 15:53:24'),
('20061b68-d705-4cf1-833b-7f329b5cf40c', 'teste.sync.b1675d@example.com', '::1', 1, '2026-10-23 12:24:08'),
('241bc799-6c0f-4443-aae4-15d74d6def79', 'user@teste.com', '::1', 1, '2026-11-04 16:59:08'),
('26213b60-6d56-4c8b-a602-f2c6a2a4fa81', 'user@teste.com', '::1', 1, '2026-10-21 18:46:18'),
('323de4ab-b021-47d0-9b52-6e085799b1ae', 'user@teste.com', '::1', 1, '2026-10-23 11:00:30'),
('33a5198d-e09a-4c57-9e7d-e999e0d48f37', 'luiz.g.souza27@aluno.senai.br', '10.141.117.13', 1, '2026-10-23 16:36:42'),
('3ddfb87c-41bf-466d-840b-30f16c9596bd', 'teste13.b5d7c3@example.com', '::1', 1, '2026-10-23 15:03:43'),
('408fbd92-fce2-4cd7-a088-3efce4de35c2', 'user2@teste.com', '::1', 1, '2026-11-04 17:11:42'),
('41e42e95-875e-4188-be2c-966ab9222602', 'user@teste.com', '::1', 1, '2026-11-04 17:09:59'),
('434a93b4-499e-4965-98b2-38e55d6f6874', 'yas@gmail.com', '::1', 1, '2026-10-16 18:20:56'),
('4554073a-414b-451f-b928-bde52ab626f8', 'yas@gmail.com', '::1', 1, '2026-10-16 18:19:23'),
('49fac437-8b74-419a-875c-bd1a6d4bac45', 'teste20.67dbc4@example.com', '::1', 1, '2026-10-23 16:47:12'),
('4cedf94c-62a7-421c-ad15-d6b103f831cd', 'teste19.cacf26@example.com', '::1', 1, '2026-10-23 16:36:03'),
('51efc780-5c19-4069-89d6-7bed7772f307', 'teste9.ce806c@example.com', '::1', 1, '2026-10-23 14:12:52'),
('55129533-de38-4d0e-bd26-c222e337b531', 'user@teste.com', '::1', 1, '2026-10-23 14:00:37'),
('56a4626a-85f2-43c6-bb68-3fed6803db58', 'user@teste.com', '::1', 1, '2026-10-23 14:14:14'),
('57c1654b-5901-49d1-8ae5-1eb7b3c7d4c0', 'teste22.d9da22@example.com', '::1', 1, '2026-10-23 16:58:35'),
('5d76eabb-55e7-49a8-9725-dcb27e17f617', 'teste14.74a00f@example.com', '::1', 1, '2026-10-23 15:12:34'),
('5ec71950-2f72-4f91-938f-176f471cd3a0', 'teste5.5993e7@example.com', '::1', 1, '2026-10-23 13:34:21'),
('5f5d3256-8b57-4280-beec-d63ea3c64244', 'yas@gmail.com', '::1', 1, '2026-10-16 18:21:01'),
('5f7b5364-062b-43df-9418-7f22d1a4b271', 'teste15.cd367e@example.com', '::1', 1, '2026-10-23 15:14:37'),
('6096437a-a692-41f3-b119-a592c36376e5', 'yas@gmail.com', '::1', 0, '2026-10-23 11:54:33'),
('60ac45cd-6a9c-4923-8080-2501cc644c42', 'user2@teste.com', '::1', 1, '2026-11-06 16:31:57'),
('6be695e1-e28d-49e5-bc80-99eca1c25694', 'teste2.d49d95@example.com', '::1', 0, '2026-10-23 13:33:44'),
('6f520329-547d-4a30-b0c4-96382e00e53a', 'yasmin@gmail.com', '::1', 0, '2026-11-04 10:42:46'),
('6fbe58ff-c139-42b8-8cc1-299bf29aa0bf', 'naoexiste@example.com', '::1', 0, '2026-11-04 18:48:12'),
('73a69639-bcbd-4588-a63b-4cc30583254a', 'teste.ui.d1dbdaef@example.com', '::1', 1, '2026-10-23 11:45:54'),
('75870f79-0c36-4aea-b8b7-fcbaf564f936', 'yas@gmail.com', '::1', 1, '2026-10-16 18:20:04'),
('7ccb667d-06a7-4689-a44b-d9e7a93783ab', 'yas@gmail.com', '::1', 1, '2026-10-16 18:21:33'),
('7de2a225-816e-41bd-8945-a9bfa06cd4a3', 'teste10.e17b38@example.com', '::1', 1, '2026-10-23 14:47:08'),
('7df24851-f17b-4bdc-bca9-5887a6717b2c', 'user@teste.com', '::1', 1, '2026-11-04 16:58:36'),
('7ee230fc-72be-46b0-8c55-3e2591595dd9', 'user@teste.com', '::1', 0, '2026-10-23 11:00:22'),
('816ec5cd-645b-4553-a5bc-bb0d7587bcc1', 'teste.verif.81d2bd@example.com', '::1', 1, '2026-10-23 11:54:47'),
('82ec7649-9c81-4344-99ed-006629c3b918', 'yas@gmail.com', '::1', 1, '2026-10-16 14:22:28'),
('870fc826-cf3d-4bb5-b19f-5e0f69a7f217', 'yas@gmail.com', '::1', 1, '2026-10-16 18:19:54'),
('8899b2ef-02d8-44f8-b670-a3894cf6ffed', 'teste25.243c77@example.com', '::1', 1, '2026-10-23 16:59:21'),
('8a7a7ec6-c656-4b0d-8046-6c4f5ed45ceb', 'yas@gmail.com', '::1', 1, '2026-10-21 18:46:03'),
('8e711ac8-cedb-4506-8769-343c6584d877', 'teste.final.1c0967@example.com', '::1', 1, '2026-10-23 12:02:20'),
('93e08061-7f8a-4b2d-8400-2849ef5fd6fb', 'yas@gmail.com', '::1', 1, '2026-10-21 18:45:48'),
('a2755797-8715-404a-8ed7-b4814c5d4c02', 'teste.automatico.1792165997@example.com', '::1', 1, '2026-10-16 15:53:25'),
('a306c703-5180-40ad-a7c7-6bb6f94be08f', 'yas@gmail.com', '::1', 1, '2026-10-16 18:19:32'),
('aac2e6cf-e9b3-45fc-8997-3f622d60b8cb', 'user2@teste.com', '::1', 1, '2026-11-18 10:49:59'),
('af1777fe-6ef1-41a4-b82a-d5871e084cba', 'teste23.adb710@example.com', '::1', 1, '2026-10-23 16:58:44'),
('b1c6a988-fe0c-4772-a1db-87788fa8b840', 'yas@gmail.com', '::1', 1, '2026-10-16 18:19:29'),
('b2d43930-8a66-43b5-b64f-b5126da02ff6', 'teste8.dfa11b@example.com', '::1', 1, '2026-10-23 14:12:33'),
('b2f4c037-3c2c-4bea-aef6-cc2ebaea9967', 'teste21.4bb38b@example.com', '::1', 1, '2026-10-23 16:47:57'),
('b4f4f8e7-8796-4d8e-b8c7-83b58e2b73cc', 'user@teste.com', '::1', 1, '2026-10-16 18:23:24'),
('baf14a51-45ef-4701-9b42-c429007ecc49', 'user@teste.com', '::1', 1, '2026-11-04 13:12:59'),
('bbbb8f98-fdad-4371-8369-7c71b8f18c39', 'yas@gmail.com', '::1', 1, '2026-10-16 18:19:50'),
('c313a93f-c3ff-4b62-835e-f3974cc04868', 'teste4.565899@example.com', '::1', 1, '2026-10-23 13:33:52'),
('c3d53ca7-c029-4b22-aa6e-acec092f5050', 'user2@teste.com', '::1', 1, '2026-11-18 16:29:37'),
('c976df3f-2f84-4435-b30a-1bd65fb2e838', 'luiz.g.souza27@aluno.senai.br', '10.141.117.13', 0, '2026-10-23 16:34:41'),
('d0237a43-4872-4658-b9ce-258505407f37', 'user2@teste.com', '::1', 1, '2026-11-06 16:55:44'),
('d4be71dc-4123-49b8-a0f0-cbb2f2ed8505', 'teste.automatico.1792165560@example.com', '::1', 1, '2026-10-16 15:49:01'),
('d51335a9-0ae8-4d00-819e-0113b52609b3', 'teste10.e17b38@example.com', '::1', 1, '2026-10-23 14:46:46'),
('d6286c75-745f-4c4a-b2d4-4caa9a52c408', 'yas@gmail.com', '::1', 0, '2026-10-16 14:20:15'),
('d9517d1b-2492-40b0-9200-2760d9faffe0', 'teste.ui.85c53a69@example.com', '::1', 1, '2026-10-23 11:47:46'),
('da2aecd2-346d-45a1-a61c-e384bfb30c72', 'yas@gmail.com', '::1', 1, '2026-10-16 18:20:17'),
('da7cd979-19bb-496c-babe-35ef406b1bee', 'user2@teste.com', '::1', 1, '2026-11-18 11:50:36'),
('dc11e9f9-d594-4be4-8268-230f654fccc3', 'user@teste.com', '::1', 1, '2026-11-04 10:38:47'),
('e42778a0-4826-45d2-816c-65e97839037a', 'teste24.024080@example.com', '::1', 1, '2026-10-23 16:59:10'),
('e75d4a4c-c343-4464-9e9b-a2c473a2bfae', 'user@teste.com', '::1', 1, '2026-11-18 10:41:32'),
('e92ffe62-ce00-405f-87d4-85bec201077f', 'user@teste.com', '::1', 1, '2026-11-04 17:09:56'),
('ecf14a12-5e2c-43b0-8866-debc127b9212', 'theme-audit-1793985283334@example.com', '::1', 1, '2026-11-06 17:14:48'),
('f1ac0cf6-8088-40d3-bdb2-361f4a2ed0d2', 'user@teste.com', '::1', 1, '2026-11-04 17:05:46'),
('f2e18a1b-6343-445e-8e9c-ae416dc9da59', 'user2@teste.com', '::1', 1, '2026-11-06 18:10:31'),
('f55d0ead-c339-489d-9238-9cae17ff4a81', 'teste6.6d1fe1@example.com', '::1', 1, '2026-10-23 13:57:23'),
('f70fc433-d412-4bd6-b870-c4b46eb835dc', 'teste16.30006e@example.com', '::1', 1, '2026-10-23 15:14:57'),
('f72afee0-1c3a-4048-9fb7-73a3cb39ce9e', 'user@teste.com', '::1', 1, '2026-11-18 10:41:47'),
('f8821109-f122-4b38-9447-ec1b942a3e0a', 'user2@teste.com', '::1', 1, '2026-11-04 18:50:45'),
('fa3f970d-c8ab-4f72-8d27-77a2d9fcc7eb', 'teste.automatico.1792165560@example.com', '::1', 1, '2026-10-16 15:46:01'),
('fb95b553-285b-4769-aa59-5935aaed2ea1', 'user@teste.com', '::1', 1, '2026-11-04 17:09:53'),
('ffb5a0b4-809e-40b9-88a2-29380ec6d8d5', 'user@teste.com', '::1', 1, '2026-11-04 17:09:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` char(36) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `autenticacao_dois_fatores_ativa` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `versao_sincronizacao` int(11) DEFAULT 1,
  `status_sincronizacao` enum('pendente','sincronizado','conflito') DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `avatar`, `autenticacao_dois_fatores_ativa`, `criado_em`, `atualizado_em`, `versao_sincronizacao`, `status_sincronizacao`) VALUES
('837d2a76-7029-46d7-aea9-0885621d04d8', 'Yas', 'yas@gmail.com', '$2y$10$Yfq4nVnA258/14qItTTQPeOqIErT/Dqm4wzEYQPnCbKXgxmCm9KOC', '/2025/practice-day/public/perfil/avatar', 0, '2026-10-16 14:22:26', '2026-10-16 14:23:08', 1, 'pendente'),
('af90ddc4-217f-4556-87b9-aec1bad3fcbc', 'luiz', 'luiz.g.souza27@aluno.senai.br', '$2y$10$/jORSF9CRjS9SGrv.sCoTuh072w3MKrC8kR5dgGDrglr6Zm8Z6hPO', NULL, 0, '2026-10-23 16:36:26', '2026-10-23 16:36:26', 1, 'pendente'),
('b20f781a-a576-4b91-8f8e-bf9a1446131e', 'Yasmin', 'user2@teste.com', '$2y$10$9sOuMm2YKnDprRyhbiWrJuTOImWtWP2KVLu8fjA3HD2Bj45e4.ozW', '/2025/practice-day/public/perfil/avatar', 0, '2026-11-04 17:11:37', '2026-11-04 17:14:47', 1, 'pendente'),
('b7d6feaa-3562-41b4-86c4-840aaef3eb6e', 'Audit Theme', 'theme-audit-1793985283334@example.com', '$2y$10$BH0EC8F8Z1yIwGIE2o8n/uympb7m3WsrlrRcb97.DxDho25NZlZjm', NULL, 0, '2026-11-06 17:14:44', '2026-11-06 17:14:44', 1, 'pendente'),
('d3de82d5-ac8c-4d12-b351-5fe32e770a10', 'usuarioteste', 'user@teste.com', '$2y$10$PmRS11eEn5cweh6zT0OMAOdUuTN13tvrNBC2LdA/x6x4xijipNCtW', '/practice-day/public/perfil/avatar', 0, '2026-10-16 18:23:20', '2026-10-21 16:50:38', 1, 'pendente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `versoes_arquivo`
--

CREATE TABLE `versoes_arquivo` (
  `id` char(36) NOT NULL,
  `arquivo_id` char(36) NOT NULL,
  `numero_versao` int(11) NOT NULL,
  `caminho_armazenamento` text NOT NULL,
  `tamanho_bytes` bigint(20) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `widgets_dashboard`
--

CREATE TABLE `widgets_dashboard` (
  `id` char(36) NOT NULL,
  `usuario_id` char(36) NOT NULL,
  `tipo_widget` varchar(80) NOT NULL,
  `visivel` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `widgets_dashboard`
--

INSERT INTO `widgets_dashboard` (`id`, `usuario_id`, `tipo_widget`, `visivel`) VALUES
('095a2617-9b35-482e-89c6-c3d139299d00', '837d2a76-7029-46d7-aea9-0885621d04d8', 'disciplinas', 1),
('183294d1-a526-457d-877a-83ae61f989ec', 'af90ddc4-217f-4556-87b9-aec1bad3fcbc', 'tarefas', 1),
('1bfcf4f3-a139-49ba-827a-885dbde74b82', '837d2a76-7029-46d7-aea9-0885621d04d8', 'arquivos', 1),
('1c39f58f-8619-488d-aef2-be833cfc073f', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'tarefas', 1),
('36515278-3185-4646-a69f-f9c1bc121a14', '837d2a76-7029-46d7-aea9-0885621d04d8', 'tarefas', 1),
('42097050-b865-4b76-a238-ede3be2921c4', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'resumo', 1),
('4a74c005-69c2-49cf-bc6a-c05e838bd8da', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'notas', 1),
('53dc53d1-c696-493b-bee3-5d946a7c7994', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'arquivos', 1),
('55182de5-b255-46ed-9bd5-73a4a891d133', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'resumo', 1),
('6e030f81-e73e-43b7-8d0a-ea51a16523c6', 'b7d6feaa-3562-41b4-86c4-840aaef3eb6e', 'resumo', 1),
('7117df42-be28-4636-85c6-b3c206d5eaf0', 'b7d6feaa-3562-41b4-86c4-840aaef3eb6e', 'disciplinas', 1),
('83317b5d-6bd5-4cb7-ad8f-b99212679b0a', 'af90ddc4-217f-4556-87b9-aec1bad3fcbc', 'arquivos', 1),
('866059d5-30b7-4ecd-b88b-ee0a0979be36', 'af90ddc4-217f-4556-87b9-aec1bad3fcbc', 'resumo', 1),
('89ec283e-b230-4d6d-b9fb-20e878354397', '837d2a76-7029-46d7-aea9-0885621d04d8', 'notas', 1),
('9e2a31e8-3d90-4b1d-87ff-43405862d872', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'notas', 1),
('a3bb8fbe-3d07-4fdd-b390-a797ce5e6731', 'af90ddc4-217f-4556-87b9-aec1bad3fcbc', 'disciplinas', 1),
('a80cde2d-50da-4acc-bdb8-0239e1cc1784', 'b7d6feaa-3562-41b4-86c4-840aaef3eb6e', 'arquivos', 1),
('adaa73de-b953-4f9f-9321-8785624a9153', 'af90ddc4-217f-4556-87b9-aec1bad3fcbc', 'notas', 1),
('b879fa56-8680-43c2-9fa9-a2d1e6daf540', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'arquivos', 1),
('c258246f-4ced-4a5e-b43c-1cddaaa1999f', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'tarefas', 1),
('c4b14b5c-216e-4fc4-b4fc-b02e06027126', 'b7d6feaa-3562-41b4-86c4-840aaef3eb6e', 'notas', 1),
('c881246c-f36d-428b-8869-a4c0e97916ee', 'd3de82d5-ac8c-4d12-b351-5fe32e770a10', 'disciplinas', 1),
('df6b2f0c-78e5-44f3-a90c-87a317294d18', 'b20f781a-a576-4b91-8f8e-bf9a1446131e', 'disciplinas', 1),
('e2231691-5fe9-4f03-954f-802c0959cb7d', 'b7d6feaa-3562-41b4-86c4-840aaef3eb6e', 'tarefas', 1),
('e42de42f-b16f-4583-bc4e-c5392c9a7137', '837d2a76-7029-46d7-aea9-0885621d04d8', 'resumo', 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `anexos_tarefa`
--
ALTER TABLE `anexos_tarefa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarefa_id` (`tarefa_id`),
  ADD KEY `arquivo_id` (`arquivo_id`);

--
-- Índices de tabela `anotacoes_leitura`
--
ALTER TABLE `anotacoes_leitura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destaque_id` (`destaque_id`);

--
-- Índices de tabela `arquivos`
--
ALTER TABLE `arquivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasta_id` (`pasta_id`),
  ADD KEY `disciplina_id` (`disciplina_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `arquivos_favoritos`
--
ALTER TABLE `arquivos_favoritos`
  ADD PRIMARY KEY (`usuario_id`,`arquivo_id`),
  ADD KEY `arquivo_id` (`arquivo_id`);

--
-- Índices de tabela `biblioteca`
--
ALTER TABLE `biblioteca`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `arquivo_id` (`arquivo_id`);

--
-- Índices de tabela `blocos_nota`
--
ALTER TABLE `blocos_nota`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nota_id` (`nota_id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `configuracoes_usuario`
--
ALTER TABLE `configuracoes_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `conteudos_ocr`
--
ALTER TABLE `conteudos_ocr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `arquivo_id` (`arquivo_id`),
  ADD KEY `pagina_digitalizacao_id` (`pagina_digitalizacao_id`);

--
-- Índices de tabela `destaques_leitura`
--
ALTER TABLE `destaques_leitura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `biblioteca_id` (`biblioteca_id`);

--
-- Índices de tabela `digitalizacoes`
--
ALTER TABLE `digitalizacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `disciplina_id` (`disciplina_id`);

--
-- Índices de tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `eventos_calendario`
--
ALTER TABLE `eventos_calendario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `disciplina_id` (`disciplina_id`);

--
-- Índices de tabela `fila_sincronizacao`
--
ALTER TABLE `fila_sincronizacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `historico_arquivo`
--
ALTER TABLE `historico_arquivo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `arquivo_id` (`arquivo_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `historico_sincronizacao`
--
ALTER TABLE `historico_sincronizacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `lembretes`
--
ALTER TABLE `lembretes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `tarefa_id` (`tarefa_id`);

--
-- Índices de tabela `listas_checagem_tarefa`
--
ALTER TABLE `listas_checagem_tarefa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarefa_id` (`tarefa_id`);

--
-- Índices de tabela `lixeira_arquivos`
--
ALTER TABLE `lixeira_arquivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `arquivo_id` (`arquivo_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `marcadores_livro`
--
ALTER TABLE `marcadores_livro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `biblioteca_id` (`biblioteca_id`);

--
-- Índices de tabela `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `disciplina_id` (`disciplina_id`);

--
-- Índices de tabela `paginas_digitalizacao`
--
ALTER TABLE `paginas_digitalizacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `digitalizacao_id` (`digitalizacao_id`);

--
-- Índices de tabela `pastas`
--
ALTER TABLE `pastas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `disciplina_id` (`disciplina_id`),
  ADD KEY `pasta_pai_id` (`pasta_pai_id`);

--
-- Índices de tabela `permissoes_pasta`
--
ALTER TABLE `permissoes_pasta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasta_id` (`pasta_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `posicoes_widget`
--
ALTER TABLE `posicoes_widget`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `widget_id` (`widget_id`);

--
-- Índices de tabela `progresso_leitura`
--
ALTER TABLE `progresso_leitura`
  ADD PRIMARY KEY (`id`),
  ADD KEY `biblioteca_id` (`biblioteca_id`);

--
-- Índices de tabela `redefinicoes_senha`
--
ALTER TABLE `redefinicoes_senha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `registros_auditoria`
--
ALTER TABLE `registros_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `registros_produtividade`
--
ALTER TABLE `registros_produtividade`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `sessoes_estudo`
--
ALTER TABLE `sessoes_estudo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `disciplina_id` (`disciplina_id`);

--
-- Índices de tabela `sessoes_usuario`
--
ALTER TABLE `sessoes_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_sessao` (`token_sessao`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tags_arquivo`
--
ALTER TABLE `tags_arquivo`
  ADD PRIMARY KEY (`arquivo_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Índices de tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `disciplina_id` (`disciplina_id`);

--
-- Índices de tabela `temas_usuario`
--
ALTER TABLE `temas_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tentativas_login`
--
ALTER TABLE `tentativas_login`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `versoes_arquivo`
--
ALTER TABLE `versoes_arquivo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `arquivo_id` (`arquivo_id`);

--
-- Índices de tabela `widgets_dashboard`
--
ALTER TABLE `widgets_dashboard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `anexos_tarefa`
--
ALTER TABLE `anexos_tarefa`
  ADD CONSTRAINT `anexos_tarefa_ibfk_1` FOREIGN KEY (`tarefa_id`) REFERENCES `tarefas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anexos_tarefa_ibfk_2` FOREIGN KEY (`arquivo_id`) REFERENCES `arquivos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `anotacoes_leitura`
--
ALTER TABLE `anotacoes_leitura`
  ADD CONSTRAINT `anotacoes_leitura_ibfk_1` FOREIGN KEY (`destaque_id`) REFERENCES `destaques_leitura` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `arquivos`
--
ALTER TABLE `arquivos`
  ADD CONSTRAINT `arquivos_ibfk_1` FOREIGN KEY (`pasta_id`) REFERENCES `pastas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `arquivos_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `arquivos_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `arquivos_favoritos`
--
ALTER TABLE `arquivos_favoritos`
  ADD CONSTRAINT `arquivos_favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `arquivos_favoritos_ibfk_2` FOREIGN KEY (`arquivo_id`) REFERENCES `arquivos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `biblioteca`
--
ALTER TABLE `biblioteca`
  ADD CONSTRAINT `biblioteca_ibfk_1` FOREIGN KEY (`arquivo_id`) REFERENCES `arquivos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `blocos_nota`
--
ALTER TABLE `blocos_nota`
  ADD CONSTRAINT `blocos_nota_ibfk_1` FOREIGN KEY (`nota_id`) REFERENCES `notas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `configuracoes_usuario`
--
ALTER TABLE `configuracoes_usuario`
  ADD CONSTRAINT `configuracoes_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `conteudos_ocr`
--
ALTER TABLE `conteudos_ocr`
  ADD CONSTRAINT `conteudos_ocr_ibfk_1` FOREIGN KEY (`arquivo_id`) REFERENCES `arquivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conteudos_ocr_ibfk_2` FOREIGN KEY (`pagina_digitalizacao_id`) REFERENCES `paginas_digitalizacao` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `destaques_leitura`
--
ALTER TABLE `destaques_leitura`
  ADD CONSTRAINT `destaques_leitura_ibfk_1` FOREIGN KEY (`biblioteca_id`) REFERENCES `biblioteca` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `digitalizacoes`
--
ALTER TABLE `digitalizacoes`
  ADD CONSTRAINT `digitalizacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `digitalizacoes_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD CONSTRAINT `disciplinas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `eventos_calendario`
--
ALTER TABLE `eventos_calendario`
  ADD CONSTRAINT `eventos_calendario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventos_calendario_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `fila_sincronizacao`
--
ALTER TABLE `fila_sincronizacao`
  ADD CONSTRAINT `fila_sincronizacao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_arquivo`
--
ALTER TABLE `historico_arquivo`
  ADD CONSTRAINT `historico_arquivo_ibfk_1` FOREIGN KEY (`arquivo_id`) REFERENCES `arquivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_arquivo_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_sincronizacao`
--
ALTER TABLE `historico_sincronizacao`
  ADD CONSTRAINT `historico_sincronizacao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `lembretes`
--
ALTER TABLE `lembretes`
  ADD CONSTRAINT `lembretes_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos_calendario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lembretes_ibfk_2` FOREIGN KEY (`tarefa_id`) REFERENCES `tarefas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `listas_checagem_tarefa`
--
ALTER TABLE `listas_checagem_tarefa`
  ADD CONSTRAINT `listas_checagem_tarefa_ibfk_1` FOREIGN KEY (`tarefa_id`) REFERENCES `tarefas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `lixeira_arquivos`
--
ALTER TABLE `lixeira_arquivos`
  ADD CONSTRAINT `lixeira_arquivos_ibfk_1` FOREIGN KEY (`arquivo_id`) REFERENCES `arquivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lixeira_arquivos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `marcadores_livro`
--
ALTER TABLE `marcadores_livro`
  ADD CONSTRAINT `marcadores_livro_ibfk_1` FOREIGN KEY (`biblioteca_id`) REFERENCES `biblioteca` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `paginas_digitalizacao`
--
ALTER TABLE `paginas_digitalizacao`
  ADD CONSTRAINT `paginas_digitalizacao_ibfk_1` FOREIGN KEY (`digitalizacao_id`) REFERENCES `digitalizacoes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pastas`
--
ALTER TABLE `pastas`
  ADD CONSTRAINT `pastas_ibfk_1` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pastas_ibfk_2` FOREIGN KEY (`pasta_pai_id`) REFERENCES `pastas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `permissoes_pasta`
--
ALTER TABLE `permissoes_pasta`
  ADD CONSTRAINT `permissoes_pasta_ibfk_1` FOREIGN KEY (`pasta_id`) REFERENCES `pastas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissoes_pasta_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `posicoes_widget`
--
ALTER TABLE `posicoes_widget`
  ADD CONSTRAINT `posicoes_widget_ibfk_1` FOREIGN KEY (`widget_id`) REFERENCES `widgets_dashboard` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `progresso_leitura`
--
ALTER TABLE `progresso_leitura`
  ADD CONSTRAINT `progresso_leitura_ibfk_1` FOREIGN KEY (`biblioteca_id`) REFERENCES `biblioteca` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `redefinicoes_senha`
--
ALTER TABLE `redefinicoes_senha`
  ADD CONSTRAINT `redefinicoes_senha_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `registros_auditoria`
--
ALTER TABLE `registros_auditoria`
  ADD CONSTRAINT `registros_auditoria_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `registros_produtividade`
--
ALTER TABLE `registros_produtividade`
  ADD CONSTRAINT `registros_produtividade_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `sessoes_estudo`
--
ALTER TABLE `sessoes_estudo`
  ADD CONSTRAINT `sessoes_estudo_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sessoes_estudo_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `sessoes_usuario`
--
ALTER TABLE `sessoes_usuario`
  ADD CONSTRAINT `sessoes_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tags_arquivo`
--
ALTER TABLE `tags_arquivo`
  ADD CONSTRAINT `tags_arquivo_ibfk_1` FOREIGN KEY (`arquivo_id`) REFERENCES `arquivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tags_arquivo_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tarefas`
--
ALTER TABLE `tarefas`
  ADD CONSTRAINT `tarefas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarefas_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `temas_usuario`
--
ALTER TABLE `temas_usuario`
  ADD CONSTRAINT `temas_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `versoes_arquivo`
--
ALTER TABLE `versoes_arquivo`
  ADD CONSTRAINT `versoes_arquivo_ibfk_1` FOREIGN KEY (`arquivo_id`) REFERENCES `arquivos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `widgets_dashboard`
--
ALTER TABLE `widgets_dashboard`
  ADD CONSTRAINT `widgets_dashboard_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
