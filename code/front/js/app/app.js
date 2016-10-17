'use strict';

// Declare app level module which depends on views, and components
angular.module('myApp', [
    'ngRoute',
    'myApp.version',
    'ui.bootstrap',
    'ui.bootstrap.demo',
    'iwg.lib.rest',
    'iwg.lib.alert',
    'ngResource'
])
/**
.config(['$locationProvider', '$routeProvider', function($locationProvider, $routeProvider) {
  $locationProvider.hashPrefix('!');

  $routeProvider.otherwise({redirectTo: '/view1'});
}])
**/
;
