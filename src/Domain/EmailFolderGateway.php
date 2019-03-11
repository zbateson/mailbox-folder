<?php
namespace ZBateson\MailboxFolder\Domain;

use ZBateson\MailboxFolder\EmailFactory;
use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Message;
use JamesMoss\Flywheel\Repository;
use JamesMoss\Flywheel\Document;
use JamesMoss\Flywheel\Query;
use DirectoryIterator;
use DateTime;

use Psr\Log\LoggerInterface;

/**
 * Description of FolderGateway
 *
 * @author Zaahid Bateson
 */
class EmailFolderGateway
{
    private $parser;
    private $repository;
    private $path;
    private $logger;

    public function __construct(MailMimeParser $parser, Repository $repository, $path, $logger)
    {
        $this->parser = $parser;
        $this->repository = $repository;
        $this->path = $path;
        $this->logger = $logger;
        $this->rescan();
    }

    private function getEmailHeader($header, Message $message)
    {
        $h = $message->getHeader($header);
        if ($h === null) {
            return null;
        }
        $ret = '';
        foreach ($h->getAddresses() as $addr) {
            $ret .= $addr->getName() . ' <' . $addr->getEmail() . '>';
        }
        return $ret;
    }

    private function rescan()
    {
        $di = new DirectoryIterator($this->path);
        $foundIds = [];

        foreach ($di as $fi) {
            if (!$fi->isFile() || !$fi->isReadable()) {
                continue;
            }

            $id = hash('crc32', $fi->getPathname());
            if ($this->repository->findById($id)) {
                array_push($foundIds, $id);
                continue;
            }

            $handle = fopen($fi->getPathname(), 'r');
            $message = $this->parser->parse($handle);
            fclose($handle);

            if ($message->getHeader('from') === null) {
                $this->logger->notice('Unable to parse email ' . $fi->getPathname() . '.');
                continue;
            }

            if ($message->getHeader('date') === null) {
                $message->setRawHeader('date', DateTime::createFromFormat('U', $fi->getCTime())->format('r'));
            }

            $preview = '';
            if ($message->getTextStream() !== null) {
                $stream = $message->getTextStream();
                $preview = $stream->read(500);
            } elseif ($message->getHtmlStream() !== null) {
                $stream = $message->getHtmlStream();
                $html = $stream->read(10240);
                $preview = substr(html_entity_decode(strip_tags($html)), 0, 500);
            }
            $preview = preg_replace('/\s+/', ' ', $preview);

            $email = new Document([
                'file' => $fi->getPathname(),
                'subject' => $message->getHeaderValue('subject'),
                'date' => $message->getHeader('date')->getDateTime(),
                'from' => $this->getEmailHeader('from', $message),
                'to' => $this->getEmailHeader('to', $message),
                'cc' => $this->getEmailHeader('cc', $message),
                'bcc' => $this->getEmailHeader('bcc', $message),
                'attachmentCount' => $message->getAttachmentCount(),
                'preview' => $preview
            ]);
            $email->setId($id);

            if (!$this->repository->store($email)) {
                $this->logger->error('Unable to store to repository');
                return;
            } else {
                $this->logger->info('Stored email: ' . $email->getId());
            }
            array_push($foundIds, $id);
        }

        $this->deleteRemoved($foundIds);
    }

    private function deleteRemoved(array $foundIds)
    {
        $all = $this->repository->findAll();
        foreach ($all as $doc) {
            if (!in_array($doc->getId(), $foundIds)) {
                $this->repository->delete($doc);
            }
        }
    }

    private function applyFilter(Query $query, array $filter)
    {
        $query = $query->where('attachmentCount', '>', ($filter['attachments']) ? 0 : -1);
        if (!empty($filter['newerThan'])) {
            $query = $query->andWhere('date', '>', $filter['newerThan']);
        }
        if (!empty($filter['startDate'])) {
            $query = $query->andWhere('date', '>=', $filter['startDate']);
        }
        if (!empty($filter['endDate'])) {
            $query = $query->andWhere('date', '<=', $filter['endDate']);
        }
        $query->orderBy('date DESC');
        $res = $query->execute();
        $arr = [];
        foreach ($res as $d) {
            if (isset($filter['text']) && $filter['text'] !== '') {
                if (stripos($d->subject, $filter['text']) === false
                    && stripos($d->from, $filter['text']) === false
                    && stripos($d->to, $filter['text']) === false
                    && stripos($d->cc, $filter['text']) === false
                    && stripos($d->bcc, $filter['text']) === false
                    && stripos($d->preview, $filter['text']) === false) {
                    $this->logger->debug($d->subject . ' ' . stripos($d->from, $filter['text']));
                    continue;
                }
            }
            array_push($arr, $d);
        }
        return $arr;
    }

    /**
     * Returns all the emails within the passed limits.
     *
     * @param int $skip
     * @param int $count
     * @return array
     */
    public function fetchAll($skip, $count, array $filter)
    {
        $arr = $this->applyFilter($this->repository->query(), $filter);
        if (count($arr) > $skip) {
            return array_slice($arr, $skip, $count);
        }
        return [];
    }

    /**
     * Returns emails newer than the passed email Document.
     *
     * @param Document $email
     * @return array
     */
    public function fetchNewerThan(Document $email, $filter)
    {
        $filter['newerThan'] = $email->date;
        $ret = $this->applyFilter($this->repository->query(), $filter);
    }

    public function getTotal($filter)
    {
        return count($this->applyFilter($this->repository->query(), $filter));
    }

    /**
     * Returns a Document object for the email with the passed id.
     *
     * @param string $id
     * @return Document
     */
    public function fetchById($id)
    {
        return $this->repository->findById($id);
    }

    /**
     * Returns a Message object for the email with the passed id.
     *
     * @param string $id
     * @return \ZBateson\MailMimeParser\Message
     */
    public function fetchMessageById($id)
    {
        $doc = $this->repository->findById($id);
        if (!$doc) {
            return null;
        }

        $filepath = $doc->file;
        $handle = fopen($filepath, 'r');
        $message = $this->parser->parse($handle);
        fclose($handle);

        $ctime = DateTime::createFromFormat('U', filectime($filepath));
        $message->setRawHeader(
            'Date',
            $message->getHeaderValue(
                'Date',
                $ctime->format('r')
            )
        );
        return $message;
    }
}
