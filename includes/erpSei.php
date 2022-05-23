<?php

/* == ERP SEI Functions == */

function qdo_isei_parse_api_url(string $uri) {
    return qdo_isei_get_map_option(QDO_SEI_BASE_URL) . qdo_isei_get_map_option(QDO_SEI_WS_URI) . $uri;
}

function qdo_isei_get_unidades_ensino() {
    return qdo_isei_get_unidade_ensino_by_ps(0);
}

function qdo_isei_get_unidade_ensino_by_ps($pscode) {
    $url = qdo_isei_parse_api_url("/consultarUnidadeEnsinoProcessoSeletivo/{$pscode}");
    return QdoJsonRequest::get((object) [
                        'url' => $url,
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN)
    ]);
}

function qdo_isei_get_all_ps() {
    $url = qdo_isei_parse_api_url(
            "/consultarProcessoSeletivo/"
            . qdo_isei_get_map_option(QDO_SEI_UE_CODE)
            . "/0/0/TODOS");
    return QdoJsonRequest::get((object) [
                        'url' => $url,
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN)
    ]);
}

function qdo_isei_get_ps_by_type($proc_type) {
    $r = qdo_isei_get_all_ps();
    if(is_null($r)){
        return $r;
    }
    if (qdo_isei_request_fail($r)) {
        $r->mensagem = qdo_isei_convert_mensagem($r->mensagem);
        return $r;
    }
    if (isset($r->procSeletivo) && !is_array($r->procSeletivo)) {
        $r->procSeletivo = [$r->procSeletivo];
    }
    $tmp = $r->procSeletivo;
    $r->procSeletivo = [];
    foreach ($tmp as $ps) {
        if (strstr(mb_convert_case($ps->descricao, MB_CASE_UPPER), $proc_type) !== false) {
            $r->procSeletivo[] = $ps;
        }
    }
    $r->proc_type = $proc_type;
    return $r;
}

function qdo_isei_get_ps_by_code($code) {
    $r = qdo_isei_get_all_ps();
    if(is_null($r)){
        return $r;
    }
    if (qdo_isei_request_fail($r)) {
        $r->mensagem = qdo_isei_convert_mensagem($r->mensagem);
        return $r;
    }
    if (isset($r->procSeletivo) && !is_array($r->procSeletivo)) {
        $r->procSeletivo = [$r->procSeletivo];
    }
    foreach ($r->procSeletivo as $ps) {
        if ($ps->codigo == $code) {
            return $ps;
        }
    }
    return (object) [
                'statusCode' => 0,
                'mensagem' => 'Processo Seletivo não encontrado'
    ];
}

function qdo_isei_get_all_estados() {
    $url = qdo_isei_parse_api_url("/consultarEstado");
    return QdoJsonRequest::get((object) [
                        'url' => $url,
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN)
    ]);
}

function qdo_isei_get_curso_by_ps($pscode) {
    $url = qdo_isei_parse_api_url("/consultarCursoProcessoSeletivo/{$pscode}/"
            . qdo_isei_get_map_option(QDO_SEI_UE_CODE));
    return QdoJsonRequest::get((object) [
                        'url' => $url,
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN)
    ]);
}

function qdo_isei_get_unidade_ensino_curso_by_ps_curso($pscode, $uni_ens_curso) {
    $r = qdo_isei_get_curso_by_ps($pscode);
    if (qdo_isei_request_fail($r)) {
        $r->mensagem = qdo_isei_convert_mensagem($r->mensagem);
        return $r;
    }
    if (isset($r->procSeletivoCurso) && !is_array($r->procSeletivoCurso)) {
        $r->procSeletivoCurso = [$r->procSeletivoCurso];
    }
    foreach ($r->procSeletivoCurso as $procSeletivoCurso) {
        if ($procSeletivoCurso->unidadeEnsinoCurso->codigo == $uni_ens_curso) {
            return $procSeletivoCurso->unidadeEnsinoCurso;
        }
    }
    return (object) [
                'statusCode' => 0,
                'mensagem' => 'Unidade de Ensino Curso não encontrado'
    ];
}

function qdo_isei_get_candidato_by_cpf(string $cpf) {
    $url = qdo_isei_parse_api_url("/consultarCandidatoProcessoSeletivo/CPF/{$cpf}");
    return QdoJsonRequest::get((object) [
                        'url' => $url,
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN)
    ]);
}

/* function qdo_isei_get_candidato_by_email(string $email) {
  $url = qdo_isei_parse_api_url("/consultarCandidatoProcessoSeletivo/email/{$email}");
  return QdoJsonRequest::get((object) [
  'url' => $url,
  'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN)
  ]);
  } */

function qdo_isei_get_inscricao_ativa_candidato_by_cpf(string $cpf) {
    $url = qdo_isei_parse_api_url("/consultarInscricaoAtivaCandidatoProcessoSeletivo/CPF/{$cpf}/INSCRICAO");
    return QdoJsonRequest::get((object) [
                        'url' => $url,
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN)
    ]);
}

function qdo_isei_get_cidades_by_estado(string $estado, string $nomeCidade) {
    if ($nomeCidade === '') {
        $url = qdo_isei_parse_api_url("/consultarCidade/{$estado}/%20");
    } else {
        $url = qdo_isei_parse_api_url("/consultarCidade/{$estado}/{$nomeCidade}");
    }
    return QdoJsonRequest::get((object) [
                        'url' => $url,
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN)
    ]);
}

function qdo_isei_get_data_prova_by_ps($pscode) {
    $url = qdo_isei_parse_api_url("/consultarDataProvaProcessoSeletivo/{$pscode}");
    return QdoJsonRequest::get((object) [
                        'url' => $url,
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN)
    ]);
}

function qdo_isei_post_candidato($candidato) {
    $url = qdo_isei_parse_api_url("/gravarCandidato");
    return QdoJsonRequest::post((object) [
                        'url' => $url,
                        'body' => json_encode($candidato),
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN),
                        'contentType' => 'application/json'
    ]);
}

function qdo_isei_post_inscricao($inscricao) {
    $url = qdo_isei_parse_api_url("/gravarInscricaoProcessoSeletivo");
    return QdoJsonRequest::post((object) [
                        'url' => $url,
                        'body' => json_encode($inscricao),
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN),
                        'contentType' => 'application/json'
    ]);
}

function qdo_isei_post_compr_enem(string $cpf, $compr_enem) {
    $url = qdo_isei_parse_api_url("/uploadArquivoInscricaoProcessoSeletivo");
    return QdoJsonRequest::post((object) [
                        'url' => $url,
                        'body' => [
                            'file' => new CURLFile($compr_enem['tmp_name'], $compr_enem['type'], $compr_enem['name']),
                            'cpf' => $cpf
                        ],
                        'authorization' => qdo_isei_get_map_option(QDO_SEI_TOKEN),
                        'contentType' => 'multipart/form-data'
    ]);
}

function qdo_isei_parse_unidade_ensino_2_options($value) {
    $r = qdo_isei_get_unidades_ensino();
    if (qdo_isei_request_fail($r)) {
        return [];
    }
    
    if(isset($r->unidadeEnsino) && !is_array($r->unidadeEnsino)){
        $r->unidadeEnsino = [$r->unidadeEnsino];
    }

    $options = [
        [
            'label' => 'Escolha uma opção',
            'value' => '',
        ]
    ];
    
    foreach ($r->unidadeEnsino as $unidade_ensino){
        $options[] = [
            'label' => $unidade_ensino->nome,
            'value' => $unidade_ensino->codigo,
            'selected' => $unidade_ensino->codigo == $value ? 'selected' : '',
        ];
    }
    
    return $options;
}
