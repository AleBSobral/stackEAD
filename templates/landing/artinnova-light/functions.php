<?php
/**
 * ArtInnova Light Residency Theme Functions
 * Standalone Theme for ArtInnova International Residency
 */

if (!defined('ABSPATH')) {
    exit;
}

// 0. Security & Hardening (Disable XML-RPC, Hide WP Version, Send Security Headers)
add_filter('xmlrpc_enabled', '__return_false');
remove_action('wp_head', 'wp_generator');

add_action('send_headers', function () {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
});

// 1. Theme Setup
function artinnova_light_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ]);
}
add_action('after_setup_theme', 'artinnova_light_setup');

// 2. Enqueue Styles & Scripts
function artinnova_light_enqueue_assets() {
    $theme_version = '2.0.2';
    $theme_uri = get_template_directory_uri();

    // Google Fonts
    wp_enqueue_style('artinnova-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap', [], null);

    // Main Design System Stylesheet (Light Theme)
    wp_enqueue_style('artinnova-light-main', $theme_uri . '/assets/css/main.css', [], $theme_version);

    // Vendor: GSAP 3 & ScrollTrigger
    wp_enqueue_script('gsap', $theme_uri . '/assets/js/gsap.min.js', [], '3.12.5', true);
    wp_enqueue_script('scrolltrigger', $theme_uri . '/assets/js/ScrollTrigger.min.js', ['gsap'], '3.12.5', true);

    // Fallback Translation Bundle
    wp_enqueue_script('artinnova-translations', $theme_uri . '/assets/js/translations-data.js', [], $theme_version, true);

    // Main App Logic (Light pixel particle engine, i18n, motion, AJAX)
    wp_enqueue_script('artinnova-light-app', $theme_uri . '/assets/js/app.js', ['gsap', 'scrolltrigger', 'artinnova-translations'], $theme_version, true);

    // Localize parameters for AJAX & dynamic asset paths
    wp_localize_script('artinnova-light-app', 'artinnova_ajax', [
        'ajax_url'     => admin_url('admin-ajax.php'),
        'nonce'        => wp_create_nonce('artinnova_lead_nonce'),
        'theme_uri'    => $theme_uri,
        'locales_path' => $theme_uri . '/locales/'
    ]);
}
add_action('wp_enqueue_scripts', 'artinnova_light_enqueue_assets');

// 3. Register Custom Post Type for Leads / Inscrições
function artinnova_light_register_lead_cpt() {
    $labels = [
        'name'               => 'Inscrições ArtInnova',
        'singular_name'      => 'Inscrição',
        'menu_name'          => 'Inscrições ArtInnova',
        'name_admin_bar'     => 'Inscrição ArtInnova',
        'add_new'            => 'Nova Inscrição',
        'add_new_item'       => 'Adicionar Nova Inscrição',
        'new_item'           => 'Nova Inscrição',
        'edit_item'          => 'Visualizar Inscrição',
        'view_item'          => 'Ver Inscrição',
        'all_items'          => 'Todas as Inscrições',
        'search_items'       => 'Buscar Inscrições',
        'not_found'          => 'Nenhuma inscrição encontrada.',
        'not_found_in_trash' => 'Nenhuma inscrição na lixeira.'
    ];

    $args = [
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-art',
        'supports'           => ['title', 'editor', 'custom-fields'],
        'show_in_rest'       => false
    ];

    register_post_type('artinnova_lead', $args);
}
add_action('init', 'artinnova_light_register_lead_cpt');

// Custom Columns for Leads Admin List
function artinnova_light_lead_columns($columns) {
    return [
        'cb'            => $columns['cb'],
        'title'         => 'Nome do Candidato',
        'lead_email'    => 'E-mail',
        'lead_location' => 'Cidade / Estado / País',
        'lead_profile'  => 'Perfil',
        'lead_referral' => 'Origem / Indicação',
        'date'          => 'Data do Registro'
    ];
}
add_filter('manage_artinnova_lead_posts_columns', 'artinnova_light_lead_columns');

function artinnova_light_lead_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'lead_email':
            $email = get_post_meta($post_id, '_artinnova_email', true);
            echo $email ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>' : '—';
            break;
        case 'lead_location':
            $city = get_post_meta($post_id, '_artinnova_city', true);
            if (!$city) {
                $city = get_post_meta($post_id, '_artinnova_phone', true);
            }
            echo $city ? esc_html($city) : '—';
            break;
        case 'lead_profile':
            $profile = get_post_meta($post_id, '_artinnova_profile', true);
            echo $profile ? esc_html($profile) : '—';
            break;
        case 'lead_referral':
            $referral = get_post_meta($post_id, '_artinnova_referral', true);
            if (!$referral) {
                $referral = get_post_meta($post_id, '_artinnova_country', true);
            }
            echo $referral ? esc_html($referral) : '—';
            break;
    }
}
add_action('manage_artinnova_lead_posts_custom_column', 'artinnova_light_lead_custom_column_content', 10, 2);

// 4. AJAX Handler for Form Submissions (Public Lead Capture)
function artinnova_light_handle_lead_submission() {
    // Optional bot honeypot protection
    if (!empty($_POST['website_hp'])) {
        wp_send_json_success(['message' => 'Ok']);
        exit;
    }

    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '0.0.0.0';

    // Rate limiting: max 6 submissions per 10 minutes per IP
    $rl_key = 'artinnova_lead_rl_' . md5($ip);
    $rl_cnt = (int) get_transient($rl_key);
    if ($rl_cnt >= 6) {
        wp_send_json_error([
            'message' => 'Muitas solicitações enviadas em sequência. Aguarde alguns minutos e tente novamente.'
        ], 429);
    }
    set_transient($rl_key, $rl_cnt + 1, 600);

    $raw_name = isset($_POST['name']) ? sanitize_text_field(trim($_POST['name'])) : '';
    $name     = trim(str_replace(["\r", "\n"], ' ', $raw_name));
    $email    = isset($_POST['email']) ? sanitize_email(trim($_POST['email'])) : '';
    $city     = isset($_POST['city']) ? sanitize_text_field(trim($_POST['city'])) : (isset($_POST['phone']) ? sanitize_text_field(trim($_POST['phone'])) : '');
    $profile  = isset($_POST['profile']) ? sanitize_text_field(trim($_POST['profile'])) : '';
    $referral = isset($_POST['referral']) ? sanitize_text_field(trim($_POST['referral'])) : (isset($_POST['country']) ? sanitize_text_field(trim($_POST['country'])) : '');
    $lang     = isset($_POST['lang']) ? strtolower(sanitize_text_field(trim($_POST['lang']))) : 'pt';

    if (!in_array($lang, ['pt', 'en', 'es', 'fr'], true)) {
        $lang = 'pt';
    }

    if (empty($name) || empty($email) || !is_email($email)) {
        $err_messages = [
            'en' => 'Please provide your full name and a valid email address.',
            'es' => 'Por favor, ingrese su nombre y un correo electrónico válido.',
            'fr' => 'Veuillez indiquer votre nom complet et une adresse e-mail valide.',
            'pt' => 'Por favor, informe seu nome completo e um e-mail válido.'
        ];
        wp_send_json_error([
            'message' => isset($err_messages[$lang]) ? $err_messages[$lang] : $err_messages['pt']
        ], 400);
    }

    $timestamp = current_time('mysql');

    $post_content = sprintf(
        "Candidato: %s\nE-mail: %s\nCidade / Estado / País: %s\nPerfil: %s\nOrigem / Indicação: %s\nIdioma da Inscrição: %s\nIP: %s\nData: %s",
        $name,
        $email,
        $city ?: 'Não informado',
        $profile ?: 'Não informado',
        $referral ?: 'Não informado',
        strtoupper($lang),
        $ip,
        $timestamp
    );

    $post_id = wp_insert_post([
        'post_title'   => wp_strip_all_tags($name),
        'post_type'    => 'artinnova_lead',
        'post_status'  => 'publish',
        'post_content' => $post_content
    ]);

    if (is_wp_error($post_id)) {
        wp_send_json_error([
            'message' => 'Erro ao processar inscrição. Por favor, tente novamente ou entre em contato diretamente pelo e-mail contact@artinnovaresidency.com.'
        ], 500);
    }

    update_post_meta($post_id, '_artinnova_email', $email);
    update_post_meta($post_id, '_artinnova_city', $city);
    update_post_meta($post_id, '_artinnova_phone', $city); // compatibility
    update_post_meta($post_id, '_artinnova_profile', $profile);
    update_post_meta($post_id, '_artinnova_referral', $referral);
    update_post_meta($post_id, '_artinnova_country', $referral); // compatibility
    update_post_meta($post_id, '_artinnova_lang', $lang);

    // Also sync into stackead_leads CRM table if StackEAD Core is active
    global $wpdb;
    $leads_table = $wpdb->prefix . 'stackead_leads';
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $leads_table))) {
        $wpdb->insert(
            $leads_table,
            [
                'course_id'  => 0,
                'name'       => $name,
                'email'      => $email,
                'phone'      => '',
                'city'       => $city,
                'profile'    => $profile,
                'referral'   => $referral ? $referral . ' (' . strtoupper($lang) . ')' : strtoupper($lang),
                'status'     => 'new',
                'ip_address' => $ip,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    // 1. Send Notification Email to the Residency Team
    $admin_email  = 'contact@artinnovaresidency.com';
    $team_subject = sprintf('[Residência ArtInnova] Nova Manifestação de Interesse: %s (%s)', $name, strtoupper($lang));
    $team_body    = "Uma nova manifestação de interesse foi registrada no site da Residência ArtInnova:\n\n"
                  . "--------------------------------------------------\n"
                  . "Nome do Candidato: " . $name . "\n"
                  . "E-mail: " . $email . "\n"
                  . "Cidade / Estado / País: " . ($city ?: 'Não informado') . "\n"
                  . "Perfil Principal: " . ($profile ?: 'Não informado') . "\n"
                  . "Quem Indicou / Origem: " . ($referral ?: 'Não informado') . "\n"
                  . "Idioma Selecionado: " . strtoupper($lang) . "\n"
                  . "Data e Hora: " . current_time('d/m/Y H:i:s') . "\n"
                  . "IP do Solicitante: " . $ip . "\n"
                  . "--------------------------------------------------\n\n"
                  . "Para responder diretamente ao candidato, basta clicar em 'Responder' a este e-mail.\n\n"
                  . "Acessar ficha no painel WordPress: " . admin_url('post.php?post=' . $post_id . '&action=edit') . "\n";

    $team_headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: Residência ArtInnova <contact@artinnovaresidency.com>',
        'Reply-To: ' . $name . ' <' . $email . '>'
    ];

    @wp_mail($admin_email, $team_subject, $team_body, $team_headers);

    // 2. Send Auto-Reply Confirmation Email to the Applicant
    $applicant_headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: Residência ArtInnova <contact@artinnovaresidency.com>',
        'Reply-To: Residência ArtInnova <contact@artinnovaresidency.com>'
    ];

    switch ($lang) {
        case 'en':
            $applicant_subject = '[ArtInnova Residency] Expression of interest received!';
            $applicant_body    = "Hello, {$name}!\n\n"
                               . "Thank you for your interest in the ArtInnova International Residency.\n\n"
                               . "We have successfully received your expression of interest. Our coordination team will review your submission and contact you shortly at this email address with full details regarding the open call, schedule, and next steps.\n\n"
                               . "Summary of your submission:\n"
                               . "• Name: {$name}\n"
                               . "• Email: {$email}\n"
                               . "• Location: " . ($city ?: 'Not specified') . "\n"
                               . "• Profile: " . ($profile ?: 'Not specified') . "\n\n"
                               . "If you have any questions, feel free to reply directly to this message or write to us at contact@artinnovaresidency.com.\n\n"
                               . "Warm regards,\n"
                               . "ArtInnova Residency Team\n"
                               . "https://artinnovaresidency.com\n";
            $response_message  = 'Expression of interest sent successfully! We have sent a confirmation email to you and our team will get in touch shortly.';
            break;

        case 'es':
            $applicant_subject = '[Residencia ArtInnova] ¡Hemos recibido su manifestación de interés!';
            $applicant_body    = "¡Hola, {$name}!\n\n"
                               . "Agradecemos su interés en la Residencia Internacional ArtInnova.\n\n"
                               . "Hemos recibido con éxito su manifestación de interés. Nuestro equipo de coordinación se pondrá en contacto muy pronto a través de este correo electrónico con los detalles de la convocatoria, cronograma y los próximos pasos.\n\n"
                               . "Resumen de su postulación:\n"
                               . "• Nombre: {$name}\n"
                               . "• Correo electrónico: {$email}\n"
                               . "• Ubicación: " . ($city ?: 'No especificado') . "\n"
                               . "• Perfil: " . ($profile ?: 'No especificado') . "\n\n"
                               . "Si tiene alguna consulta, puede responder directamente a este correo o escribirnos a contact@artinnovaresidency.com.\n\n"
                               . "Saludos cordiales,\n"
                               . "Equipo Residencia ArtInnova\n"
                               . "https://artinnovaresidency.com\n";
            $response_message  = '¡Manifestación de interés enviada con éxito! Le hemos enviado un correo de confirmación y nuestro equipo se comunicará pronto.';
            break;

        case 'fr':
            $applicant_subject = '[Résidence ArtInnova] Nous avons bien reçu votre manifestation d\'intérêt !';
            $applicant_body    = "Bonjour, {$name} !\n\n"
                               . "Nous vous remercions de votre intérêt pour la Résidence Internationale ArtInnova.\n\n"
                               . "Nous vous confirmons la bonne réception de votre manifestation d'intérêt. Notre équipe de coordination reviendra très prochainement vers vous par cet e-mail avec toutes les précisions concernant l'appel à candidatures, le calendrier et les étapes suivantes.\n\n"
                               . "Récapitulatif de votre demande :\n"
                               . "• Nom : {$name}\n"
                               . "• E-mail : {$email}\n"
                               . "• Localisation : " . ($city ?: 'Non spécifié') . "\n"
                               . "• Profil : " . ($profile ?: 'Non spécifié') . "\n\n"
                               . "Pour toute question, n'hésitez pas à répondre directement à ce message ou à nous écrire à contact@artinnovaresidency.com.\n\n"
                               . "Bien cordialement,\n"
                               . "L'équipe de la Résidence ArtInnova\n"
                               . "https://artinnovaresidency.com\n";
            $response_message  = 'Manifestation d\'intérêt envoyée avec succès ! Nous vous avons envoyé un e-mail de confirmation et notre équipe vous contactera bientôt.';
            break;

        default: // 'pt'
            $applicant_subject = '[Residência ArtInnova] Recebemos sua manifestação de interesse!';
            $applicant_body    = "Olá, {$name}!\n\n"
                               . "Agradecemos seu interesse na Residência Internacional ArtInnova.\n\n"
                               . "Confirmamos o recebimento da sua manifestação de interesse! Nossa equipe de coordenação já recebeu seus dados e entrará em contato em breve através deste e-mail com todas as orientações sobre a convocatória, cronograma e próximos passos.\n\n"
                               . "Resumo dos seus dados:\n"
                               . "• Nome: {$name}\n"
                               . "• E-mail: {$email}\n"
                               . "• Localização: " . ($city ?: 'Não informada') . "\n"
                               . "• Perfil Principal: " . ($profile ?: 'Não informado') . "\n\n"
                               . "Caso tenha alguma dúvida, basta responder diretamente a esta mensagem ou escrever para contact@artinnovaresidency.com.\n\n"
                               . "Com estima,\n"
                               . "Equipe Residência ArtInnova\n"
                               . "https://artinnovaresidency.com\n";
            $response_message  = 'Manifestação enviada com sucesso! Enviamos um e-mail de confirmação para você e nossa equipe entrará em contato em breve.';
            break;
    }

    @wp_mail($email, $applicant_subject, $applicant_body, $applicant_headers);

    wp_send_json_success([
        'message' => $response_message,
        'lead_id' => $post_id
    ]);
}
add_action('wp_ajax_artinnova_submit_lead', 'artinnova_light_handle_lead_submission');
add_action('wp_ajax_nopriv_artinnova_submit_lead', 'artinnova_light_handle_lead_submission');
