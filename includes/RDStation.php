<?php

/* == RD Station Functions == */

function qdo_isei_get_rd_integration_stage(){
    if( !is_null(get_option(QDO_RD_CLIENT_ID, null)) &&
        !is_null(get_option(QDO_RD_CLIENT_SECRET, null)) &&
        ( 
            is_null(get_option(QDO_RD_CODE, null)) || 
            is_null(get_option(QDO_RD_TOKEN, null)) 
        )
    ){
        return QDO_RD_INTEGRATION_OFF;
    }
    
    if( !is_null(get_option(QDO_RD_CLIENT_ID, null)) && 
        !is_null(get_option(QDO_RD_CODE, null)) && 
        !is_null(get_option(QDO_RD_TOKEN, null))
    ){
        return QDO_RD_INTEGRATION_ON;
    }
    
    return QDO_RD_INTEGRATION_NO_CONFIG;
}

function qdo_isei_post_token_rd() {
    if( !is_null(get_option(QDO_RD_TOKEN, null)) &&
        !is_null(get_option(QDO_RD_REFRESH_TOKEN, null)) &&
        !is_null(get_option(QDO_RD_TOKEN_EXPIRE_IN, null)) &&
        intval(get_option(QDO_RD_TOKEN_EXPIRE_IN, 0)) > time()
    ) {
        return (object) [
                    'access_token' => get_option(QDO_RD_TOKEN),
                    'expires_in' => get_option(QDO_RD_TOKEN_EXPIRE_IN),
                    'refresh_token' => get_option(QDO_RD_REFRESH_TOKEN),
        ];
    }
    
    if (is_null(get_option(QDO_RD_TOKEN, null)) || empty(get_option(QDO_RD_TOKEN))) {
        $url = "https://api.rd.services/auth/token";
        return QdoJsonRequest::post((object) [
                            'url' => $url,
                            'body' => [
                                'client_id' => get_option(QDO_RD_CLIENT_ID),
                                'client_secret' => get_option(QDO_RD_CLIENT_SECRET),
                                'code' => get_option(QDO_RD_CODE),
                            ],
                        //'contentType' => 'application/json'
        ]);
    } else {
        $url = "https://api.rd.services/auth/token";
        $rd_token = QdoJsonRequest::post((object) [
                            'url' => $url,
                            'body' => [
                                'client_id' => get_option(QDO_RD_CLIENT_ID),
                                'client_secret' => get_option(QDO_RD_CLIENT_SECRET),
                                'refresh_token' => get_option(QDO_RD_REFRESH_TOKEN),
                            ],
                        //'contentType' => 'application/json'
        ]);
        
        if (!qdo_isei_save_option(QDO_RD_TOKEN, $rd_token->access_token) ||
                !qdo_isei_save_option(QDO_RD_TOKEN_EXPIRE_IN, ( time() + $rd_token->expires_in)) ||
                !qdo_isei_save_option(QDO_RD_REFRESH_TOKEN, $rd_token->refresh_token)) {
            return QdoJsonResponse::error(400, 'Falha ao tentar atualizar o RD Token');
        }
        return $rd_token;
    }
}

function qdo_isei_post_conversion_rd(array $body, string $token) {
    $url = "https://api.rd.services/platform/events";
    return QdoJsonRequest::post((object) [
                        'url' => $url,
                        'body' => json_encode($body),
                        'authorization' => "Bearer {$token}",
                        'contentType' => 'application/json',
    ]);
}

function qdo_isei_get_rd_event_id($proc_type, $ano, $semestre, bool $pre = false) {
    $inscricao_type = $pre ? 'pre-inscricao' : 'inscricao';
    switch ($proc_type) {
        case QDO_PS_POS_GRADUAÇÃO:
            return "{$inscricao_type}.posgraduacao";
        case QDO_PS_VESTIBULAR_TRADICIONAL:
            return "{$inscricao_type}.vestibular-tradicional{$ano}.{$semestre}";
        case QDO_PS_VESTIBULAR_ONLINE:
            return "{$inscricao_type}.vestibular-online{$ano}.{$semestre}";
        case QDO_PS_ENEM:
            return "{$inscricao_type}.vestibular-simplificado{$ano}.{$semestre}";
        default:
            return '';
    }
}

function qdo_isei_get_rd_event_id2($campaign_name, bool $pre = false) {
    $inscricao_type = $pre ? 'pre-inscricao' : 'inscricao';
    return "{$inscricao_type}.{$campaign_name}";
}

function qdo_isei_rd_conversion(
    string $conversion_identifier, 
    string $email, 
    string $proc_type, 
    $client_tracking_id = false, 
    $traffic_source = false, 
    $traffic_medium = false, 
    $traffic_campaign = false, 
    array $fields = [],
    array $tags = []
    ) {
    //Realiza a conversão do lead
    $conversion_data = [
        'event_type' => 'CONVERSION',
        'event_family' => 'CDP',
        'payload' => [
            'conversion_identifier' => $conversion_identifier,
            'email' => $email,
            'client_tracking_id' => $client_tracking_id,
            'traffic_source' => $traffic_source,
            'traffic_medium' => $traffic_medium,
            'traffic_campaign' => $traffic_campaign,
            'tags' => $tags,
            'legal_bases' => [
                [
                    'category' => 'communications',
                    'type' => 'consent',
                    'status' => 'granted'
                ]
            ]
        ]
    ];
    
    foreach ($fields as $field_name => $field_value) {
        $conversion_data['payload'][$field_name] = $field_value;
    }
    
    if ($proc_type === QDO_PS_VESTIBULAR_TRADICIONAL ||
            $proc_type === QDO_PS_VESTIBULAR_ONLINE ||
            $proc_type === QDO_PS_ENEM) {
        $conversion_data['payload']['cf_tipo_de_inscricao_vestibular'] = qdo_isei_get_tipo_de_inscricao_vestibular($proc_type);
    }
    return qdo_isei_post_conversion_rd($conversion_data, get_option(QDO_RD_TOKEN));
}

function qdo_isei_get_rdtrk(){
    return isset($_COOKIE['rdtrk']) ? json_decode(
            preg_replace('/\\\\/', '', $_COOKIE['rdtrk'])
        ) : false;
}

function qdo_isei_get_rdtrk_id(){
    $rdtrk = qdo_isei_get_rdtrk();
    return $rdtrk !== false && isset($rdtrk->id) ? $rdtrk->id : false;
}

function qdo_isei_get_rd_traffic_source(){
    return isset($_COOKIE['__trf_src']) ? $_COOKIE['__trf_src'] : false;
}