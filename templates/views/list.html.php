<?php
$this->title('Outgoing emails listing');
?>
<h1 class="heading">
    <a href="<?= $this->escape()->attr($this->route('list')); ?>">Outgoing emails - listing</a>
</h1>
<div class="inbox-list">
    <div class="row header">
        <div class="column subject">Subject</div>
        <div class="column date">Date</div>
        <div class="column to">To</div>
    </div>
    <?php
    foreach ($this->emails as $filename => $email) {
        $subject = $email->getHeaderValue('Subject', '<Empty Subject>');
        ?>
        <a href="<?= $this->escape()->attr($this->route('view') . '?name=' . urlencode($filename));?>" class="row">
            <span class="column subject"><?= $this->escape()->html($subject);?></span>
            <span class="column date"><?= $this->escape()->html($email->getHeader('Date')->getDateTime()->format('Y-m-d H:i:s'));?></span>
            <span class="column to"><?= $this->escape()->html($email->getHeaderValue('To'));?></span>
        </a>
        <?php
    }
    ?>
</div>
