-- ============================================================================
-- CRIAÇÃO DO BANCO DE DADOS
-- ============================================================================
CREATE DATABASE IF NOT EXISTS practice_day
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE practice_day;

-- ============================================================================
-- 1. USUÁRIOS E AUTENTICAÇÃO
-- ============================================================================

CREATE TABLE usuarios (
    id CHAR(36) PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(180) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    avatar VARCHAR(255),
    autenticacao_dois_fatores_ativa BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    versao_sincronizacao INT DEFAULT 1,
    status_sincronizacao ENUM('pendente', 'sincronizado', 'conflito') DEFAULT 'pendente'
);

CREATE TABLE sessoes_usuario (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    token_sessao VARCHAR(255) UNIQUE NOT NULL,
    ip_origem VARCHAR(100),
    agente_usuario TEXT,
    expira_em TIMESTAMP NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE configuracoes_usuario (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL UNIQUE,
    modo_escuro BOOLEAN DEFAULT FALSE,
    cor_primaria VARCHAR(20) DEFAULT '#000000',
    cor_secundaria VARCHAR(20) DEFAULT '#FFFFFF',
    papel_parede TEXT,
    tamanho_fonte VARCHAR(20) DEFAULT 'médio',
    animacoes_ativas BOOLEAN DEFAULT TRUE,
    idioma VARCHAR(20) DEFAULT 'pt-BR',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE temas_usuario (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    nome_tema VARCHAR(80) NOT NULL,
    propriedades_json LONGTEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE redefinicoes_senha (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    token VARCHAR(255) NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    expira_em TIMESTAMP NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE tentativas_login (
    id CHAR(36) PRIMARY KEY,
    email VARCHAR(180) NOT NULL,
    endereco_ip VARCHAR(100) NOT NULL,
    sucesso BOOLEAN NOT NULL,
    tentativa_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 2. ORGANIZAÇÃO ACADÊMICA E ARQUIVOS
-- ============================================================================

CREATE TABLE disciplinas (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cor VARCHAR(20),
    icone VARCHAR(100),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE categorias (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    nome VARCHAR(80) NOT NULL,
    cor VARCHAR(20),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE pastas (
    id CHAR(36) PRIMARY KEY,
    disciplina_id CHAR(36),
    pasta_pai_id CHAR(36),
    nome VARCHAR(150) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
    FOREIGN KEY (pasta_pai_id) REFERENCES pastas(id) ON DELETE CASCADE
);

CREATE TABLE permissoes_pasta (
    id CHAR(36) PRIMARY KEY,
    pasta_id CHAR(36) NOT NULL,
    usuario_id CHAR(36) NOT NULL,
    tipo_permissao ENUM('leitura', 'escrita', 'administrador') DEFAULT 'leitura',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pasta_id) REFERENCES pastas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE tags (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    nome VARCHAR(80) NOT NULL,
    cor VARCHAR(20),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE arquivos (
    id CHAR(36) PRIMARY KEY,
    pasta_id CHAR(36),
    disciplina_id CHAR(36),
    usuario_id CHAR(36) NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    extensao VARCHAR(15),
    tipo_mime VARCHAR(120),
    tamanho_bytes BIGINT,
    caminho_armazenamento TEXT NOT NULL,
    favorito BOOLEAN DEFAULT FALSE,
    excluido BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    versao_sincronizacao INT DEFAULT 1,
    status_sincronizacao ENUM('pendente', 'sincronizado', 'conflito') DEFAULT 'pendente',
    FOREIGN KEY (pasta_id) REFERENCES pastas(id) ON DELETE SET NULL,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE tags_arquivo (
    arquivo_id CHAR(36) NOT NULL,
    tag_id CHAR(36) NOT NULL,
    PRIMARY KEY (arquivo_id, tag_id),
    FOREIGN KEY (arquivo_id) REFERENCES arquivos(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

CREATE TABLE versoes_arquivo (
    id CHAR(36) PRIMARY KEY,
    arquivo_id CHAR(36) NOT NULL,
    numero_versao INT NOT NULL,
    caminho_armazenamento TEXT NOT NULL,
    tamanho_bytes BIGINT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (arquivo_id) REFERENCES arquivos(id) ON DELETE CASCADE
);

CREATE TABLE historico_arquivo (
    id CHAR(36) PRIMARY KEY,
    arquivo_id CHAR(36) NOT NULL,
    usuario_id CHAR(36) NOT NULL,
    acao ENUM('criado', 'modificado', 'renomeado', 'movido', 'restaurado') NOT NULL,
    detalhes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (arquivo_id) REFERENCES arquivos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE arquivos_favoritos (
    usuario_id CHAR(36) NOT NULL,
    arquivo_id CHAR(36) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, arquivo_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (arquivo_id) REFERENCES arquivos(id) ON DELETE CASCADE
);

CREATE TABLE lixeira_arquivos (
    id CHAR(36) PRIMARY KEY,
    arquivo_id CHAR(36) NOT NULL,
    usuario_id CHAR(36) NOT NULL,
    excluido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expira_em TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (arquivo_id) REFERENCES arquivos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
-- ============================================================================
-- 3. SCANNER E OCR
-- ============================================================================

CREATE TABLE digitalizacoes (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    total_paginas INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE paginas_digitalizacao (
    id CHAR(36) PRIMARY KEY,
    digitalizacao_id CHAR(36) NOT NULL,
    numero_pagina INT NOT NULL,
    caminho_imagem TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (digitalizacao_id) REFERENCES digitalizacoes(id) ON DELETE CASCADE
);

CREATE TABLE conteudos_ocr (
    id CHAR(36) PRIMARY KEY,
    arquivo_id CHAR(36),
    pagina_digitalizacao_id CHAR(36),
    texto_extraido LONGTEXT,
    idioma VARCHAR(20),
    precisao DECIMAL(5, 2),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (arquivo_id) REFERENCES arquivos(id) ON DELETE CASCADE,
    FOREIGN KEY (pagina_digitalizacao_id) REFERENCES paginas_digitalizacao(id) ON DELETE CASCADE
);

-- ============================================================================
-- 4. BIBLIOTECA E LEITURA
-- ============================================================================

CREATE TABLE biblioteca (
    id CHAR(36) PRIMARY KEY,
    arquivo_id CHAR(36) NOT NULL UNIQUE,
    pagina_atual INT DEFAULT 1,
    total_paginas INT NOT NULL,
    progresso_porcentagem DECIMAL(5,2) DEFAULT 0.00,
    ultimo_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (arquivo_id) REFERENCES arquivos(id) ON DELETE CASCADE
);

CREATE TABLE marcadores_livro (
    id CHAR(36) PRIMARY KEY,
    biblioteca_id CHAR(36) NOT NULL,
    numero_pagina INT NOT NULL,
    titulo VARCHAR(150),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (biblioteca_id) REFERENCES biblioteca(id) ON DELETE CASCADE
);

CREATE TABLE destaques_leitura (
    id CHAR(36) PRIMARY KEY,
    biblioteca_id CHAR(36) NOT NULL,
    numero_pagina INT NOT NULL,
    texto_selecionado TEXT NOT NULL,
    cor VARCHAR(20) DEFAULT '#FFFF00',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (biblioteca_id) REFERENCES biblioteca(id) ON DELETE CASCADE
);

CREATE TABLE anotacoes_leitura (
    id CHAR(36) PRIMARY KEY,
    destaque_id CHAR(36) NOT NULL,
    conteudo TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destaque_id) REFERENCES destaques_leitura(id) ON DELETE CASCADE
);

CREATE TABLE progresso_leitura (
    id CHAR(36) PRIMARY KEY,
    biblioteca_id CHAR(36) NOT NULL,
    tempo_leitura_segundos INT DEFAULT 0,
    paginas_lidas_sessao INT DEFAULT 0,
    registrado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (biblioteca_id) REFERENCES biblioteca(id) ON DELETE CASCADE
);

-- ============================================================================
-- 5. PLANNER E CALENDÁRIO
-- ============================================================================

CREATE TABLE tarefas (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    disciplina_id CHAR(36),
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    prioridade ENUM('baixa', 'media', 'alta') DEFAULT 'media',
    status ENUM('a_fazer', 'em_andamento', 'concluido') DEFAULT 'a_fazer',
    data_vencimento DATETIME,
    recorrente BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE SET NULL
);

CREATE TABLE listas_checagem_tarefa (
    id CHAR(36) PRIMARY KEY,
    tarefa_id CHAR(36) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    concluido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (tarefa_id) REFERENCES tarefas(id) ON DELETE CASCADE
);

CREATE TABLE anexos_tarefa (
    id CHAR(36) PRIMARY KEY,
    tarefa_id CHAR(36) NOT NULL,
    arquivo_id CHAR(36) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tarefa_id) REFERENCES tarefas(id) ON DELETE CASCADE,
    FOREIGN KEY (arquivo_id) REFERENCES arquivos(id) ON DELETE CASCADE
);

CREATE TABLE eventos_calendario (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    disciplina_id CHAR(36),
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    tipo ENUM('prova', 'trabalho', 'evento', 'lembrete') NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE SET NULL
);

CREATE TABLE lembretes (
    id CHAR(36) PRIMARY KEY,
    evento_id CHAR(36),
    tarefa_id CHAR(36),
    data_hora_lembrete DATETIME NOT NULL,
    disparado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (evento_id) REFERENCES eventos_calendario(id) ON DELETE CASCADE,
    FOREIGN KEY (tarefa_id) REFERENCES tarefas(id) ON DELETE CASCADE
);

-- ============================================================================
-- 6. NOTAS E DASHBOARD
-- ============================================================================

CREATE TABLE notas (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    disciplina_id CHAR(36),
    titulo VARCHAR(255) NOT NULL,
    conteudo LONGTEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE SET NULL
);

CREATE TABLE blocos_nota (
    id CHAR(36) PRIMARY KEY,
    nota_id CHAR(36) NOT NULL,
    tipo_bloco ENUM('texto', 'codigo', 'imagem', 'lista', 'equacao') NOT NULL,
    conteudo LONGTEXT NOT NULL,
    ordem INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nota_id) REFERENCES notas(id) ON DELETE CASCADE
);

CREATE TABLE widgets_dashboard (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    tipo_widget VARCHAR(80) NOT NULL,
    visivel BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE posicoes_widget (
    id CHAR(36) PRIMARY KEY,
    widget_id CHAR(36) NOT NULL UNIQUE,
    posicao_x INT NOT NULL,
    posicao_y INT NOT NULL,
    largura INT NOT NULL,
    altura INT NOT NULL,
    FOREIGN KEY (widget_id) REFERENCES widgets_dashboard(id) ON DELETE CASCADE
);

-- ============================================================================
-- 7. ESTATÍSTICAS E PRODUTIVIDADE
-- ============================================================================

CREATE TABLE sessoes_estudo (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    disciplina_id CHAR(36),
    inicio_sessao DATETIME NOT NULL,
    fim_sessao DATETIME,
    duracao_minutos INT,
    tipo_tecnica ENUM('pomodoro', 'livre', 'cronometro') DEFAULT 'livre',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE SET NULL
);

CREATE TABLE registros_produtividade (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    data_registro DATE NOT NULL,
    tarefas_concluidas INT DEFAULT 0,
    tempo_estudo_minutos INT DEFAULT 0,
    pontuacao_produtividade INT DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================================
-- 8. SINCRONIZAÇÃO E AUDITORIA
-- ============================================================================

CREATE TABLE fila_sincronizacao (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    entidade VARCHAR(100) NOT NULL,
    entidade_id CHAR(36) NOT NULL,
    operacao ENUM('insercao', 'atualizacao', 'exclusao') NOT NULL,
    dados_json LONGTEXT,
    processado BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE historico_sincronizacao (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    sincronizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sucesso', 'falha', 'conflito_resolvido') NOT NULL,
    detalhes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE registros_auditoria (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36),
    acao VARCHAR(255) NOT NULL,
    entidade VARCHAR(120) NOT NULL,
    entidade_id CHAR(36),
    endereco_ip VARCHAR(100),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);