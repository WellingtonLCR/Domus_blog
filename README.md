# Mini Blog API

Uma API RESTful para um mini blog desenvolvida com Laravel 12, utilizando autenticação via Laravel Sanctum e frontend em HTML/Bootstrap.

Este README está organizado em formato de **checklist de requisitos**, indicando **onde cada funcionalidade foi implementada** para facilitar a avaliação do código.

---

## Tecnologias Utilizadas

- **Laravel 12** – Framework PHP
- **Laravel Sanctum** – Autenticação de API (`config/sanctum.php`)
- **Banco de dados** – SQLite por padrão (pode ser alterado para outro no `.env`)
- **Composer** – Gerenciamento de dependências (`composer.json`)
- **Bootstrap 5 + JS vanilla** – Frontend (`public/index.html`, `public/js/app.js`)

---

## Arquitetura e Padrões do Projeto

- **Migrations** – Definição das tabelas:
  - `database/migrations/*_create_admins_table.php`
  - `database/migrations/*_create_usuarios_table.php`
  - `database/migrations/*_create_posts_table.php`
  - `database/migrations/*_create_comentarios_table.php`

- **Models** – Representação das entidades:
  - `App\Models\Admin`
  - `App\Models\Usuario`
  - `App\Models\Post`
  - `App\Models\Comentario`

- **Controllers + Services (regras de negócio)**
  - Admins: `App\Http\Controllers\AdminController`, `App\Services\AdminService`
  - Usuários: `App\Http\Controllers\UsuarioController`, `App\Services\UsuarioService`
  - Posts: `App\Http\Controllers\PostController`, `App\Services\PostService`
  - Comentários: `App\Http\Controllers\ComentarioController`, `App\Services\ComentarioService`

- **Form Requests (validação de dados)**
  - Admin: `App\Http\Requests\StoreAdminRequest`, `UpdateAdminRequest`
  - Usuário: `App\Http\Requests\StoreUsuarioRequest`, `UpdateUsuarioRequest`
  - Post: `App\Http\Requests\StorePostRequest`, `UpdatePostRequest`
  - Comentário: `App\Http\Requests\StoreComentarioRequest`

- **DTOs (Data Transfer Objects)**
  - `App\DTOs\AdminDTO`
  - `App\DTOs\UsuarioDTO`
  - `App\DTOs\PostDTO`
  - `App\DTOs\ComentarioDTO`

- **Resources (apresentação dos dados)**
  - `App\Http\Resources\AdminResource`
  - `App\Http\Resources\UsuarioResource`
  - `App\Http\Resources\PostResource`
  - `App\Http\Resources\ComentarioResource`

- **Autenticação com Sanctum**
  - Configuração: `config/sanctum.php`
  - Serviço de autenticação: `App\Services\AuthService`
  - Controller: `App\Http\Controllers\AuthController`
  - Rotas: `routes/api.php` (`/api/auth/...`)

- **Middlewares + Gates (autorização)**
  - Middlewares:
    - `App\Http\Middleware\CheckBannedUser` (apelido `auth.banned` em `app/Http/Kernel.php`)
  - Gates:
    - Definidos em `App\Providers\AppServiceProvider::registerGates()`
    - Ver seção **Regras de Negócio** abaixo para o mapeamento regra → gate.

---

## Tabelas e Regras de Negócio

### Tabela "admins"

- **Campos**: `id`, `nome`, `usuario` (único), `senha`  
  - Migration: `database/migrations/*_create_admins_table.php`

#### Regras

- **O primeiro admin deve ser criado automaticamente por seeder**
  - Seeder: `database/seeders/AdminSeeder.php`
  - Registrado em: `Database\Seeders\DatabaseSeeder`

- **Somente um admin pode criar outros admins**
  - Gate: `create-admin` em `App\Providers\AppServiceProvider`
  - Aplicado em: `AdminController@store`
  - Rotas: `routes/api.php` → grupo `/api/admins` protegido por `auth:sanctum` + `auth.banned`

---

### Tabela "usuarios"

- **Campos**: `id`, `nome`, `usuario` (único), `senha`, `data_criacao`, `data_alteracao`, `biografia`, `banido_em`  
  - Migration: `database/migrations/*_create_usuarios_table.php`

#### Regras

- **Qualquer pessoa pode criar um usuário**
  - Rota pública: `POST /api/usuarios` em `routes/api.php`
  - Controller: `UsuarioController@store` (sem middleware de autenticação)

- **Somente um admin pode banir usuários**
  - Gates: `ban-usuario`, `unban-usuario` em `AppServiceProvider`
  - Controller: `UsuarioController@ban`, `UsuarioController@unban`
  - Rotas protegidas: `POST /api/usuarios/{usuario}/ban`, `POST /api/usuarios/{usuario}/unban` (grupo `auth:sanctum`, `auth.banned`)

- **Um usuário banido não pode fazer login ou qualquer outra ação**
  - Login:
    - Lógica em `App\Services\AuthService` e `AuthController` (verificação de `banido_em` antes de gerar token)
  - Acesso a endpoints autenticados:
    - Middleware `CheckBannedUser` aplicado ao grupo de rotas autenticadas em `routes/api.php` (`auth.banned`)

---

### Tabela "posts"

- **Campos**: `id`, `usuario_id`, `titulo` (único), `slug` (único), `data_criacao`, `data_alteracao`, `status` (`rascunho` / `publicado` / `arquivado`), `data_publicacao`, `texto`  
  - Migration: `database/migrations/*_create_posts_table.php`
  - Model: `App\Models\Post` (scopes de filtro e helpers `isPublished`, etc.)

#### Regras

- **Apenas um usuário pode criar um post**
  - Gate: `create-post` em `AppServiceProvider`
  - Controller: `PostController@store` (usa `$request->user()` e associa `usuario_id`)
  - Serviço: `PostService::create`
  - Rotas protegidas: `POST /api/posts` (grupo autenticado)

- **Um usuário pode ter múltiplos posts**
  - Relação: `Usuario` → `hasMany(Post)` (em `App\Models\Usuario`)
  - Sem restrição de unicidade em `usuario_id` na migration de posts.

- **Um usuário pode editar apenas seus próprios posts**
  - Gate: `update-post` em `AppServiceProvider` (valida `usuario_id` e banimento)
  - Controller: `PostController@update`
  - Serviço: `PostService::update`

- **Um admin pode excluir qualquer post**
  - Gate: `delete-post` em `AppServiceProvider` (admin sempre permitido; usuário apenas se autor)
  - Controller: `PostController@destroy`

- **Apenas um admin pode ver posts de usuários banidos**
  - Gate: `view-post` em `AppServiceProvider`
  - Para requests autenticadas: `PostController@show` chama `Gate::authorize('view-post', $post)`

- **Qualquer pessoa pode ver posts publicados de usuários não banidos**
  - Rota pública: `GET /api/posts/published` → `PostController@published` → `PostService::getPublished` (filtra autores não banidos)
  - Rota pública: `GET /api/posts/{post}` → `PostController@show`
    - Se não houver usuário autenticado, permite apenas posts `publicado` de autores não banidos.

#### Filtros opcionais de listagem de posts

Implementados em `PostController@index` + `PostService::getAll`:

- **status**  
  - Filtro `status` → scope `Post::byStatus()`

- **posts com comentário**  
  - Filtro `with_comments=true` → scope `Post::withComments()`

- **posts de um usuário específico**  
  - Filtro `usuario_id` → scope `Post::byUser()`

- **posts que têm comentário de um usuário específico**  
  - Filtro `comment_usuario_id` → scope `Post::withCommentsByUser()`

- **posts criados em um determinado intervalo de datas**  
  - Filtros `start_date` e `end_date` → scope `Post::byDateRange()`

- **posts de usuários banidos**
  - Controle via filtro `show_banned` (padrão `false`, exclui posts de autores banidos)

---

### Tabela "comentarios"

- **Campos**: `id`, `post_id`, `usuario_id`, `data_criacao`, `texto`  
  - Migration: `database/migrations/*_create_comentarios_table.php`
  - Model: `App\Models\Comentario`

#### Regras

- **Apenas um usuário pode criar um comentário**
  - Gate: `create-comentario` em `AppServiceProvider` (usuário autenticado, não banido, em post publicado de autor não banido)
  - Controller: `ComentarioController@store`
  - Serviço: `ComentarioService::create`

- **Um post pode ter múltiplos comentários**
  - Relação: `Post::comentarios()` (`hasMany`)

- **Um usuário pode excluir apenas seus próprios comentários**
  - Gate: `delete-comentario` em `AppServiceProvider` (usuário autor e não banido)
  - Controller: `ComentarioController@destroy`

- **Um admin pode excluir qualquer comentário**
  - Mesmo gate `delete-comentario` (admin sempre permitido)

- **Apenas um admin pode ver comentários de usuários banidos**
  - Gate: `view-comentario` em `AppServiceProvider`
  - Controller: `ComentarioController@show`

- **Qualquer pessoa pode ver comentários de usuários não banidos**
  - Rota pública: `GET /api/comentarios/post/{post}`
  - Serviço: `ComentarioService::getByPost` (filtra apenas comentários de usuários com `banido_em = null`)

---

## Instalação

1. Clone o repositório
2. Instale as dependências:
   ```bash
   composer install
   ```

3. Configure o arquivo `.env` (opcional, usa SQLite por padrão)

4. Execute as migrations e seeders:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. Inicie o servidor de desenvolvimento (exemplos):
   - Via Laravel:  
     ```bash
     php artisan serve
     ```
   - Via Apache/XAMPP (recomendado em Windows): apontar o DocumentRoot para `public/` ou acessar via `http://localhost/desafio_domus/public/`.

## Endpoints da API

### Autenticação

#### Login Admin
```
POST /api/auth/admin/login
Content-Type: application/json

{
    "usuario": "admin",
    "senha": "admin123"
}
```

#### Login Usuário
```
POST /api/auth/usuario/login
Content-Type: application/json

{
    "usuario": "joao",
    "senha": "senha123"
}
```

#### Logout
```
POST /api/auth/logout
Authorization: Bearer {token}
```

#### Meus Dados
```
GET /api/auth/me
Authorization: Bearer {token}
```

### Admins (Apenas Admins)

#### Listar Admins
```
GET /api/admins
Authorization: Bearer {token}
```

#### Criar Admin
```
POST /api/admins
Authorization: Bearer {token}
Content-Type: application/json

{
    "nome": "Novo Admin",
    "usuario": "novo_admin",
    "senha": "senha123"
}
```

### Usuários

#### Listar Usuários
```
GET /api/usuarios
Authorization: Bearer {token}
```

#### Criar Usuário (Público)
```
POST /api/usuarios
Content-Type: application/json

{
    "nome": "Novo Usuário",
    "usuario": "novo_usuario",
    "senha": "senha123",
    "biografia": "Biografia do usuário"
}
```

#### Banir Usuário (Apenas Admin)
```
POST /api/usuarios/{id}/ban
Authorization: Bearer {token}
```

### Posts

#### Listar Posts Públicos
```
GET /api/posts/published
```

#### Listar Posts (Com Filtros)
```
GET /api/posts?status=publicado&with_comments=true&usuario_id=1
```

#### Criar Post
```
POST /api/posts
Authorization: Bearer {token}
Content-Type: application/json

{
    "titulo": "Meu Post",
    "status": "rascunho",
    "texto": "Conteúdo do post..."
}
```

#### Publicar Post
```
POST /api/posts/{id}/publish
Authorization: Bearer {token}
```

### Comentários

#### Listar Comentários de um Post
```
GET /api/comentarios/post/{post_id}
```

#### Criar Comentário
```
POST /api/comentarios
Authorization: Bearer {token}
Content-Type: application/json

{
    "post_id": 1,
    "texto": "Ótimo post!"
}
```

## Credenciais Padrão

### Admin
- **Usuário:** admin
- **Senha:** admin123

### Usuários de Exemplo
- **joao** - senha123
- **maria** - senha123
- **pedro** - senha123
- **ana** - senha123
- **carlos** - senha123

