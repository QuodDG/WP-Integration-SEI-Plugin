<?php

if (!class_exists('QdoJsonResponse')) {

    class QdoJsonResponse {

        /**
         * 
         * @param string $message Mensagem da resposta
         * @param mixed $data Dados a serem enviados para o client
         * @param int $code Código da resposta
         * @return string JsonString (json_encode)
         */
        static function success(string $message, $data = null, int $code = 200) {
            /* if (!is_array($data)) {
              $data = [$data];
              } */
            //header('Content-Type: application/json');
            return [
                'message' => $message,
                'code' => $code,
                'data' => $data,
                'error' => false,
                'response_time' => time()
            ];
        }

        /**
         * 
         * @param int $code Código da resposta
         * @param string $message Mensagem da resposta
         * @param mixed $data Dados a serem enviados para o client
         * @return string
         */
        static function error(int $code, string $message, $data = null) {
            /* if (!is_array($data)) {
              $data = [$data];
              } */
            //header('Content-Type: application/json');
            return [
                'message' => $message,
                'code' => $code,
                'data' => $data,
                'error' => true,
                'response_time' => time()
            ];
        }

        static function internalError() {
            return self::error(500, 'Erro interno de sistema. Estamos trabalhando para solucionar o problema. Tenta novamente mais trade.');
        }

    }

}