<?php
namespace ZBateson\MailboxFolder\App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;
use Aura\View\View;
use ZBateson\MailboxFolder\Domain\EmailFolderGateway;

/**
 * Description of EmailListAction
 *
 * @author Zaahid Bateson
 */
class EmailListAction
{
    private $request;
    private $response;
    private $view;
    private $emailFolderGateway;
    
    public function __construct(Request $request, Response $response, View $view, EmailFolderGateway $emailFolderGateway)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->emailFolderGateway = $emailFolderGateway;
    }
    
    public function __invoke()
    {
        $this->view->setData([
            'emails' => $this->emailFolderGateway->fetchAll()
        ]);
        $this->view->setView('list.html');
        $this->view->setLayout('default.html');
        $this->response->content->set($this->view->__invoke());
    }
}
