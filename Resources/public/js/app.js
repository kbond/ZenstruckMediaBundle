'use strict';

/**
 * App
 */
var ZenstruckMediaApp = angular.module('ZenstruckMedia', ['ngUpload'])
    .config(['$routeProvider', '$interpolateProvider', function($routeProvider, $interpolateProvider) {
        // configure routes
        $routeProvider
            .when('/', {templateUrl: 'list.html', controller: listCtrl})
            .otherwise({redirectTo: '/'})
        ;

        // use square brackets
        $interpolateProvider.startSymbol('[[').endSymbol(']]');
    }])

    /**
     * Services
     */
    .factory('ZenstruckMediaItem', function() {
        return {
            click: function(file) {
                if (file.image && jQuery.fancybox) {
                    jQuery.fancybox.open({
                        href : file.web_path,
                        title : file.filename
                    });

                    return;
                }

                window.open(file.web_path, '_blank');
            }
        }
    })

    .factory('ZenstruckMediaConfig', function() {
        var $el = $('#zenstruck-media');

        // configure hover event for file actions
        $(document)
            .on('mouseenter', '#zenstruck-media-thumb li', function() {
                $('.zenstruck-media-actions', $(this)).removeClass('hide');
            })
            .on('mouseleave', '#zenstruck-media-thumb li', function() {
                $('.zenstruck-media-actions', $(this)).addClass('hide');
            })
        ;

        return {
            routes: {
                list_url: $el.data('list-url'),
                mkdir_url: $el.data('mkdir-url'),
                delete_url: $el.data('delete-url'),
                rename_url: $el.data('rename-url'),
                upload_url: $el.data('upload-url')
            },
            filesystem: $el.data('filesystem'),
            opener: $el.data('opener'),
            opener_param: $el.data('opener-param')
        }
    })
;

/**
 * Controllers
 */
var listCtrl = ['$scope', '$routeParams', '$http', 'ZenstruckMediaConfig', 'ZenstruckMediaItem',
    function($scope, $routeParams, $http, Config, Media) {

    // public properties
    $scope.path = $routeParams.path ? $routeParams.path : '';
    $scope.ancestors = $scope.path.split('/');
    $scope.new_dir_name = '';
    $scope.rename_old = null;
    $scope.rename_new = '';
    $scope.current_dir = $scope.ancestors.pop();
    $scope.prevPath = $scope.ancestors.join('/');
    $scope.files = [];
    $scope.pathHistory = [];
    $scope.alert = { message: '', 'type': 'success'};
    $scope.upload_url = Config.routes.upload_url + '?filesystem=' + Config.filesystem + '&path=' + $scope.path;

    // private properties
    var $renameDialog = $('#zenstruck-media-rename');
    var $mkdirDialog = $('#zenstruck-media-mkdir');
    var $uploadDialog = $('#zenstruck-media-upload');

    // setup history paths
    var history_paths = [];
    $scope.ancestors.forEach(function(item) {
        history_paths.push(item);
        $scope.pathHistory.push({ name: item, path: history_paths.join('/') });
    });

    // setup autofocus
    $renameDialog.on('shown', function() {
        $(this).find('input').first().focus(function() {
            this.select();
        }).focus();
    });
    $mkdirDialog.on('shown', function() {
        $(this).find('input').first().focus();
    });
    $uploadDialog.on('shown', function() {
        $(this).find('input').first().val('');
    });

    var buildHttpParams = function(params) {
        if (!params) {
            params = {};
        }

        params.path = $scope.path;
        params.filesystem = Config.filesystem;

        return params;
    };

    $scope.refresh = function() {
        $http.get(Config.routes.list_url, { params: buildHttpParams() })
            .success(function(data) {
                $scope.files = data;
            })
            .error(function(data) {
                $scope.setAlert(data.message, 'error');
            })
        ;
    };

    $scope.openMkdirDialog = function() {
        $mkdirDialog.modal('show');
    };

    $scope.openRenameDialog = function(file) {
        $scope.rename_old = file;
        $scope.rename_new = file.filename;
        $renameDialog.modal('show');
    };

    $scope.openUploadDialog = function() {
        $uploadDialog.modal('show');
    };

    $scope.rename = function() {
        $http.put(Config.routes.rename_url, {}, {
            params: buildHttpParams({
                old_name: $scope.rename_old.filename,
                new_name: $scope.rename_new
            })
        })
        .success(function(data) {
            $scope.setAlert(data.message);
            $scope.refresh();
        })
        .error(function(data) {
            $scope.setAlert(data.message, 'error');
        })
    };

    $scope.upload = function(content, completed) {
        if (completed && content.length > 0) {
            var response = JSON.parse(content);
            $scope.setAlert(response.message, response.code == 201 ? 'success' : 'error');
            $scope.refresh();

            $uploadDialog.modal('hide');
        }
    };

    $scope.delete = function(file) {
        $http.delete(Config.routes.delete_url, {
            params: buildHttpParams({
                filename: file.filename
            })
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
                params: buildHttpParams({
                    dir_name: $scope.new_dir_name
                })
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
                if (parent.ZenstruckMediaWidget) {
                    parent.ZenstruckMediaWidget.selectFile(file.web_path);
                }
                break;

            default:
                Media.click(file);
        }
    };

    $scope.getPathForFilename = function(filename) {
        if (!$scope.path) {
            return filename;
        }

        return $scope.path + '/' + filename;
    };

    $scope.refresh();
}];
