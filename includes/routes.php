<?php

/* == Routes == */

add_action('rest_api_init', function () {
    register_rest_route('isei/v2', '/home', [
        'methods' => 'GET',
        'callback' => 'qdo_isei_home'
            ], true);

    /* register_rest_route('isei/v2', '/get-ps', [
      'methods' => 'GET',
      'callback' => 'qdo_isei_get_ps'
      ], true);

      register_rest_route('isei/v2', '/get-curso-e-turno-by-ps/(?P<pscode>\d+)', [
      'methods' => 'GET',
      'callback' => 'qdo_isei_get_curso_e_turno_by_ps'
      ], true);

      register_rest_route('isei/v2', '/get-candidato/cpf/(?P<cpf>\d+)', [
      'methods' => 'GET',
      'callback' => 'qdo_isei_ps_get_candidato'
      ], true);

      register_rest_route('isei/v2', '/get-estados', [
      'methods' => 'GET',
      'callback' => 'qdo_isei_get_estados'
      ], true);

      register_rest_route('isei/v2', '/get-cidades', [
      'methods' => 'GET',
      'callback' => 'qdo_isei_ps_get_cidades'
      ], true);

      register_rest_route('isei/v2', '/ps/cadastrar', [
      'methods' => 'POST',
      'callback' => 'qdo_isei_ps_cadastrar'
      ], true);
     */

    register_rest_route('isei/v1', '/rd-code', [
        'methods' => 'GET',
        'callback' => 'qdo_isei_rd_code'
            ], true);

    register_rest_route('isei/v2', '/logout-rdstation/(?P<hash>\w+)', [
        'methods' => 'GET',
        'callback' => 'qdo_isei_logout_rdstation'
            ], true);

    register_rest_route('isei/v2', '/ps/step-1', [
        'methods' => 'POST',
        'callback' => 'qdo_isei_ps_step_1'
            ], true);

    register_rest_route('isei/v2', '/ps/step-2', [
        'methods' => 'POST',
        'callback' => 'qdo_isei_ps_step_2'
            ], true);

    register_rest_route('isei/v2', '/ps/step-21/(?P<pscode>\d+)', [
        'methods' => 'GET',
        'callback' => 'qdo_isei_get_curso_e_turno_by_ps'
            ], true);

    register_rest_route('isei/v2', '/ps/step-3', [
        'methods' => 'POST',
        'callback' => 'qdo_isei_ps_step_3'
            ], true);
});
