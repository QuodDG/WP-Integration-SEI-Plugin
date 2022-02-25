<?php

if (!class_exists('QdoJsonRequest')) {

    class QdoJsonRequest {

        /**
         * 
         * @param stdClass $options [params, url, authorization]
         * @return string
         */
        static function get(stdClass $options) {
            self::verifyOptions($options, 'get');
            $curl = curl_init();
            curl_reset($curl);
            if (isset($options->params) && is_array($options->params)) {
                $data = http_build_query(self::prepareParams($options->params));
                $options->url .= "?${$data}";
            }

            curl_setopt_array($curl, array(
                CURLOPT_URL => $options->url,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "gzip",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            if (!is_null($options->authorization)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    "Authorization: {$options->authorization}"
                ]);
            }

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($http_code !== 200 || curl_errno($curl) > 0) {
                $responseObj = json_decode($response);
                return (object) [
                            'statusCode' => 0,
                            'mensagem' => !empty(curl_error($curl)) ? curl_error($curl) :
                            ( isset($responseObj->mensagem) && !empty($responseObj->mensagem) ? $responseObj->mensagem : self::getResponseCode(intval($http_code)) ),
                            'httpCode' => $http_code
                ];
            }

            curl_close($curl);
            return json_decode($response);
        }

        /**
         * 
         * @param stdClass $options [url*, params, authorization, contentType, body]
         * @param type $_method
         * @return type
         */
        static function post(stdClass $options, $_method = 'POST') {
            //self::verifyOptions($options, 'post');
            $curl = curl_init();
            curl_reset($curl);
            if (isset($options->params) && is_array($options->params)) {
                $data = http_build_query(self::prepareParams($options->params));
                $options->url .= "?{$data}";
            }
            curl_setopt_array($curl, [
                CURLOPT_URL => $options->url,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => 'gzip',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $_method
            ]);

            $header = [];
            if (isset($options->authorization)) {
                $header[] = "Authorization: {$options->authorization}";
            }
            if (isset($options->contentType)) {
                $header[] = "Content-Type: {$options->contentType}";
            }
            if (count($header)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            }
            if (isset($options->body)) {
                //$options->body = http_build_query(self::prepareParams($options->body));
                curl_setopt($curl, CURLOPT_POSTFIELDS, $options->body);
            }

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            //if (isset($options->debug)) {echo $response;die;}
            if ($http_code !== 200 || curl_errno($curl) > 0) {
                $responseObj = json_decode($response);
                return (object) [
                            'statusCode' => 0,
                            'mensagem' => !empty(curl_error($curl)) ? curl_error($curl) :
                            ( isset($responseObj->mensagem) && !empty($responseObj->mensagem) ? $responseObj->mensagem : self::getResponseCode(intval($http_code)) ),
                            'httpCode' => $http_code
                ];
            }

            curl_close($curl);
            return json_decode($response);
        }

        static function put(stdClass $options) {
            return self::post($options, 'PUT');
        }

        static function delete(stdClass $options) {
            return self::post($options, 'DELETE');
        }

        static function verifyOptions(stdClass $options, string $typeRequest = 'get') {
            if (!isset($options->url)) {
                throw new Exception('Um ou mais parâmetros da requisição são inválidos. Parâmetros: ' . join(', ', ((array) $options)));
            }
        }

        static function prepareParams(array $params): array {
            $_params = [];
            foreach ($params as $field => $value) {
                if (is_object($value)) {
                    $value = (array) $value;
                }
                if (is_array($value)) {
                    $_params[$field] = self::prepareParams($value);
                    continue;
                }
                $_params[$field] = $value;
            }
            return $_params;
        }

        static function getResponseCode(int $code) {
            $text = 'Unknown http status code "' . htmlentities($code) . '"';
            switch ($code) {
                case 100: $text = 'Continue';
                    break;
                case 101: $text = 'Switching Protocols';
                    break;
                case 200: $text = 'OK';
                    break;
                case 201: $text = 'Created';
                    break;
                case 202: $text = 'Accepted';
                    break;
                case 203: $text = 'Non-Authoritative Information';
                    break;
                case 204: $text = 'No Content';
                    break;
                case 205: $text = 'Reset Content';
                    break;
                case 206: $text = 'Partial Content';
                    break;
                case 300: $text = 'Multiple Choices';
                    break;
                case 301: $text = 'Moved Permanently';
                    break;
                case 302: $text = 'Moved Temporarily';
                    break;
                case 303: $text = 'See Other';
                    break;
                case 304: $text = 'Not Modified';
                    break;
                case 305: $text = 'Use Proxy';
                    break;
                case 400: $text = 'Bad Request';
                    break;
                case 401: $text = 'Unauthorized';
                    break;
                case 402: $text = 'Payment Required';
                    break;
                case 403: $text = 'Forbidden';
                    break;
                case 404: $text = 'Not Found';
                    break;
                case 405: $text = 'Method Not Allowed';
                    break;
                case 406: $text = 'Not Acceptable';
                    break;
                case 407: $text = 'Proxy Authentication Required';
                    break;
                case 408: $text = 'Request Time-out';
                    break;
                case 409: $text = 'Conflict';
                    break;
                case 410: $text = 'Gone';
                    break;
                case 411: $text = 'Length Required';
                    break;
                case 412: $text = 'Precondition Failed';
                    break;
                case 413: $text = 'Request Entity Too Large';
                    break;
                case 414: $text = 'Request-URI Too Large';
                    break;
                case 415: $text = 'Unsupported Media Type';
                    break;
                case 500: $text = 'Internal Server Error';
                    break;
                case 501: $text = 'Not Implemented';
                    break;
                case 502: $text = 'Bad Gateway';
                    break;
                case 503: $text = 'Service Unavailable';
                    break;
                case 504: $text = 'Gateway Time-out';
                    break;
                case 505: $text = 'HTTP Version not supported';
                    break;
            }
            return $text;
        }

    }

}