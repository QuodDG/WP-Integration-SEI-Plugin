<?php

require_once 'routes.php';
require_once 'QdoJsonRequest.php';
require_once 'QdoJsonResponse.php';
require_once 'miscellaneous.php';
require_once 'RDStation.php';
require_once 'erpSei.php';
require_once 'Controllers.php';

define('QDO_ISEI_VERSION', 'qdo_isei_plugin_version');
define('QDO_ISEI_PLUGIN_SETTING_PAGE', 'qdo-integration-sei/includes/default-page.php');
define('QDO_ISEI_PLUGIN_BASE', 'qdo-integration-sei/qdo-integration-sei.php');
define('QDO_ISEI_PRIVATE_HASH', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855');

define('QDO_PS_POS_GRADUAÇÃO', 'PÓS-GRADUAÇÃO');
define('QDO_PS_VESTIBULAR_ONLINE', 'VESTIBULAR ONLINE');
define('QDO_PS_VESTIBULAR_TRADICIONAL', 'VESTIBULAR TRADICIONAL'); //<-- Presencial
define('QDO_PS_ENEM', 'ENEM');

define('QDO_SEI_BASE_URL', 'qdo_isei_config_base_url');
define('QDO_SEI_WS_URI', 'qdo_isei_config_base_url_ws_uri');
define('QDO_SEI_TOKEN', 'qdo_isei_config_token');
define('QDO_SEI_UE_CODE', 'qdo_isei_config_uecode');
define('QDO_MOODLE_URL_PROVA', 'qdo_isei_config_url_prova');
define('QDO_MOODLE_SENHA', 'qdo_isei_config_senha_padrao');

define('QDO_RD_REFERER_APP', 'qdo_isei_config_rd_referer_app');
define('QDO_RD_CLIENT_ID', 'qdo_isei_config_rd_client_id');
define('QDO_RD_CLIENT_SECRET', 'qdo_isei_config_rd_client_secret');
define('QDO_RD_REDIRECT_URI', 'qdo_isei_config_rd_redirec_uri');
define('QDO_RD_CODE', 'qdo_isei_config_rd_code');
define('QDO_RD_TOKEN', 'qdo_isei_config_rd_token');
define('QDO_RD_REFRESH_TOKEN', 'qdo_isei_config_rd_refresh_token');
define('QDO_RD_TOKEN_EXPIRE_IN', 'qdo_isei_config_rd_token_expire_in');
define('QDO_RD_INTEGRATION_NO_CONFIG', 0);
define('QDO_RD_INTEGRATION_OFF', 1);
define('QDO_RD_INTEGRATION_ON', 2);

$qdo_isei_version = 100;

/* == Dependences == */

add_action('admin_notices', 'qdo_isei_dependences');

function qdo_isei_dependences() {
    //Advanced noCaptcha & invisible Captcha
    if (!is_plugin_active('advanced-nocaptcha-recaptcha/advanced-nocaptcha-recaptcha.php')) {
        echo '<div class="error"><p><strong>QDO Integration SEI</strong>: O plugin "Advanced noCaptcha & invisible Captcha" é necessário.</p></div>';
    } else if (!function_exists('anr_verify_captcha') || !function_exists('anr_get_option')) {
        echo '<div class="error"><p><strong>QDO Integration SEI</strong>: O plugin "Advanced noCaptcha & invisible Captcha" está em um versão incompatível.</p></div>';
    }
}

/* == Styles and Scripts == */

add_action('wp_enqueue_scripts', 'qdo_add_styles_scripts', 999);

function qdo_add_styles_scripts() {
    global $qdo_isei_version;

    //Styles
    wp_register_style('qdo-isei-css', plugins_url('css/default.css', __FILE__), [], $qdo_isei_version);
    wp_enqueue_style('qdo-isei-css');

    //Scripts
    wp_register_script('qdo-isei-js', plugins_url('js/default.js', __FILE__), ['jquery'], $qdo_isei_version, true);
    wp_enqueue_script('qdo-isei-js');

    //Pass nonce to JS.
    wp_localize_script('qdo-isei-js', 'qdoIseiSettings', [
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
}

add_action('admin_enqueue_scripts', 'qdo_add_admin_style_script', 999);

function qdo_add_admin_style_script($hook) {
    global $qdo_isei_version;

    if (QDO_ISEI_PLUGIN_SETTING_PAGE !== $hook) {
        return;
    }

    //Scripts
    wp_register_script('qdo-isei-admin-js', plugins_url('js/admin-default.js', __FILE__), ['jquery'], $qdo_isei_version, true);
    wp_enqueue_script('qdo-isei-admin-js');

    //Pass nonce to JS.
    wp_localize_script('qdo-isei-admin-js', 'qdoIseiSettings', [
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
}

/* == Initials == */

function qdo_isei_chk_version() {
    global $qdo_isei_version;

    if (empty(get_option(QDO_ISEI_VERSION))) {
        add_option(QDO_ISEI_VERSION, $qdo_isei_version);
    } else if (get_option(QDO_ISEI_VERSION) != $qdo_isei_version) {
        update_option(QDO_ISEI_VERSION, $qdo_isei_version);
    }
}

/* == Settings Page == */

add_action('admin_menu', 'qdo_isei_add_admin_menu');

function qdo_isei_add_admin_menu() {
    add_menu_page('Integração SEI', 'Integração SEI', 'manage_options', QDO_ISEI_PLUGIN_SETTING_PAGE, '', 'dashicons-randomize');
}

add_filter('plugin_action_links_' . QDO_ISEI_PLUGIN_BASE, 'qdo_isei_setting_link');

function qdo_isei_setting_link($links) {
    $url = qdo_isei_get_setting_page_url();
    $setting_link = "<a href=\"{$url}\">" . __('Settings') . "</a>";
    $links[] = $setting_link;
    return $links;
}

/* == Options == */

function qdo_isei_default_map_options() {

    return [
        'API SEI' => [
            [
                'classes' => 'col-lx-6 col-lg-6 col-md-12 col-sm-12 col-12',
                'label' => 'URL Base',
                'type' => 'url',
                'name' => QDO_SEI_BASE_URL,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                'place_holder' => '',
                'field_type' => 'input',
            ],
            [
                'classes' => 'col-lx-6 col-lg-6 col-md-12 col-sm-12 col-12',
                'label' => 'WS URI',
                'type' => 'text',
                'name' => QDO_SEI_WS_URI,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                'place_holder' => '',
                'field_type' => 'input',
            ],
            [
                'classes' => 'col-lx-12 col-lg-12 col-md-12 col-sm-12 col-12',
                'label' => 'Token',
                'type' => 'text',
                'name' => QDO_SEI_TOKEN,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                'place_holder' => '',
                'field_type' => 'input',
            ],
            /*[
                'classes' => 'col-lx-6 col-lg-6 col-md-12 col-sm-12 col-12',
                'label' => 'Código da Unidade de Ensino',
                'type' => 'number',
                'name' => QDO_SEI_UE_CODE,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                'place_holder' => '',
                'field_type' => 'input',
            ],*/
            [
                'classes' => 'col-lx-6 col-lg-6 col-md-12 col-sm-12 col-12',
                'label' => 'Unidade de Ensino',
                //'type' => 'number',
                'name' => QDO_SEI_UE_CODE,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                //'place_holder' => '',
                'field_type' => 'select',
                'options' => 'qdo_isei_parse_unidade_ensino_2_options',
                'depend_on' => [QDO_SEI_BASE_URL, QDO_SEI_WS_URI, QDO_SEI_TOKEN]
            ],
        ],
        'Moodle' => [
            [
                'classes' => 'col-lx-12 col-lg-12 col-md-12 col-sm-12 col-12',
                'label' => 'Url da Prova',
                'type' => 'url',
                'name' => QDO_MOODLE_URL_PROVA,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                'place_holder' => '',
                'field_type' => 'input',
            ],
            [
                'classes' => 'col-lx-12 col-lg-12 col-md-12 col-sm-12 col-12',
                'label' => 'Senha padrão para acessar a prova',
                'type' => 'text',
                'name' => QDO_MOODLE_SENHA,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                'place_holder' => '',
                'field_type' => 'input',
            ],
        ],
        'RD Station' => [
            [
                'classes' => 'col-lx-12 col-lg-12 col-md-12 col-sm-12 col-12',
                'label' => 'Domínio do RD Station APP',
                'type' => 'text',
                'name' => QDO_RD_REFERER_APP,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                'place_holder' => 'app.rdstation.com.br',
                'field_type' => 'input',
            ],
            [
                'classes' => 'col-lx-6 col-lg-6 col-md-12 col-sm-12 col-12',
                'label' => 'Client ID',
                'type' => 'text',
                'name' => QDO_RD_CLIENT_ID,
                'id' => 'i__rd-client_id',
                'value' => '',
                'status_classes' => '',
                'place_holder' => '',
                'field_type' => 'input',
            ],
            [
                'classes' => 'col-lx-6 col-lg-6 col-md-12 col-sm-12 col-12',
                'label' => 'Client secret',
                'type' => 'text',
                'name' => QDO_RD_CLIENT_SECRET,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                'place_holder' => '',
                'field_type' => 'input',
            ],
            [
                'classes' => 'col-lx-12 col-lg-12 col-md-12 col-sm-12 col-12',
                'label' => 'URI de Redirecionamento',
                'type' => 'url',
                'name' => QDO_RD_REDIRECT_URI,
                'id' => '',
                'value' => '',
                'status_classes' => '',
                'place_holder' => 'app.rdstation.com.br',
                'field_type' => 'input',
            ],
        ],
    ];
}

/* == Shortcode == */
//[isei_form proc_type=vestibular-online ano=2022 semestre=1 campanha=vestibular2022.1-1ed]
//[isei_form proc_type=enem ano=2022 semestre=1 campanha=vestibular2022.1-1ed]
add_shortcode('isei_form', 'qdo_isei_form');

function qdo_isei_form(array $atts) {
    $atts = shortcode_atts([
        'proc_type' => '',
        'ano' => '',
        'semestre' => '',
        'campanha' => '',
            ], $atts, 'isei_form');

    foreach ($atts as $att) {
        if (empty($att)) {
            return qdo_isei_render_template('message-form', [
                'title' => 'Atenção',
                'message' => 'O formulário não pode ser exibido. #001',
                'status_class' => 'error'
            ]);
        }
    }

    //Verifica se há algum processo seletivo aberto
    $ps = qdo_isei_get_ps_by_type(qdo_isei_get_proc_type_name($atts['proc_type']));
    if (qdo_isei_request_fail($ps)) {
        return qdo_isei_render_template('message-form', [
            'title' => 'Atenção',
            'message' => 'Não foi possível se conectar ao servidor. #001',
            'status_class' => 'error'
        ]);
    }

    if (count($ps->procSeletivo) <= 0) {
        return qdo_isei_render_template('message-form', [
            'title' => 'Inscrições Encerradas',
            'message' => 'As inscrições para este tipo de processo seletivo estão encerradas.',
            'status_class' => 'info'
        ]);
    }

    return qdo_isei_render_template('form', [
        'rsk' => anr_get_option('site_key'),
        'proc_type' => $atts['proc_type'],
        'ano' => $atts['ano'],
        'semestre' => $atts['semestre'],
        'campanha' => $atts['campanha'],
    ]);
}
