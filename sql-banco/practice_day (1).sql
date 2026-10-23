-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 16/10/2026 às 20:12
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
CREATE DATABASE IF NOT EXISTS `practice_day` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `practice_day`;

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
  `status_sincronizacao` enum('pendente','sincronizado','conflito') DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `arquivos`
--

INSERT INTO `arquivos` (`id`, `pasta_id`, `disciplina_id`, `usuario_id`, `nome_arquivo`, `nome_original`, `extensao`, `tipo_mime`, `tamanho_bytes`, `caminho_armazenamento`, `favorito`, `excluido`, `criado_em`, `atualizado_em`, `versao_sincronizacao`, `status_sincronizacao`) VALUES
('8cc5587b-2e49-4d35-b223-d62e7a1c0cbc', 'c2f614c4-afbc-4142-a959-f46b05988320', 'e0468669-f80a-4635-a6fc-dcf2de924849', '837d2a76-7029-46d7-aea9-0885621d04d8', '8cc5587b-2e49-4d35-b223-d62e7a1c0cbc.jpg', 'dante.jpg', 'jpg', 'image/jpeg', 152100, 'uploads/837d2a76-7029-46d7-aea9-0885621d04d8/8cc5587b-2e49-4d35-b223-d62e7a1c0cbc.jpg', 1, 0, '2026-10-16 16:27:18', '2026-10-16 16:28:08', 1, 'pendente');

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
  `ultimo_acesso` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('9eabc6a1-7ba5-4fbc-bef2-cf7dcab2facb', '837d2a76-7029-46d7-aea9-0885621d04d8', 0, '#486093', '#c4acdc', NULL, 'grande', 1, 'pt-BR');

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
('762734a7-5db1-4d92-a849-ce4381c464e7', '837d2a76-7029-46d7-aea9-0885621d04d8', 'Matemática', '#7c4d89', 'bi-calculator', '2026-10-16 14:24:48', '2026-10-16 14:24:48'),
('7ce2fd63-7005-4cdd-9101-2265965545ba', '837d2a76-7029-46d7-aea9-0885621d04d8', 'Física', '#4077a0', 'bi-lightning-charge', '2026-10-16 14:25:57', '2026-10-16 14:25:57'),
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
('06c170b2-92ce-4f5e-8994-ac26c734358a', '837d2a76-7029-46d7-aea9-0885621d04d8', NULL, 'Vestibular UNOESTE', 'Vestibular UNOESTE, ADS', '2026-10-18 09:00:00', '2026-10-18 12:00:00', 'evento');

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
('0fec2ab8-bcf4-487c-b636-2d1d83c84bee', '5aa2f58b-4845-41b6-bcc1-b60ecd88b223', 'Montar leds', 1),
('5e4b1b72-2311-43a3-bf3c-06cd12c033d8', '5aa2f58b-4845-41b6-bcc1-b60ecd88b223', 'Montar LCD', 0),
('8dd68c23-ede2-4cf3-bcf9-8562e168f560', '5aa2f58b-4845-41b6-bcc1-b60ecd88b223', 'Testar Projeto', 0);

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
('c2f614c4-afbc-4142-a959-f46b05988320', 'e0468669-f80a-4635-a6fc-dcf2de924849', NULL, 'batata', '2026-10-16 16:27:00');

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
('5dca31e6-58dd-40ea-a07f-df8c016dcf9a', 'c2f614c4-afbc-4142-a959-f46b05988320', '837d2a76-7029-46d7-aea9-0885621d04d8', 'administrador', '2026-10-16 16:27:00');

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
('bac4df8f-0f98-4410-8b83-4ba280352b2f', '837d2a76-7029-46d7-aea9-0885621d04d8', '7c84c70381d94799f79b546783e4b0b43746e5ceb458c5841b4f550300aea372', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-16 22:22:28', '2026-10-16 14:22:28');

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

-- --------------------------------------------------------

--
-- Estrutura para tabela `tags_arquivo`
--

CREATE TABLE `tags_arquivo` (
  `arquivo_id` char(36) NOT NULL,
  `tag_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('5aa2f58b-4845-41b6-bcc1-b60ecd88b223', '837d2a76-7029-46d7-aea9-0885621d04d8', '7ce2fd63-7005-4cdd-9101-2265965545ba', 'Projeto - Feira de Ciências', 'Projeto da feira de Ciências, Balada Tecnologica, Jogo de Botões, Site.', 'alta', 'em_andamento', '2026-10-22 12:20:00', 0, '2026-10-16 14:28:05');

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
('83273230-c105-4d35-abff-8dc9765fe057', '837d2a76-7029-46d7-aea9-0885621d04d8', 'notificacoes_visualizadas', '{\"visualizadas\":{\"lembrete:15caf627-4eea-4b17-8bc9-0ec2eb589556\":\"2026-10-16 13:33:14\",\"evento:06c170b2-92ce-4f5e-8994-ac26c734358a\":\"2026-10-16 13:33:19\",\"tarefa_proxima:5aa2f58b-4845-41b6-bcc1-b60ecd88b223\":\"2026-10-16 13:33:21\"}}', '2026-10-16 16:33:14');

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
('0eb57c01-545d-4b9d-9bac-f5dc10ddb49f', 'teste.automatico.1792165997@example.com', '::1', 1, '2026-10-16 15:53:18'),
('11c7d1f4-2cc3-43a1-99f8-eb1fa63a42b7', 'teste.automatico.1792165525@example.com', '::1', 1, '2026-10-16 15:45:25'),
('1de8622c-ca46-4dd2-9837-9322fcf1702a', 'teste.automatico.1792165997@example.com', '::1', 1, '2026-10-16 15:53:24'),
('82ec7649-9c81-4344-99ed-006629c3b918', 'yas@gmail.com', '::1', 1, '2026-10-16 14:22:28'),
('a2755797-8715-404a-8ed7-b4814c5d4c02', 'teste.automatico.1792165997@example.com', '::1', 1, '2026-10-16 15:53:25'),
('d4be71dc-4123-49b8-a0f0-cbb2f2ed8505', 'teste.automatico.1792165560@example.com', '::1', 1, '2026-10-16 15:49:01'),
('d6286c75-745f-4c4a-b2d4-4caa9a52c408', 'yas@gmail.com', '::1', 0, '2026-10-16 14:20:15'),
('fa3f970d-c8ab-4f72-8d27-77a2d9fcc7eb', 'teste.automatico.1792165560@example.com', '::1', 1, '2026-10-16 15:46:01');

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
('837d2a76-7029-46d7-aea9-0885621d04d8', 'Yas', 'yas@gmail.com', '$2y$10$Yfq4nVnA258/14qItTTQPeOqIErT/Dqm4wzEYQPnCbKXgxmCm9KOC', '/2025/practice-day/public/perfil/avatar', 0, '2026-10-16 14:22:26', '2026-10-16 14:23:08', 1, 'pendente');

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
('1bfcf4f3-a139-49ba-827a-885dbde74b82', '837d2a76-7029-46d7-aea9-0885621d04d8', 'arquivos', 1),
('36515278-3185-4646-a69f-f9c1bc121a14', '837d2a76-7029-46d7-aea9-0885621d04d8', 'tarefas', 1),
('89ec283e-b230-4d6d-b9fb-20e878354397', '837d2a76-7029-46d7-aea9-0885621d04d8', 'notas', 1),
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
