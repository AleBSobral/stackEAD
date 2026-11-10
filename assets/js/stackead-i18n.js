/**
 * StackEAD Internationalization (i18n) & Multi-Currency System
 * Supports: Portuguese (pt-BR), English (en-US), Spanish (es-ES), French (fr-FR)
 * Currencies: BRL (R$), USD ($), EUR (€)
 */
(function () {
	'use strict';

	const dictionaries = {
		'pt-BR': {
			'nav.dashboard': 'Meu Painel',
			'nav.curriculum': 'Trilha & Aulas',
			'nav.player': 'Sala de Aula',
			'nav.sessions': 'Sessões Ao Vivo',
			'nav.assignments': 'Projetos Práticos',
			'nav.community': 'Fórum da Turma',
			'nav.gamification': 'Conquistas & XP',
			'nav.certificate': 'Certificação',
			'nav.attendance-desk': 'Diário de Presença',
			'nav.grading-desk': 'Bancada de Correção',
			'nav.users-admin': 'Usuários & Membros',
			'nav.courses-admin': 'Gestão de Cursos',
			'nav.accommodations-desk': 'Laudos & COGA',
			'nav.finance-desk': 'Finanças & Planos',
			'nav.gamification-admin': 'Gestão de Gamificação',
			'nav.whitelabel-desk': 'Identidade White Label',
			'nav.landing-builder': 'Construtor de Landing Page',
			'nav.news-admin': 'Mural de Notícias',
			'nav.categories-admin': 'Gestão de Categorias',
			'nav.leads-admin': 'Leads & Inscrições',

			'timeline.title': 'Jornada de Aprendizado & Linha de Marcos',
			'timeline.completed': 'Concluído',
			'timeline.in_progress': 'Em Andamento',
			'timeline.locked': 'Bloqueado',
			'timeline.module_final_project': 'Projeto Integrador de Módulo',
			'timeline.final_project_badge': 'Projeto Integrador',
			'timeline.final_project_desc': 'Projeto prático obrigatório para destrancar o módulo seguinte.',
			'timeline.submit_project': 'Entregar Projeto',
			'timeline.view_project': 'Ver Projeto',
			'timeline.locked_desc': 'Este módulo será destravado após a aprovação no Projeto Integrador anterior.',

			'payment.title': 'Formas de Pagamento & Inscrição',
			'payment.pay_with_mercadopago': 'Pagar com Mercado Pago',
			'payment.pay_with_paypal': 'Pagar com PayPal',
			'payment.pay_with_stripe': 'Pagar com Cartão Internacional (Stripe)',
			'payment.pay_with_pix': 'Pagar com Pix Imediato',
			'payment.instant_access': 'Acesso liberado automaticamente após confirmação.',
			'payment.confirm_enrollment': 'Confirmar & Liberar Minha Vaga',

			'gamification.coins': 'Moedas Virtuais',
			'gamification.xp': 'Experiência (XP)',
			'gamification.ranking': 'Classificação Geral',
			'gamification.badges': 'Emblemas Conquistados',
			'gamification.store': 'Loja de Recompensas',

			'action.save': 'Salvar Alterações',
			'action.cancel': 'Cancelar',
			'action.back': 'Voltar',
			'action.continue': 'Continuar',
			'action.loading': 'Carregando...',
			'action.view_details': 'Ver Detalhes',
			'action.apply': 'Inscreva-se Agora'
		},

		'en-US': {
			'nav.dashboard': 'My Dashboard',
			'nav.curriculum': 'Curriculum & Lessons',
			'nav.player': 'Classroom',
			'nav.sessions': 'Live Sessions',
			'nav.assignments': 'Practical Projects',
			'nav.community': 'Class Forum',
			'nav.gamification': 'Achievements & XP',
			'nav.certificate': 'Certification',
			'nav.attendance-desk': 'Attendance Log',
			'nav.grading-desk': 'Grading Desk',
			'nav.users-admin': 'Users & Members',
			'nav.courses-admin': 'Course Management',
			'nav.accommodations-desk': 'Medical Reports & COGA',
			'nav.finance-desk': 'Finance & Plans',
			'nav.gamification-admin': 'Gamification Management',
			'nav.whitelabel-desk': 'White Label Identity',
			'nav.landing-builder': 'Landing Page Builder',
			'nav.news-admin': 'News Feed',
			'nav.categories-admin': 'Category Management',
			'nav.leads-admin': 'Leads & Applications',

			'timeline.title': 'Learning Journey & Milestone Stepper',
			'timeline.completed': 'Completed',
			'timeline.in_progress': 'In Progress',
			'timeline.locked': 'Locked',
			'timeline.module_final_project': 'Module Capstone Project',
			'timeline.final_project_badge': 'Capstone Project',
			'timeline.final_project_desc': 'Mandatory hands-on project required to unlock the next module.',
			'timeline.submit_project': 'Submit Project',
			'timeline.view_project': 'View Project',
			'timeline.locked_desc': 'This module unlocks once the previous module capstone project is approved.',

			'payment.title': 'Payment Methods & Enrollment',
			'payment.pay_with_mercadopago': 'Pay with Mercado Pago',
			'payment.pay_with_paypal': 'Pay with PayPal',
			'payment.pay_with_stripe': 'Pay with International Card (Stripe)',
			'payment.pay_with_pix': 'Pay with Instant Pix',
			'payment.instant_access': 'Automatic access released upon payment confirmation.',
			'payment.confirm_enrollment': 'Confirm & Unlock My Seat',

			'gamification.coins': 'Virtual Coins',
			'gamification.xp': 'Experience Points (XP)',
			'gamification.ranking': 'Leaderboard',
			'gamification.badges': 'Unlocked Badges',
			'gamification.store': 'Rewards Shop',

			'action.save': 'Save Changes',
			'action.cancel': 'Cancel',
			'action.back': 'Back',
			'action.continue': 'Continue',
			'action.loading': 'Loading...',
			'action.view_details': 'View Details',
			'action.apply': 'Apply Now'
		},

		'es-ES': {
			'nav.dashboard': 'Mi Panel',
			'nav.curriculum': 'Itinerario & Clases',
			'nav.player': 'Aula Virtual',
			'nav.sessions': 'Sesiones en Vivo',
			'nav.assignments': 'Proyectos Prácticos',
			'nav.community': 'Foro del Grupo',
			'nav.gamification': 'Logros & XP',
			'nav.certificate': 'Certificación',
			'nav.attendance-desk': 'Registro de Asistencia',
			'nav.grading-desk': 'Mesa de Calificación',
			'nav.users-admin': 'Usuarios & Miembros',
			'nav.courses-admin': 'Gestión de Cursos',
			'nav.accommodations-desk': 'Dictámenes & COGA',
			'nav.finance-desk': 'Finanzas & Planes',
			'nav.gamification-admin': 'Gestión de Gamificación',
			'nav.whitelabel-desk': 'Identidad White Label',
			'nav.landing-builder': 'Constructor de Landing Page',
			'nav.news-admin': 'Muro de Noticias',
			'nav.categories-admin': 'Gestión de Categorías',
			'nav.leads-admin': 'Leads & Inscripciones',

			'timeline.title': 'Trayectoria de Aprendizaje & Línea de Hitos',
			'timeline.completed': 'Completado',
			'timeline.in_progress': 'En Curso',
			'timeline.locked': 'Bloqueado',
			'timeline.module_final_project': 'Proyecto Integrador del Módulo',
			'timeline.final_project_badge': 'Proyecto Integrador',
			'timeline.final_project_desc': 'Proyecto obligatorio para desbloquear el siguiente módulo.',
			'timeline.submit_project': 'Entregar Proyecto',
			'timeline.view_project': 'Ver Proyecto',
			'timeline.locked_desc': 'Este módulo se desbloqueará tras la aprobación del proyecto integrador anterior.',

			'payment.title': 'Métodos de Pago & Matrícula',
			'payment.pay_with_mercadopago': 'Pagar con Mercado Pago',
			'payment.pay_with_paypal': 'Pagar con PayPal',
			'payment.pay_with_stripe': 'Pagar con Tarjeta Internacional (Stripe)',
			'payment.pay_with_pix': 'Pagar con Pix Inmediato',
			'payment.instant_access': 'Acceso habilitado automáticamente tras la confirmación.',
			'payment.confirm_enrollment': 'Confirmar & Desbloquear Mi Cupo',

			'gamification.coins': 'Monedas Virtuales',
			'gamification.xp': 'Experiencia (XP)',
			'gamification.ranking': 'Clasificación General',
			'gamification.badges': 'Insignias Desbloqueadas',
			'gamification.store': 'Tienda de Recompensas',

			'action.save': 'Guardar Cambios',
			'action.cancel': 'Cancelar',
			'action.back': 'Volver',
			'action.continue': 'Continuar',
			'action.loading': 'Cargando...',
			'action.view_details': 'Ver Detalles',
			'action.apply': 'Inscribirme Ahora'
		},

		'fr-FR': {
			'nav.dashboard': 'Mon Tableau de Bord',
			'nav.curriculum': 'Parcours & Cours',
			'nav.player': 'Salle de Classe',
			'nav.sessions': 'Sessions en Direct',
			'nav.assignments': 'Projets Pratiques',
			'nav.community': 'Forum de Promotion',
			'nav.gamification': 'Succès & XP',
			'nav.certificate': 'Certification',
			'nav.attendance-desk': 'Registre de Présence',
			'nav.grading-desk': 'Bureau de Notation',
			'nav.users-admin': 'Utilisateurs & Membres',
			'nav.courses-admin': 'Gestion des Formations',
			'nav.accommodations-desk': 'Dossiers Médicaux & COGA',
			'nav.finance-desk': 'Finances & Tarifs',
			'nav.gamification-admin': 'Gestion de la Gamification',
			'nav.whitelabel-desk': 'Identité White Label',
			'nav.landing-builder': 'Créateur de Page d\'Accueil',
			'nav.news-admin': 'Mur d\'Actualités',
			'nav.categories-admin': 'Gestion des Catégories',
			'nav.leads-admin': 'Prospects & Candidatures',

			'timeline.title': 'Parcours Pédagogique & Ligne des Jalons',
			'timeline.completed': 'Terminé',
			'timeline.in_progress': 'En Cours',
			'timeline.locked': 'Verrouillé',
			'timeline.module_final_project': 'Projet Intégrateur de Module',
			'timeline.final_project_badge': 'Projet Intégrateur',
			'timeline.final_project_desc': 'Projet pratique obligatoire pour déverrouiller le module suivant.',
			'timeline.submit_project': 'Soumettre le Projet',
			'timeline.view_project': 'Voir le Projet',
			'timeline.locked_desc': 'Ce module sera débloqué après validation du projet intégrateur précédent.',

			'payment.title': 'Modes de Paiement & Inscription',
			'payment.pay_with_mercadopago': 'Payer avec Mercado Pago',
			'payment.pay_with_paypal': 'Payer avec PayPal',
			'payment.pay_with_stripe': 'Payer par Carte Internationale (Stripe)',
			'payment.pay_with_pix': 'Payer avec Pix Immédiat',
			'payment.instant_access': 'Accès débloqué automatiquement après confirmation.',
			'payment.confirm_enrollment': 'Confirmer & Activer Mon Accès',

			'gamification.coins': 'Pièces Virtuelles',
			'gamification.xp': 'Points d\'Expérience (XP)',
			'gamification.ranking': 'Classement',
			'gamification.badges': 'Badges Débloqués',
			'gamification.store': 'Boutique de Récompenses',

			'action.save': 'Enregistrer',
			'action.cancel': 'Annuler',
			'action.back': 'Retour',
			'action.continue': 'Continuer',
			'action.loading': 'Chargement...',
			'action.view_details': 'Voir Détails',
			'action.apply': 'Postuler Maintenant'
		}
	};

	// Comprehensive UI Phrase Translations (PT-BR -> [EN-US, ES-ES, FR-FR])
	const phraseRows = [
		// Shell, Sidebar & Topbar
		['Plataforma', 'Platform', 'Plataforma', 'Plateforme'],
		['Turma Selecionada', 'Selected Cohort', 'Grupo Seleccionado', 'Promotion Sélectionnée'],
		['Carregando turmas...', 'Loading cohorts...', 'Cargando grupos...', 'Chargement des promotions...'],
		['Nenhuma turma ativa', 'No active cohorts', 'Ningún grupo activo', 'Aucune promotion active'],
		['Nova Turma', 'New Cohort', 'Nuevo Grupo', 'Nouvelle Promotion'],
		['Acessibilidade COGA', 'COGA Accessibility', 'Accesibilidad COGA', 'Accessibilité COGA'],
		['Ambiente de Estudos', 'Learning Environment', 'Entorno de Estudio', 'Environnement d\'Étude'],
		['Comunidade & Conquistas', 'Community & Achievements', 'Comunidad & Logros', 'Communauté & Succès'],
		['Gestão Docente', 'Faculty Management', 'Gestión Docente', 'Gestion Pédagogique'],
		['Gestão & Coordenação', 'Administration & Coordination', 'Gestión & Coordinación', 'Direction & Coordination'],
		['Portal & Marketing', 'Portal & Marketing', 'Portal & Marketing', 'Portail & Marketing'],
		['Administrador Geral', 'General Administrator', 'Administrador General', 'Administrateur Général'],
		['Administrador', 'Administrator', 'Administrador', 'Administrateur'],
		['Coordenação Pedagógica', 'Academic Coordinator', 'Coordinación Pedagógica', 'Coordination Pédagogique'],
		['Coordenação', 'Coordination', 'Coordinación', 'Coordination'],
		['Mestre / Instrutor', 'Master / Instructor', 'Maestro / Instructor', 'Maître / Instructeur'],
		['Estudante EAD', 'Online Student', 'Estudiante EAD', 'Étudiant EAD'],
		['Aluno / Assinante', 'Student / Subscriber', 'Alumno / Suscriptor', 'Étudiant / Abonné'],

		// Navigation & View Titles
		['Meu Painel', 'My Dashboard', 'Mi Panel', 'Mon Tableau de Bord'],
		['Painel Docente', 'Instructor Dashboard', 'Panel Docente', 'Tableau de Bord Enseignant'],
		['Painel de Coordenação', 'Coordination Dashboard', 'Panel de Coordinación', 'Tableau de Bord Coordination'],
		['Trilha & Aulas', 'Curriculum & Lessons', 'Itinerario & Clases', 'Parcours & Cours'],
		['Conteúdos & Aulas', 'Content & Lessons', 'Contenidos & Clases', 'Contenus & Cours'],
		['Sala de Aula', 'Classroom', 'Aula Virtual', 'Salle de Classe'],
		['Sessões Ao Vivo', 'Live Sessions', 'Sesiones en Vivo', 'Sessions en Direct'],
		['Projetos Práticos', 'Practical Projects', 'Proyectos Prácticos', 'Projets Pratiques'],
		['Atividades & Projetos', 'Assignments & Projects', 'Actividades & Proyectos', 'Activités & Projets'],
		['Entregas dos Alunos', 'Student Submissions', 'Entregas de Alumnos', 'Travaux des Étudiants'],
		['Fórum da Turma', 'Class Forum', 'Foro del Grupo', 'Forum de Promotion'],
		['Conquistas & XP', 'Achievements & XP', 'Logros & XP', 'Succès & XP'],
		['Conquistas & Ranking', 'Achievements & Leaderboard', 'Logros & Clasificación', 'Succès & Classement'],
		['Certificação', 'Certification', 'Certificación', 'Certification'],
		['Certificação Oficial', 'Official Certification', 'Certificación Oficial', 'Certification Officielle'],
		['Diário de Presença', 'Attendance Log', 'Registro de Asistencia', 'Registre de Présence'],
		['Bancada de Correção', 'Grading Desk', 'Mesa de Calificación', 'Bureau de Notation'],
		['Laudos & COGA', 'Medical Reports & COGA', 'Dictámenes & COGA', 'Dossiers Médicaux & COGA'],
		['Laudos & Acomodações COGA', 'COGA Medical Reports & Accommodations', 'Dictámenes & Acomodaciones COGA', 'Dossiers & Aménagements COGA'],
		['Finanças & Planos', 'Finance & Plans', 'Finanzas & Planes', 'Finances & Tarifs'],
		['Usuários & Membros', 'Users & Members', 'Usuarios & Miembros', 'Utilisateurs & Membres'],
		['Gestão de Usuários & Membros', 'Users & Members Management', 'Gestión de Usuarios & Miembros', 'Gestion des Utilisateurs & Membres'],
		['Gestão de Cursos', 'Course Management', 'Gestión de Cursos', 'Gestion des Formations'],
		['Gestão & Criação de Cursos', 'Course Creation & Management', 'Gestión & Creación de Cursos', 'Création & Gestion des Formations'],
		['Identidade White Label', 'White Label Identity', 'Identidad White Label', 'Identité White Label'],
		['Identidade & White Label', 'Brand Identity & White Label', 'Identidad & White Label', 'Identité & White Label'],
		['Construtor de Landing Page', 'Landing Page Builder', 'Constructor de Landing Page', 'Créateur de Page d\'Accueil'],
		['Mural de Notícias', 'News Feed', 'Muro de Noticias', 'Mur d\'Actualités'],
		['Mural de Notícias & Avisos', 'News & Announcements Feed', 'Muro de Noticias & Avisos', 'Mur d\'Actualités & Annonces'],
		['Gestão de Categorias', 'Category Management', 'Gestión de Categorías', 'Gestion des Catégories'],
		['Leads & Inscrições', 'Leads & Applications', 'Leads & Inscripciones', 'Prospects & Candidatures'],
		['Gestão de Gamificação', 'Gamification Management', 'Gestión de Gamificación', 'Gestion de la Gamification'],
		['Gestão de Gamificação & Moedas', 'Gamification & Coins Management', 'Gestión de Gamificación & Monedas', 'Gestion Gamification & Pièces'],
		['Gestão de Gamificação & Moedas Virtuais', 'Gamification & Virtual Coins Management', 'Gestión de Gamificación & Monedas Virtuales', 'Gestion de la Gamification & Pièces Virtuelles'],

		// COGA Modal
		['Recursos de Acessibilidade & Neurodivergência', 'Accessibility & Neurodiversity Resources', 'Recursos de Accesibilidad & Neurodivergencia', 'Ressources d\'Accessibilité & Neurodiversité'],
		['Régua de Foco / Guia de Leitura', 'Focus Ruler / Reading Guide', 'Regla de Enfoque / Guía de Lectura', 'Règle de Concentration / Guide de Lecture'],
		['Faixa translúcida guiada pelo mouse para leitura fluida sem salto de linhas.', 'Mouse-guided translucent strip for smooth reading without skipping lines.', 'Franja translúcida guiada por el ratón para lectura fluida sin saltar líneas.', 'Bande translucide guidée par la souris pour une lecture fluide sans sauter de lignes.'],
		['Modo Sem Distrações (Focus Mode)', 'Distraction-Free Mode (Focus Mode)', 'Modo Sin Distracciones (Focus Mode)', 'Mode Sans Distraction (Focus Mode)'],
		['Oculta menus e barras laterais durante o estudo do conteúdo da aula.', 'Hides menus and sidebars while studying lesson content.', 'Oculta menús y barras laterales durante el estudio del contenido.', 'Masque les menus et barres latérales pendant l\'étude du cours.'],
		['Tipografia Adaptativa', 'Adaptive Typography', 'Tipografía Adaptativa', 'Typographie Adaptative'],
		['Alternar fonte para facilitar leitura e decodificação.', 'Switch font to ease reading and decoding.', 'Cambiar fuente para facilitar la lectura y decodificación.', 'Changer de police pour faciliter la lecture et le décodage.'],
		['Padrão', 'Default', 'Estándar', 'Par défaut'],
		['Espaçamento Ampliado de Texto', 'Expanded Text Spacing', 'Espaciado de Texto Ampliado', 'Espacement de Texte Élargi'],
		['Aumenta entrelinhas e espaçamento entre palavras.', 'Increases line height and word spacing.', 'Aumenta el interlineado y el espacio entre palabras.', 'Augmente l\'interligne et l\'espacement entre les mots.'],
		['Alto Contraste', 'High Contrast', 'Alto Contraste', 'Contraste Élevé'],
		['Máxima distinção entre texto e fundo.', 'Maximum distinction between text and background.', 'Máxima distinción entre texto y fondo.', 'Distinction maximale entre le texte et l\'arrière-plan.'],
		['Solicitar Acomodação Pedagógica Oficial', 'Request Official Pedagogical Accommodation', 'Solicitar Acomodación Pedagógica Oficial', 'Demander un Aménagement Pédagogique Officiel'],
		['Tempo extra em provas, linguagem simplificada ou tutor de apoio.', 'Extra exam time, simplified language, or support tutor.', 'Tiempo extra en exámenes, lenguaje simplificado o tutor de apoyo.', 'Temps supplémentaire aux examens, langage simplifié ou tuteur de soutien.'],
		['Fazer Solicitação', 'Submit Request', 'Hacer Solicitud', 'Faire une Demande'],
		['✕ Sair do Modo Foco', '✕ Exit Focus Mode', '✕ Salir del Modo Enfoque', '✕ Quitter le Mode Concentration'],

		// Dashboard & Overview
		['Carregando dados da plataforma...', 'Loading platform data...', 'Cargando datos de la plataforma...', 'Chargement des données de la plateforme...'],
		['Carregando conteúdo...', 'Loading content...', 'Cargando contenido...', 'Chargement du contenu...'],
		['Continuar Estudos', 'Continue Learning', 'Continuar Estudios', 'Continuer les Études'],
		['Ver Trilha Completa', 'View Full Curriculum', 'Ver Itinerario Completo', 'Voir le Parcours Complet'],
		['Progresso na Trilha', 'Curriculum Progress', 'Progreso en el Itinerario', 'Progression du Parcours'],
		['Aulas Concluídas', 'Completed Lessons', 'Clases Completadas', 'Cours Terminés'],
		['Frequência Ao Vivo', 'Live Attendance', 'Asistencia en Vivo', 'Présence en Direct'],
		['Média em Quizzes', 'Quiz Average', 'Promedio en Quizzes', 'Moyenne aux Quiz'],
		['Próxima Aula Recomendada', 'Next Recommended Lesson', 'Próxima Clase Recomendada', 'Prochain Cours Recommandé'],
		['Iniciar Aula Agora', 'Start Lesson Now', 'Iniciar Clase Ahora', 'Démarrer le Cours'],
		['Próximas Sessões Ao Vivo', 'Upcoming Live Sessions', 'Próximas Sesiones en Vivo', 'Prochaines Sessions en Direct'],
		['Ver Todas', 'View All', 'Ver Todas', 'Voir Tout'],
		['Entrar na Sala', 'Join Room', 'Entrar a la Sala', 'Rejoindre la Salle'],
		['Acessibilidade & Neurodiversidade', 'Accessibility & Neurodiversity', 'Accesibilidad & Neurodiversidad', 'Accessibilité & Neurodiversité'],
		['Personalize a interface ou solicite acomodações pedagógicas (tempo extra em quizzes, resumo simplificado).', 'Customize the interface or request pedagogical accommodations (extra quiz time, simplified summary).', 'Personalice la interfaz o solicite acomodaciones pedagógicas (tiempo extra en quizzes, resumen simplificado).', 'Personnalisez l\'interface ou demandez des aménagements pédagogiques (temps supplémentaire aux quiz, résumé simplifié).'],
		['Solicitar Acomodação Oficial', 'Request Official Accommodation', 'Solicitar Acomodación Oficial', 'Demander un Aménagement Officiel'],

		// Curriculum & Classroom Player
		['Trilha Formativa & Matriz Curricular', 'Learning Path & Curriculum Matrix', 'Itinerario Formativo & Matriz Curricular', 'Parcours Pédagogique & Matrice Curriculaire'],
		['Editar Turma', 'Edit Cohort', 'Editar Grupo', 'Modifier la Promotion'],
		['Novo Módulo', 'New Module', 'Nuevo Módulo', 'Nouveau Module'],
		['Nova Aula', 'New Lesson', 'Nueva Clase', 'Nouveau Cours'],
		['Voltar para a Trilha', 'Back to Curriculum', 'Volver al Itinerario', 'Retour au Parcours'],
		['Concluir Aula (+20 XP)', 'Complete Lesson (+20 XP)', 'Completar Clase (+20 XP)', 'Terminer le Cours (+20 XP)'],
		['Aula Concluída ✓', 'Lesson Completed ✓', 'Clase Completada ✓', 'Cours Terminé ✓'],
		['Resumo em Linguagem Simples (W3C COGA)', 'Plain Language Summary (W3C COGA)', 'Resumen en Lenguaje Sencillo (W3C COGA)', 'Résumé en Langage Clair (W3C COGA)'],
		['Conteúdo & Notas de Aula', 'Lesson Content & Notes', 'Contenido & Notas de Clase', 'Contenu & Notes de Cours'],
		['Materiais & Downloads', 'Materials & Downloads', 'Materiales & Descargas', 'Supports & Téléchargements'],
		['Avaliação Formativa (Quiz)', 'Formative Assessment (Quiz)', 'Evaluación Formativa (Quiz)', 'Évaluation Formative (Quiz)'],
		['Responder Quiz', 'Take Quiz', 'Responder Quiz', 'Passer le Quiz'],
		['Gerenciar Quiz', 'Manage Quiz', 'Gestionar Quiz', 'Gérer le Quiz'],
		['Criar Quiz para esta Aula', 'Create Quiz for this Lesson', 'Crear Quiz para esta Clase', 'Créer un Quiz pour ce Cours'],
		['Dúvidas & Discussões da Aula', 'Lesson Q&A & Discussions', 'Dudas & Discusiones de la Clase', 'Questions & Discussions du Cours'],
		['Enviar Pergunta', 'Post Question', 'Enviar Pregunta', 'Envoyer la Question'],

		// Live Sessions & Attendance
		['Sessões Síncronas & Aulas Ao Vivo', 'Synchronous & Live Sessions', 'Sesiones Síncronas & Clases en Vivo', 'Sessions Synchrones & Cours en Direct'],
		['Agendar Nova Sessão', 'Schedule New Session', 'Programar Nueva Sesión', 'Planifier une Nouvelle Session'],
		['Diário de Presença & Frequência Síncrona', 'Attendance Log & Synchronous Frequency', 'Registro de Asistencia & Frecuencia Síncrona', 'Registre de Présence & Assiduité'],
		['Salvar Diário de Presença', 'Save Attendance Log', 'Guardar Registro de Asistencia', 'Enregistrer le Registre de Présence'],
		['Presente', 'Present', 'Presente', 'Présent'],
		['Ausente', 'Absent', 'Ausente', 'Absent'],
		['Justificado', 'Excused', 'Justificado', 'Justifié'],

		// Assignments & Grading
		['Atividades Práticas & Projetos', 'Practical Assignments & Projects', 'Actividades Prácticas & Proyectos', 'Activités Pratiques & Projets'],
		['Nova Atividade Prática', 'New Practical Assignment', 'Nueva Actividad Práctica', 'Nouvelle Activité Pratique'],
		['Enviar Trabalho', 'Submit Work', 'Enviar Trabajo', 'Soumettre le Travail'],
		['Reenviar Trabalho', 'Resubmit Work', 'Reenviar Trabajo', 'Renvoyer le Travail'],
		['Bancada Docente de Correção', 'Instructor Grading Desk', 'Mesa Docente de Calificación', 'Bureau de Notation Enseignant'],
		['Avaliar Entrega', 'Grade Submission', 'Calificar Entrega', 'Évaluer le Travail'],
		['Aprovado', 'Approved', 'Aprobado', 'Approuvé'],
		['Pendente', 'Pending', 'Pendiente', 'En Attente'],
		['Aprovados', 'Approved', 'Aprobados', 'Approuvés'],
		['Pendentes', 'Pending', 'Pendientes', 'En Attente'],
		['Revisão Solicitada', 'Revision Requested', 'Revisión Solicitada', 'Révision Demandée'],

		// Community & Gamification
		['Fórum & Comunidade da Turma', 'Class Forum & Community', 'Foro & Comunidad del Grupo', 'Forum & Communauté de Promotion'],
		['Espaço de diálogo, colaboração e mentoria com outros alunos e mestres.', 'Space for dialogue, collaboration, and mentorship with peers and instructors.', 'Espacio de diálogo, colaboración y mentoría con otros alumnos y maestros.', 'Espace de dialogue, collaboration et mentorat avec les étudiants et enseignants.'],
		['Novo Tópico', 'New Topic', 'Nuevo Tema', 'Nouveau Sujet'],
		['Conquistas, Insígnias & Ranking', 'Achievements, Badges & Leaderboard', 'Logros, Insignias & Clasificación', 'Succès, Badges & Classement'],
		['Certificação Oficial ArtInnova', 'Official ArtInnova Certification', 'Certificación Oficial ArtInnova', 'Certification Officielle ArtInnova'],
		['Quadro de Requisitos Pedagógicos', 'Pedagogical Requirements Board', 'Cuadro de Requisitos Pedagógicos', 'Tableau des Exigences Pédagogiques'],
		['Emitir Certificado Oficial Agora', 'Issue Official Certificate Now', 'Emitir Certificado Oficial Ahora', 'Émettre le Certificat Officiel'],

		// Admin Desks (Users, Courses, Finance, Accommodations, White Label, Builder, News, Categories, Leads, Gamification)
		['Controle de credenciais, papéis docentes e vínculos contextuais de turmas.', 'Manage credentials, faculty roles, and contextual cohort memberships.', 'Control de credenciales, roles docentes y vínculos contextuales de grupos.', 'Contrôle des identifiants, rôles enseignants et affectations aux promotions.'],
		['Cadastrar Novo Usuário', 'Create New User', 'Registrar Nuevo Usuario', 'Créer un Nouvel Utilisateur'],
		['TOTAL DE USUÁRIOS', 'TOTAL USERS', 'TOTAL DE USUARIOS', 'TOTAL UTILISATEURS'],
		['ESTUDANTES', 'STUDENTS', 'ESTUDIANTES', 'ÉTUDIANTS'],
		['MESTRES / INSTRUTORES', 'MASTERS / INSTRUCTORS', 'MAESTROS / INSTRUCTORES', 'MAÎTRES / INSTRUCTEURS'],
		['COORDENAÇÃO & GESTÃO', 'COORDINATION & ADMIN', 'COORDINACIÓN & GESTIÓN', 'COORDINATION & GESTION'],
		['Estudantes', 'Students', 'Estudiantes', 'Étudiants'],
		['Mestres', 'Instructors', 'Maestros', 'Instructeurs'],
		['Usuário', 'User', 'Usuario', 'Utilisateur'],
		['E-mail', 'Email', 'Correo', 'E-mail'],
		['Papel Global', 'Global Role', 'Rol Global', 'Rôle Global'],
		['Turmas & Papéis', 'Cohorts & Roles', 'Grupos & Roles', 'Promotions & Rôles'],
		['Ações', 'Actions', 'Acciones', 'Actions'],
		['Ação', 'Action', 'Acción', 'Action'],
		['Turmas', 'Cohorts', 'Grupos', 'Promotions'],
		['Editar', 'Edit', 'Editar', 'Modifier'],
		['Excluir', 'Delete', 'Eliminar', 'Supprimer'],
		['Renomear', 'Rename', 'Renombrar', 'Renommer'],
		['Adicionar', 'Add', 'Añadir', 'Ajouter'],
		['Cancelar', 'Cancel', 'Cancelar', 'Annuler'],
		['Fechar', 'Close', 'Cerrar', 'Fermer'],
		['Salvar Alterações', 'Save Changes', 'Guardar Cambios', 'Enregistrer les Modifications'],

		['Cadastrar Novo Curso', 'Create New Course', 'Registrar Nuevo Curso', 'Créer une Nouvelle Formation'],
		['Editar Curso', 'Edit Course', 'Editar Curso', 'Modifier la Formation'],
		['Excluir Curso', 'Delete Course', 'Eliminar Curso', 'Supprimer la Formation'],
		['Gratuito', 'Free', 'Gratuito', 'Gratuit'],
		['HORAS', 'HOURS', 'HORAS', 'HEURES'],
		['Ementa:', 'Syllabus:', 'Temario:', 'Programme :'],

		['Gestão Financeira & Matrículas', 'Financial & Enrollment Management', 'Gestión Financiera & Matrículas', 'Gestion Financière & Inscriptions'],
		['Acompanhe receitas, pedidos e liberação automática de matrículas.', 'Track revenue, orders, and automatic enrollment releases.', 'Siga ingresos, pedidos y liberación automática de matrículas.', 'Suivez les revenus, commandes et validations automatiques d\'inscription.'],
		['Receita Total Aprovada', 'Total Approved Revenue', 'Ingresos Totales Aprobados', 'Revenu Total Approuvé'],
		['Pedidos Aprovados', 'Approved Orders', 'Pedidos Aprobados', 'Commandes Approuvées'],
		['Pedidos Pendentes', 'Pending Orders', 'Pedidos Pendientes', 'Commandes en Attente'],
		['Histórico Recente de Transações', 'Recent Transaction History', 'Historial Reciente de Transacciones', 'Historique Récent des Transactions'],

		['Personalize a identidade da instituição, logotipos, paleta de cores e o modo da página inicial.', 'Customize institution identity, logos, color palette, and homepage mode.', 'Personalice la identidad de la institución, logotipos, paleta de colores y modo de inicio.', 'Personnalisez l\'identité de l\'établissement, les logos, la palette de couleurs et la page d\'accueil.'],
		['Ver Landing Page', 'View Landing Page', 'Ver Landing Page', 'Voir la Page d\'Accueil'],
		['1. Identidade da Marca & Cores', '1. Brand Identity & Colors', '1. Identidad de Marca & Colores', '1. Identité de Marque & Couleurs'],
		['2. Configuração da Página Inicial (Home)', '2. Homepage Configuration', '2. Configuración de la Página de Inicio', '2. Configuration de la Page d\'Accueil'],
		['3. Canais de Atendimento & Redes', '3. Support Channels & Social Media', '3. Canales de Atención & Redes', '3. Canaux de Support & Réseaux'],
		['Salvar Identidade White Label', 'Save White Label Identity', 'Guardar Identidad White Label', 'Enregistrer l\'Identité White Label'],

		['Curso:', 'Course:', 'Curso:', 'Formation :'],
		['Restaurar Padrão', 'Restore Default', 'Restaurar Predeterminado', 'Restaurer par Défaut'],
		['Salvar Landing Page', 'Save Landing Page', 'Guardar Landing Page', 'Enregistrer la Page'],
		['Selecione o Template Base:', 'Select Base Template:', 'Seleccione la Plantilla Base:', 'Sélectionnez le Modèle de Base :'],
		['7 Blocos Modulares', '7 Modular Blocks', '7 Bloques Modulares', '7 Blocs Modulaires'],
		['Use as setas para reordenar', 'Use arrows to reorder', 'Use las flechas para reordenar', 'Utilisez les flèches pour réorganiser'],
		['Atualizar', 'Refresh', 'Actualizar', 'Actualiser'],
		['Ver Página Pública', 'View Public Page', 'Ver Página Pública', 'Voir la Page Publique'],
		['Ativo', 'Active', 'Activo', 'Actif'],
		['Oculto', 'Hidden', 'Oculto', 'Masqué'],

		['Publicar Nova Notícia', 'Publish New Article', 'Publicar Nueva Noticia', 'Publier une Nouvelle Actualité'],
		['Gerencie comunicados oficiais, editais, eventos e artigos do portal institucional.', 'Manage official announcements, calls, events, and articles on the institutional portal.', 'Gestione comunicados oficiales, convocatorias, eventos y artículos del portal.', 'Gérez les communiqués officiels, appels, événements et articles du portail.'],
		['Publicado', 'Published', 'Publicado', 'Publié'],
		['Rascunho', 'Draft', 'Borrador', 'Brouillon'],

		['Organize as áreas temáticas dos Cursos (ex: Arte Mídia, Culinária) e as Categorias de Notícias.', 'Organize course thematic areas (e.g., Media Art, Culinary) and News Categories.', 'Organice las áreas temáticas de los Cursos y las Categorías de Noticias.', 'Organisez les domaines thématiques des formations et les catégories d\'actualités.'],
		['Categorias de Cursos', 'Course Categories', 'Categorías de Cursos', 'Catégories de Formations'],
		['Categorias de Notícias', 'News Categories', 'Categorías de Noticias', 'Catégories d\'Actualités'],

		['Contatos qualificados capturados através da Landing Page do curso e formulários de interesse.', 'Qualified contacts captured via the course Landing Page and interest forms.', 'Contactos calificados capturados a través de la Landing Page y formularios.', 'Contacts qualifiés capturés via la page d\'accueil et les formulaires d\'intérêt.'],
		['Exportar CSV', 'Export CSV', 'Exportar CSV', 'Exporter CSV'],
		['Nenhum lead capturado ainda', 'No leads captured yet', 'Ningún lead capturado aún', 'Aucun prospect capturé pour le moment'],
		['Nome', 'Name', 'Nombre', 'Nom'],
		['Cidade', 'City', 'Ciudad', 'Ville'],
		['Perfil', 'Profile', 'Perfil', 'Profil'],
		['Data', 'Date', 'Fecha', 'Date'],

		['Configure as taxas de premiação em moedas virtuais, XP e cadastre novos emblemas personalizados.', 'Configure virtual coin reward rates, XP, and create custom badges.', 'Configure las tasas de recompensa en monedas virtuales, XP y registre nuevas insignias.', 'Configurez les récompenses en pièces virtuelles, XP et créez des badges personnalisés.'],
		['Novo Emblema / Insígnia', 'New Badge / Emblem', 'Nueva Insignia / Emblema', 'Nouveau Badge / Insigne'],
		['Configuração da Moeda Virtual', 'Virtual Coin Configuration', 'Configuración de la Moneda Virtual', 'Configuration de la Pièce Virtuelle'],
		['Nome da Moeda:', 'Coin Name:', 'Nombre de la Moneda:', 'Nom de la Pièce :'],
		['Símbolo / Sigla:', 'Symbol / Ticker:', 'Símbolo / Sigla:', 'Symbole / Sigle :'],
		['Recompensas por Conquistas Didáticas:', 'Rewards for Learning Milestones:', 'Recompensas por Logros Didácticos:', 'Récompenses par Réussite Pédagogique :'],
		['Moedas por Aula:', 'Coins per Lesson:', 'Monedas por Clase:', 'Pièces par Cours :'],
		['Moedas por Sessão Ao Vivo:', 'Coins per Live Session:', 'Monedas por Sesión en Vivo:', 'Pièces par Session en Direct :'],
		['Moedas por Projeto Prático:', 'Coins per Practical Project:', 'Monedas por Proyecto Práctico:', 'Pièces par Projet Pratique :'],
		['Moedas por Formatura:', 'Coins per Graduation:', 'Monedas por Graduación:', 'Pièces par Diplôme :'],
		['Salvar Regras de Moedas', 'Save Coin Rules', 'Guardar Reglas de Monedas', 'Enregistrer les Règles des Pièces'],
		['Pontos de Experiência (XP)', 'Experience Points (XP)', 'Puntos de Experiencia (XP)', 'Points d\'Expérience (XP)'],
		['Os pontos de XP determinam o nível do estudante na plataforma e destravam privilégios de tutoria e destaque na comunidade.', 'XP points determine the student level on the platform and unlock mentorship privileges and community highlights.', 'Los puntos de XP determinan el nivel del estudiante en la plataforma y desbloquean privilegios en la comunidad.', 'Les points XP déterminent le niveau de l\'étudiant sur la plateforme et débloquent des privilèges dans la communauté.'],
		['XP por Aula Concluída', 'XP per Completed Lesson', 'XP por Clase Completada', 'XP par Cours Terminé'],
		['XP por Presença em Aula Ao Vivo', 'XP per Live Session Attendance', 'XP por Asistencia en Vivo', 'XP par Présence en Direct'],
		['XP por Projeto Prático Aprovado', 'XP per Approved Practical Project', 'XP por Proyecto Práctico Aprobado', 'XP par Projet Pratique Approuvé'],
		['Ícone', 'Icon', 'Icono', 'Icône'],
		['Título', 'Title', 'Título', 'Titre'],
		['Critério / Descrição', 'Criteria / Description', 'Criterio / Descripción', 'Critère / Description'],
		['Recompensa', 'Reward', 'Recompensa', 'Récompense'],
		['Tipo', 'Type', 'Tipo', 'Type'],
		['Personalizado', 'Custom', 'Personalizado', 'Personnalisé'],
		['Sistema', 'System', 'Sistema', 'Système'],
		['Buscar por nome, e-mail ou usuário...', 'Search by name, email, or username...', 'Buscar por nombre, correo o usuario...', 'Rechercher par nom, e-mail ou identifiant...']
	];

	const phraseLookup = {
		'en-US': new Map(),
		'es-ES': new Map(),
		'fr-FR': new Map()
	};

	phraseRows.forEach(([pt, en, es, fr]) => {
		phraseLookup['en-US'].set(pt, en);
		phraseLookup['es-ES'].set(pt, es);
		phraseLookup['fr-FR'].set(pt, fr);
	});

	// Dynamic pattern replacements for strings with numbers/interpolation
	const patternRules = [
		{
			regex: /^Todos \((\d+)\)$/,
			'en-US': 'All ($1)',
			'es-ES': 'Todos ($1)',
			'fr-FR': 'Tous ($1)'
		},
		{
			regex: /^(\d+) Inscritos$/,
			'en-US': '$1 Leads',
			'es-ES': '$1 Inscritos',
			'fr-FR': '$1 Inscrits'
		},
		{
			regex: /^(\d+) áreas$/,
			'en-US': '$1 areas',
			'es-ES': '$1 áreas',
			'fr-FR': '$1 domaines'
		},
		{
			regex: /^(\d+) tipos$/,
			'en-US': '$1 types',
			'es-ES': '$1 tipos',
			'fr-FR': '$1 types'
		},
		{
			regex: /^\((\d+) cursos\)$/,
			'en-US': '($1 courses)',
			'es-ES': '($1 cursos)',
			'fr-FR': '($1 formations)'
		},
		{
			regex: /^\((\d+) notícias\)$/,
			'en-US': '($1 articles)',
			'es-ES': '($1 noticias)',
			'fr-FR': '($1 actualités)'
		},
		{
			regex: /^Emblemas & Insígnias Cadastradas \((\d+)\)$/,
			'en-US': 'Registered Badges & Emblems ($1)',
			'es-ES': 'Insignias & Emblemas Registrados ($1)',
			'fr-FR': 'Badges & Insignes Enregistrés ($1)'
		},
		{
			regex: /^(\d+) HORAS$/,
			'en-US': '$1 HOURS',
			'es-ES': '$1 HORAS',
			'fr-FR': '$1 HEURES'
		},
		{
			regex: /^Nv\.\s*(\d+)$/,
			'en-US': 'Lv. $1',
			'es-ES': 'Nv. $1',
			'fr-FR': 'Niv. $1'
		}
	];

	function translateString(rawText, lang) {
		if (!rawText || typeof rawText !== 'string') return rawText;
		const trimmed = rawText.trim();
		if (!trimmed) return rawText;
		if (lang === 'pt-BR') return rawText;

		const map = phraseLookup[lang];
		if (!map) return rawText;

		const exact = map.get(trimmed);
		if (exact) {
			return rawText.replace(trimmed, exact);
		}

		for (let i = 0; i < patternRules.length; i++) {
			const rule = patternRules[i];
			if (rule.regex.test(trimmed) && rule[lang]) {
				const replaced = trimmed.replace(rule.regex, rule[lang]);
				return rawText.replace(trimmed, replaced);
			}
		}

		return rawText;
	}

	// Standard exchange rates against base BRL
	const rates = {
		'BRL': 1.0,
		'USD': 0.18,  // 1 BRL = ~0.18 USD
		'EUR': 0.17   // 1 BRL = ~0.17 EUR
	};

	const currencySymbols = {
		'BRL': 'R$',
		'USD': '$',
		'EUR': '€'
	};

	let currentLang = localStorage.getItem('stackead_lang') || 'pt-BR';
	let currentCurrency = localStorage.getItem('stackead_currency') || 'BRL';
	let isApplyingTranslations = false;

	function applyTranslations(rootEl = document.body) {
		if (!rootEl || isApplyingTranslations) return;
		isApplyingTranslations = true;

		try {
			// 1. Explicit [data-i18n] elements
			const i18nEls = rootEl.querySelectorAll ? rootEl.querySelectorAll('[data-i18n]') : [];
			i18nEls.forEach(el => {
				const key = el.getAttribute('data-i18n');
				if (!el.dataset.origI18n) {
					el.dataset.origI18n = el.textContent;
				}
				if (currentLang === 'pt-BR') {
					el.textContent = window.StackEAD_I18n.t(key, el.dataset.origI18n);
				} else {
					el.textContent = window.StackEAD_I18n.t(key, translateString(el.dataset.origI18n, currentLang));
				}
			});

			// 2. Sidebar Navigation links by data-view
			const navLinks = document.querySelectorAll('.sidebar-nav .nav-link[data-view]');
			navLinks.forEach(btn => {
				const view = btn.getAttribute('data-view');
				const span = btn.querySelector('span');
				if (span) {
					if (!span.dataset.origText) {
						span.dataset.origText = span.textContent.trim();
					}
					const dictKey = 'nav.' + view;
					const dict = dictionaries[currentLang] || dictionaries['pt-BR'];
					if (currentLang === 'pt-BR') {
						span.textContent = span.dataset.origText;
					} else if (dict[dictKey]) {
						span.textContent = dict[dictKey];
					} else {
						span.textContent = translateString(span.dataset.origText, currentLang);
					}
				}
			});

			// 3. Placeholders & Titles
			const attrEls = rootEl.querySelectorAll ? rootEl.querySelectorAll('[placeholder], [title]') : [];
			attrEls.forEach(el => {
				if (el.hasAttribute('placeholder')) {
					if (el.dataset.origPlaceholder === undefined) {
						el.dataset.origPlaceholder = el.getAttribute('placeholder');
					}
					el.setAttribute(
						'placeholder',
						currentLang === 'pt-BR'
							? el.dataset.origPlaceholder
							: translateString(el.dataset.origPlaceholder, currentLang)
					);
				}
				if (el.hasAttribute('title')) {
					if (el.dataset.origTitle === undefined) {
						el.dataset.origTitle = el.getAttribute('title');
					}
					el.setAttribute(
						'title',
						currentLang === 'pt-BR'
							? el.dataset.origTitle
							: translateString(el.dataset.origTitle, currentLang)
					);
				}
			});

			// 4. Walk text nodes in the DOM for seamless multi-language UI
			const walker = document.createTreeWalker(
				rootEl,
				NodeFilter.SHOW_TEXT,
				{
					acceptNode(node) {
						const parent = node.parentElement;
						if (!parent) return NodeFilter.FILTER_REJECT;
						const tag = parent.tagName;
						if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'TEXTAREA' || tag === 'CODE' || tag === 'PRE') {
							return NodeFilter.FILTER_REJECT;
						}
						if (parent.isContentEditable || parent.closest('[contenteditable="true"]')) {
							return NodeFilter.FILTER_REJECT;
						}
						// Skip nav-link spans already handled above
						if (parent.tagName === 'SPAN' && parent.parentElement && parent.parentElement.classList.contains('nav-link')) {
							return NodeFilter.FILTER_REJECT;
						}
						if (!node.nodeValue || !node.nodeValue.trim()) {
							return NodeFilter.FILTER_REJECT;
						}
						return NodeFilter.FILTER_ACCEPT;
					}
				}
			);

			const textNodes = [];
			let currentNode = walker.nextNode();
			while (currentNode) {
				textNodes.push(currentNode);
				currentNode = walker.nextNode();
			}

			textNodes.forEach(node => {
				if (node.__origText === undefined) {
					node.__origText = node.nodeValue;
				}
				const targetVal = currentLang === 'pt-BR'
					? node.__origText
					: translateString(node.__origText, currentLang);
				if (node.nodeValue !== targetVal) {
					node.nodeValue = targetVal;
				}
			});
		} finally {
			isApplyingTranslations = false;
		}
	}

	window.StackEAD_I18n = {
		t(key, fallback = '') {
			const dict = dictionaries[currentLang] || dictionaries['pt-BR'];
			if (dict[key]) return dict[key];
			if (fallback) return translateString(fallback, currentLang);
			return key;
		},

		translateText(str) {
			return translateString(str, currentLang);
		},

		applyTranslations,

		getLanguage() {
			return currentLang;
		},

		setLanguage(lang) {
			if (dictionaries[lang]) {
				currentLang = lang;
				localStorage.setItem('stackead_lang', lang);
				document.documentElement.setAttribute('lang', lang);
				applyTranslations(document.body);
				window.dispatchEvent(new CustomEvent('stackead:language-changed', { detail: { lang } }));
			}
		},

		getCurrency() {
			return currentCurrency;
		},

		setCurrency(curr) {
			if (rates[curr]) {
				currentCurrency = curr;
				localStorage.setItem('stackead_currency', curr);
				window.dispatchEvent(new CustomEvent('stackead:currency-changed', { detail: { currency: curr } }));
			}
		},

		formatPrice(amountInBrl, targetCurrency = null) {
			const num = parseFloat(amountInBrl) || 0;
			const curr = targetCurrency || currentCurrency;
			const rate = rates[curr] || 1.0;
			const converted = num * rate;

			if (curr === 'BRL') {
				return `R$ ${converted.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
			} else if (curr === 'EUR') {
				return `€ ${converted.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
			} else {
				return `$ ${converted.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
			}
		},

		getCurrencies() {
			return [
				{ code: 'BRL', symbol: 'R$', label: 'R$ Real Brasileiro' },
				{ code: 'USD', symbol: '$', label: '$ Dólar Americano' },
				{ code: 'EUR', symbol: '€', label: '€ Euro' }
			];
		},

		getLanguages() {
			return [
				{ code: 'pt-BR', label: '🇧🇷 Português' },
				{ code: 'en-US', label: '🇺🇸 English' },
				{ code: 'es-ES', label: '🇪🇸 Español' },
				{ code: 'fr-FR', label: '🇫🇷 Français' }
			];
		}
	};

	// Initialize document language and observe dynamic SPA renders
	document.documentElement.setAttribute('lang', currentLang);

	function setupAutoTranslationObserver() {
		if (!document.body) return;
		applyTranslations(document.body);

		let rafPending = false;
		const observer = new MutationObserver(() => {
			if (isApplyingTranslations || rafPending) return;
			rafPending = true;
			requestAnimationFrame(() => {
				rafPending = false;
				applyTranslations(document.body);
			});
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupAutoTranslationObserver);
	} else {
		setupAutoTranslationObserver();
	}
})();
