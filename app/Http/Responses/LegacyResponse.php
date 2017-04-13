<?php

namespace App\Http\Responses;


use Illuminate\Http\Response;

class LegacyResponse extends Response
{
    protected $filter = ['content-type', 'content-disposition', 'content-length', 'location'];

    /**
     * @param mixed|string $include
     * @param int $status
     * @param array $headers
     */
    public function __construct($include, $status = 200, $headers = [])
    {
        error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
        ob_start();
        include(base_path($include));
        $content = ob_get_contents();
        ob_end_clean();
        if(!empty(headers_list())) {
            $headers = $this->transformHeaders(headers_list());
        }
        if(http_response_code() != 200) {
            $status = http_response_code();
        }
        parent::__construct($content, $status, $headers);
    }

    protected function transformHeaders(array $headers) {
        $headers_count = count($headers);
        for($i = 0; $i < $headers_count; $i++) {
            $head = explode(':', $headers[$i]);
            if (in_array(strtolower(trim($head[0])), $this->filter)) {
                $headers[trim($head[0])] = trim($head[1]);
            }
            unset($headers[$i]);
        }
        return $headers;
    }
}