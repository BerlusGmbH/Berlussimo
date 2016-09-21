<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Http\Responses\LegacyResponse;

class LegacyController extends Controller
{
    protected $submenu = '';
    protected $include = '';

    public function __construct()
    {
        error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING ^ E_NOTICE);
    }

    public function render()
    {
        $response = $this->renderResponse();
        if ($this->responseIsFile($response)) {
            return $response;
        } elseif ($response->headers->has('Location')) {
            return redirect()->to($response->headers->get('Location'))->withHeaders($response->headers->all());
        } else {
            return $this->renderView($response->content(), $response->headers->all());
        }
    }

    /**
     * @return LegacyResponse
     */
    protected function renderResponse()
    {
        return response()->legacy($this->include);
    }

    protected function responseIsFile(LegacyResponse $response)
    {
        return $response->headers->has('Content-Type');
    }

    protected function renderView($content, $headers)
    {
        return response()->view('berlusssimo', ['content' => $content, 'submenu' => $this->submenu])->header($headers);
    }
}