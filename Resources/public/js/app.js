'use strict';

/**
 * App
 */
angular.module('ZenstruckMedia', ['ngResource'])
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

        return {
            routes: {
                list_url: $el.data('list-url')
            },
            opener: $el.data('opener'),
            opener_param: $el.data('opener-param')
        }
    })
    .factory('File', function($resource, Config) {
        return $resource(Config.routes.list_url, {}, {
            list: { method: 'GET', isArray: true }
        });
    })
;

/**
 * Controllers
 */
function listCtrl($scope, $routeParams, File, Config) {
    $scope.path = $routeParams.path ? $routeParams.path : '';
    $scope.ancestors = $scope.path.split('/');
    $scope.ancestors.pop();
    $scope.prevPath = $scope.ancestors.join('/');
    $scope.files = File.list({path: $scope.path});
    $scope.pathHistory = [];

    // setup history paths
    var history_paths = [];
    $scope.ancestors.forEach(function(item) {
        history_paths.push(item);
        $scope.pathHistory.push({ name: item, path: history_paths.join('/') });
    });

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
}

