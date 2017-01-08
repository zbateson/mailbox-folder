<?php
$this->title(' - MailboxFolder');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="<?= $this->escape()->attr($this->route('/'));?>css/default.css">
        <?= $this->title(); ?>
    </head>
    <body>
        <?= $this->getContent(); ?>
    </body>
</html>
