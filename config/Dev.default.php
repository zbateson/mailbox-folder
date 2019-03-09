<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Dev extends Config
{
    public function define(Container $di)
    {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', true);

        $di->values['maildir'] = dirname(__DIR__) . '/vendor/zbateson/mail-mime-parser/tests/_data/emails';
    }

    public function modify(Container $di)
    {
    }
}
