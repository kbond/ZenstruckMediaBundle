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
    .factory('File', function($resource) {
        return $resource('/media/files', {}, {
            list: { method: 'GET', isArray: true }
        });
    })
;

/**
 * Controllers
 */
function listCtrl($scope, $routeParams, File) {
    $scope.path = $routeParams.path ? $routeParams.path : '';
    $scope.ancestors = $scope.path.split('/');
    $scope.ancestors.pop();
    $scope.prevPath = $scope.ancestors.join('/');

    console.log('"'+$scope.prevPath+'"');

    $scope.getPathForFilename = function(filename) {
        if (!$scope.path) {
            return filename;
        }

        return $scope.path + '/' + filename;
    };

    $scope.files = File.list({path: $scope.path});
}

