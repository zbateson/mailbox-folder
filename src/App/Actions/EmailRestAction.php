<?php
namespace ZBateson\MailboxFolder\App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;
use ZBateson\MailboxFolder\Domain\EmailFolderGateway;
use ZBateson\MailMimeParser\IMessage;
use DOMDocument;
use DOMNode;
use DOMElement;

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
    private $basepath;

    public function __construct(
        Request $request,
        Response $response,
        EmailFolderGateway $emailFolderGateway,
        $basepath
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->emailFolderGateway = $emailFolderGateway;
        $this->basepath = $basepath;
    }

    private function replaceHtmlContentId(IMessage $message, $emailId)
    {
        return preg_replace_callback(
            [
                '~<img\b[^>]*src\s*=\s*["\']\s*(cid:([^"\']+))["\'][^>]*>~i',
                '~\burl\(\s*[\'"]?(cid:([^\'"\)]+))[\'"]?\s*\)~i'
            ],
            function ($m) use ($message, $emailId) {
                foreach ($message->getAllAttachmentParts() as $id => $at) {
                    if ($at->getContentId() === $m[2]) {
                        return str_replace(
                            $m[1],
                            rtrim($this->basepath, '/') . '/emails/' . $emailId
                                . '/attachments/' . $id . '/'
                                . urlencode($at->getFilename())
                                . '?inline=true',
                            $m[0]
                        );
                    }
                }
            },
            $message->getHtmlContent()
        );
    }

    private function sanitizeNode(DOMNode $node)
    {
        $sanitize = [ 'script', 'iframe', 'link' ];
        $rm = [];
        foreach ($node->childNodes as $c) {
            if (!($c instanceof DOMElement)) {
                continue;
            }
            if (in_array($c->tagName, $sanitize)) {
                // removing here messes up the iterator
                array_push($rm, $c);
                continue;
            }
            if ($c->attributes !== null) {
                foreach ($c->attributes as $attr) {
                    if (stripos($attr->nodeName, 'on') === 0) {
                        $c->removeAttribute($attr->nodeName);
                    }
                }
                $href = $c->getAttribute('href');
                if (!empty($href) && stripos(trim($href), 'javascript:') === 0) {
                    $c->removeAttribute('href');
                }
            }
            if ($node->hasChildNodes()) {
                $this->sanitizeNode($c);
            }
        }
        foreach ($rm as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    private function sanitizeHtml($html)
    {
        $dom = new DOMDocument();
        $dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        $this->sanitizeNode($dom);
        return $dom->saveHTML();
    }

    private function prepareHtml(IMessage $message, $emailId)
    {
        $html = $message->getHtmlContent();
        if (!empty($html)) {
            return $this->sanitizeHtml($this->replaceHtmlContentId($message, $emailId));
        }
        return null;
    }

    public function __invoke($id)
    {
        $this->response->content->setType('application/json');

        $message = $this->emailFolderGateway->fetchMessageById($id);
        if (!$message) {
            $this->response->status->set('404', 'Not Found');
            return;
        }

        $resp = [
            'email' => [
                'id' => $id,
                'subject' => $message->getHeaderValue('subject'),
                'date' => $message->getHeader('date')->getDateTime()->format('Y-m-d\TH:i:s.vP'),
                'from' => $this->emailFolderGateway->getEmailArrayFromMessageHeader('from', $message),
                'to' => $this->emailFolderGateway->getEmailArrayFromMessageHeader('to', $message),
                'cc' => $this->emailFolderGateway->getEmailArrayFromMessageHeader('cc', $message),
                'bcc' => $this->emailFolderGateway->getEmailArrayFromMessageHeader('bcc', $message),
                'attachments' => [],
                'text' => $message->getTextContent(),
                'html' => $this->prepareHtml($message, $id),
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
