<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Prod extends Config
{
    public function define(Container $di)
    {
        $di->setter['ZBateson\MailboxFolder\Domain\EmailFolderGateway']['setPath'] = '/path/to/mailbox/folder';
    }

    public function modify(Container $di)
    {
    }
}
