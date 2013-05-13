'use strict';

/**
 * App
 */
angular.module('ZenstruckMedia', [])
    .config(['$routeProvider', function($routeProvider) {
        $routeProvider
            .when('/', {templateUrl: 'list.html', controller: listCtrl})
            .otherwise({redirectTo: '/'});
    }])

    .config(
        function($interpolateProvider){
            $interpolateProvider.startSymbol('[[').endSymbol(']]');
        })

    /**
     * Services
     */
    .factory('Config', function() {
        var $el = $('#zenstruck-media');

        var $mkdirDialog = $('#zenstruck-media-mkdir');
        $mkdirDialog.on('shown', function() {
            $(this).find('input').first().focus();
        });

        return {
            routes: {
                list_url: $el.data('list-url'),
                mkdir_url: $el.data('mkdir-url'),
                delete_url: $el.data('delete-url')
            },
            opener: $el.data('opener'),
            opener_param: $el.data('opener-param')
        }
    })
;

/**
 * Controllers
 */
function listCtrl($scope, $routeParams, $http, Config) {
    $scope.path = $routeParams.path ? $routeParams.path : '';
    $scope.ancestors = $scope.path.split('/');
    $scope.new_dir_name = '';
    $scope.ancestors.pop();
    $scope.prevPath = $scope.ancestors.join('/');
    $scope.files = [];
    $scope.pathHistory = [];
    $scope.alert = { message: '', 'type': 'success'};

    // setup history paths
    var history_paths = [];
    $scope.ancestors.forEach(function(item) {
        history_paths.push(item);
        $scope.pathHistory.push({ name: item, path: history_paths.join('/') });
    });

    $scope.refresh = function() {
        $http.get(Config.routes.list_url, { params: {path: $scope.path } })
            .success(function(data) {
                $scope.files = data;
            })
            .error(function(data) {
                $scope.setAlert(data.message, 'error');
            })
        ;
    };

    $scope.delete = function(file) {
        $http.delete(Config.routes.delete_url, {
            params: {
                path: $scope.path,
                type: file.type,
                filename: file.filename
            }
        })
        .success(function(data) {
            $scope.setAlert(data.message);
            $scope.refresh();
        })
        .error(function(data) {
            $scope.setAlert(data.message, 'error');
        })
    };

    $scope.mkdir = function() {
        if (!$scope.new_dir_name) {
            $scope.setAlert('No directory name was entered.', 'error');
            return;
        }

        $http.post(Config.routes.mkdir_url, {}, {
                params: {
                    path: $scope.path,
                    dir_name: $scope.new_dir_name
                }
            })
            .success(function(data) {
                $scope.setAlert(data.message);
                $scope.refresh();
            })
            .error(function(data) {
                $scope.setAlert(data.message, 'error');
            })
        ;

        $scope.new_dir_name = '';
    };

    $scope.setAlert = function(message, type) {
        if (!type) {
            type = 'success';
        }

        $scope.alert.message = message;
        $scope.alert.type = type;
    };

    $scope.clickFile = function(file) {
        switch (Config.opener) {
            case 'ckeditor':
                if (window.opener.CKEDITOR) {
                    window.opener.CKEDITOR.tools.callFunction(Config.opener_param, file.web_path);
                    window.close();
                }
                break;

            case 'media-widget':
                if (parent.ZenstuckMedia) {
                    parent.ZenstuckMedia.currentMediaInputFile = file.web_path;
                    parent.jQuery.fancybox.close();
                }
                break;
        }
    };

    $scope.getPathForFilename = function(filename) {
        if (!$scope.path) {
            return filename;
        }

        return $scope.path + '/' + filename;
    };

    $scope.refresh();
}

