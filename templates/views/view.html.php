<?php
$subject = $this->email->getHeaderValue('Subject', '<Empty Subject>');
$this->title($subject);
?>
<h1 class="heading">
    <a href="/">Outgoing emails - listing</a> &gt;
    <span class="sub"><?= $this->escape()->html($subject);?></span>
</h1>
<div class="email-headers">
    <dl>
        <?php
        foreach ($this->email->getHeaders() as $header) {
            echo "<dt>", $this->escape()->html($header->getName()), "</dt>\r\n";
            echo "<dd>", $this->escape()->html($header->getValue()), "</dd>\r\n";
        }
        ?>
    </dl>
    <div class="back">
        <a href="/">Back to email list</a>
    </div>
</div>

<p class="email-body">
    <?php
    $html = $this->email->getHtmlContent();
    if ($html !== null) {
        echo preg_replace('/.*?<body>(.*?)<\/body>.*?/ims', '$1', $html);
    } else {
        echo nl2br($this->escape()->html($this->email->getTextContent()));
    }
    ?>
</p>
