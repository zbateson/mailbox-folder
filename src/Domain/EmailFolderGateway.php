<?php
namespace ZBateson\MailboxFolder\Domain;

use ZBateson\MailboxFolder\EmailFactory;
use ZBateson\MailboxFolder\EmailMetaDataFactory;
use ZBateson\MailMimeParser\MailMimeParser;
use DirectoryIterator;
use DateTime;

/**
 * Description of FolderGateway
 *
 * @author Zaahid Bateson
 */
class EmailFolderGateway
{
    private $mailMimeParser;
    private $path = '.';
    
    public function __construct(MailMimeParser $mailMimeParser)
    {
        $this->mailMimeParser = $mailMimeParser;
    }
    
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    /**
     * Returns an array of email messages as ZBateson\MailMimeParser\Message
     * objects.
     * 
     * @return \ZBateson\MailMimeParser\Message[]
     */
    public function fetchAll()
    {
        $di = new DirectoryIterator($this->path);
        $emails = [];
        foreach ($di as $fi) {
            if (!$fi->isFile() || !$fi->isReadable()) {
                continue;
            }
            $handle = fopen($fi->getPathname(), 'r');
            $message = $this->mailMimeParser->parse($handle);
            fclose($handle);
            $ctime = DateTime::createFromFormat('U', $fi->getCTime());
            $message->setRawHeader(
                'Date',
                $message->getHeaderValue(
                    'Date',
                    $ctime->format('r')
                )
            );
            $emails[$fi->getFilename()] = $message;
        }
        uasort(
            $emails,
            function ($a, $b) {
                return ($b->getHeader('Date')->getDateTime()->getTimestamp() - $a->getHeader('Date')->getDateTime()->getTimestamp());
            }
        );
        return $emails;
    }
    
    /**
     * Returns a Message object for the email
     * 
     * @param string $filename
     * @return \ZBateson\MailMimeParser\Message
     */
    public function fetchBy($filename)
    {
        $filepath = $this->path . DIRECTORY_SEPARATOR . $filename;
        $uctime = filectime($filepath);
        $handle = fopen($filepath, 'r');
        $message = $this->mailMimeParser->parse($handle);
        fclose($handle);
        $ctime = DateTime::createFromFormat('U', $uctime);
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
