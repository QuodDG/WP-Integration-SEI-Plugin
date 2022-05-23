<?php

/*
  Plugin Name: Integração SEI
  Plugin URI:
  Description: Plugin para integrar funções do PS do SEI da Otimize e RD Station Marketing
  Author: QuodDG
  Version: 1.0.4
  Author URI: https://github.com/QuodDG
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

register_activation_hook( __FILE__, 'qdo_isei_chk_version' );