<?php

class ParserController
{
    public function parse(Request $request): Response
    {
        $url = $request->get('url');

        $error = '';
        $arTags = [];

        if ($url) {
            if (false === filter_var($url, FILTER_VALIDATE_URL)) {
                $error = 'Uncorrected URL';
            }  else {
                $content = file_get_contents($url);
                if (false !== $content) {
                    $service = new ParseService();
                    $arTags = $service->calcTags($content);
                } else {
                    $error = 'Cannot open URL';
                }
            }
        }

        return Response::render('test.tpl.php', ['tags' => $arTags, 'error' => $error]);
    }
}

class ParseService
{
    private $str;

    public function calcTags(string $str): array
    {
        $length = mb_strlen($str);
        $arTags = [];

        for ($i = 0; $i < $length; $i++) {

            $char = $str[$i];
            $charNext = $str[$i + 1];

            if ($char == '<' && $charNext != '/' && $charNext != '!' && $charNext != '\\') {
                $tag = '';
                do {
                    $i++;
                    $char = $str[$i];
                    $charNext = $str[$i + 1];
                    $tag .= $char;
                } while (($charNext != ' ' && $charNext != '>' && $charNext != '/') && ($i + 2) < $length);
                $tag = mb_strtolower($tag);
                if ($charNext == ' ' || $charNext == '>' || $charNext == '/') {
                    if (!isset($arTags[$tag])) $arTags[$tag] = 0;
                    $arTags[$tag]++;
                }
            }
        }

        return $arTags;
    }
}

class Request
{
    private $vars;
    private $server;

    public function __construct(array $arVars, array $arServer)
    {
        $this->vars = $arVars;
        $this->server = $arServer;
    }

    public static function create(): Request
    {
        return new Request($_REQUEST, $_SERVER);
    }

    public function getPath(): string
    {
        return parse_url($this->server['REQUEST_URI'], PHP_URL_PATH);
    }

    public function get(string $key)
    {
       return $this->vars[$key] ?? null;
    }
}

class Response
{
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public static function render(string $page, array $arVars): Response
    {
        if (file_exists($page)) {
            extract($arVars);
            ob_start();
            include $page;
            $content = ob_get_contents();
            ob_end_clean();
            return new Response($content);
        } else {
            throw new Exception('Page not found');
        }
    }

    public function send(): void
    {
        echo $this->message;
    }
}

header('Content-Type: text/html; charset=utf-8');

$routes = [];
$routes['/test.php'] =  ['ParserController', 'parse'];

$request = Request::create();
$path = $request->getPath();
if (isset($routes[$path])) {
    $response = call_user_func([new $routes[$path][0], $routes[$path][1]], $request);
} else {
    $response = new Response('Not Found');
}

$response->send();