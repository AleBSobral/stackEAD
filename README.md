# StackEAD Core — Plataforma EAD & Residência Acadêmica (White Label)

**StackEAD Core** é uma plataforma educacional e de gestão de residências artísticas/acadêmicas *standalone* e *White Label* para WordPress (`PHP 8.2+ / 8.3+`), projetada com foco em **Acessibilidade Cognitiva (W3C COGA)**, **Papéis Cumulativos e Contextuais (Multi-Role Engine)**, **Internacionalização (PT/EN/ES/FR)** e **Segurança (OWASP API Top 10)**.

---

## Principais Recursos

- **Aplicação SPA Desacoplada (`/plataforma` e `/login`):**
  - Experiência independente do `wp-admin`, com suporte nativo aos temas **Dark** e **Light**.
  - Páginas dedicadas e responsivas (Desktop & Mobile) para cadastros e gestão acadêmica.
- **Motor Multi-Papéis (Globais + Contextuais por Turma):**
  - Suporte a **papéis globais cumulativos** no mesmo usuário (`administrator`, `stackead_coordinator`, `stackead_master`, `stackead_student`).
  - Suporte a **vínculos contextuais por turma/coorte** (`wp_stackead_cohort_members`), permitindo que um mesmo membro atue como **Professor/Mestre** em uma turma e **Estudante/Aluno** ou **Coordenador** em outra.
- **Currículo em Blocos ("Lego Didático"):**
  - Módulos, aulas multimídia (vídeo, texto rico, anexos, links), quizzes anti-ansiedade com pausa respiratória, entregas de projetos e diário de presença.
- **Acessibilidade Cognitiva W3C COGA:**
  - Régua de leitura focada, fontes adaptativas (*Lexend* e *OpenDyslexic*), espaçamento expandido (WCAG 1.4.12), alto contraste e modo foco livre de distrações.
- **Gamificação, Moedas Virtuais (`ArtCoins`) & Certificados Verificáveis:**
  - Sistema de XP, níveis, carteira de moedas virtuais, mural de insígnias e emissão de certificados com validação pública (`/certificados/{codigo}`).
- **Tema Oficial Multilíngue Integrado (`templates/landing/artinnova-light`):**
  - Landing Page internacionalizada (`PT`, `EN`, `ES`, `FR`), captura de leads integrada ao CRM interno (`wp_stackead_leads`) e disparo transacional com autenticação SMTP/SPF/DKIM.
- **Blindagem e Segurança (Hardening):**
  - Rate limiting por IP nas rotas públicas de autenticação e captura de leads.
  - Validação criptográfica (`hash_equals`) para webhooks financeiros.
  - Proteção contra *Open Redirect*, *CSV Formula Injection*, *CRLF Header Injection*, *Path Disclosure* e *SSRF*.
  - Isolamento de cache dinâmico (`X-LiteSpeed-Cache-Control: no-cache`) nas rotas autenticadas.
- **Conformidade Internacional LGPD (Brasil) & GDPR / RGPD (União Europeia):**
  - Banner de cookies com botões de igual destaque (*Privacy by Default* conforme diretrizes ANPD e CNIL).
  - Rastreabilidade auditável de consentimento (`consent_given`, `consent_version`, `lang`) em `wp_stackead_leads` e metadados de usuários.
  - Central de Privacidade no perfil do usuário (`/plataforma`) com gestão granular de consentimentos (`GET/POST /privacy/consent`), portabilidade integral em JSON (`GET /privacy/my-data`) e protocolo de solicitação de exclusão ao DPO (`POST /privacy/request-erasure`).
  - Direito ao esquecimento administrativo para remoção permanente de leads (`DELETE /leads/{id}`).

---

## Estrutura do Repositório

```text
stackead-core/
├── assets/
│   ├── css/                  # Design System (Dark/Light, Formulários, COGA)
│   ├── images/               # Ativos institucionais e logos White Label
│   └── js/                   # Aplicação SPA (stackead-app.js, i18n, COGA)
├── includes/
│   ├── Admin/                # Configurações White Label e Tokens CSS
│   ├── App/                  # Shell da aplicação (/plataforma) e validação de certificados
│   ├── Auth/                 # Tela de Login White Label e guarda de rotas
│   ├── Core/                 # Activator, Deactivator, Plugin e RewriteRules
│   ├── Database/             # Schema e migrações (13 tabelas customizadas)
│   ├── Landing/              # Motor de templates de Landing Pages
│   ├── RestApi/              # API REST completa (/wp-json/stackead/v1/*)
│   ├── Roles/                # RoleManager, AccessControl e ScopeFilter (Multi-Role)
│   └── Services/             # ImageOptimizer, GamificationEngine e CertificateGenerator
├── templates/
│   └── landing/
│       └── artinnova-light/  # Tema institucional multilíngue ArtInnova (PT/EN/ES/FR)
├── stackead-core.php         # Bootstrap principal do plugin
└── uninstall.php             # Rotina de desinstalação segura
```

---

## Instalação

1. Copie a pasta `stackead-core` para `wp-content/plugins/stackead-core`.
2. *(Opcional)* Copie `templates/landing/artinnova-light` para `wp-content/themes/artinnova-light` caso deseje ativar o tema institucional na página inicial.
3. Ative o plugin **StackEAD Core** via painel ou WP-CLI:
   ```bash
   wp plugin activate stackead-core
   wp rewrite flush --hard
   ```
4. Acesse a plataforma em `/plataforma`.

