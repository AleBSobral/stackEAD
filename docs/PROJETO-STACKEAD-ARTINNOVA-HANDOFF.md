# Documentação Geral, Arquitetura, Histórico e Guia de Continuidade (Handoff para Equipes e Agentes de IA)

> **Projeto:** Residência Internacional ArtInnova (Brasil · França) & Plataforma **StackEAD Core**  
> **Domínio em Produção:** `https://artinnovaresidency.com`  
> **Repositório Oficial (GitHub):** `https://github.com/AleBSobral/stackEAD`  
> **Versão do Banco (`DB_VERSION`):** `1.0.2`  
> **Versão do Tema (`artinnova-light`):** `2.0.3`  
> **Stack do Servidor:** PHP `8.3.36` · WordPress `7.1.4+` · LiteSpeed Web Server + LiteSpeed Cache `7.9.3+` · MySQL/MariaDB

---

## Sumário Executivo

Este documento registra toda a memória técnica, arquitetural e operacional do projeto **ArtInnova / StackEAD Core**, desde sua concepção até o estado atual em produção. Ele foi estruturado especificamente para que **qualquer equipe de engenharia humana** ou **qualquer Agente de IA operando em um harness** (Antigravity, Claude Code, Cursor, Codex, etc.) consiga compreender o ecossistema em minutos, evitar regressões em problemas já resolvidos, realizar deploys seguros e dar continuidade aos pontos em aberto.

---

## 1. Visão Geral do Produto e Contexto Institucional

A **Residência Internacional ArtInnova** é um programa híbrido e bilateral de formação, criação e inovação em Artes Midiáticas que conecta duas cidades criativas da UNESCO:
- **Campina Grande (PB, Brasil)**
- **Enghien-les-Bains (Região de Paris, França / União Europeia)**

Para sustentar tanto a captação internacional de candidatos quanto toda a jornada acadêmica, financeira, gamificada e acessível da residência sem depender da interface legada do `wp-admin`, construímos duas camadas integradas:
1. **Portal Institucional & Landing Page Multilíngue (`artinnova-light`)**:
   - Opera na raiz do domínio (`https://artinnovaresidency.com/`).
   - Suporte instantâneo a **4 idiomas (`PT`, `EN`, `ES`, `FR`)** e adaptação automática de moedas (`BRL`, `USD`, `EUR`).
   - Captura de manifestações de interesse integrada via AJAX ao CRM interno (`wp_stackead_leads` e CPT `artinnova_lead`), com disparo transacional autenticado via SMTP (notificação para a coordenação em `contact@artinnovaresidency.com` + resposta automática para o candidato no idioma selecionado).
   - Conformidade integral com **LGPD (Brasil)** e **GDPR / CNIL (União Europeia / França)**.
2. **Plataforma EAD Standalone & White Label (`stackead-core`)**:
   - Aplicação SPA (Single Page Application) desacoplada do `wp-admin`, acessível em `https://artinnovaresidency.com/plataforma` e autenticada via `https://artinnovaresidency.com/login` (ou `/plataforma-login`).
   - Validação pública de certificados com QR Code/código único em `https://artinnovaresidency.com/certificados/{codigo}`.
   - Suporte nativo a **Múltiplos Papéis Cumulativos e Contextuais**, **Acessibilidade Cognitiva W3C COGA**, **Gamificação (`ArtCoins`, XP, Emblemas)**, **Temas Dark e Light** e **Central de Privacidade LGPD/GDPR do Titular**.

---

## 2. Arquitetura Técnica e Mapa de Arquivos

O código-fonte versionado no GitHub vive em `wp-content/plugins/stackead-core/` (e inclui dentro de si o código-fonte canônico do tema `templates/landing/artinnova-light/`, que no servidor de produção é espelhado para `wp-content/themes/artinnova-light/`).

```text
wp-content/plugins/stackead-core/
├── assets/
│   ├── css/
│   │   ├── stackead-app.css          # Design System Dark/Light, Global Inputs, Full-Page Views
│   │   └── coga-accessibility.css    # W3C COGA (Régua de leitura, OpenDyslexic/Lexend, Alto Contraste, Modo Foco)
│   ├── images/
│   │   ├── artinnova-logo-white.png  # Logo branca para fundos escuros (Login e Sidebar)
│   │   ├── artinnova-logo-black.png  # Logo escura para fundos claros
│   │   └── artinnova-logo-color.png  # Ícone colorido / Favicon
│   └── js/
│       ├── stackead-app.js           # Aplicação SPA completa (~8.150 linhas: Router, Views, CRUDs, Perfil LGPD/GDPR)
│       ├── stackead-i18n.js          # Motor de tradução e conversão multi-moeda (PT/EN/ES/FR e BRL/USD/EUR)
│       └── coga-dock.js              # Controlador interativo de acessibilidade cognitiva W3C COGA
├── docs/
│   └── PROJETO-STACKEAD-ARTINNOVA-HANDOFF.md  # Este documento mestre de arquitetura e continuidade
├── includes/
│   ├── Admin/
│   │   ├── AdminMetaBoxes.php        # Metaboxes administrativas de apoio no WP
│   │   └── WhiteLabelSettings.php    # Identidade institucional, tokens CSS dinâmicos e logos
│   ├── App/
│   │   ├── AppTemplate.php           # Shell HTML da SPA (/plataforma), Sidebar Sanfonada e Topbar
│   │   └── CertificateVerifyTemplate.php # Página pública de verificação de certificados (/certificados/{code})
│   ├── Auth/
│   │   ├── AuthManager.php           # Redirecionamentos e proteção de rotas autenticadas
│   │   └── LoginTemplate.php         # Tela de Login White Label unificada + Modal LGPD/GDPR
│   ├── Core/
│   │   ├── Autoloader.php            # Autoloader PSR-4 para o namespace StackEAD\*
│   │   ├── Database.php              # Schema e migrações dbDelta (13 tabelas customizadas, DB_VERSION 1.0.2)
│   │   └── RewriteRules.php          # Rotas (/plataforma, /login, /certificados) + headers anti-cache LiteSpeed
│   ├── Landing/
│   │   ├── BlockManager.php          # Gerenciador de blocos modulares de Landing Page por curso
│   │   └── LandingTemplateEngine.php # Motor de renderização de Landing Pages de cursos individuais
│   ├── PostTypes/
│   │   └── PostTypeRegistrar.php     # CPTs: stackead_course, stackead_cohort, stackead_module, stackead_lesson, etc.
│   ├── RestApi/
│   │   └── ApiRouter.php             # API REST completa (/wp-json/stackead/v1/*) com Rate Limit e Hardening
│   ├── Roles/
│   │   ├── RoleManager.php           # Gestão cumulativa de papéis globais (add_role/remove_role sem sobrescrever)
│   │   ├── AccessControl.php         # Menu dinâmico segregado por papéis globais + contextuais
│   │   └── ScopeFilter.php           # Isolamento de escopo de dados (turmas/cursos que cada papel pode ver/editar)
│   ├── Services/
│   │   ├── AccommodationService.php  # Adaptações neurodivergentes / W3C COGA por estudante
│   │   ├── AttendanceService.php     # Diário de presença em sessões síncronas
│   │   ├── CertificateService.php    # Emissão e validação criptográfica de certificados
│   │   ├── CommunityService.php      # Fóruns de discussão e dúvidas por aula/turma
│   │   ├── EnrollmentService.php     # Matrículas em turmas e trilhas
│   │   ├── FinanceService.php        # Pedidos, gateways e liberação financeira
│   │   ├── GamificationService.php   # Motor de XP, níveis, ArtCoins e insígnias
│   │   ├── GradingService.php        # Submissão e avaliação de projetos/entregas com rubricas
│   │   ├── ImageOptimizer.php        # Pipeline seguro de recorte e conversão automática para WebP
│   │   ├── ProgressService.php       # Rastreamento de progresso de aulas e tempo assistido
│   │   └── QuizService.php           # Quizzes anti-ansiedade com pausa respiratória e correção server-side
│   └── Sync/
│       └── CiiamBridge.php           # Ponte de integração institucional CIIAM
├── templates/
│   └── landing/
│       └── artinnova-light/          # Código-fonte canônico do tema da Landing Page (espelhado em wp-content/themes/artinnova-light)
│           ├── assets/css/main.css   # Estilos da Landing Page + Banner de Cookies CNIL/GDPR + Modal Legal
│           ├── assets/js/app.js      # Motor i18n, partículas, GSAP, AJAX de Leads e Consentimento v2
│           ├── assets/js/translations-data.js # Bundle compilado das 4 línguas (gerado a partir de locales/*.json)
│           ├── locales/{pt,en,es,fr}.json     # Arquivos JSON fonte das traduções nos 4 idiomas
│           ├── front-page.php        # Estrutura HTML completa da Landing Page
│           ├── header.php            # Cabeçalho com seletor de idiomas (PT/EN/ES/FR) e link para a plataforma
│           ├── footer.php            # Rodapé institucional + Banner de Cookies + Modal LGPD/GDPR
│           └── functions.php         # Hardening HTTP, CPT artinnova_lead, AJAX Handler, Sync CRM e SMTP
├── README.md
├── stackead-core.php                 # Bootstrap do plugin
└── uninstall.php
```

### 2.1. As 13 Tabelas Customizadas do Banco de Dados (`Database.php` — `DB_VERSION = 1.0.2`)
1. `wp_stackead_enrollments`: Matrículas formais de alunos em turmas (`cohort_id`) e trilhas (`path_id`).
2. `wp_stackead_progress`: Progresso percentual e segundos assistidos por aula (`user_id`, `lesson_id`, `cohort_id`).
3. `wp_stackead_attendances`: Presença e minutos em sessões síncronas (`session_id`, `user_id`, `cohort_id`).
4. `wp_stackead_submissions`: Entregas de atividades/projetos integradores, notas, feedbacks e rubricas JSON.
5. `wp_stackead_quiz_attempts`: Tentativas de quiz, pontuação, rascunhos e estado de pausa anti-ansiedade (`is_paused`).
6. `wp_stackead_accommodations`: Solicitações e aprovações de acomodações cognitivas/acessibilidade W3C COGA.
7. `wp_stackead_gamification_points`: Razão contábil (ledger) de XP e moedas virtuais (`ArtCoins`).
8. `wp_stackead_gamification_badges`: Emblemas conquistados pelos estudantes.
9. `wp_stackead_discussions`: Tópicos, respostas e upvotes nos fóruns de turma e aulas.
10. `wp_stackead_orders`: Pedidos financeiros (`BRL`, `USD`, `EUR`), método de pagamento e status.
11. `wp_stackead_leads`: CRM de manifestações de interesse da Landing Page, com colunas de auditoria **`consent_given`**, **`consent_version`** e **`lang`**.
12. `wp_stackead_quiz_questions`: Banco de questões dos quizzes.
13. `wp_stackead_cohort_members`: **Vínculos Contextuais por Turma** (`cohort_id`, `user_id`, `role` = `student` | `master` | `coordinator`), permitindo que uma mesma pessoa seja Professor na Turma A e Aluno na Turma B.

---

## 3. Histórico do Projeto: Problemas Encontrados, Erros, Soluções e Acertos

Esta seção documenta os problemas reais enfrentados durante o desenvolvimento e operação online, como foram diagnosticados e a solução definitiva aplicada. **Não reverta estas decisões.**

### 3.1. Formulário de Contato Abrindo Cliente de E-mail (`mailto:`) e E-mails Caindo no Spam
- **O Problema Inicial:** Na primeira versão online da Landing Page, ao enviar o formulário de interesse, o JavaScript exibia uma mensagem pedindo para abrir o cliente de e-mail local (`mailto:`) caso o envio falhasse, e quando o servidor enviava e-mails via `wp_mail()` padrão (PHP `mail()`), as mensagens de confirmação para os candidatos caíam na caixa de **Spam** do Gmail/Outlook.
- **Causa Raiz:**
  1. O JS antigo tinha um fallback `mailto:` e o handler AJAX não estava sincronizado com o endereço oficial `contact@artinnovaresidency.com`.
  2. Os e-mails saíam pelo MTA local do PHP sem autenticação SMTP na conta real `contact@artinnovaresidency.com`, falhando no alinhamento SPF/DKIM/DMARC, além de usarem cabeçalhos que acionavam filtros anti-spam.
- **Solução Aplicada (Acerto):**
  1. Configuramos autenticação SMTP real via `PHPMailer` (`smtp.hostinger.com`, porta `465` SSL) autenticada na caixa oficial `contact@artinnovaresidency.com`.
  2. Em `templates/landing/artinnova-light/functions.php` (`artinnova_light_handle_lead_submission()`), padronizamos os cabeçalhos `From: Residência ArtInnova <contact@artinnovaresidency.com>` e `Reply-To` limpos (com sanitização estrita contra *CRLF Header Injection*), enviando:
     - Um e-mail de notificação imediata para a equipe (`contact@artinnovaresidency.com`);
     - Um e-mail transacional automático para o candidato no idioma em que ele preencheu o formulário (`PT`, `EN`, `ES` ou `FR`).
  3. Sincronizamos cada envio da Landing Page tanto no CPT `artinnova_lead` quanto diretamente na tabela `wp_stackead_leads` para visualização dentro de `/plataforma` → **Leads & Inscrições**.

### 3.2. Limitação de Papel Único no WordPress vs. Arquitetura Multi-Papéis (Cumulativa e Contextual)
- **O Problema:** O modelo padrão do WordPress (`$user->set_role($role)`) **apaga** todos os papéis anteriores do usuário ao atribuir um novo. Na Residência ArtInnova, os founders e membros da equipe frequentemente acumulam funções (ex.: **Administrador + Coordenador + Professor**, ou **Professor em um curso e Aluno em outro**).
- **Solução Aplicada (Acerto):**
  1. Refatoramos `RoleManager.php` (`RoleManager::set_user_roles()`) para trabalhar com arrays de papéis usando `$user->add_role()` e `$user->remove_role()`, preservando papéis cumulativos.
  2. Refatoramos `AccessControl.php` e `ScopeFilter.php` para avaliar a **união das capacidades** de todos os papéis globais do usuário (`administrator`, `stackead_coordinator`, `stackead_master`, `stackead_student`) somada aos seus **papéis contextuais por turma** (`wp_stackead_cohort_members`).
  3. Na interface (`stackead-app.js`), criamos seletores multi-checkbox com botões de atalho rápido (*Admin + Coord. + Professor*, *Coordenador + Professor*, *Professor + Aluno*, etc.) e múltiplas linhas de vínculo contextual por turma.

### 3.3. Auditoria de Segurança e Blindagem (OWASP API Top 10 & CVEs)
Durante a revisão de segurança antes do deploy de produção, identificamos e corrigimos preventivamente falhas críticas:
1. **Arbitrary Local File Read / SSRF e Path Disclosure em `ImageOptimizer.php`:**
   - *Antes:* `extract_binary()` aceitava qualquer string que passasse em `file_exists($source)` e fazia `file_get_contents($source)`, além de retornar o caminho físico do servidor (`'path' => $file_path`) no JSON.
   - *Correção:* `extract_binary()` agora aceita **exclusivamente** uploads reais verificados via `is_uploaded_file()` ou strings `data:image/(png|jpe?g|webp|gif);base64,...` com limite máximo de **8 MB**, e o caminho físico interno foi removido da resposta REST.
2. **Proteção Financeira e Webhooks em `ApiRouter.php`:**
   - `POST /media/upload` foi restrito de `check_logged_in` para `check_master_or_admin` (mantendo `POST /users/me/avatar` apenas para o próprio avatar do aluno).
   - `POST /finance/payment-webhook` (`payment_webhook()`) passou a exigir validação criptográfica `hash_equals()` via cabeçalho `X-StackEAD-Webhook-Secret`.
   - `POST /orders/{id}/simulate-pay` e `POST /finance/checkout-instant` foram blindados para impedir que estudantes aprovem cursos pagos por conta própria sem confirmação de gateway ou privilégio administrativo.
3. **Rate Limiting, Anti-Open Redirect e Anti-CSV Injection:**
   - Adicionado `ApiRouter::check_rate_limit()` por IP via transients nas rotas públicas: Login (`8 req / 10 min`), Recuperação de Senha (`4 req / 15 min`) e Captura de Leads (`6 req / 10 min`).
   - Redirecionamento pós-login blindado com `wp_validate_redirect()`.
   - Exportação CSV (`export_leads_csv()`) blindada com `sanitize_csv_cell()` contra *CSV Formula Injection* (`=`, `+`, `-`, `@`, `\t`, `\r`).
4. **Atualização da Stack no Servidor:**
   - Atualizamos `litespeed-cache` para `7.9.3` (mitigando CVEs históricas da série 2024–2026), desativamos `xmlrpc.php`, ocultamos `wp_generator` e adicionamos headers HTTP de segurança (`X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy`).

### 3.4. Conflito de Cache do LiteSpeed em Rotas Dinâmicas (`/plataforma` e `/login`)
- **O Problema:** Com o LiteSpeed Cache ativo em produção, rotas customizadas via `add_rewrite_rule` (`/plataforma`, `/login`, `/plataforma-login`) corriam o risco de retornar `Cache-Control: public, max-age=604800`, podendo cachear o HTML/nonce de um usuário para outro.
- **Solução Aplicada (Acerto):**
  - Tanto em `RewriteRules::handle_template_redirect()` quanto dentro de `LoginTemplate::render()`, definimos `DONOTCACHEPAGE = true`, chamamos `nocache_headers()`, enviamos o cabeçalho `X-LiteSpeed-Cache-Control: no-cache` e disparamos `do_action('litespeed_control_set_nocache', 'StackEAD dynamic route')`.
  - Resultado verificado via `curl.exe -I`: `/` (Landing Page) aproveita o cache de alta performance do LiteSpeed, enquanto `/plataforma`, `/login` e `/plataforma-login` sempre respondem com `Cache-Control: no-cache, must-revalidate, max-age=0, no-store, private` e `X-LiteSpeed-Cache-Control: no-cache`.

### 3.5. Conflito de Rota na Home (`LandingTemplateEngine.php`)
- **O Problema:** Quando `stackead-core` foi ativado em produção, `LandingTemplateEngine::filter_template_include()` interceptava `is_front_page()` para exibir um template genérico de curso, substituindo a Landing Page oficial do tema `artinnova-light`.
- **Solução Aplicada (Acerto):**
  - Ajustamos `LandingTemplateEngine.php` para verificar `if ( 'artinnova-light' === get_stylesheet() || 'artinnova-light' === get_template() ) return $template;` na front-page, garantindo que `https://artinnovaresidency.com/` sempre carregue o tema oficial multilíngue `artinnova-light`.

### 3.6. Evolução de UI/UX: Login Único, Logo Branca, Modo Light e Fim dos Modais Espremidos
- **Problema 1 (Duas telas de login e logo preta invisível no fundo escuro):**
  - Ao acessar `/plataforma` deslogado, `AppTemplate.php` exibia uma tela intermediária ("Bem-vindo ao Ambiente EAD -> Acessar Minha Conta") que levava para `/login`. Além disso, `LoginTemplate.php` priorizava `logo_dark_url` (`artinnova-logo-black.png`) sobre o fundo escuro `#07090e`.
  - *Solução:* Agora `AppTemplate::render()` chama diretamente `LoginTemplate::render()` quando `!is_user_logged_in()` (tela única de login). Tanto no Login quanto na Sidebar escura, usamos `artinnova-logo-white.png` combinado com `filter: brightness(0) invert(1)`, garantindo logo 100% branca e nítida.
- **Problema 2 (Baixo contraste das moedas `0 ArtCoins` e seletores `PT` / `R$ BRL` no Modo Light):**
  - No tema claro (`html[data-theme="light"]`), o badge `.coin-wallet-pill` tinha texto amarelo-claro (`#fef08a`) sobre fundo claro translúcido (ilegível) e os selects `.i18n-select` tinham fundo escuro fixo.
  - *Solução:* Criamos regras dedicadas `html[data-theme="light"]` em `stackead-app.css` com gradiente âmbar dourado (`#fef3c7` → `#fde68a`), borda `#f59e0b` e texto escuro `#78350f` para `ArtCoins`, e estilo claro harmonizado em formato pill para os seletores de idioma e moeda.
- **Problema 3 (Inputs pequenos/sem estilo e Modais ruins no celular):**
  - Diversos formulários usavam `.form-control`, `.form-input` e `.form-select` que não tinham definição global em `stackead-app.css` (caindo no input branco padrão do navegador), e abriam dentro de caixas modais flutuantes que ficavam ruins em telas móveis.
  - *Solução:*
    1. Criamos o **Global Form & Input Design System** em `stackead-app.css` (`min-height: 46px`, `padding: 11px 16px`, `border-radius: 12px`, `width: 100%`, `font-size: 16px` no mobile para impedir auto-zoom no iOS/Android e colapso automático para 1 coluna em `@media (max-width: 768px)`).
    2. Convertimos os fluxos de **Cadastrar Novo Usuário**, **Editar Usuário & Papéis**, **Gerenciar Vínculos de Turma** e **Meu Perfil & Privacidade (LGPD/GDPR)** em **Páginas Completas (Full-Page Views)** renderizadas diretamente dentro de `#app-content`, e transformamos `.modal-backdrop` em workspace de página inteira alinhado à área de conteúdo.

### 3.7. Adequação Internacional Dupla: LGPD (Brasil) + GDPR / CNIL (União Europeia / França)
- **O que foi implementado:**
  1. **Landing Page (`artinnova-light` v2.0.3):**
     - **Banner de Cookies (`#cookie-consent-banner`)** nos 4 idiomas (`PT`, `EN`, `ES`, `FR`) com botões de **mesmo peso visual** (*Aceitar Todos* e *Apenas Essenciais* + *Personalizar & Política*), seguindo a diretriz anti-dark-patterns da **CNIL (França)** e da **ANPD (Brasil)**, com *Privacy by Default* (analíticos desativados até opt-in explícito, persistido em `localStorage['artinnova_cookie_consent_v2']`).
     - **Modal da Central de Privacidade (`#privacy-modal`)** detalhando Controlador Bilateral Brasil–França, DPO (`contact@artinnovaresidency.com`), Bases Legais (LGPD Art. 7º / GDPR Art. 6º), Transferência Internacional Brasil ↔ França/UE (LGPD Art. 33 / GDPR Cap. V Arts. 44–49), Retenção (24 meses) e autoridades **ANPD** e **CNIL**.
     - **Auditoria Server-Side de Consentimento:** `artinnova_light_handle_lead_submission()` e `ApiRouter::capture_lead()` rejeitam com HTTP `400` qualquer submissão sem `consent` explícito, gravam `consent_given = 1`, `consent_version = 'v2.0-LGPD-GDPR-2026'` e `lang`, e incluem rodapé de direitos LGPD/GDPR no e-mail automático de confirmação nos 4 idiomas.
  2. **Plataforma EAD (`/plataforma`) e Login (`/login`):**
     - Modal informativo `#login-privacy-modal` na tela de login.
     - **Central de Privacidade do Titular** em *Meu Perfil & Privacidade*: toggles granulares de consentimento (`GET/POST /stackead/v1/privacy/consent`), download imediato de todos os dados pessoais em JSON (`GET /stackead/v1/privacy/my-data`) e abertura de protocolo formal de exclusão/anonimização ao DPO (`POST /stackead/v1/privacy/request-erasure`).
     - Botão **`Excluir (LGPD/GDPR)`** (`DELETE /stackead/v1/leads/{id}`) na gestão de Leads para atendimento imediato ao Direito ao Esquecimento.

---

## 4. Playbook de Deploy, Sincronização e Backup (Como Fazemos Deploy)

### 4.1. Ambientes e Caminhos
- **Ambiente Local (Workspace):** `c:\laragon\www\artinnova`
  - Plugin e fonte canônica: `c:\laragon\www\artinnova\wp-content\plugins\stackead-core`
  - Binário PHP local (para lint `php -l`): `C:\laragon\bin\php\php-*\php.exe`
- **Servidor de Produção (Hostinger Cloud / LiteSpeed):**
  - **Host SSH/SCP:** `u132424892@45.152.46.188`
  - **Porta SSH/SCP:** `65002` (usar `-p 65002` no `ssh` e `-P 65002` no `scp`)
  - **Raiz do WordPress em Produção:** `domains/artinnovaresidency.com/public_html`
  - **Caminho do Plugin em Produção:** `domains/artinnovaresidency.com/public_html/wp-content/plugins/stackead-core`
  - **Caminho do Tema Ativo em Produção:** `domains/artinnovaresidency.com/public_html/wp-content/themes/artinnova-light`

> **REGRA DE OURO DO DEPLOY DO TEMA:**  
> Sempre que editar qualquer arquivo dentro de `wp-content/plugins/stackead-core/templates/landing/artinnova-light/` (como `front-page.php`, `footer.php`, `functions.php`, `main.css`, `app.js`, `locales/*.json` ou `translations-data.js`), você **DEVE**:
> 1. Regenerar `translations-data.js` caso tenha editado `locales/*.json`;
> 2. Enviar os arquivos para `wp-content/plugins/stackead-core/templates/landing/artinnova-light/` no servidor; e
> 3. Copiá-los também para `wp-content/themes/artinnova-light/` no servidor (`cp -r wp-content/plugins/stackead-core/templates/landing/artinnova-light/. wp-content/themes/artinnova-light/`), pois é de `wp-content/themes/artinnova-light/` que o WordPress serve a Landing Page principal!

### 4.2. Passo a Passo de Validação e Deploy Seguro

#### Passo 1: Validar Sintaxe Localmente (JS, JSON e PHP)
```powershell
$base = "c:\laragon\www\artinnova\wp-content\plugins\stackead-core"
$phpExe = (Get-ChildItem "C:\laragon\bin\php\*\php.exe" | Select-Object -First 1).FullName

# 1. Se alterou locales/*.json, reconstruir translations-data.js:
node -e "const fs=require('fs'),p='c:/laragon/www/artinnova/wp-content/plugins/stackead-core/templates/landing/artinnova-light',b={};for(const l of ['pt','en','es','fr'])b[l]=JSON.parse(fs.readFileSync(p+'/locales/'+l+'.json','utf8'));fs.writeFileSync(p+'/assets/js/translations-data.js','window.ARTINNOVA_TRANSLATIONS = '+JSON.stringify(b,null,2)+';\n','utf8');"

# 2. Validar JS
node --check "$base\assets\js\stackead-app.js"
node --check "$base\templates\landing\artinnova-light\assets\js\app.js"
node --check "$base\templates\landing\artinnova-light\assets\js\translations-data.js"

# 3. Validar PHP
& $phpExe -l "$base\includes\Core\Database.php"
& $phpExe -l "$base\includes\RestApi\ApiRouter.php"
& $phpExe -l "$base\templates\landing\artinnova-light\functions.php"
```

#### Passo 2: Enviar Arquivos via SCP e Sincronizar Tema + Cache via SSH
```powershell
$base = "c:\laragon\www\artinnova\wp-content\plugins\stackead-core"
$remotePlugin = "u132424892@45.152.46.188:domains/artinnovaresidency.com/public_html/wp-content/plugins/stackead-core"

# Exemplo enviando arquivos modificados:
scp -P 65002 "$base\assets\js\stackead-app.js" "$remotePlugin/assets/js/stackead-app.js"
scp -P 65002 "$base\assets\css\stackead-app.css" "$remotePlugin/assets/css/stackead-app.css"
scp -P 65002 "$base\includes\RestApi\ApiRouter.php" "$remotePlugin/includes/RestApi/ApiRouter.php"

# Sincronizar o tema ativo no servidor e limpar todo o cache do LiteSpeed:
ssh -p 65002 u132424892@45.152.46.188 "cd domains/artinnovaresidency.com/public_html && cp -r wp-content/plugins/stackead-core/templates/landing/artinnova-light/. wp-content/themes/artinnova-light/ && wp litespeed-purge all"
```

#### Passo 3: Lição Aprendida ao Rodar Scripts PHP Remotos via PowerShell + SSH
- **Armadilha Conhecida:** No Windows PowerShell, passar código PHP complexo com barras invertidas de namespace (`\StackEAD\Core\Database::migrate()`) ou variáveis (`$wpdb`) dentro de `ssh ... "wp eval '...'"` falha por causa do escape duplo de aspas e barras entre PowerShell e Bash.
- **Padrão Recomendado (Infalível):** Sempre crie um script PHP temporário em `$env:TEMP\task_runner.php`, envie via `scp -P 65002`, execute com `php task_runner.php` e remova-o imediatamente com `rm -f task_runner.php`:
```powershell
$phpScript = @'
<?php
require_once __DIR__ . '/wp-load.php';
\StackEAD\Core\Database::migrate();
echo "DB_VERSION=" . get_option('stackead_db_version') . "\n";
'@
$tmp = "$env:TEMP\run_task.php"
[System.IO.File]::WriteAllText($tmp, $phpScript, (New-Object System.Text.UTF8Encoding($false)))
scp -P 65002 $tmp u132424892@45.152.46.188:domains/artinnovaresidency.com/public_html/run_task.php
ssh -p 65002 u132424892@45.152.46.188 "cd domains/artinnovaresidency.com/public_html && php run_task.php && rm -f run_task.php"
Remove-Item $tmp -Force
```

#### Passo 4: Backups Existentes (Para Restauração / Rollback se Necessário)
- **No Servidor Remoto:**
  - `/home/u132424892/backups/artinnova_backup_20261110/themes_backup.tar.gz`
  - `/home/u132424892/backups/artinnova_backup_20261110/db_backup_full.sql`
- **No Workspace Local:**
  - `c:\laragon\www\artinnova\backups\themes_backup_20261110.tar.gz`
  - `c:\laragon\www\artinnova\backups\db_backup_20261110.sql`

#### Passo 5: Versionamento no GitHub
O repositório Git está inicializado em `c:\laragon\www\artinnova\wp-content\plugins\stackead-core` apontando para `https://github.com/AleBSobral/stackEAD.git` (branch `main`).
- **Nunca versione** a pasta `scratch/`, dumps `.sql`, `.tar.gz`, `wp-config.php` ou senhas em texto puro (já protegidos no `.gitignore`).

---

## 5. Usuários Administrativos / Founders Configurados em Produção

Em produção (`https://artinnovaresidency.com/plataforma`), os seguintes usuários administrativos já estão criados com **papéis cumulativos** (`administrator` + `stackead_coordinator` + `stackead_master`):
1. **Alexandre Sobral** (`ID=1`, login: `admin`, e-mail: `alexandre.sobral@gmail.com`)
2. **Marcelo Alves de Barros** (`ID=2`, login: `marcelo-alves-de-barros`, e-mail: `mbarros@computacao.ufcg.edu.br`)
3. **Flávio Romero Brasil de Souza** (`ID=3`, login: `flavio-romero-brasil-de-souza`, e-mail: `fbrasilcg@gmail.com`)
4. **Carlos Alejandro Rueda Angarita** (`ID=4`, login: `carlos-alejandro-rueda-angarita`, e-mail: `rueda.carlos@gmail.com`)

---

## 6. Pontos Ainda em Aberto e Propostas de Trabalho (Roadmap de Continuidade)

Abaixo estão os próximos passos mapeados para evolução do sistema por qualquer desenvolvedor ou agente de IA:

### 6.1. Integração em Produção dos Gateways de Pagamento (Stripe / MercadoPago / PayPal)
- **Estado Atual:** A arquitetura financeira (`FinanceService.php`, tabela `wp_stackead_orders` e webhook `POST /wp-json/stackead/v1/finance/payment-webhook` protegido por `X-StackEAD-Webhook-Secret`) já está implementada e blindada.
- **Próximo Passo:** Configurar as chaves de produção (Live API Keys do Stripe para cobranças internacionais em `EUR`/`USD` e MercadoPago/Pix/Cartão em `BRL` para os 10x de R$ 300), definir o segredo `stackead_webhook_secret` em produção e testar o fluxo de matrícula automática pós-pagamento.

### 6.2. Povoamento Acadêmico da Turma Piloto 2026/2027 (60 Vagas: 50 Brasil + 10 França)
- **Estado Atual:** Todo o CRUD de Cursos, Turmas (`Cohorts`), Módulos ("Lego Didático"), Aulas Multimídia, Quizzes Anti-Ansiedade e Projetos Integradores está funcional em `/plataforma`.
- **Próximo Passo:** Cadastrar a estrutura curricular oficial dos 10 Ateliers Híbridos, configurar o cronograma das sessões síncronas (Janeiro a Abril de 2027), cadastrar os critérios de avaliação do **HackArtThon** (Abril de 2027) e abrir o fluxo de seleção das **20 vagas sociais** (10 Brasil + 10 França).

### 6.3. Fila Administrativa do DPO na Plataforma (`/plataforma`)
- **Estado Atual:** Os usuários já podem exportar seus dados em JSON (`GET /privacy/my-data`), gerenciar consentimentos (`POST /privacy/consent`) e abrir protocolo formal de exclusão (`POST /privacy/request-erasure`), que notifica `contact@artinnovaresidency.com` e salva o protocolo na opção `stackead_gdpr_erasure_requests`. Administradores também já podem excluir leads com 1 clique (`DELETE /leads/{id}`).
- **Próximo Passo:** Adicionar uma aba visual dedicada **"Solicitações DPO (LGPD/GDPR)"** dentro da visão administrativa de `/plataforma` para listar os protocolos de `stackead_gdpr_erasure_requests` e permitir aprovar a anonimização/exclusão de conta com geração automática de log de auditoria.

### 6.4. Testes Automatizados E2E e Pipeline CI/CD
- **Proposta de Trabalho:** Configurar uma suíte Playwright para testes automatizados de fumaça (Landing Page em PT/EN/ES/FR, Banner de Cookies CNIL/LGPD, Login em `/plataforma`, criação de usuário multi-papel e download JSON de portabilidade) e um workflow GitHub Actions em `https://github.com/AleBSobral/stackEAD` para deploy automatizado via SSH a cada push na branch `main`.
