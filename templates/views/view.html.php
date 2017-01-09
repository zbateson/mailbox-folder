<?php
$subject = $this->email->getHeaderValue('Subject', '<Empty Subject>');
$this->title($subject);
?>
<h1 class="heading">
    <a href="<?= $this->escape()->attr($this->route('list')); ?>">Outgoing emails - listing</a> &gt;
    <span class="sub"><?= $this->escape()->html($subject);?></span>
</h1>
<div class="email-headers">
    <dl>
        <?php
        foreach ($this->email->getHeaders() as $header) {
            echo "<dt>", $this->escape()->html($header->getName()), "</dt>\r\n";
            echo "<dd>&nbsp;";
            if ($header instanceof \ZBateson\MailMimeParser\Header\AddressHeader) {
                $addresses = array_map(
                    function($a) {
                        $name = $a->getName();
                        if ($name !== '') {
                            return $name . ' <' . $a->getEmail() . '>';
                        }
                        return $a->getEmail();
                    },
                    $header->getAddresses()
                );
                echo $this->escape()->html(implode(', ', $addresses));
            } elseif ($header instanceof \ZBateson\MailMimeParser\Header\ParameterHeader) {
                $parameters = array_map(
                    function($a) {
                        if ($a instanceof \ZBateson\MailMimeParser\Header\Part\ParameterPart) {
                            return $a->getName() . '=' . $a->getValue();
                        }
                        return $a->getValue();
                    },
                    $header->getParts()
                );
                echo $this->escape()->html(implode('; ', $parameters));
            } else {
                echo $this->escape()->html($header->getValue());
            }
            echo "</dd>";
        }
        ?>
    </dl>
    <div class="back">
        <a href="<?= $this->escape()->attr($this->route('list')); ?>">Back to email list</a>
    </div>
</div>

<p class="email-body">
    <?php
    $html = $this->email->getHtmlContent();
    if ($html !== null) {
        echo preg_replace('/^.*?<body>(.*?)<\/body>.*$/ims', '$1', $html);
    } else {
        echo nl2br($this->escape()->html($this->email->getTextContent()));
    }
    ?>
</p>
