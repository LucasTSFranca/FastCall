# FastCall

Sistema web para organizar solicitações de atendimento em restaurantes e outros estabelecimentos. O cliente acessa uma página vinculada à mesa por QR Code e solicita um atendente ou pede a conta. A equipe acompanha a fila em tempo real, e administradores e gerentes podem consultar relatórios.

## Funcionalidades

- Autenticação de usuários e controle de acesso por perfil.
- Cadastro e gerenciamento de empresas, funcionários e mesas.
- Página do cliente acessível por QR Code, adaptada para dispositivos móveis.
- Solicitações de serviço: **Chamar atendente** e **Pedir a conta**.
- Fila de chamados atualizada automaticamente a cada cinco segundos.
- Posição recalculada conforme chamados anteriores são atendidos, encerrados ou cancelados.
- Alerta visual na fila quando uma solicitação continua aguardando por mais de cinco minutos.
- Ações para assumir, encerrar e cancelar chamados.
- Relatórios de quantidade por empresa, responsável, serviço e situação, com exportação CSV.
- Três temas visuais: escuro, claro e personalizado. No tema personalizado, cada usuário pode opcionalmente escolher as cores de fundo, botões e textos. As alterações só são aplicadas após clicar em **Salvar cores**, e a combinação de textos é validada para manter o contraste. A preferência é individual e armazenada no perfil do usuário.

> O pedido de conta é registrado como um tipo de atendimento. O sistema não calcula valores nem processa pagamentos.

## Perfis e permissões

| Perfil | Permissões |
|---|---|
| Administrador | Gerencia empresas e usuários; acessa relatórios de todas as empresas ou filtra por empresa. |
| Gerente | Gerencia mesas da própria empresa, cadastra funcionários com perfil de atendente para essa mesma empresa e consulta relatórios restritos à própria empresa. |
| Atendente | Acompanha a fila e atende solicitações da empresa à qual está vinculado. |

O escopo por empresa é validado no servidor. A interface ocultar opções não substitui as verificações de permissão nas rotas.

## Tecnologias

### Aplicação

- **PHP 8.2 ou superior**
- **Laravel 12**
- **Livewire 4** para atualização periódica da fila.
- **Eloquent ORM** para persistência e relacionamentos.
- **SQLite** como configuração padrão; a configuração do Laravel também inclui conexões MySQL/MariaDB, PostgreSQL e SQL Server.
- **Simple QrCode** para gerar QR Codes em SVG.

### Interface e ferramentas

- **Blade** para as páginas e componentes.
- **Tailwind CSS 4** e **Vite 7** para estilos e build dos recursos frontend.
- **JavaScript** para interações da página do cliente e preferências visuais.
- **PHPUnit 11** e **Faker** para testes.
- **Laravel Pint** para formatação de código PHP.

## Dependências essenciais

As versões abaixo são as faixas definidas nos manifests do projeto; `composer.lock` e `package-lock.json` registram as versões resolvidas.

### Dependências PHP de execução

| Pacote | Versão declarada | Uso |
|---|---:|---|
| `php` | `^8.2` | Runtime da aplicação. |
| `laravel/framework` | `^12.0` | Framework, rotas, autenticação, ORM, migrations e views. |
| `livewire/livewire` | `^4.4` | Componente de fila com atualização automática. |
| `simplesoftwareio/simple-qrcode` | `^4.2` | Geração de QR Codes das mesas. |
| `laravel/tinker` | `^2.10.1` | Console interativo do Laravel. |

### Dependências frontend

| Pacote | Versão declarada | Uso |
|---|---:|---|
| `vite` | `^7.0.7` | Servidor de desenvolvimento e build frontend. |
| `laravel-vite-plugin` | `^2.0.0` | Integração entre Laravel e Vite. |
| `tailwindcss` | `^4.0.0` | Utilitários CSS. |
| `@tailwindcss/vite` | `^4.0.0` | Integração do Tailwind com Vite. |
| `axios` | `^1.11.0` | Cliente HTTP disponível no frontend. |

### Ferramentas de desenvolvimento e teste

- `phpunit/phpunit` `^11.5.50`
- `fakerphp/faker` `^1.23`
- `laravel/pint` `^1.24`
- `laravel/sail` `^1.41`
- `mockery/mockery` `^1.6`
- `nunomaduro/collision` `^8.6`
- `concurrently` `^9.0.1`

### Requisitos locais

- Composer 2.
- Node.js e npm em versões compatíveis com Vite 7.
- Um driver PDO para o banco escolhido (`pdo_sqlite` para SQLite ou `pdo_mysql` para MySQL/MariaDB).
- Extensões PHP requeridas pelo Laravel, incluindo `ctype`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo`, `tokenizer` e `xml`.

## Instalação e execução local

Execute os comandos na raiz do projeto.

1. Instale as dependências PHP:

   ```bash
   composer install
   ```

2. Crie o arquivo de ambiente e gere a chave da aplicação:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   No PowerShell, use `Copy-Item .env.example .env` no lugar de `cp`.

3. Configure o banco no `.env`.

   Por padrão, o projeto usa SQLite. Crie o arquivo local do banco antes de migrar:

   ```bash
   touch database/database.sqlite
   ```

   No PowerShell:

   ```powershell
   New-Item database/database.sqlite -ItemType File
   ```

   A configuração padrão de `DB_CONNECTION` é `sqlite`; o arquivo usado quando `DB_DATABASE` não está definido é `database/database.sqlite`. Para usar MySQL/MariaDB, configure `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no `.env` e crie o banco vazio no servidor.

4. Execute as migrations:

   ```bash
   php artisan migrate
   ```

5. (Opcional) Insira os dados de demonstração:

   ```bash
   php artisan db:seed
   ```

6. Instale as dependências frontend:

   ```bash
   npm install
   ```

7. Em terminais separados, inicie o servidor e o Vite:

   ```bash
   php artisan serve
   ```

   ```bash
   npm run dev
   ```

   Acesse `http://127.0.0.1:8000`.

Para gerar os arquivos frontend de produção:

```bash
npm run build
```

Também é possível usar `composer run dev`, que inicia vários processos de desenvolvimento em paralelo (servidor, Vite, worker de fila e logs).

## Docker no Windows

Na raiz de `FastCall`, execute:

```powershell
docker compose build --no-cache
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

A aplicação ficará disponível em `http://localhost:8000`. O Docker usa PHP-FPM 8.3, Nginx, MySQL 8 e Node.js 22. O banco já é configurado pelo `docker-compose.yml` com usuário `root` e senha vazia; não use `127.0.0.1` no `DB_HOST` dentro dos contêineres, pois o endereço do banco é `mysql`. `migrate:fresh` apaga todas as tabelas do banco selecionado antes de recriá-las.

## Banco de dados: migrations e seeders

As migrations da aplicação criam o esquema e as tabelas de suporte do Laravel. As principais tabelas do domínio são:

- `empresas`: empresas, identificador, tema e estado ativo.
- `users`: usuários, credenciais, perfil, empresa e preferência de tema. O nome da tabela e colunas convencionais de autenticação permanecem compatíveis com o Laravel.
- `mesas`: mesas, identificador, URL/token do QR Code e identificador opcional de etiqueta NFC.
- `chamados`: empresa, mesa, responsável, posição, tipo de serviço, situação e datas do atendimento.

O seeder principal está em `database/seeders/DatabaseSeeder.php`. Ele cria uma empresa de demonstração, um administrador global, um gerente, dois atendentes e dez mesas. O seeder não é idempotente: execute-o uma vez em um banco vazio; executá-lo novamente pode falhar devido a identificadores ou e-mails únicos.

Comandos úteis:

```bash
# Ver migrations executadas e pendentes
php artisan migrate:status

# Aplicar somente migrations ainda pendentes
php artisan migrate

# Executar os dados de demonstração
php artisan db:seed

# Executar um seeder específico
php artisan db:seed --class=DatabaseSeeder
```

### Atenção a comandos destrutivos

`migrate:fresh` exclui todas as tabelas antes de recriá-las; `migrate:refresh` reverte e executa novamente as migrations. Ambos podem apagar todos os dados do banco selecionado. Use comandos desse tipo somente em um banco local descartável, depois de conferir cuidadosamente a configuração de conexão. Não os execute em produção.

O projeto não executa migrations ou seeders automaticamente ao iniciar o servidor.

### Contas criadas pelo seeder

| Perfil | E-mail | Senha inicial |
|---|---|---|
| Administrador | `admin@fastcall.com` | `admin123` |
| Gerente | `gerente@fastcall.com` | `password` |
| Atendente | `atendente@fastcall.com` | `password` |
| Atendente | `carlos@fastcall.com` | `password` |

Essas são credenciais públicas de demonstração. Não as use em produção; altere ou remova os usuários de demonstração e configure credenciais seguras.

## Como utilizar

### Administrador

1. Entre pelo endereço `/login`.
2. Cadastre empresas e usuários nas seções **Empresas** e **Usuários**.
3. Consulte **Relatórios** para ver registros de todas as empresas ou filtrar por uma empresa específica.

### Gerente

1. Entre com uma conta vinculada à empresa.
2. Cadastre mesas na seção **Mesas** e abra o QR Code para imprimir ou testar o link.
3. Use **Adicionar funcionário** para criar atendentes vinculados à própria empresa.
4. Acompanhe a fila e consulte relatórios limitados à sua empresa.

### Atendente

1. Acesse **Fila** para acompanhar as solicitações abertas.
2. A fila atualiza automaticamente. Chamados aguardando há mais de cinco minutos geram um alerta visual.
3. Selecione **Atender** para assumir uma solicitação e **Fechar** ao concluir.

### Cliente

1. O cliente escaneia o QR Code da mesa.
2. Na página aberta, escolhe **Chamar Atendente** ou **Pedir a Conta**.
3. A página mostra a posição na fila e atualiza o estado do pedido.

Cada mesa tem no máximo um chamado ativo por vez; um novo tipo de serviço pode ser solicitado depois que o atual for encerrado ou cancelado. O campo de NFC guarda um identificador da etiqueta; para abrir a mesma página por NFC, programe a etiqueta com a URL da mesa.

### Relatórios

O acesso é permitido a administradores e gerentes. É possível filtrar por:

- Empresa (administradores; gerentes ficam restritos à sua empresa).
- Responsável que assumiu o atendimento.
- Tipo de serviço.

O relatório mostra contagens por empresa, responsável, serviço e situação e permite baixar CSV compatível com planilhas. Os registros são contabilizados por situação, incluindo concluídos e cancelados; o relatório atual não tem filtro por período.

## Testes e qualidade

Os testes de integração usam SQLite em memória conforme `phpunit.xml`; não usam o banco configurado no `.env`.

Execute a suíte completa:

```bash
php artisan test
```

Ou use o script do Composer:

```bash
composer test
```

Para executar um teste ou grupo específico:

```bash
php artisan test --filter=AttendanceReportTest
```

Verifique a formatação PHP:

```bash
./vendor/bin/pint --test
```

Formate os arquivos PHP:

```bash
./vendor/bin/pint
```

Compile os recursos frontend:

```bash
npm run build
```

Os testes cobrem autenticação, permissões, cadastro, relacionamentos, preferências de tema, solicitações de serviço, alerta de espera e relatórios/exportação.

## Estrutura do projeto

```text
app/
  Http/Controllers/       Controladores de autenticação, fila, relatórios e cadastros
  Http/Middleware/        Verificação de perfil e estado da conta
  Livewire/Queue/         Componente da fila atualizada periodicamente
  Models/                 Models de empresas, mesas, usuários e chamados
config/themes.php         Paletas de cores da aplicação
database/
  factories/              Factories para testes
  migrations/             Estrutura do banco de dados
  seeders/                Dados de demonstração
lang/pt_BR/               Traduções em Português Brasileiro
resources/
  css/                    Estilos e temas
  js/                     Interações do frontend
  views/                  Views Blade e componentes
routes/web.php            Rotas web
tests/                    Testes unitários e de funcionalidades
```

## Configurações importantes

- Idioma: `pt_BR`.
- Fuso horário padrão: `America/Sao_Paulo`.
- Banco padrão: SQLite.
- Arquivo de ambiente: `.env` (não deve ser versionado).
- Configuração de exemplo: `.env.example`.

Em produção, defina `APP_ENV=production`, `APP_DEBUG=false`, uma `APP_KEY` segura, credenciais de banco próprias e HTTPS. Nunca publique o `.env` ou use as contas de demonstração.
