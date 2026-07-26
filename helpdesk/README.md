# Help Desk Corporativo

Sistema de chamados (Help Desk) interno, desenvolvido em **PHP 8+ (POO, MVC, PDO)**, **MySQL**, **HTML5/CSS3** e **JavaScript ES6 + AJAX**, com dashboard em **Chart.js**. Tema escuro, responsivo, pronto para produção.

Testado de ponta a ponta neste ambiente (PHP 8.3 + MariaDB 10.11): login, abertura de chamado, comentários, anexos, atribuição, encaminhamento entre setores, fechamento com resolução obrigatória, relatórios (CSV/PDF), CRUD de usuários/setores/categorias e controle de permissões por perfil.

## 1. Requisitos

- PHP 8.1 ou superior, com extensões: `pdo_mysql`, `fileinfo`, `mbstring` (opcional — há fallback caso não exista)
- MySQL 8+ ou MariaDB 10.6+
- Servidor Apache (com `mod_rewrite`) ou Nginx, **ou** o servidor embutido do PHP para testes

## 2. Instalação

### 2.1. Banco de dados

```bash
mysql -u root -p < database.sql
```

Isso cria o banco `helpdesk`, todas as tabelas e os dados iniciais:

- **5 setores**: TI, Comercial, Jurídico, Financeiro, Recursos Humanos
- **3 usuários de teste** (senha para todos: `Senha@123`):
  - `admin@empresa.com` — Administrador
  - `atendente.ti@empresa.com` — Atendente (setor TI)
  - `colaborador@empresa.com` — Colaborador (setor Comercial)
- **12 categorias** distribuídas entre os setores

### 2.2. Configuração

Edite `config/config.php` e ajuste:

```php
define('APP_URL', 'http://localhost/helpdesk/public'); // URL onde o /public ficará acessível
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'helpdesk');
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha');
define('APP_ENV', 'production'); // troque para 'development' se precisar depurar erros
```

### 2.3. Permissões

A pasta de uploads precisa ter permissão de escrita pelo usuário do servidor web:

```bash
chmod -R 775 public/assets/uploads
```

### 2.4. Rodando

**Opção A — Apache/Nginx**: aponte o *DocumentRoot* (ou um VirtualHost) para a pasta `public/`. O arquivo `public/.htaccess` já cuida das URLs amigáveis no Apache. Para Nginx, use:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

**Opção B — Servidor embutido do PHP (rápido, para testes)**:

```bash
cd public
php -S localhost:8080 index.php
```

Acesse `http://localhost:8080/login`.

## 3. Estrutura de pastas (MVC)

```
helpdesk/
├── database.sql                  # Script completo do banco (schema + seed)
├── config/
│   └── config.php                # Configurações gerais, autoload, sessão
├── app/
│   ├── Core/                     # Núcleo do framework
│   │   ├── Database.php          # Conexão PDO (Singleton)
│   │   ├── Model.php             # CRUD genérico base
│   │   ├── Controller.php        # Base de controllers (view, redirect, json...)
│   │   ├── Router.php            # Roteador (mapa de URIs -> Controller::ação)
│   │   ├── Auth.php              # Autenticação e sessão
│   │   ├── Csrf.php              # Proteção CSRF
│   │   ├── Upload.php            # Validação e armazenamento seguro de arquivos
│   │   └── helpers.php           # Funções globais (e(), badges, flash...)
│   ├── Models/                   # Usuario, Setor, Categoria, Chamado, Comentario, Historico, Anexo
│   └── Controllers/               # Auth, Dashboard, Chamado, Usuario, Setor, Categoria, Perfil, Relatorio
├── views/
│   ├── layouts/                  # header, footer, sidebar (tema escuro)
│   ├── auth/, dashboard/, chamados/, usuarios/, setores/, categorias/, perfil/, relatorios/, errors/
└── public/                       # DocumentRoot
    ├── index.php                 # Front controller (todas as rotas)
    ├── .htaccess
    └── assets/
        ├── css/style.css         # Tema escuro (#111827, #1F2937, #4F46E5...)
        ├── js/app.js
        └── uploads/              # Arquivos enviados (por chamado, em subpastas)
```

## 4. Perfis de usuário

| Perfil          | Permissões                                                                 |
|------------------|-----------------------------------------------------------------------------|
| **Administrador** | Acesso total: usuários, setores, categorias, todos os chamados, relatórios |
| **Atendente**      | Vê todos os chamados do sistema, altera status, atribui, encaminha, comenta (inclusive notas internas), acessa relatórios |
| **Colaborador**    | Abre chamados e só visualiza/comenta os próprios chamados                 |

## 5. Regras de negócio implementadas

- Código do chamado gerado automaticamente no formato `CH-AAAA-00001`
- **Fechamento bloqueado** sem preencher "Como foi resolvido"
- SLA calculado automaticamente (`sla_previsto`) a partir das horas configuradas por setor, recalculado a cada encaminhamento
- Histórico (`historicos`) é **imutável**: os métodos `update()`/`delete()` do model lançam exceção
- Comentários podem ser marcados como **nota interna** (visível só para atendentes/admin)
- Upload valida extensão **e** MIME real do arquivo (não confia só na extensão)
- Colaboradores só enxergam os próprios chamados; tentativa de acessar chamado de terceiros retorna 403
- Exclusões de usuários/setores/categorias são *soft delete* (campo `ativo`), preservando o histórico

## 6. Exportação de relatórios

- **Excel**: gera CSV com BOM UTF-8 (abre corretamente acentuação no Excel) — `/relatorios/excel`
- **PDF**: gera uma página HTML de impressão que aciona `window.print()` automaticamente, permitindo "Salvar como PDF" pelo navegador — `/relatorios/pdf`. Caso prefira geração de PDF no servidor, adicione uma biblioteca como `dompdf/dompdf` via Composer e adapte `RelatorioController::exportarPdf()`.

## 7. Segurança aplicada

- Senhas com `password_hash()` (bcrypt) e `password_verify()`
- 100% das queries via PDO com *prepared statements* (zero SQL concatenado)
- Token CSRF em todos os formulários POST, validado em `Csrf::validateRequest()`
- Escape de saída via `e()` (equivalente a `htmlspecialchars`) em todas as views
- Validação de tipo MIME real (`finfo`) nos uploads, com nomes de arquivo aleatórios (evita path traversal e sobrescrita)
- Cookies de sessão `HttpOnly` + `SameSite=Lax`, regeneração de ID de sessão no login

## 8. Próximos passos sugeridos (não incluídos neste pacote)

- Notificações por e-mail (ex: via PHPMailer) ao abrir/atribuir/fechar chamados
- Geração de PDF nativa no servidor (dompdf/mPDF) em vez do modelo de impressão
- Painel de configuração de SLA por prioridade (hoje o SLA é por setor)
- Testes automatizados (PHPUnit)
