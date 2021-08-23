<?php
namespace ZBateson\MailboxFolder\App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;
use Aura\View\View;
use ZBateson\MailboxFolder\Domain\EmailFolderGateway;

/**
 * Description of EmailAttachmentAction
 *
 * @author Zaahid Bateson
 */
class EmailAttachmentAction
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

    public function __invoke($emailId, $id, $name)
    {
        $message = $this->emailFolderGateway->fetchMessageById($emailId);
        if (!$message || !$message->getAttachmentPart(intval($id))) {
            var_dump('here');
            //$response->status->set('404', 'Not Found');
            return;
        }
        $part = $message->getAttachmentPart(intval($id));

        $inline = $this->request->query->get('inline');
        if (!filter_var($inline, FILTER_VALIDATE_BOOLEAN)) {
            $this->response->headers->set('Content-Disposition', 'attachment');
        }

        $this->response->content->setType($part->getContentType());
        $this->response->content->set($part->getContent());
    }
}
