<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    public function define(Container $di)
    {
        $di->set('aura/project-kernel:logger', $di->lazyNew('Monolog\Logger'));
        
        $di->params['Aura\View\View']['helpers'] = $di->lazyGet('aura/html:helper');
        $di->params['Aura\View\TemplateRegistry']['paths'] = [
            dirname(__DIR__) . '/templates/views',
            dirname(__DIR__) . '/templates/layouts',
        ];
        $di->set('view', $di->lazyNew('Aura\View\View'));
        
        $di->params['ZBateson\MailboxFolder\Domain\EmailFolderGateway'] = [
            'mailMimeParser' => $di->lazyNew('ZBateson\MailMimeParser\MailMimeParser'),
        ];
        $di->params['ZBateson\MailboxFolder\App\Actions\EmailListAction'] = [
            'request' => $di->lazyGet('aura/web-kernel:request'),
            'response' => $di->lazyGet('aura/web-kernel:response'),
            'view' => $di->lazyGet('view'),
            'emailFolderGateway' => $di->lazyNew('ZBateson\MailboxFolder\Domain\EmailFolderGateway'),
        ];
        $di->params['ZBateson\MailboxFolder\App\Actions\EmailViewAction'] = [
            'request' => $di->lazyGet('aura/web-kernel:request'),
            'response' => $di->lazyGet('aura/web-kernel:response'),
            'view' => $di->lazyGet('view'),
            'emailFolderGateway' => $di->lazyNew('ZBateson\MailboxFolder\Domain\EmailFolderGateway'),
        ];
    }

    public function modify(Container $di)
    {
        $this->modifyLogger($di);
        $this->modifyWebRouter($di);
        $this->modifyWebDispatcher($di);
    }

    public function modifyLogger(Container $di)
    {
        $project = $di->get('project');
        $mode = $project->getMode();
        $file = $project->getPath("tmp/log/{$mode}.log");

        $logger = $di->get('aura/project-kernel:logger');
        $logger->pushHandler($di->newInstance(
            'Monolog\Handler\StreamHandler',
            [
                'stream' => $file,
            ]
        ));
    }

    public function modifyWebRouter(Container $di)
    {
        $router = $di->get('aura/web-kernel:router');

        $router->add('list', '/')
            ->setValues(['action' => 'list']);
        $router->add('view', '/view')
            ->setValues(['action' => 'view']);
    }

    public function modifyWebDispatcher($di)
    {
        $dispatcher = $di->get('aura/web-kernel:dispatcher');
        $dispatcher->setObject(
            'list', 
            $di->lazyNew('ZBateson\MailboxFolder\App\Actions\EmailListAction')
        );
        $dispatcher->setObject(
            'view', 
            $di->lazyNew('ZBateson\MailboxFolder\App\Actions\EmailViewAction')
        );
    }
}
