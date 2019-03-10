<?php
namespace ZBateson\MailboxFolder\App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;
use Aura\View\View;
use ZBateson\MailboxFolder\Domain\EmailFolderGateway;

/**
 * Description of EmailRestAction
 *
 * @author Zaahid Bateson
 */
class EmailRestAction
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
        $this->response->content->setType('application/json');

        $message = $this->emailFolderGateway->fetchMessageById($id);
        if (!$message) {
            $response->status->set('404', 'Not Found');
            return;
        }

        $resp = [
            'email' => [
                'id' => $id,
                'subject' => $message->getHeaderValue('subject'),
                'date' => $message->getHeader('date')->getDateTime()->format('Y-m-d\TH:i:s.vP'),
                'from' => $message->getHeaderValue('from'),
                'to' => $message->getHeaderValue('to'),
                'cc' => $message->getHeaderValue('cc'),
                'bcc' => $message->getHeaderValue('bcc'),
                'attachments' => [],
                'text' => $message->getTextContent(),
                'html' => $message->getHtmlContent(),
                'headers' => []
            ]
        ];
        foreach ($message->getAllHeaders() as $h) {
            array_push($resp['email']['headers'], [ 'name' => $h->getName(), 'value' => $h->getValue() ]);
        }
        foreach ($message->getAllAttachmentParts() as $id => $at) {
            array_push($resp['email']['attachments'], [ 'id' => $id, 'name' => $at->getFilename(), 'contentId' => $at->getContentId(), 'contentType' => $at->getContentType() ]);
        }

        $this->response->content->set(json_encode($resp));
    }
}
