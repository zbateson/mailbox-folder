<?php
namespace ZBateson\MailboxFolder\Helper;

use Aura\Router\Router;

class Route
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function __invoke($name, $data = array())
    {
        return $this->router->generate($name, $data);
    }
}