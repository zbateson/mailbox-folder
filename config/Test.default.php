<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Test extends Config
{
    public function define(Container $di)
    {
        $di->values['basepath'] = '/';
        $di->setter['ZBateson\MailboxFolder\Domain\EmailFolderGateway']['setPath'] = '/path/to/mailbox/folder';
    }

    public function modify(Container $di)
    {
    }
}
