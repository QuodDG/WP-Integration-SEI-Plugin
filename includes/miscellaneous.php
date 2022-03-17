<?php

/* == Miscellaneous == */

function qdo_isei_get_setting_page_url()
{
    return esc_url(add_query_arg(
        'page',
        'qdo-integration-sei/includes/default-page.php',
        get_admin_url() . 'admin.php'
    ));
}

function qdo_isei_chk_input($type, $var_name, $chk_empty = true)
{
    $in = filter_input($type, $var_name);
    if (($chk_empty && empty($in)) ||
        is_null($in) ||
        $in === false
    ) {
        return false;
    }

    return $in;
}

function qdo_isei_get_map_options()
{
    $map = qdo_isei_default_map_options();
    foreach ($map as $section => $options) {
        foreach ($options as $i => $opt) {
            $map[$section][$i]['value'] = get_option($opt['name'], '');
        }
    }
    return $map;
}

function qdo_isei_get_map_option(string $option_name)
{
    $map = qdo_isei_default_map_options();
    foreach ($map as $options) {
        foreach ($options as $opt) {
            if ($option_name === $opt['name']) {
                return get_option($opt['name'], '');
            }
        }
    }
    return '';
}

function qdo_isei_save_option($option_name, $value)
{
    $striped_value = strip_tags($value);

    return is_null(get_option($option_name, null)) ? add_option($option_name, $striped_value) : (get_option($option_name) !== $striped_value ? update_option($option_name, $striped_value) : true);
}

function qdo_isei_save_options(array &$map)
{
    $result = true;

    foreach ($map as $section => $options) {
        foreach ($options as $i => $opt) {
            $value = qdo_isei_chk_input(INPUT_POST, $opt['name']);

            if ($value === false) {
                delete_option($opt['name']);
                $map[$section][$i]['value'] = '';
                //$map[$section][$i]['status'] = false;
                continue;
            }

            if (!qdo_isei_save_option($opt['name'], $value)) {
                //$map[$section][$i]['value'] = '';
                $map[$section][$i]['status_classes'] = ' border border-danger';
                $result = false;
                continue;
            }

            $map[$section][$i]['value'] = $value;
            $map[$section][$i]['status_classes'] = '';
        }
    }

    return $result;
}

function qdo_isei_render_template($template, array $args = [])
{
    ob_start();
    extract($args);
    require dirname(__FILE__) . "/templates/{$template}.php";

    return ob_get_clean();
}

function qdo_isei_convert_mensagem($mensagem)
{
    if (strstr($mensagem, 'Failed to connect to')) {
        $mensagem = 'Não foi possível conectar ao servidor';
    }
    return $mensagem;
}

function qdo_isei_get_grecaptcha_site_key()
{
    return trim(c4wp_get_option('site_key'));
}

function qdo_isei_verify_recaptcha_v3(string $response)
{
    return c4wp_verify_captcha($response);
}

function qdo_isei_get_user_ip()
{
    if (!empty(filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP'))) {
        $ip = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP');
    } else if (!empty(filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR'))) {
        $ip = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR');
    } else {
        $ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
    }
    return $ip;
}

function qdo_isei_get_proc_type_name($type)
{
    switch ($type) {
        case 'pos':
            return QDO_PS_POS_GRADUAÇÃO;
        case 'vestibular-tradicional':
            return QDO_PS_VESTIBULAR_TRADICIONAL;
        case 'vestibular-online':
            return QDO_PS_VESTIBULAR_ONLINE;
        case 'enem':
            return QDO_PS_ENEM;
        default:
            return '';
    }
}

function qdo_isei_get_tipo_de_inscricao_vestibular($proc_type)
{
    switch ($proc_type) {
        case QDO_PS_POS_GRADUAÇÃO:
            return 'Pós-graduação';
        case QDO_PS_VESTIBULAR_TRADICIONAL:
            return 'Vestibular Tradicional';
        case QDO_PS_VESTIBULAR_ONLINE:
            return 'Vestibular Online';
        case QDO_PS_ENEM:
            return 'ENEM';
        default:
            return '';
    }
}

function qdo_isei_request_fail($request)
{
    return !is_object($request) || (isset($request->statusCode) && $request->statusCode == 0);
}

function qdo_isei_clear_cpf(string $cpf)
{
    return preg_replace('/[^0-9]/is', '', $cpf);
}

function qdo_isei_check_depend_on(array $options, array $opt_depend)
{
    foreach ($options as $opt) {
        foreach ($opt_depend['depend_on'] as $depend) {
            if ($opt['name'] === $depend && empty($opt['value'])) {
                return false;
            }
        }
    }

    return true;
}

function qdo_isei_has_depend(array $opt)
{
    return array_key_exists('depend_on', $opt);
}
