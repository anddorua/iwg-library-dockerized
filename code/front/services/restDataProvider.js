angular.module('iwg.lib.rest', [])
	.factory('librest', function($q){
		var sayHello = function(msg){
			console.log(msg);
		};


		var fakeQuery = function(url, method, request){
			return $q(function(resolve, reject){
				if (url == '/categories') {
					resolve( { cat: "Hello"} )
				} else {
					reject("wrong url");
				}
			});
		};

		var actualQueryMethod = fakeQuery;

		var queryWithErrorDisplay = function(url, method, request, onsuccess){
			actualQueryMethod(url, method, request).then(
				onsuccess,
				function(error){
					console.log(error);
				}
			);
		};

		var catGetList = function(onsuccess){
			queryWithErrorDisplay("/categories", "GET", {}, onsuccess);
		};

		return {
			sayHello: sayHello,
			categories:{
				getList: catGetList
			}
		}
	});