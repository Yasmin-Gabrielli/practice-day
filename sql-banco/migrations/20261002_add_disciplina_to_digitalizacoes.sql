ALTER TABLE digitalizacoes
    ADD COLUMN disciplina_id CHAR(36) NULL AFTER usuario_id,
    ADD CONSTRAINT fk_digitalizacoes_disciplina
        FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE SET NULL;
