angular.module('e-homework').config(function($routeProvider, $locationProvider) {
    $routeProvider
    .when("/", {
        templateUrl : "views/home.html",
        controller : "HomeController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/HomeController.js" ]
				});
			} ]
		}
	})

	.when("/guest/logon", {
        templateUrl : "views/login.html",
        controller : "LoginController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/LoginController.js" ]
				});
			} ]
		}
	})

	.when("/thirdparty/authen/:username/:loginSession", {
        templateUrl : "views/thirdparty.html",
        controller : "ThirdpartyController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/ThirdpartyController.js" ]
				});
			} ]
		}
	})

	

	.when("/menu-manage", {
        templateUrl : "views/menu/main.html",
        controller : "MenuController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/MenuController.js" ]
				});
			} ]
		}
	})

	.when("/menu-manage/update/:id?", {
        templateUrl : "views/menu/update.html",
        controller : "MenuUpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/MenuUpdateController.js" ]
				});
			} ]
		}
	})

	.when("/update/pages/:pagetype", {
        templateUrl : "views/update/pages.html",
        controller : "UpdatePageController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/UpdatePageController.js" ]
				});
			} ]
		}
	})

	.when("/account-permission", {
        templateUrl : "views/account-permission/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/account-permission/MainController.js" ]
				});
			} ]
		}
	})

	.when("/account-permission/update/:id?", {
        templateUrl : "views/account-permission/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/account-permission/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/master-goal", {
        templateUrl : "views/master-goal/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/master-goal/MainController.js" ]
				});
			} ]
		}
	})

	.when("/master-goal/update/:id?", {
        templateUrl : "views/master-goal/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/master-goal/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/goal-mission", {
        templateUrl : "views/goal-mission/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/goal-mission/MainController.js" ]
				});
			} ]
		}
	})

	.when("/goal-mission/update/:id?", {
        templateUrl : "views/goal-mission/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/goal-mission/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/mou", {
        templateUrl : "views/mou/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/mou/MainController.js" ]
				});
			} ]
		}
	})

	.when("/mou/update/:id?", {
        templateUrl : "views/mou/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/mou/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/dairy-farming", {
        templateUrl : "views/dairy-farming/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/dairy-farming/MainController.js" ]
				});
			} ]
		}
	})

	.when("/dairy-farming/update/:id?", {
        templateUrl : "views/dairy-farming/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/dairy-farming/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/veterinary", {
        templateUrl : "views/veterinary/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/veterinary/MainController.js" ]
				});
			} ]
		}
	})

	.when("/veterinary/update/:id?", {
        templateUrl : "views/veterinary/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/veterinary/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/production-factor", {
        templateUrl : "views/production-factor/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/production-factor/MainController.js" ]
				});
			} ]
		}
	})

	.when("/production-factor/update/:id?", {
        templateUrl : "views/production-factor/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/production-factor/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/food", {
        templateUrl : "views/food/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/food/MainController.js" ]
				});
			} ]
		}
	})

	.when("/food/update/:id?", {
        templateUrl : "views/food/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/food/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/cow-food", {
        templateUrl : "views/cow-food/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/cow-food/MainController.js" ]
				});
			} ]
		}
	})

	.when("/cow-food/update/:id?", {
        templateUrl : "views/cow-food/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/cow-food/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/training", {
        templateUrl : "views/training/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/training/MainController.js" ]
				});
			} ]
		}
	})

	.when("/training/update/:id?", {
        templateUrl : "views/training/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/training/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/insemination", {
        templateUrl : "views/insemination/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/insemination/MainController.js" ]
				});
			} ]
		}
	})

	.when("/insemination/update/:id?", {
        templateUrl : "views/insemination/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/insemination/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/mineral", {
        templateUrl : "views/mineral/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/mineral/MainController.js" ]
				});
			} ]
		}
	})

	.when("/mineral/update/:id?", {
        templateUrl : "views/mineral/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/mineral/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/sperm", {
        templateUrl : "views/sperm/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/sperm/MainController.js" ]
				});
			} ]
		}
	})

	.when("/sperm/update/:id?", {
        templateUrl : "views/sperm/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/sperm/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/sperm-sale", {
        templateUrl : "views/sperm-sale/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/sperm-sale/MainController.js" ]
				});
			} ]
		}
	})

	.when("/sperm-sale/update/:id?", {
        templateUrl : "views/sperm-sale/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/sperm-sale/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/material", {
        templateUrl : "views/material/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/material/MainController.js" ]
				});
			} ]
		}
	})

	.when("/material/update/:id?", {
        templateUrl : "views/material/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/material/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/cow-breed", {
        templateUrl : "views/cow-breed/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/cow-breed/MainController.js" ]
				});
			} ]
		}
	})

	.when("/cow-breed/update/:id?", {
        templateUrl : "views/cow-breed/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/cow-breed/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/training-cowbreed", {
        templateUrl : "views/training-cowbreed/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/training-cowbreed/MainController.js" ]
				});
			} ]
		}
	})

	.when("/training-cowbreed/update/:id?", {
        templateUrl : "views/training-cowbreed/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/training-cowbreed/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/travel", {
        templateUrl : "views/travel/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/travel/MainController.js" ]
				});
			} ]
		}
	})

	.when("/travel/update/:id?", {
        templateUrl : "views/travel/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/travel/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/cooperative-milk", {
        templateUrl : "views/cooperative-milk/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/cooperative-milk/MainController.js" ]
				});
			} ]
		}
	})

	.when("/cooperative-milk/update/:id?", {
        templateUrl : "views/cooperative-milk/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/cooperative-milk/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/cow-group", {
        templateUrl : "views/cow-group/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/cow-group/MainController.js" ]
				});
			} ]
		}
	})

	.when("/cow-group/update/:id?", {
        templateUrl : "views/cow-group/update.html",
        controller : "UpdateController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/cow-group/UpdateController.js" ]
				});
			} ]
		}
	})

	.when("/report-monthly", {
        templateUrl : "views/report/monthly.html",
        controller : "MonthlyController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/report/MonthlyController.js" ]
				});
			} ]
		}
	})

	.when("/report-quarter", {
        templateUrl : "views/report/quarter.html",
        controller : "QuarterController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/report/QuarterController.js" ]
				});
			} ]
		}
	})

	.when("/report-annually", {
        templateUrl : "views/report/annually.html",
        controller : "AnnuallyController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/report/AnnuallyController.js" ]
				});
			} ]
		}
	})

	.when("/report-subcommittee", {
        templateUrl : "views/report/subcommittee.html",
        controller : "SubcommitteeController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/report/SubcommitteeController.js" ]
				});
			} ]
		}
	})

	.when("/production-info", {
        templateUrl : "views/production-info/main.html",
        controller : "MainController",
        resolve : {
			loadMyCtrl : [ '$ocLazyLoad', function($ocLazyLoad) {
				return $ocLazyLoad.load({
					files : [ "scripts/controllers/production-info/MainController.js" ]
				});
			} ]
		}
	})

	;



	$locationProvider.hashPrefix('');
	// $locationProvider.html5Mode({
 //                 enabled: true,
 //                 requireBase: false
 //          });
	
});

/*app.config(function($routeProvider) {
	
	$routeProvider.when('/', {

	  templateUrl: function(rd) {
	    return 'views/home.html';
	  },

	  resolve: {
	    load: function($q, $route, $rootScope) {

	      var deferred = $q.defer();
	      var dependencies = [
	        'scripts/controllers/HomeController.js'
	      ];

	      $script(dependencies, function () {
	        $rootScope.$apply(function() {
	          deferred.resolve();
	        });
	      });

	      console.log(deferred);
	      return deferred.promise;
	    }
	  }
	});

});*/