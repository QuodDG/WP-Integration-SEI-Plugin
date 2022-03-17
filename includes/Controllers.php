<?php

/* == Controllers == */

function qdo_isei_authorizable($request) {
    $nonce = $request->get_header('X-WP-Nonce');
    $verify_nonce = wp_verify_nonce($nonce, 'wp_rest');
    if ($verify_nonce === false) {
        return false;
    }

    return true;
}

function qdo_isei_home() {
    return [
        'message' => 'Olá SEI :)'
    ];
}

function qdo_isei_logout_rdstation($request) {
    if (!qdo_isei_authorizable($request) ||
            !isset($request['hash']) ||
            $request['hash'] !== QDO_ISEI_PRIVATE_HASH) {
        return QdoJsonResponse::error(401, 'Acesso inválido');
    }

    if (!delete_option(QDO_RD_TOKEN) ||
            !delete_option(QDO_RD_REFRESH_TOKEN) ||
            !delete_option(QDO_RD_TOKEN_EXPIRE_IN) ||
            !delete_option(QDO_RD_CODE)) {
        return QdoJsonResponse::error(400, 'Algo de errado não está certo.');
    }

    return QdoJsonResponse::success('Tudo correu como esperado.');
}

function qdo_isei_rd_code($request) {
    if (strstr(
                    wp_get_raw_referer(),
                    qdo_isei_get_map_option(QDO_RD_REFERER_APP)
            ) === false) {
        return QdoJsonResponse::error(401, 'Acesso inválido');
    }

    $edcode = $request['code'];
    //Salva o RD Code
    if (!qdo_isei_save_option(QDO_RD_CODE, $edcode)) {
        return QdoJsonResponse::error(400, 'Falha ao tentar salvar o RD code');
    }

    //Captura o Token do RD Station
    $rd_token = qdo_isei_post_token_rd();
    if (isset($rd_token->statusCode) && $rd_token->statusCode == 0) {
        return QdoJsonResponse::error($rd_token->httpCode, "Falha na obtenção do token", ['rd_token' => $rd_token]);
    }

    //Salva o Token, a data de expiração e o Refresh Token
    if (!qdo_isei_save_option(QDO_RD_TOKEN, $rd_token->access_token) ||
            !qdo_isei_save_option(QDO_RD_TOKEN_EXPIRE_IN, ( time() + $rd_token->expires_in)) ||
            !qdo_isei_save_option(QDO_RD_REFRESH_TOKEN, $rd_token->refresh_token)) {
        return QdoJsonResponse::error(400, 'Falha ao tentar salvar o RD Token');
    }

    //Redireciona para a página de configurações
    wp_redirect(qdo_isei_get_setting_page_url());
    die;
}

function qdo_isei_get_curso_e_turno_by_ps($request) {
    if (!qdo_isei_authorizable($request)) {
        return QdoJsonResponse::error(401, 'Acesso inválido');
    }

    //Dados
    $ps_code = $request['pscode'];

    if (empty($ps_code)) {
        return JsonResponse::error(400, 'Processo Seletivo inválido');
    }

    //Get cursos by PS
    $cursos = qdo_isei_get_curso_by_ps($ps_code);
    if (qdo_isei_request_fail($cursos)) {
        $cursos->mensagem = qdo_isei_convert_mensagem($cursos->mensagem);
        return QdoJsonResponse::error($cursos->httpCode, $cursos->mensagem);
    }

    if (isset($cursos->procSeletivoCurso) && !is_array($cursos->procSeletivoCurso)) {
        $cursos->procSeletivoCurso = [$cursos->procSeletivoCurso];
    }

    return QdoJsonResponse::success('procSeletivoCurso', $cursos);
}

function qdo_isei_ps_step_1($request) {
    if (!qdo_isei_authorizable($request)) {
        return QdoJsonResponse::error(401, 'Acesso inválido');
    }

    $body = (object) $request->get_body_params();
    $proc_type = qdo_isei_get_proc_type_name($body->isei_ps_proc_type);
    $ano = isset($body->isei_ps_ano) ? $body->isei_ps_ano : '';
    $semestre = isset($body->isei_ps_semestre) ? $body->isei_ps_semestre : '';
    $traffic_campaign = isset($body->isei_ps_campanha) ? $body->isei_ps_campanha : false;
    $recaptcha = isset($body->isei_ps_recaptcha) ? $body->isei_ps_recaptcha : '';

    //Dados do Lead
    $nome = isset($body->isei_ps_nome) ? $body->isei_ps_nome : '';
    $email = isset($body->isei_ps_email) ? filter_var($body->isei_ps_email, FILTER_SANITIZE_EMAIL) : '';
    $email2 = isset($body->isei_ps_email_2) ? filter_var($body->isei_ps_email_2, FILTER_SANITIZE_EMAIL) : '';
    $celular = isset($body->isei_ps_celular) ? $body->isei_ps_celular : '';
    
    //Verify Captcha
    if (empty($recaptcha) || !qdo_isei_verify_recaptcha_v3($recaptcha)) {
        return QdoJsonResponse::error(422, 'Captcha Inválido');
    }

    if (empty($ano) || empty($semestre)) {
        return QdoJsonResponse::error(400, 'Dados incompletos. #100');
    }

    if (empty($nome) ||
            empty($email) ||
            empty($celular)) {
        return QdoJsonResponse::error(400, 'Dados incompletos. #100');
    }
    
    if($email !== $email2){
        return QdoJsonResponse::error(400, 'Você digitou e-mail diferentes. Verifique se o seu e-mail está correto. #100');
    }
    
    // Valida o e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return QdoJsonResponse::error(400, 'O e-mail informado é inválido. #100');
    }
    
    //Verifica se a integração com o RD Station Marketing está ativa
    //e converte o Lead no Evento
    if(qdo_isei_get_rd_integration_stage() === QDO_RD_INTEGRATION_ON){
        //Captura e salva o Token do RD Station
        $rd_token = qdo_isei_post_token_rd();
        if (qdo_isei_request_fail($rd_token)) {
            return QdoJsonResponse::error($rd_token->httpCode, 'Falha na obtenção do token. #100');
        }

        //Captura os valores dos Cookies
        $client_tracking_id = qdo_isei_get_rdtrk_id();
        $traffic_source = qdo_isei_get_rd_traffic_source();
        //Identificador de conversão de pré-inscrição
        $conversion_identifier = qdo_isei_get_rd_event_id2($traffic_campaign, true);

        $conversion = qdo_isei_rd_conversion(
                $conversion_identifier,
                $email,
                $proc_type,
                $client_tracking_id,
                $traffic_source,
                '(none)',
                $traffic_campaign,
                [
                    'name' => $nome,
                    'mobile_phone' => $celular,
                ]
        );

        if (qdo_isei_request_fail($conversion)) {
            $conversion->mensagem = qdo_isei_convert_mensagem($conversion->mensagem);
            return QdoJsonResponse::error(400, 'Não foi possível dar continuidade ao processo. #100 ');
        }
    }

    return QdoJsonResponse::success('Pré-inscrição realizada com sucesso!');
}

function qdo_isei_ps_step_2($request) {
    if (!qdo_isei_authorizable($request)) {
        return QdoJsonResponse::error(401, 'Acesso inválido');
    }

    $body = (object) $request->get_body_params();
    $recaptcha = isset($body->isei_ps_recaptcha) ? $body->isei_ps_recaptcha : '';

    //Dados
    $proc_type = qdo_isei_get_proc_type_name($body->isei_ps_proc_type);

    //Verify Captcha
    if (empty($recaptcha) || !qdo_isei_verify_recaptcha_v3($recaptcha)) {
        return QdoJsonResponse::error(422, 'Captcha Inválido');
    }

    if (empty($proc_type)) {
        return QdoJsonResponse::error(400, 'Dados incompletos. #200');
    }

    $ps = qdo_isei_get_ps_by_type($proc_type);
    if (qdo_isei_request_fail($ps)) {
        //$ps->mensagem = qdo_isei_convert_mensagem($ps->mensagem);
        return QdoJsonResponse::error($ps->httpCode, $ps->mensagem);
    }

    return QdoJsonResponse::success('Processos Seletivos!', $ps);
}

function qdo_isei_ps_step_3($request) {
    if (!qdo_isei_authorizable($request)) {
        return QdoJsonResponse::error(401, 'Acesso inválido');
    }

    $body = (object) $request->get_body_params();
    $files = (object) $request->get_file_params();
    $proc_type = qdo_isei_get_proc_type_name($body->isei_ps_proc_type);
    $ano = isset($body->isei_ps_ano) ? $body->isei_ps_ano : '';
    $semestre = isset($body->isei_ps_semestre) ? $body->isei_ps_semestre : '';
    $traffic_campaign = isset($body->isei_ps_campanha) ? $body->isei_ps_campanha : false;
    $recaptcha = isset($body->isei_ps_recaptcha) ? $body->isei_ps_recaptcha : '';

    //Verify Captcha
    if (empty($recaptcha) || !qdo_isei_verify_recaptcha_v3($recaptcha)) {
        return QdoJsonResponse::error(422, 'Captcha Inválido');
    }

    //Dados
    //Etapa 1
    $nome = isset($body->isei_ps_nome) ? $body->isei_ps_nome : '';
    $email = isset($body->isei_ps_email) ? filter_var($body->isei_ps_email, FILTER_SANITIZE_EMAIL) : '';
    $celular = isset($body->isei_ps_celular) ? $body->isei_ps_celular : '';
    //Etapa 2
    $cpf = isset($body->isei_ps_cpf) ? qdo_isei_clear_cpf($body->isei_ps_cpf) : '';
    $dt_nascimento = isset($body->isei_ps_dt_nascimento) ? $body->isei_ps_dt_nascimento : '';
    $sexo = isset($body->isei_ps_sexo) ? $body->isei_ps_sexo : '';
    //Etapa 3
    $ps_code = isset($body->isei_ps_ps) ? (int) $body->isei_ps_ps : '';
    $uni_ens_curso = isset($body->isei_ps_uni_ens_curso) ? (int) $body->isei_ps_uni_ens_curso : '';

    if (empty($nome) ||
            empty($email) ||
            empty($celular) ||
            empty($cpf) ||
            empty($dt_nascimento) ||
            empty($sexo) ||
            empty($ps_code) ||
            empty($uni_ens_curso)) {
        return QdoJsonResponse::error(400, 'Dados incompletos. #300');
    }
    
    // Valida o e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return QdoJsonResponse::error(400, 'O e-mail informado é inválido. #300');
    }
    
    //FUNÇÃO INEXISTENE NO WEBSERVICE DO SEI
    //Verifica se o e-mail já está sendo utilizado por outra pessoa
    /*$candidato_existente = qdo_isei_get_candidato_by_email($email);
    if(!empty($candidato_existente->cpf) && qdo_isei_clear_cpf($candidato_existente->cpf) !== $cpf){
        return QdoJsonResponse::error(400, 'O e-mail informado já está em uso por outra pessoa. #300');
    }*/

    //Verifica se a integração com o RD Station Marketing está ativa
    //e captura e salva o Token do RD Station
    if(qdo_isei_get_rd_integration_stage() === QDO_RD_INTEGRATION_ON){
        $rd_token = qdo_isei_post_token_rd();
        if (qdo_isei_request_fail($rd_token)) {
            return QdoJsonResponse::error($rd_token->httpCode, 'Falha na obtenção do token. #300');
        }
    }

    //Get PS
    $ps = qdo_isei_get_ps_by_code($ps_code);
    if (qdo_isei_request_fail($ps)) {
        //$ps->mensagem = qdo_isei_convert_mensagem($ps->mensagem);
        return QdoJsonResponse::error($ps->httpCode, $ps->mensagem);
    }

    //Verifica se o candidato já está inscrito no processo seletivo
    $inscricoes_ativas = qdo_isei_get_inscricao_ativa_candidato_by_cpf($cpf);
    if (qdo_isei_request_fail($inscricoes_ativas)) {
        $inscricoes_ativas->mensagem = qdo_isei_convert_mensagem($inscricoes_ativas->mensagem);
        return QdoJsonResponse::error($inscricoes_ativas->httpCode, $inscricoes_ativas->mensagem);
    }
    if(isset($inscricoes_ativas->inscricaoVO) && !is_array($inscricoes_ativas->inscricaoVO)){
        $inscricoes_ativas->inscricaoVO = [$inscricoes_ativas->inscricaoVO];
    }
    foreach($inscricoes_ativas->inscricaoVO as $inscricao_ativa){
        if($inscricao_ativa->procSeletivo->codigo === $ps->codigo){
            return QdoJsonResponse::error(422, 'Já existe uma inscrição com esse CPF no processo seletivo pretendido. #300');
        }
    }

    $dt_nascimento = DateTime::createFromFormat('Y-m-d', $dt_nascimento);
    if ($dt_nascimento === false) {
        return QdoJsonResponse::error(400, 'Data de nascimento inválida. #300');
    }
    
    //Get Candidato
    $candidato = qdo_isei_get_candidato_by_cpf($cpf);
    if (qdo_isei_request_fail($candidato)) {
        $candidato->mensagem = qdo_isei_convert_mensagem($candidato->mensagem);
        return QdoJsonResponse::error($candidato->httpCode, $candidato->mensagem);
    }

    //Prepare Candidato
    $candidato->nome = $nome;
    $candidato->nomeBatismo = $nome;
    $candidato->email = $email;
    $candidato->celular = preg_replace('/\D/', '', $celular);
    $candidato->CPF = preg_replace('/\D/', '', $cpf);
    $candidato->dataNasc = $dt_nascimento->format('m-d-Y H:i:s');
    $candidato->sexo = $sexo;
    $candidato->codigoCurso1DadosUnicidadeCandidatoCurso = $uni_ens_curso;

    //Post Candidato
    $candidato = qdo_isei_post_candidato($candidato);
    if (qdo_isei_request_fail($candidato)) {
        $candidato->mensagem = qdo_isei_convert_mensagem($candidato->mensagem);
        return QdoJsonResponse::error($candidato->httpCode, $candidato->mensagem);
    }

    //Prepare Inscricao
    $inscricao = new stdClass();
    $inscricao->codigo = 0;
    $inscricao->formaIngresso = $ps->listaTipoIngressoProcSeletivo->chave;

    //Arquivo Comprovante ENEM
    $inscricao->arquivoVO = new stdClass();
    $aceita_arquivo = in_array($ps->listaTipoIngressoProcSeletivo->chave, ['EN', 'PD', 'TR']);
    if ($aceita_arquivo && isset($files->isei_ps_compr_enem) && !is_null($files->isei_ps_compr_enem)) {
        $compr_enem = qdo_isei_post_compr_enem($candidato->CPF, $files->isei_ps_compr_enem);
        if (qdo_isei_request_fail($compr_enem)) {
            $compr_enem->mensagem = qdo_isei_convert_mensagem($compr_enem->mensagem);
            return QdoJsonResponse::error($compr_enem->httpCode, $compr_enem->mensagem, ['compr_enem' => $compr_enem]);
        }
        $inscricao->arquivoVO = $compr_enem;
    }

    //Candidato
    $inscricao->candidato = $candidato;

    //Processo Seletivo
    $inscricao->procSeletivo = $ps;

    //Data Prova
    $data_prova = qdo_isei_get_data_prova_by_ps($ps->codigo);
    if (qdo_isei_request_fail($data_prova)) {
        $data_prova->mensagem = qdo_isei_convert_mensagem($data_prova->mensagem);
        return QdoJsonResponse::error($data_prova->httpCode, $data_prova->mensagem);
    }
    $inscricao->itemProcessoSeletivoDataProva = $data_prova->itemProcessoSeletivoDataProva;

    //Opção curso
    $curso_opcao = qdo_isei_get_unidade_ensino_curso_by_ps_curso($ps->codigo, $uni_ens_curso);
    if (qdo_isei_request_fail($curso_opcao)) {
        //$curso_opcao->mensagem = qdo_isei_convert_mensagem($curso_opcao->mensagem);
        return QdoJsonResponse::error($curso_opcao->httpCode, $curso_opcao->mensagem);
    }
    $inscricao->cursoOpcao1 = $curso_opcao;

    //Unidade Ensino
    $unidade_ensino = qdo_isei_get_unidade_ensino_by_ps($ps->codigo);
    if (qdo_isei_request_fail($unidade_ensino)) {
        $unidade_ensino->mensagem = qdo_isei_convert_mensagem($unidade_ensino->mensagem);
        return QdoJsonResponse::error($unidade_ensino->httpCode, $unidade_ensino->mensagem);
    }
    $inscricao->unidadeEnsino = $unidade_ensino->unidadeEnsino;

    //POST Inscricao
    $inscricao = qdo_isei_post_inscricao($inscricao);
    if (qdo_isei_request_fail($inscricao)) {
        $inscricao->mensagem = qdo_isei_convert_mensagem($inscricao->mensagem);
        return QdoJsonResponse::error($inscricao->httpCode, $inscricao->mensagem);
    }

    //Verifica se a integração com o RD Station Marketing está ativa
    //e converte o Lead no Evento
    if(qdo_isei_get_rd_integration_stage() === QDO_RD_INTEGRATION_ON){
        //Captura os valores dos Cookies
        $client_tracking_id = qdo_isei_get_rdtrk_id();
        $traffic_source = qdo_isei_get_rd_traffic_source();
        //Identificador de conversão de pré-inscrição
        //$conversion_identifier = qdo_isei_get_rd_event_id($proc_type, $ano, $semestre);
        $conversion_identifier = qdo_isei_get_rd_event_id2($traffic_campaign, false);

        $conversion = qdo_isei_rd_conversion(
                $conversion_identifier,
                $email,
                $proc_type,
                $client_tracking_id,
                $traffic_source,
                '(none)',
                $traffic_campaign,
                [
                    'name' => $nome,
                    'mobile_phone' => $celular,
                    'cf_curso_de_interesse' => mb_convert_case($curso_opcao->curso->nome, MB_CASE_UPPER),
                ]
        );

        if (qdo_isei_request_fail($conversion)) {
            $conversion->mensagem = qdo_isei_convert_mensagem($conversion->mensagem);
            return QdoJsonResponse::error(400, 'Não foi possível dar continuidade ao processo. #300');
        }
    }

    //Mensagem para Vestibular Online
    if ($inscricao->formaIngresso === 'PS' && $proc_type === QDO_PS_VESTIBULAR_ONLINE) {
        $text_2 = 'Em poucos minutos você receberá um e-mail com as instruções para fazer a prova.';
    }
    //Mensagem para Pós-graduação
    else if ($inscricao->formaIngresso === 'PS' && $proc_type === QDO_PS_POS_GRADUAÇÃO) {
        $text_2 = 'Você receberá um e-mail com os detalhes da sua inscrição.';
    }
    //Mensagem para Nota ENEM
    else if ($inscricao->formaIngresso === 'EN') {
        $text_2 = 'Nossa equipe irá entrar em contato em breve com o resultado.';
    }

    return QdoJsonResponse::success('Inscrição realizada com sucesso!', [
                'title' => 'Muito bem!',
                'text_1' => 'Você concluiu sua inscrição.',
                'text_2' => $text_2,
                'inscr_number' => $inscricao->codigo,
                'ps' => $inscricao->procSeletivo->descricao,
                'curso' => $inscricao->cursoOpcao1->curso->nome
    ]);
}
