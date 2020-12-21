<?php

namespace rapidPHP\modules\application\classier\context;


use rapidPHP\modules\application\classier\Context;
use rapidPHP\modules\server\classier\interfaces\Request;
use rapidPHP\modules\server\classier\interfaces\Response;

class WebContext extends Context
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * WebContext constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct();

        $this->request = $request;

        $this->response = $response;

        $this->supportsParameter([
            WebContext::class => $this,
            Request::class => $this->request,
            Response::class => $this->response,
        ]);
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}