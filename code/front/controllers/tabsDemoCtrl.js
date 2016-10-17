angular.module('ui.bootstrap.demo', ['ui.bootstrap', 'iwg.lib.rest', 'iwg.lib.alert', 'ngResource'])

    // thanks to http://www.webdeveasy.com/interceptors-in-angularjs-and-useful-examples/
    .factory('httpInterceptor', ['terminalService', '$q', function(terminalService, $q) {
        var urlToWatch = [
            {method: '*', urlMask: '/categories'},
            {method: '*', urlMask: '/authors'},
            {method: '*', urlMask: '/books'}
        ];

        function isNeedLog(method, url){
            return urlToWatch.some(function(item){
                return (item.method == method || item.method == "*")
                    && (new RegExp(item.urlMask, 'i')).test(url);
            });
        }

        var myInterceptor = {
            request: function(config){
                if (isNeedLog(config.method, config.url)) {
                    terminalService.out(">>> " + config.method + " " + config.url);
                }
                return config;
            },
            response: function(response){
                if (isNeedLog(response.config.method, response.config.url)) {
                    terminalService.out("<<< " + response.config.method + " " + response.config.url
                        + " " + response.status + " " + response.statusText
                        + (response.headers("Location") ? " Location:" + response.headers("Location") : ""));
                }
                return response;
            }
            ,
            responseError: function(response){
                if (isNeedLog(response.config.method, response.config.url)) {
                    terminalService.out("<<< " + response.config.method + " " + response.config.url
                        + " " + response.status + " " + response.statusText);
                }
                return $q.reject(response);
            }
        };

        return myInterceptor;
    }])
    .config(['$resourceProvider', function($resourceProvider) {
        // Don't strip trailing slashes from calculated URLs
        $resourceProvider.defaults.stripTrailingSlashes = false;
    }])

    .config(['$httpProvider', function($httpProvider) {
        $httpProvider.interceptors.push('httpInterceptor');
    }])

    .controller('AlertDemoCtrl', function (alertService, $scope) {
        $scope.alert = alertService;

        $scope.addAlert = function(msg) {
            $scope.alert.msg = msg;
        };

        $scope.closeAlert = function() {
            $scope.alert.msg = "";
        };
    })

    .controller('TerminalCtrl', function (terminalService, $scope) {
        $scope.lines = terminalService.lines;
        $scope.clear = function(){
            terminalService.clear();
        };
    })

    .controller('TabsDemoCtrl', function ($scope, $window, $resource, $http, $q, librest, alertService) {
          $scope.user = {
            isAuthorized: true,
            logout: function() {
              setTimeout(function() {
                $window.alert('Logout function not implemented');
              });
            }
          };

  var getter = function(id){
      return this.list.find(function(item) { return item.id == id; });
  };

    $scope.categories = {
        list:[],
        current: {},
        find: function(id){
            return this.list.find(function(item){ return item.id == id; });
        },
        get: getter,
        fetchList: function(){
            return $http({
                method:'GET',
                url:'/categories/'
            })
                .then(function(response){
                    $scope.categories.list = response.data;
                    $scope.categories.current = $scope.categories.list.length > 0 ? $scope.categories.list[0] : null;
                }, function(error){
                    alertService.setMsg(error);
                })
        },
        add: function(name) {
            return $http({
                method:'POST',
                url:'/categories/',
                data: {name: name}
            })
                .then(function(response){
                    $scope.categories.fetchList();
                }, function(error){
                    alertService.setMsg(error);
                });
        },
        update: function(id, name) {
            return $http({
                method:'PUT',
                url:'/categories/' + id,
                data: {name: name}
            })
                .then(function(response){
                    $scope.categories.fetchList()
                        .then(function(){ $scope.categories.current = $scope.categories.find(id); });
                }, function(error){
                    alertService.setMsg(error);
                });
        },
        delete: function(id) {
            return $http({
                method:'DELETE',
                url:'/categories/' + id,
                data: {}
            })
                .then(function(response){
                    $scope.categories.fetchList();
                }, function(error){
                    alertService.setMsg(error);
                });
        }
    };

    $scope.authors = {
        list:[],
        current: {},
        get: getter,
        getByList: function(idList) {
          return this.list.filter(function(author){ return idList.indexOf(author.id) >= 0; });
        },
        getListCopy: function(){
            var dest = [];
            angular.copy(this.list, dest);
            return dest;
        },

        fetchList: function(){
            return $http({
                method:'GET',
                url:'/authors/'
            })
                .then(function(response){
                    $scope.authors.list = response.data;
                    $scope.authors.current = $scope.authors.list.length > 0 ? $scope.authors.list[0] : null;
                }, function(error){
                    alertService.setMsg(error);
                })
        },

        add: function(name, fname, yob) {
            return $http({
                method:'POST',
                url:'/authors/',
                data: { name: name, f_name: fname, year_of_birth: yob }
            })
                .then(function(response){
                    $scope.authors.fetchList();
                }, function(error){
                    alertService.setMsg(error);
                });
        },

        update: function(id, name, fname, yob) {
            return $http({
                method:'PUT',
                url:'/authors/' + id,
                data: { name: name, f_name: fname, year_of_birth: yob }
            })
                .then(function(response){
                    $scope.authors.fetchList()
                        .then(function(){ $scope.authors.current = $scope.authors.get(id); });
                }, function(error){
                    alertService.setMsg(error);
                });
        },

        delete: function(id) {
            return $http({
                method:'DELETE',
                url:'/authors/' + id,
                data: {}
            })
                .then(function(response){
                    $scope.authors.fetchList();
                }, function(error){
                    alertService.setMsg(error);
                });
        }
    };

    $scope.books = {
        list:[],
        bookNameSelected: null,
        bookYearOfIssueSelected: null,
        bookAuthors: [],
        bookAuthorSelected: null,
        authorSrcList: [],
        authorSrcListSelected: null,
        current: null,
        categorySelected: null,
        findIndex: function(id){
            return $scope.books.list.findIndex(function(item) { return item.id == id; });
        },
        get: getter,
        fetchList: function(){
            return $q.all([
                $scope.books.fetchBookList(),
                $scope.authors.fetchList(),
                $scope.categories.fetchList()
            ])
                .then(
                    function(){
                        $scope.books.current = $scope.books.list.length > 0 ? $scope.books.list[0] : null;
                    },
                    function(error){
                        alertService.setMsg(error);
                    }
                );
        },
        fetchBookList: function(){
            return $http({
                method:'GET',
                url:'/books/'
            })
                .then(function(response){
                    $scope.books.list = response.data;
                }, function(error){
                    alertService.setMsg(error);
                });
        },

        fetchItem: function(id) {
            return $http({
                method:'GET',
                url:'/books/' + id
            })
                .then(function(response){
                    var index = $scope.books.findIndex(id);
                    if (index >= 0) {
                        $scope.books.list[index] = response.data;
                    } else {
                        $scope.books.list.push(response.data);
                    }
                });
        },

        add: function(name, year, category, authors) {
            var createdBookId;
            this.addEntity(name, year)
                .then(function(response){
                    return response.headers("Location").match(/\d+/);
                })
                .then(function(bookId){
                    createdBookId = bookId;
                    return $q.all([
                        $scope.books.updateCategory(bookId, category),
                        $scope.books.updateAuthors(bookId, authors)
                    ]);
                })
                .then(function(data){
                    return $scope.books.fetchItem(createdBookId);
                })
                .then(function(){
                    $scope.books.current = $scope.books.list[$scope.books.findIndex(createdBookId)];
                }, function(error){
                    alertService.setMsg(error);
                });
        },

        update: function(id, name, year, category, authors) {
            return $q.all([
                $scope.books.updateOwnProperties(id, name, year),
                $scope.books.updateCategory(id, category),
                $scope.books.updateAuthors(id, authors)
            ])
                .then(function(){
                    return $scope.books.fetchItem(id);
                })
                .then(function(){
                    $scope.books.current = $scope.books.list[$scope.books.findIndex(id)];
                }, function(error){
                    alertService.setMsg(error);
                });
        },

        addEntity: function(name, year) {
            return $http({
                method:'POST',
                url:'/books/',
                data: {
                    name: name,
                    year_of_issue: year
                }
            });
        },

        updateOwnProperties: function(id, name, year) {
            return $http({
                method:'PUT',
                url:'/books/' + id,
                data: {
                    name: name,
                    year_of_issue: year
                }
            });
        },
        updateCategory: function(id, category) {
            return $http({
                method:'PUT',
                url:'/books/' + id + '/category',
                data: category
            });
        },
        updateAuthors: function(id, authors) {
            return $http({
                method:'PUT',
                url:'/books/' + id + '/authors',
                data: authors
            });
        },
        delete: function(id) {
            return $http({
                method:'DELETE',
                url:'/books/' + id,
                data: {}
            })
                .then(function(response){
                    $scope.books.fetchBookList();
                    $scope.books.current = $scope.books.list.length > 0 ? $scope.books.list[0] : null;
                }, function(error){
                    alertService.setMsg(error);
                });
        },
        removeAuthor: function(author){
          var pos = this.bookAuthors.indexOf(author);
          if (pos >= 0) {
            this.authorSrcList.push(this.bookAuthors[pos]);
            this.bookAuthors.splice(pos, 1);
            this.bookAuthorSelected = pos < this.bookAuthors.length ? this.bookAuthors[pos]
              : (this.bookAuthors.length > 0 ? this.bookAuthors.length : null);
          }
        },
        moveAuthorToBookList: function(authorId){
            if (!this.authorSrcList) {
                return;
            }
            var index = this.authorSrcList.findIndex(function(author){ return author.id == authorId; });
            if (index >= 0) {
                this.bookAuthors.push(this.authorSrcList[index]);
                this.authorSrcList.splice(index, 1);
                this.authorSrcListSelected = index < this.authorSrcList.length ? this.authorSrcList[index]
                    : (this.authorSrcList.length > 0 ? this.authorSrcList[this.authorSrcList.length - 1] : null);
                this.bookAuthorSelected = this.bookAuthors.length > 0 ? this.bookAuthors[this.bookAuthors.length - 1] : null;
            }
        },
        assignAuthor: function(author) {
          this.moveAuthorToBookList(author.id);
        },
        fillBookAuthorList: function(authorList){
            if (!authorList) {
                return;
            }
            this.bookAuthors.splice(0, this.bookAuthors.length);
            authorList.forEach(function(author){
                $scope.books.moveAuthorToBookList(author.id);
            });
        },
        fillAuthorSrcList: function(authors) {
            $scope.books.authorSrcList.splice(0, this.authorSrcList.length);
            authors.forEach(function (item) {
                $scope.books.authorSrcList.push(item);
            });
            $scope.books.authorSrcListSelected = $scope.books.authorSrcList.length > 0 ? $scope.books.authorSrcList[0] : null;
        }
    };

    $scope.$watch('books.current', function(newCurrent, oldCurrent) {
        $scope.books.fillAuthorSrcList($scope.authors.list);
        if (!newCurrent || angular.isUndefined(newCurrent.id)) {
            return;
        }
        var item = $scope.books.get(newCurrent.id);
        $scope.books.bookNameSelected = item.name;
        $scope.books.bookYearOfIssueSelected = item.year_of_issue;
        $scope.books.categorySelected = item.category ? $scope.categories.get(item.category.id) : null;
        $scope.books.fillBookAuthorList(item.authors);
    });

  $scope.$watch('active', function(newTab, oldTab){
      switch (newTab) {
          case 0:
              $scope.books.fetchList();
              break;
          case 1:
              $scope.authors.fetchList();
              break;
          case 2:
              $scope.categories.fetchList();
              break;
      }
  });

});