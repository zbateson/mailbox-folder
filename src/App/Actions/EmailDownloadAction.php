<?php
namespace ZBateson\MailboxFolder\App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;
use Aura\View\View;
use ZBateson\MailboxFolder\Domain\EmailFolderGateway;

/**
 * Description of EmailDownloadAction
 *
 * @author Zaahid Bateson
 */
class EmailDownloadAction
{
    private $request;
    private $response;
    private $emailFolderGateway;

    public function __construct(Request $request, Response $response, EmailFolderGateway $emailFolderGateway)
    {
        $this->request = $request;
        $this->response = $response;
        $this->emailFolderGateway = $emailFolderGateway;
    }

    public function __invoke($id)
    {
        $message = $this->emailFolderGateway->fetchMessageById($id);
        if (!$message) {
            $response->status->set('404', 'Not Found');
            return;
        }

        $this->response->headers->set('Content-Disposition', 'attachment; filename="' . $id . '.eml"');
        $this->response->content->setType('message/rfc822');
        $this->response->content->set($message);
    }
}
