<?php
namespace ZBateson\MailboxFolder\App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;
use Aura\View\View;

/**
 * Description of EmailListAction
 *
 * @author Zaahid Bateson
 */
class MainAction
{
    private $request;
    private $response;
    private $view;
    private $appName;
    
    public function __construct(Request $request, Response $response, View $view, $appName)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->appName = $appName;
    }
    
    public function __invoke()
    {
        $this->view->setView('main.html');
        $this->view->setLayout('default.html');
        $this->view->setData([
			'appName' => $this->appName
		]);
        $this->response->content->set($this->view->__invoke());
    }
}
