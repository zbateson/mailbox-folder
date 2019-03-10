/**
 * MainController
 * @namespace mailboxfolder
 */
(function() {
    'use strict';

    angular
        .module('mailboxfolder')
        .controller('EmailsController', EmailsController);

    var serviceUrl = 'api/emails';

    function Emails($http) {

        var self = this;

        this.service = serviceUrl;
        this.pages = {};
        this.pageSize = 50;
        this.numItems = 0;

        this.getItemAtIndex = getItemAtIndex;
        this.getLength = getLength;
        this.fetchNewer = fetchNewer;

        fetchItemCount();

        //////////////////////

        function getItemAtIndex(index) {
            var pageNumber = Math.floor(index / self.pageSize);
            var page = self.pages[pageNumber];

            if (page) {
                return page[index % self.pageSize];
            } else if (page !== null) {
                // by default 'undefined', set to null if it's already being fetched
                fetchPage(pageNumber);
            }
        }

        function getLength() {
            return self.numItems;
        }

        function fetchPage(pageNumber) {
            // Set the page to null so we know it is already being fetched.
            self.pages[pageNumber] = null;

            var params = { page: pageNumber, perPage: self.pageSize };
            $http.get(self.service, { params: params }).then(function(response) {
                self.pages[pageNumber] = response.data.emails;
            });
        }

        function fetchItemCount() {
            $http.get(self.service, { params: { total: true } }).then(angular.bind(self, function(response) {
                this.numItems = response.data.count;
            }));
        }

        function fetchNewer() {
            if (self.pages && self.pages[0] !== null && self.pages[0].length > 0) {
                $http.get(self.service, { params: { newer: self.pages[0][0].id } }).then(angular.bind(self, function(response) {
                    this.numItems += response.data.emails.length;
                    for (var i = response.data.emails.length - 1; i > -1; --i) {
                        self.pages[0].unshift(response.data.emails[i]);
                    }
                }));
            }
        }
    }

    /**
     * @namespace EmailsController
     * @desc
     * @memberof mailboxfolder
     */
    EmailsController.$inject = [ '$scope', '$http', '$interval', '$mdDialog', '$sce', '$window' ];
    function EmailsController($scope, $http, $interval, $mdDialog, $sce, $window) {

        var windowFocused = true;
        var win = angular.element($window);
        win.on('focus', onFocus);
        win.on('blur', onBlur);

        $scope.$on("$destroy", function handler() {
            win.off('focus', onFocus);
            win.off('blur', onBlur);
            $interval.cancel(interval);
        });

        // ViewModel
        var vm = this;
        vm.emails = new Emails($http);
        vm.emailDialogTitle = 'Opening Email...';
        vm.selectedEmail = null;
        vm.selectedHtml = null;
        vm.showHtml = true;

        vm.openEmailDialog = openEmailDialog;
        vm.cancelEmailDialog = cancelEmailDialog;
        vm.formatDate = formatDate;
        vm.filterSelectedHeaders = filterSelectedHeaders;
        vm.isRead = isRead;
        vm.encode = encode;

        $window.resizeIframe = resizeIframe;
        var interval = $interval(fetchNewerEmails, 4000);

        //////////////////////////

        function formatDate(date) {
            return moment(date).fromNow();
        }

        function openEmailDialog($event, item) {
            vm.emailDialogTitle = 'Loading Email ' + item.id + '...';
            $mdDialog.show({
                multiple: true,
                contentElement: '#emailDialog',
                clickOutsideToClose: true,
                targetEvent: $event
            }).then(function() {
                vm.selectedEmail = null;
            }, function() {
                vm.selectedEmail = null;
            });

            var service = serviceUrl + "/" + item.id;
            $http.get(service).then(function(response) {
                vm.selectedEmail = response.data.email;
                if (vm.selectedEmail.html) {
                    vm.showHtml = true;
                } else {
                    vm.showHtml = false;
                }
                vm.emailDialogTitle = vm.selectedEmail.subject;
                $window.localStorage.setItem(item.id, true);
            });
        }

        function cancelEmailDialog() {
            $mdDialog.cancel();
        }

        function filterSelectedHeaders() {
            if (vm.selectedEmail && vm.selectedEmail.headers) {
                return vm.selectedEmail.headers.filter(function(e) {
                    return ([
                            'subject', 'from', 'to', 'cc', 'bcc', 'date'
                        ].indexOf(e.name.toLowerCase()) === -1);
                });
            }
            return [];
        }

        function encode(input) {
            return $window.encodeURIComponent(input);
        }

        function resizeIframe(ob) {
            var emailHtml = vm.selectedEmail.html.replace(
                /<img\b[^>]*src\s*=\s*"(cid:([^"]*))"[^>]*>/ig,
                function(match, p1, p2) {
                    var att = vm.selectedEmail.attachments;
                    for (var i = 0; i < att.length; ++i) {
                        if (att[i].contentId === p2) {
                            return match.replace(
                                p1,
                                'emails/' + vm.selectedEmail.id
                                    + '/attachments/' + att[i].id + '/'
                                    + $window.encodeURIComponent(att[i].name) +
                                    '?inline=true'
                            );
                        }
                    }
                    return match;
                }
            );
            ob.contentDocument.open();
            ob.contentDocument.write(emailHtml);
            ob.contentDocument.close();
            ob.contentDocument.body.style.margin = "0px";
            ob.contentDocument.body.firstElementChild.style.marginTop = "0px";
            var a = ob.contentDocument.getElementsByTagName("a");
            for (var i = 0; i < a.length; ++i) {
                a[0].setAttribute("target", "_blank");
            }
            ob.style.height = 0;
            ob.style.height = ob.contentDocument.body.scrollHeight + 'px';
        }

        function onFocus() {
            if (!windowFocused) {
                windowFocused = true;
                fetchNewerEmails();
            }
        }

        function onBlur() {
            windowFocused = false;
        }

        function fetchNewerEmails() {
            if (windowFocused) {
                vm.emails.fetchNewer();
            }
        }

        function isRead(id) {
            return $window.localStorage.getItem(id);
        }
    }
})();

