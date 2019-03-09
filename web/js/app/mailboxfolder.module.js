(function() {
    'use strict';
    angular
    .module('mailboxfolder', ['ngMaterial', 'ngAnimate'])
    .config(function($mdThemingProvider) {
        $mdThemingProvider.theme('default')
            .primaryPalette('indigo')
            .accentPalette('deep-orange');
    });
})();
