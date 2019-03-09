<?php
$this->title(' - ' . $this->appName);
?>
<!DOCTYPE html>
<html ng-app="mailboxfolder" ng-controller="MainController as vm">
    <head>
        <meta charset="UTF-8">
        <?= $this->title(); ?>

        <link rel="stylesheet" type="text/css" href="<?= $this->escape()->attr($this->route(''));?>/css/default.css">
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.12/angular-material.min.css">

        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.6/angular.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.6/angular-animate.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.6/angular-aria.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.6/angular-messages.min.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/angular_material/1.1.12/angular-material.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>

        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic">

        <script src="js/app/mailboxfolder.module.js"></script>
        <script src="js/app/main.controller.js"></script>
        <script src="js/app/emails.controller.js"></script>
    </head>
    <body>
        <?= $this->getContent(); ?>
    </body>
</html>
