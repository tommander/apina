<?php

declare(strict_types=1);

namespace Apina\Helpers;

/**
 * @psalm-type ApiRequestUri = array{
 *   path: non-empty-string,
 *   query: array<string, string>,
 * }
 * @psalm-type ApiRequest = array{
 *   authScheme?: string,
 *   authParams?: string,
 *   uri: ApiRequestUri,
 *   headers: array<non-empty-string, string>,
 *   method: non-empty-string,
 *   contentType: string,
 *   content: mixed,
 *   test: bool,
 * }
 * @psalm-type ApiResponse = array{
 *   code: int<100,599>,
 *   method: non-empty-string,
 *   headers?: array<non-empty-string, string>,
 *   contentType?: non-empty-string,
 *   body?: mixed
 * } $response
 */
final class ApiHelper
{
    public const HTTP_VERBS_INT = [100,101,102,103,200,201,202,203,204,205,206,207,208,226,300,301,302,303,304,305,306,307,308,400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,421,422,423,424,425,426,428,429,431,451,500,501,502,503,504,505,506,507,508,510,511];
    public const HTTP_VERBS_STR = ['100','101','102','103','200','201','202','203','204','205','206','207','208','226','300','301','302','303','304','305','306','307','308','400','401','402','403','404','405','406','407','408','409','410','411','412','413','414','415','416','417','418','421','422','423','424','425','426','428','429','431','451','500','501','502','503','504','505','506','507','508','510','511'];
    public const HTTP_100_CONTINUE = 100;
    public const HTTP_101_SWITCHING_PROTOCOLS = 101;
    public const HTTP_102_PROCESSING = 102;
    public const HTTP_103_EARLY_HINTS = 103;
    public const HTTP_200_OK = 200;
    public const HTTP_201_CREATED = 201;
    public const HTTP_202_ACCEPTED = 202;
    public const HTTP_203_NON_AUTHORITATIVE_INFORMATION = 203;
    public const HTTP_204_NO_CONTENT = 204;
    public const HTTP_205_RESET_CONTENT = 205;
    public const HTTP_206_PARTIAL_CONTENT = 206;
    public const HTTP_207_MULTI_STATUS = 207;
    public const HTTP_208_ALREADY_REPORTED = 208;
    public const HTTP_226_IM_USED = 226;
    public const HTTP_300_MULTIPLE_CHOICES = 300;
    public const HTTP_301_MOVED_PERMANENTLY = 301;
    public const HTTP_302_FOUND = 302;
    public const HTTP_303_SEE_OTHER = 303;
    public const HTTP_304_NOT_MODIFIED = 304;
    public const HTTP_305_USE_PROXY = 305;
    public const HTTP_306_SWITCH_PROXY = 306;
    public const HTTP_307_TEMPORARY_REDIRECT = 307;
    public const HTTP_308_PERMANENT_REDIRECT = 308;
    public const HTTP_400_BAD_REQUEST = 400;
    public const HTTP_401_UNAUTHORIZED = 401;
    public const HTTP_402_PAYMENT_REQUIRED = 402;
    public const HTTP_403_FORBIDDEN = 403;
    public const HTTP_404_NOT_FOUND = 404;
    public const HTTP_405_METHOD_NOT_ALLOWED = 405;
    public const HTTP_406_NOT_ACCEPTABLE = 406;
    public const HTTP_407_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const HTTP_408_REQUEST_TIMEOUT = 408;
    public const HTTP_409_CONFLICT = 409;
    public const HTTP_410_GONE = 410;
    public const HTTP_411_LENGTH_REQUIRED = 411;
    public const HTTP_412_PRECONDITION_FAILED = 412;
    public const HTTP_413_PAYLOAD_TOO_LARGE = 413;
    public const HTTP_414_URI_TOO_LONG = 414;
    public const HTTP_415_UNSUPPORTED_MEDIA_TYPE = 415;
    public const HTTP_416_RANGE_NOT_SATISFIABLE = 416;
    public const HTTP_417_EXPECTATION_FAILED = 417;
    public const HTTP_418_IM_A_TEAPOT = 418;
    public const HTTP_421_MISDIRECTED_REQUEST = 421;
    public const HTTP_422_UNPROCESSABLE_CONTENT = 422;
    public const HTTP_423_LOCKED = 423;
    public const HTTP_424_FAILED_DEPENDENCY = 424;
    public const HTTP_425_TOO_EARLY = 425;
    public const HTTP_426_UPGRADE_REQUIRED = 426;
    public const HTTP_428_PRECONDITION_REQUIRED = 428;
    public const HTTP_429_TOO_MANY_REQUESTS = 429;
    public const HTTP_431_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const HTTP_451_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const HTTP_500_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_501_NOT_IMPLEMENTED = 501;
    public const HTTP_502_BAD_GATEWAY = 502;
    public const HTTP_503_SERVICE_UNAVAILABLE = 503;
    public const HTTP_504_GATEWAY_TIMEOUT = 504;
    public const HTTP_505_HTTP_VERSION_NOT_SUPPORTED = 505;
    public const HTTP_506_VARIANT_ALSO_NEGOTIATES = 506;
    public const HTTP_507_INSUFFICIENT_STORAGE = 507;
    public const HTTP_508_LOOP_DETECTED = 508;
    public const HTTP_510_NOT_EXTENDED = 510;
    public const HTTP_511_NETWORK_AUTHENTICATION_REQUIRED = 511;

    /** @var array<int, string> */
    public const HTTP_CODE_MESSAGES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",
        421 => 'Misdirected Request',
        422 => 'Unprocessable Content',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * @param string $pathBase URL path prefix to remove (e.g. "/api/v1")
     * @return ApiRequestUri
     */
    private static function processUri(string $pathBase, string|null $customUri = null): array
    {
        $res = [
            'path' => '/',
            'query' => [],
        ];
        $uri = $customUri ?? $_SERVER['REQUEST_URI'] ?? '/';
        if ($pathBase !== '' && str_starts_with($uri, $pathBase)) {
            $uri = str_replace($pathBase, '', $uri);
        }
        $parsedUrl = parse_url("http://example.com{$uri}");
        if ($parsedUrl !== false) {
            (isset($parsedUrl['path']) && !empty($parsedUrl['path'])) && ($res['path'] = $parsedUrl['path']);
            parse_str($parsedUrl['query'] ?? '', $res['query']);
        }

        return $res;
    }

    /**
     * @param array<non-empty-string, string>|null $customHeaders
     * @return ApiRequest
     */
    public static function parseRequest(string $pathBase, string|null $customMethod = null, string|null $customType = null, array|null $customHeaders = null): array
    {

        $request = [];
        $request['uri'] = self::processUri($pathBase); //self::explodeUrl($pathBase);
        $request['method'] = $customMethod ?? $_SERVER['REQUEST_METHOD'] ?? 'NONE';
        if (empty($request['method'])) {
            $request['method'] = 'NONE';
        }
        $request['contentType'] = $customType ?? $_SERVER['CONTENT_TYPE'] ?? 'text/plain';
        $content = '';

        $input = fopen("php://input", "r");
        if ($input) {
            try {
                $body = '';
                while ($data = fread($input, 1024)) {
                    $body .= $data;
                }
                $content = $body;
            } finally {
                fclose($input);
            }
        }
        /** @var mixed */
        $request['content'] = self::unprepareBody($request['contentType'], $content);
        $request['headers'] = [];
        $request['test'] = (isset($request['uri']['query']) && (($request['uri']['query']['test'] ?? false) === 'true'));

        foreach (($customHeaders ?? $_SERVER) as $key => $value) {
            if (!str_starts_with($key, 'HTTP_') && !str_starts_with($key, 'REDIRECT_HTTP_')) {
                continue;
            }
            $valueStr = is_array($value) ? implode('||', $value) : Helper::anyToStr($value);
            $request['headers'][$key] = $valueStr;

            if ($key !== 'HTTP_AUTHORIZATION' && $key !== 'REDIRECT_HTTP_AUTHORIZATION') {
                continue;
            }

            $rawAuthHeaders = is_array($value)
                ? $value
                : [strval($value)];
            foreach ($rawAuthHeaders as $rawAuthHeader) {
                if (preg_match('/^(.+?) (.+)$/', $rawAuthHeader, $matches) !== 1) {
                    continue;
                }
                $request['authScheme'] = $matches[1];
                $request['authParams'] = $matches[2];
                break;
            }
        }

        return $request;
    }

    /**
     * @param array<non-empty-string, array{href: non-empty-string}> $links
     * @param array<non-empty-string, array> $embedded
     */
    public static function halResourceObject(array $data, array $links = [], array $embedded = []): array
    {
        $res = [];
        if (count($links) > 0) {
            $res['_links'] = $links;
        }
        if (count($embedded) > 0) {
            $res['_embedded'] = $embedded;
        }
        $res += $data;
        return $res;
    }

    /**
     * This function encodes the content according to given contentType. So there is no need to
     * give this function an output from `json_encode`, since it will apply this function again.
     *
     * @param ApiResponse $response
     */
    public static function sendResponse(array $response): void
    {
        $responseCode = ($response['code'] ?? 500);
        if (!in_array($responseCode, ApiHelper::HTTP_VERBS_INT, true)) {
            $responseCode = ApiHelper::HTTP_500_INTERNAL_SERVER_ERROR;
        }
        header(sprintf(
            '%1$s %2$d %3$s',
            $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1',
            $responseCode,
            ApiHelper::HTTP_CODE_MESSAGES[$responseCode] ?? 'No Message',
        ));
        if (isset($response['headers'])) {
            foreach ($response['headers'] as $name => $value) {
                if (strtolower($name) === 'content-type' || strtolower($name) === 'content-length') {
                    continue;
                }
                header("{$name}: {$value}");
            }
        }

        $contentType = $response['contentType'] ?? 'text/plain';
        $content = self::prepareBody($contentType, $response['body'] ?? null);
        if (self::isEmptyBody($content) && !in_array($response['method'] ?? '', ['HEAD', 'GET'])) {
            return;
        }

        header("Content-Type: {$content['contentType']}");
        header("Content-Length: {$content['contentLength']}");
        if ($response['method'] !== 'HEAD') {
            echo $content['content'];
        }
    }

    public static function unprepareBody(string $contentType, string $body): mixed
    {
        if ($contentType === 'application/json') {
            /** @var mixed */
            $decoded = json_decode($body, true);
            return (json_last_error() === JSON_ERROR_NONE ? $decoded : $body);
        }
        return $body;
    }

    /**
     * @return array{contentType: non-empty-string, contentLength: numeric-string, content: string}
     */
    public static function prepareBody(string $contentType, mixed $body): array
    {
        $ret = [
            'contentType' => 'text/plain',
            'contentLength' => '0',
            'content' => '',
        ];
        if ($contentType === 'application/json') {
            $json = (string) json_encode($body);
            $ret['contentType'] = $contentType;
            $ret['contentLength'] = (string) strlen($json);
            $ret['content'] = $json;
            return $ret;
        }
        $txt = Helper::anyToStr($body);
        $ret['contentLength'] = (string) strlen($txt);
        $ret['content'] = $txt;
        return $ret;
    }

    public static function isEmptyBody(array $content): bool
    {
        return (
            $content['contentType'] === 'text/plain' &&
            $content['contentLength'] === '0' &&
            $content['content'] === ''
        );
    }
}
