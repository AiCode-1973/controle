# Controle de Projetos — Sistema de Gestão

**Versão:** 1.0  
**Tecnologia:** PHP 8.0+ · MySQL · Bootstrap 5.3 · Font Awesome 6  
**URL:** https://controle.aicode.dev.br  
**Desenvolvido por:** [AiCode](https://aicode.dev.br)

---

## Índice

1. [Visão Geral](#visão-geral)
2. [Módulos do Sistema](#módulos-do-sistema)
3. [Estrutura de Arquivos](#estrutura-de-arquivos)
4. [Banco de Dados](#banco-de-dados)
5. [Segurança e Proteções](#segurança-e-proteções)
6. [Configuração e Instalação](#configuração-e-instalação)

---

## Visão Geral

Sistema web interno para gestão completa de projetos de sistemas, clientes, finanças, cursos, ferramentas de IA e links pessoais. Acesso exclusivo por autenticação com logout automático por inatividade.

---

## Módulos do Sistema

### 1. Dashboard (`index.php`)
Painel principal com resumo geral do sistema.

| Informação exibida | Detalhe |
|---|---|
| Projetos em andamento | Contador com link para listagem filtrada |
| Projetos concluídos | Contador |
| Tarefas pendentes | Total de tarefas não concluídas |
| Financeiro recebido | Total pago vs total contratado com barra de progresso |
| Tarefas com prazo vencido | Alerta em vermelho com link para cada projeto |
| Projetos recentes | Últimos 6 projetos cadastrados |

**Notificação:** Ícone de sino 🔔 na topbar com contagem de tarefas vencidas.

---

### 2. Clientes (`clientes/`)

| Arquivo | Função |
|---|---|
| `index.php` | Lista todos os clientes com total de projetos vinculados |
| `form.php` | Cadastro e edição (nome, empresa, telefone, e-mail, observação) |
| `excluir.php` | Exclusão com confirmação (POST + CSRF) |

---

### 3. Projetos (`projetos/`)

Módulo central do sistema. Cada projeto agrupa tarefas, arquivos, horas, anotações e credenciais.

| Arquivo | Função |
|---|---|
| `index.php` | Lista com filtro por status (Em andamento / Concluído / Pausado / Cancelado) |
| `form.php` | Cadastro e edição completa |
| `ver.php` | Tela detalhada do projeto |
| `excluir.php` | Remove projeto e todos os arquivos físicos associados |
| `relatorio.php` | Relatório completo para impressão/PDF (sem autenticação visual) |

**Campos do projeto:**
- Nome, cliente, descrição, status, URL do projeto
- Valores: total, pago, pendente
- Datas: início, previsão de entrega, conclusão
- **Conexão MySQL:** host, banco, usuário, senha (com blur + copiar)
- **Acesso ao sistema:** login, senha (com blur + copiar)

**Status disponíveis:** Em andamento · Concluído · Pausado · Cancelado

---

### 4. Tarefas (`tarefas/`)

Vinculadas a projetos. Gerenciadas na tela `projetos/ver.php`.

| Arquivo | Função |
|---|---|
| `form.php` | Adiciona nova tarefa ao projeto |
| `editar.php` | Edita tarefa via modal (título, status, prioridade, prazo, descrição) |
| `excluir.php` | Remove tarefa |
| `status.php` | Alterna status rápido (pendente ↔ concluída) com registro de data/hora |

**Campos:** Título · Descrição · Status · Prioridade (Baixa/Média/Alta) · Data de prazo · Data de conclusão

---

### 5. Horas Trabalhadas (`horas/`)

Registro de tempo por projeto. Exibido em `projetos/ver.php`.

| Arquivo | Função |
|---|---|
| `form.php` | Registra horas (descrição, data, quantidade em horas) |
| `excluir.php` | Remove registro de horas |

**Total de horas** calculado automaticamente. Incluído no relatório PDF.

---

### 6. Arquivos (`arquivos/`)

Upload de arquivos por projeto com armazenamento seguro.

| Arquivo | Função |
|---|---|
| `upload.php` | Recebe e valida o upload |
| `excluir.php` | Remove arquivo do banco e do disco |
| `download.php` | Serve o arquivo com validação de path traversal |
| `uploads/.htaccess` | Desabilita execução de PHP na pasta de uploads |

**Extensões permitidas:** pdf, doc, docx, xls, xlsx, ppt, pptx, png, jpg, jpeg, gif, webp, zip, rar, txt, csv, md  
**Tamanho máximo:** 10 MB  
**Nomes:** Renomeados para hash aleatório (previne sobrescrita e execução)

---

### 7. Anotações (`anotacoes/`)

Notas de texto por projeto. Exibidas em `projetos/ver.php`.

| Arquivo | Função |
|---|---|
| `form.php` | Adiciona anotação |
| `excluir.php` | Remove anotação |

---

### 8. Financeiro (`financeiro/index.php`)

Relatório financeiro consolidado de todos os projetos.

| Coluna | Detalhe |
|---|---|
| Total contratado | Soma de todos os valores |
| Recebido | Soma dos valores pagos |
| Pendente | Diferença por projeto |
| % Pago | Barra de progresso visual por projeto |

---

### 9. Prompts (`prompts/`)

Biblioteca pessoal de prompts de IA.

| Arquivo | Função |
|---|---|
| `index.php` | Grade de cards com busca, filtro por categoria e favoritos |
| `form.php` | Cadastro/edição com contador de caracteres em tempo real |
| `excluir.php` | Remove prompt |
| `favorito.php` | Alterna favorito (estrela) |

**Funcionalidades:** Preview do conteúdo · Modal para ver texto completo · Botão copiar com feedback visual · Contador de caracteres

---

### 10. Minhas IAs (`ias/`)

Catálogo das ferramentas de IA utilizadas.

| Arquivo | Função |
|---|---|
| `index.php` | Cards com identidade visual por marca, filtro por categoria |
| `form.php` | Cadastro com botões visuais de marca e categoria |
| `excluir.php` | Remove IA |
| `favorito.php` | Alterna favorito |

**Marcas suportadas:** ChatGPT · Claude · Gemini · Copilot · Perplexity · Midjourney · DALL·E · Grok · DeepSeek · Llama · Runway · ElevenLabs · Cursor  
**Categorias:** Chat · Imagem · Código · Vídeo · Áudio · Pesquisa  
**Planos:** Gratuito · Freemium · Pago · Créditos  
**Credenciais:** Login e senha armazenados com blur e botão copiar

---

### 11. Meus Links (`links/`)

Gerenciador de links pessoais e redes sociais.

| Arquivo | Função |
|---|---|
| `index.php` | Cards com ícone e cor da rede, filtros por tipo e pasta |
| `form.php` | Cadastro com seleção de tipo por botões visuais |
| `excluir.php` | Remove link |
| `favorito.php` | Alterna favorito |

**Tipos:** Web · Instagram · LinkedIn · YouTube · GitHub · WhatsApp · Facebook · X/Twitter · Outro  
**Recursos:** Botão abrir (nova aba) · Copiar URL · Pastas/categorias com autocomplete

---

### 12. Meus Cursos (`cursos/`)

Controle de cursos online com progresso e avaliação.

| Arquivo | Função |
|---|---|
| `index.php` | Cards com resumo estatístico e filtro por status/área |
| `form.php` | Cadastro completo com slider de progresso e estrelas interativas |
| `excluir.php` | Remove curso |
| `favorito.php` | Alterna favorito |

**Plataformas:** Udemy · Coursera · YouTube · Alura · Rocketseat · DIO · Hotmart · Kiwify · Eduzz · Origamid  
**Status:** Não iniciado · Em andamento · Concluído · Pausado  
**Campos:** Progresso (0–100%) · Avaliação 1–5 estrelas · Preço pago · Certificado · Datas · Observações  
**Resumo no topo:** Contadores por status + Total investido

---

### 13. Usuários (`usuarios/`)

Gerenciamento de contas de acesso ao sistema.

| Arquivo | Função |
|---|---|
| `index.php` | Lista usuários com indicação do usuário logado |
| `form.php` | Cria/edita usuário (nome, e-mail, senha com hash bcrypt) |
| `excluir.php` | Remove usuário (não permite auto-exclusão) |

---

### 14. Meu Perfil (`perfil.php`)

Edição dos dados do próprio usuário logado.

- Alterar nome e e-mail
- Trocar senha (exige senha atual para confirmar)
- Atualiza nome na sessão imediatamente

---

## Estrutura de Arquivos

```
controle/
├── .htaccess                  ← Segurança Apache
├── robots.txt                 ← Bloqueia indexação
├── banco.sql                  ← Schema completo do banco
├── config.php                 ← Configurações, headers, timeout
├── conexao.php                ← Conexão PDO MySQL remota
├── index.php                  ← Dashboard
├── login.php / logout.php
├── perfil.php
├── includes/
│   ├── auth.php               ← Verificação de sessão + timeout
│   ├── funcoes.php            ← Helpers: sanitize, redirect, CSRF, flash
│   ├── header.php             ← HTML head, topbar, watermark
│   ├── sidebar.php            ← Navegação lateral
│   └── footer.php             ← Scripts JS
├── clientes/
├── projetos/
├── tarefas/
├── horas/
├── arquivos/
│   └── uploads/
│       └── .htaccess          ← Desabilita PHP na pasta de uploads
├── anotacoes/
├── financeiro/
├── prompts/
├── ias/
├── links/
├── cursos/
├── usuarios/
└── assets/
    ├── css/style.css          ← Design system completo
    └── js/app.js              ← Sidebar toggle + proteções
```

---

## Banco de Dados

**Host:** 186.209.113.107  
**Banco:** dema5738_controle

| Tabela | Descrição |
|---|---|
| `usuarios` | Contas de acesso (senha em bcrypt) |
| `clientes` | Cadastro de clientes |
| `projetos` | Projetos com credenciais MySQL e de sistema |
| `tarefas` | Tarefas por projeto |
| `horas` | Registro de horas por projeto |
| `arquivos` | Metadados dos arquivos enviados |
| `anotacoes` | Notas por projeto |
| `prompts` | Biblioteca de prompts de IA |
| `ias` | Ferramentas de IA com credenciais |
| `links` | Links e redes sociais |
| `cursos` | Cursos online com progresso |

---

## Segurança e Proteções

### Autenticação
| Proteção | Implementação |
|---|---|
| Login com senha | `password_hash()` / `password_verify()` com bcrypt |
| CSRF em todos os formulários | Token gerado com `random_bytes(32)` e `hash_equals()` |
| Session ID regenerado no login | `session_regenerate_id(true)` |
| Timeout de sessão | Logout automático após **2 horas** de inatividade |
| Redirecionamento forçado | `includes/auth.php` em todas as páginas autenticadas |

### Headers HTTP
| Header | Valor |
|---|---|
| `X-Frame-Options` | `DENY` — impede embedding em iframes |
| `X-Content-Type-Options` | `nosniff` |
| `X-XSS-Protection` | `1; mode=block` |
| `Referrer-Policy` | `no-referrer` |
| `Cache-Control` | `no-store, no-cache, must-revalidate, private` |

### Proteção de Conteúdo
| Proteção | Detalhe |
|---|---|
| `robots.txt` | `Disallow: /` — bloqueia todos os robôs de busca |
| Meta noindex | `<meta name="robots" content="noindex, nofollow">` |
| Clique direito | Bloqueado via JavaScript (`contextmenu`) |
| DevTools | F12, Ctrl+Shift+I/J/C bloqueados |
| Ver código-fonte | Ctrl+U bloqueado |
| Salvar página | Ctrl+S bloqueado |
| Seleção de texto | `user-select: none` no layout (formulários liberados) |
| Arrastar conteúdo | Evento `dragstart` bloqueado |
| **Watermark** | Nome do usuário logado em diagonal translúcida em todas as páginas |

### Upload de Arquivos
| Proteção | Detalhe |
|---|---|
| Whitelist de extensões | Apenas tipos seguros permitidos (sem `.php`, `.exe`, etc.) |
| Renomeação de arquivos | Nome aleatório com `bin2hex(random_bytes(16))` |
| PHP desabilitado | `.htaccess` em `arquivos/uploads/` desabilita execução |
| Path traversal | `realpath()` + comparação de prefixo em `download.php` |
| Tamanho máximo | 10 MB |

### Banco de Dados
| Proteção | Detalhe |
|---|---|
| Prepared statements | 100% das queries usam PDO com `?` — sem SQL injection |
| PDO exceptions | `ERRMODE_EXCEPTION` + `EMULATE_PREPARES = false` |
| `.htaccess` raiz | Bloqueia acesso direto a `.sql`, `.env`, `.log`, `.ini` |

---

## Configuração e Instalação

### Requisitos
- PHP 8.0 ou superior
- MySQL 5.7+ / MariaDB 10.3+
- Apache com `mod_rewrite` e `AllowOverride All`

### Passos
1. Copiar os arquivos para o servidor
2. Configurar o VirtualHost Apache apontando para a pasta `controle/`
3. Acessar `instalar.php` — cria as tabelas e o usuário administrador padrão
4. **Deletar `instalar.php` imediatamente após a instalação**
5. Fazer login com as credenciais geradas

### Credenciais padrão (pós-instalação)
```
E-mail: admin@controle.com
Senha:  Admin@1973
```
> Altere a senha imediatamente após o primeiro acesso em **Meu Perfil**.

### Conexão com o banco (conexao.php)
```php
$host = '186.209.113.107';
$db   = 'dema5738_controle';
$user = 'dema5738_controle';
$pass = 'Dema@1973';
```

---

*Sistema desenvolvido com ❤️ por [AiCode](https://aicode.dev.br) — 2025*
