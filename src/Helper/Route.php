<?php
namespace ZBateson\MailboxFolder\Helper;

use Aura\Router\Router;

class Route
{
    protected $router;
    protected $basepath;

    public function __construct(Router $router, $basepath)
    {
        $this->router = $router;
        $this->basepath = $basepath;
    }

    public function __invoke($name, $data = array())
    {
        if (rtrim($name, '/') === '') {
            return rtrim($this->basepath, '/');
        }
        return $this->router->generate($name, $data);
    }
}