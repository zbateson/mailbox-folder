<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Prod extends Config
{
    public function define(Container $di)
    {
        $di->values['logfile'] = '/path/to/log/file.log';
        $di->values['basepath'] = '/';
        $di->values['appname'] = 'mailbox-folder';
        $di->values['maildir'] = '/path/to/mailbox/folder';
    }

    public function modify(Container $di)
    {
    }
}
