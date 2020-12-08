<?php

namespace rapidPHP\modules\application\classier\apps;


use Exception;
use rapidPHP\modules\application\classier\Application;
use rapidPHP\modules\router\classier\router\WebRouter;
use rapidPHP\modules\server\classier\websocket\swoole\Request;
use rapidPHP\modules\server\classier\websocket\swoole\Response;
use rapidPHP\modules\server\classier\websocket\swoole\Server;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as SwooleWebSocketServer;
use function rapidPHP\formatException;

class SwooleWebsocketApplication extends WebApplication
{

    /**
     * @var Server
     */
    private $server;

    /**
     * @return Server
     */
    public function getServer(): ?Server
    {
        return $this->server;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        parent::run();

        $this->server = new Server($this->getConfig()->getServer()->getSwoole()->getWebsocket());

        $this->server->on('message', [$this, 'onMessage']);

        $this->server->start();
    }

    /**
     * @param SwooleWebSocketServer $server
     * @param Frame $frame
     */
    public function onMessage(SwooleWebSocketServer $server, Frame $frame)
    {
        $startTime = microtime(true);

        try {
            $sessionConfig = $this->server->getConfig()->getSession();

            if (empty($sessionConfig)) throw new Exception('session error', 1008);

            $this->server->parseRequestBody($frame->fd, $header, $cookie, $sessionConfig, $sessionId);

            if (empty($sessionId)) throw new Exception('session id error', 1008);

            $request = new Request($this->server->getServer(), $frame, $header, $cookie, $sessionConfig, $sessionId);

            $response = new Response($this->server->getServer(), $sessionConfig, $sessionId);

            $context = parent::newWebContext($request, $response, $this->server->getConfig()->getContext());

            WebRouter::getInstance()->run($this, $context);

            $endTime = microtime(true);

            $requestTime = $endTime - $startTime;

            $this->logger(Application::LOGGER_ACCESS)
                ->info("-{$request->getIp()} -{$request->getMethod()} -{$request->getUrl(true)} -{$requestTime}");
        } catch (Exception $e) {
            $this->logger(Application::LOGGER_ACCESS)->error(formatException($e));
        }
    }
}