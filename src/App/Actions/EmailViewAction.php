<?php
namespace ZBateson\MailboxFolder\App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;
use Aura\View\View;
use ZBateson\MailboxFolder\Domain\EmailFolderGateway;

/**
 * Description of EmailViewAction
 *
 * @author Zaahid Bateson
 */
class EmailViewAction
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
        $file = $this->request->query->get("name");
        $this->view->setData([
            'email' => $this->emailFolderGateway->fetchBy($file)
        ]);
        $this->view->setView('view.html');
        $this->view->setLayout('default.html');
        $this->response->content->set($this->view->__invoke());
    }
}
