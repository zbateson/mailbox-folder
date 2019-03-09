<?php
namespace ZBateson\MailboxFolder\App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;
use Aura\View\View;
use ZBateson\MailboxFolder\Domain\EmailFolderGateway;

/**
 * Description of EmailListRestAction
 *
 * @author Zaahid Bateson
 */
class EmailListRestAction
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

    private function getArrayFromResult($res)
    {
        $returnFetchAllFields = [
            'subject', 'date', 'from', 'to', 'cc', 'bcc',
            'attachmentCount', 'preview'
        ];

        $ret = [];
        foreach ($res as $doc) {
            $a = [ 'id' => $doc->getId() ];
            foreach ($returnFetchAllFields as $field) {
                $val = $doc->getNestedProperty($field);
                if ($val instanceof DateTime) {
                    $val = $val->format('Y-m-d\TH:i:s.vP');
                }
                $a[$field] = $val;
            }
            array_push($ret, $a);
        }
        return $ret;
    }

    public function __invoke()
    {
        $page = $this->request->query->get('page');
        $count = $this->request->query->get('perPage');
        $total = $this->request->query->get('total');
        $newer = $this->request->query->get('newer');

        $this->response->content->setType('application/json');
        if (filter_var($total, FILTER_VALIDATE_BOOLEAN)) {
            $this->response->content->set(json_encode([ 'count' => $this->emailFolderGateway->getTotal() ]));
            return;
        }

        $list = [];
        if ($newer) {
            $email = $this->emailFolderGateway->fetchById($newer);
            if ($email) {
                $list = $this->getArrayFromResult($this->emailFolderGateway->fetchNewerThan($email));
            }
        } else {
            if (empty($page) || $page < 0) {
                $page = 0;
            }
            if (empty($count) || $count < 0) {
                $count = 50;
            }
            $skip = $page * $count;
            $list = $this->getArrayFromResult($this->emailFolderGateway->fetchAll($skip, $count));
        }
        $this->response->content->set(json_encode([ 'emails' => $list ]));
    }
}
