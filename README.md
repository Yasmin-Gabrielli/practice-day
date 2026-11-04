# PracticeDay

Sistema de organização de estudos em PHP 8.3+, com disciplinas, arquivos, notas, planner, calendário, Pomodoro, estatísticas, biblioteca e scanner.

Este guia mostra como colocar o projeto para funcionar em **outra máquina**, partindo de um clone do GitHub.

---

## Requisitos

Antes de começar, instale:

- **PHP 8.3 ou superior**
- **MySQL 8+** ou **MariaDB 10.4+**
- **Apache** com `mod_rewrite` habilitado
- **Git**
- Extensões PHP: `pdo_mysql`, `mbstring`, `fileinfo` e `zip`

A forma mais simples no Windows é usar o **XAMPP**, que já inclui Apache, PHP, MySQL/MariaDB e phpMyAdmin.

> O site usa Bootstrap e Bootstrap Icons via CDN. Por isso, a máquina precisa ter acesso à internet para carregar esses recursos visuais.

---

## 1. Clone o projeto

### Windows com XAMPP

Abra o PowerShell e execute:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/Yasmin-Gabrielli/practice-day.git practice-day
```

O projeto ficará em:

```text
C:\xampp\htdocs\practice-day
```

### Linux ou macOS

Clone dentro da pasta pública do seu servidor Apache:

```bash
cd /var/www/html
sudo git clone https://github.com/Yasmin-Gabrielli/practice-day.git practice-day
```

Se estiver usando Laragon, clone em `C:\laragon\www\practice-day`.

> Se você clonar com outro nome de pasta, ajuste a URL de acesso e o `APP_URL` conforme o caminho usado.

> Esta versão não exige `composer install` nem build de front-end. O Bootstrap e os ícones são carregados por CDN.

---

## 2. Inicie o Apache e o MySQL

No painel do XAMPP, clique em **Start** em:

- Apache
- MySQL

Se estiver usando Linux, inicie os serviços correspondentes:

```bash
sudo systemctl start apache2
sudo systemctl start mysql
```

---

## 3. Importe o banco de dados

O projeto inclui o arquivo:

```text
sql-banco/practice_day (1).sql
```

Ele cria o banco `practice_day`, as tabelas e alguns dados de exemplo.

### Opção A — phpMyAdmin

1. Acesse `http://localhost/phpmyadmin`.
2. Vá em **Importar**.
3. Selecione o arquivo `sql-banco/practice_day (1).sql`.
4. Clique em **Executar**.

### Opção B — Linha de comando no Windows

No PowerShell, dentro da pasta do projeto:

```powershell
cmd /c '"C:\xampp\mysql\bin\mysql.exe" -u root < "sql-banco\practice_day (1).sql"'
```

Se o usuário `root` tiver senha, use:

```powershell
cmd /c '"C:\xampp\mysql\bin\mysql.exe" -u root -p < "sql-banco\practice_day (1).sql"'
```

### Opção C — Linux ou macOS

```bash
mysql -u root -p < "sql-banco/practice_day (1).sql"
```

> O dump inclui dados de exemplo, incluindo um usuário já cadastrado. Para usar sua própria conta, acesse a tela de cadastro do site e crie um novo usuário. Não reutilize os dados de exemplo em produção.

### Se você já tem dados na máquina antiga

Os arquivos enviados pelos usuários **não ficam no GitHub**, pois a pasta `storage` é ignorada pelo Git. Para levar também os anexos, avatares e digitalizações:

1. Importe o dump do banco na máquina nova.
2. Copie o conteúdo de `storage/uploads`, `storage/scanner`, `storage/documentos` e `storage/users` da máquina antiga para as mesmas pastas na máquina nova.
3. Não é necessário copiar `storage/sessions`, pois são sessões temporárias.

Se o banco da máquina antiga estiver mais atualizado que o dump do repositório, gere um novo dump antes de transferir:

```powershell
C:\xampp\mysql\bin\mysqldump.exe -u root --result-file="backup-practice-day.sql" practice_day
```

---

## 4. Configure o arquivo `.env`

Na raiz do projeto, copie o arquivo de exemplo:

### Windows

```powershell
Copy-Item .env.example .env
notepad .env
```

### Linux ou macOS

```bash
cp .env.example .env
nano .env
```

Preencha com os dados do seu ambiente:

```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/practice-day/public

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=practice_day
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

Observações:

- No XAMPP padrão, o usuário é `root` e a senha fica em branco.
- Se o seu MySQL usar outra porta, altere `DB_PORT`.
- Se estiver usando outra pasta, ajuste `APP_URL` para o endereço correto.
- O arquivo `.env` não deve ser enviado para o GitHub.

---

## 5. Habilite o `mod_rewrite` no Apache

O projeto usa URLs amigáveis, como `/login` e `/cadastro`, configuradas pelo arquivo `public/.htaccess`.

### XAMPP no Windows

1. Abra o arquivo `C:\xampp\apache\conf\httpd.conf`.
2. Verifique se esta linha está sem `#`:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

3. Localize o bloco do `htdocs` e confirme que está assim:

```apache
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

4. Salve o arquivo e reinicie o Apache no painel do XAMPP.

### Linux

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 6. Permissões de escrita

O site grava sessões, uploads, avatares, papéis de parede e páginas do scanner na pasta `storage`.

### Windows

Normalmente não é necessário fazer nada.

### Linux ou macOS

```bash
sudo chmod -R 775 storage
```

Em servidores Debian/Ubuntu, ajuste também o proprietário para o usuário do Apache:

```bash
sudo chown -R www-data:www-data storage
```

---

## 7. Acesse o site

Abra no navegador:

```text
http://localhost/practice-day/public/
```

Páginas principais:

- Login: `http://localhost/practice-day/public/login`
- Cadastro: `http://localhost/practice-day/public/cadastro`

Se ainda não tiver uma conta, clique em **Criar conta** e cadastre-se.

> Não abra o arquivo `public/index.php` diretamente pelo explorador de arquivos. O projeto precisa ser executado pelo Apache, com PHP e MySQL ativos.

---

## 8. Teste a conexão com o banco

### Windows

```powershell
C:\xampp\php\php.exe database\test_connection.php
```

### Linux ou macOS

```bash
php database/test_connection.php
```

Se estiver tudo certo, a saída será parecida com:

```text
Conexão confirmada com o banco practice_day.
```

---

## Recursos opcionais

### OCR no Scanner

O OCR usa o Tesseract como programa externo. Ele não é obrigatório para usar o restante do sistema.

1. Instale o [Tesseract OCR](https://tesseract-ocr.github.io/tessdoc/Installation.html).
2. Instale o pacote de português (`por`).
3. Informe o caminho no `.env`, se necessário:

```env
TESSERACT_PATH=C:\Program Files\Tesseract-OCR\tesseract.exe
```

No Linux, o caminho costuma ser `/usr/bin/tesseract`.

### Acesso pela câmera

O scanner usa a câmera do navegador. Para funcionar, o site deve estar em `http://localhost` ou em HTTPS, e o navegador precisa permitir o acesso à câmera.

---

## Problemas comuns

### Página 404 ao acessar `/login`

Verifique:

- Se está acessando pela URL com `/public/`.
- Se o `mod_rewrite` está habilitado.
- Se o `AllowOverride All` está configurado no Apache.
- Se o Apache foi reiniciado depois da alteração.

### Erro ao conectar ao banco

Verifique:

- Se o MySQL está iniciado.
- Se o banco `practice_day` foi importado.
- Se `DB_HOST`, `DB_PORT`, `DB_USERNAME` e `DB_PASSWORD` estão corretos no `.env`.
- Se a extensão `pdo_mysql` está habilitada no PHP.

### Tela branca ou erro 500

- Confira o log de erros do Apache (`C:\xampp\apache\logs\error.log`).
- Confirme que está usando PHP 8.3 ou superior.
- Verifique se a pasta `storage` tem permissão de escrita.
- Ative temporariamente `APP_DEBUG=true` no `.env`.

### CSS ou ícones não carregam

- Confirme que a máquina tem internet, pois Bootstrap e Bootstrap Icons vêm de CDN.
- Confirme que a pasta do projeto está dentro do diretório servido pelo Apache.
- Verifique se a URL de acesso corresponde à pasta real do projeto.

### Login não funciona com o usuário de exemplo

A senha do usuário presente no dump não é divulgada. Crie uma conta nova pela tela de cadastro.

---

## Organização do projeto

- `public/`: ponto de entrada HTTP e regras de reescrita.
- `app/`: controllers, services, repositories, middleware, helpers e validações.
- `config/`: ambiente, inicialização, sessão e configurações.
- `views/`: templates PHP separados por área da aplicação.
- `assets/`: CSS, JavaScript, TypeScript e recursos visuais.
- `storage/`: sessões, uploads, avatares, papéis de parede e páginas do scanner.
- `sql-banco/`: dump do banco e migrations SQL.
- `database/`: documentação e scripts de verificação do banco.
- `api/`: entrada reservada para endpoints JSON.
- `docs/`: documentação complementar.

---

## Segurança

- Nunca envie o arquivo `.env` para o GitHub.
- Não envie a pasta `storage` com dados de usuários.
- Em produção, use:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
```

- Utilize HTTPS e um usuário de banco com permissões restritas.
- Revise os dados de exemplo do dump antes de publicar o sistema.

---

## Atualizando o projeto

Depois de já ter o projeto na máquina, para buscar novas alterações do GitHub:

```bash
git pull origin main
```

Se houver alterações no banco, importe ou execute as migrations indicadas na pasta `sql-banco/migrations`.
