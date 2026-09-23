# PracticeDay

Base em PHP 8.3+ para o sistema PracticeDay, organizada por responsabilidades e preparada para o banco existente `practice_day`.

## Início rápido

1. Copie `.env.example` para `.env`.
2. Informe no `.env` as credenciais do MySQL/MariaDB que já contém o banco `practice_day`.
3. No Apache do XAMPP, habilite `mod_rewrite` e acesse `http://localhost/practice-day/public/`.

Nenhuma tabela, schema ou dado é criado, alterado ou carregado por esta estrutura.

## Organização

- `public/`: ponto de entrada HTTP e regras de reescrita.
- `app/`: controllers, regras de negócio, acesso a dados, middleware, helpers e validações.
- `config/`: ambiente, inicialização, sessão e configurações.
- `views/`: templates PHP separados por área da aplicação.
- `assets/`: CSS, JavaScript, TypeScript e recursos visuais.
- `storage/`: área gravável para arquivos futuros.
- `api/`: entrada reservada para endpoints JSON.
- `database/`: documentação e futuros artefatos não destrutivos do banco.

## Acesso ao banco

O acesso segue a cadeia `Controller → Service → Repository → Database/PDO`. A configuração fica em `config/database.php` e recebe suas credenciais do `.env`; consultas SQL não pertencem às views. Execute `php database/test_connection.php` para uma verificação somente de leitura com `SELECT DATABASE()`.

## Autenticação

As rotas `GET/POST /cadastro`, `GET/POST /login`, `POST /logout` e o dashboard protegido estão disponíveis pelo front controller. O cadastro cria `usuarios` e suas `configuracoes_usuario` padrão em uma transação. O login usa sessões PHP, token aleatório armazenado como hash em `sessoes_usuario` e registra todas as tentativas em `tentativas_login`. Não há recuperação de senha por e-mail: a tabela e o repositório correspondente ficam reservados para essa etapa futura.
