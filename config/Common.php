<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    public function define(Container $di)
    {
        $di->set('aura/project-kernel:logger', $di->lazyNew('Monolog\Logger'));

        $di->values['maildir'] = '.';
        $di->values['basepath'] = '/';
        $di->values['writedir'] = sys_get_temp_dir() . '/mailbox-folder';
        $di->params['Aura\Router\Router']['basepath'] = $di->lazyValue('basepath');

        $di->params['Aura\View\View']['helpers'] = $di->lazyGet('aura/html:helper');
        $di->params['ZBateson\MailboxFolder\Helper\Route'] = [
            'router' => $di->lazyGet('aura/web-kernel:router'),
            'basepath' => $di->lazyValue('basepath')
        ];
        $di->params['Aura\Html\HelperLocator']['map']['route'] = $di->lazyNew('ZBateson\MailboxFolder\Helper\Route');

        $di->params['Aura\View\TemplateRegistry']['paths'] = [
            dirname(__DIR__) . '/templates/views',
            dirname(__DIR__) . '/templates/layouts',
        ];
        $di->set('view', $di->lazyNew('Aura\View\View'));

        $di->params['JamesMoss\Flywheel\Config'] = [
            'path' => $di->lazyValue('writedir')
        ];
        $di->params['JamesMoss\Flywheel\Repository'] = [
            'name' => $di->lazy(function ($dir) {
                return hash('crc32', $dir);
            }, $di->lazyValue('maildir')),
            'config' => $di->lazyNew('JamesMoss\Flywheel\Config')
        ];

        $di->params['ZBateson\MailboxFolder\Domain\EmailFolderGateway'] = [
            'parser' => $di->lazyNew('ZBateson\MailMimeParser\MailMimeParser'),
            'repository' => $di->lazyNew('JamesMoss\Flywheel\Repository'),
            'path' => $di->lazyValue('maildir'),
            'logger' => $di->lazyGet('aura/project-kernel:logger')
        ];
        $di->params['ZBateson\MailboxFolder\App\Actions\EmailListRestAction'] = [
            'request' => $di->lazyGet('aura/web-kernel:request'),
            'response' => $di->lazyGet('aura/web-kernel:response'),
            'emailFolderGateway' => $di->lazyNew('ZBateson\MailboxFolder\Domain\EmailFolderGateway'),
        ];
        $di->params['ZBateson\MailboxFolder\App\Actions\EmailRestAction'] = [
            'request' => $di->lazyGet('aura/web-kernel:request'),
            'response' => $di->lazyGet('aura/web-kernel:response'),
            'emailFolderGateway' => $di->lazyNew('ZBateson\MailboxFolder\Domain\EmailFolderGateway'),
        ];
        $di->params['ZBateson\MailboxFolder\App\Actions\EmailAttachmentAction'] = [
            'request' => $di->lazyGet('aura/web-kernel:request'),
            'response' => $di->lazyGet('aura/web-kernel:response'),
            'emailFolderGateway' => $di->lazyNew('ZBateson\MailboxFolder\Domain\EmailFolderGateway'),
        ];
        $di->params['ZBateson\MailboxFolder\App\Actions\EmailDownloadAction'] = [
            'request' => $di->lazyGet('aura/web-kernel:request'),
            'response' => $di->lazyGet('aura/web-kernel:response'),
            'emailFolderGateway' => $di->lazyNew('ZBateson\MailboxFolder\Domain\EmailFolderGateway'),
        ];
        $di->params['ZBateson\MailboxFolder\App\Actions\MainAction'] = [
            'request' => $di->lazyGet('aura/web-kernel:request'),
            'response' => $di->lazyGet('aura/web-kernel:response'),
            'view' => $di->lazyGet('view'),
            'appName' => $di->lazyValue('appname'),
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

        $router->add('main', '/')
            ->setValues(['action' => 'main']);
        $router->add('list', '/api/emails')
            ->setValues(['action' => 'list']);
        $router->add('view', '/api/emails/{id}')
            ->setValues(['action' => 'view']);
        $router->add('download', '/emails/{id}.mime')
            ->setValues(['action' => 'download']);
        $router->add('attachment', '/emails/{emailId}/attachments/{id}/{name}')
            ->setValues(['action' => 'attachment']);
    }

    public function modifyWebDispatcher($di)
    {
        $dispatcher = $di->get('aura/web-kernel:dispatcher');
        $dispatcher->setObject(
            'main',
            $di->lazyNew('ZBateson\MailboxFolder\App\Actions\MainAction')
        );
        $dispatcher->setObject(
            'list',
            $di->lazyNew('ZBateson\MailboxFolder\App\Actions\EmailListRestAction')
        );
        $dispatcher->setObject(
            'view',
            $di->lazyNew('ZBateson\MailboxFolder\App\Actions\EmailRestAction')
        );
        $dispatcher->setObject(
            'download',
            $di->lazyNew('ZBateson\MailboxFolder\App\Actions\EmailDownloadAction')
        );
        $dispatcher->setObject(
            'attachment',
            $di->lazyNew('ZBateson\MailboxFolder\App\Actions\EmailAttachmentAction')
        );
    }
}
