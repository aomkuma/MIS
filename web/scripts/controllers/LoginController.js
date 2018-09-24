angular.module('e-homework').controller('LoginController',function($scope, $routeParams, HTTPService, IndexOverlayFactory){
	
	$scope.user = {'Username':'','Password':''};

    var reDirect = '';
    if($routeParams.redirect_url !== undefined){
        reDirect = $routeParams.redirect_url;
        console.log(reDirect);
    }
	$scope.showError = false; // set Error flag
	$scope.showSuccess = false; // set Success Flag
    
	//------- Authenticate function
	$scope.authenticate = function (action, data){
		var flag= false;
        $scope.showError = false;
        $scope.showSuccess = false;
        IndexOverlayFactory.overlayShow();
        HTTPService.loginRequest(action, data).then(function(user){
            // console.log(user);
            if(user.data.STATUS == 'OK'){

                // Load menu
                action = 'menu/list';
                HTTPService.clientRequest(action, data).then(function(menu){
                    sessionStorage.setItem('menu_session' , JSON.stringify(menu.data.DATA.Menu));
                });

                $scope.showError = false;
                $scope.showSuccess = true;
                sessionStorage.setItem('user_session' , JSON.stringify(user.data.DATA.UserData));

                sessionStorage.setItem('person_region_session' , JSON.stringify(user.data.DATA.PersonRegion));
                setTimeout(function(){
                    window.location.replace('#/' + reDirect);    
                }, 1000);
            }else{
                $scope.showError = true;
                $scope.showSuccess = false;
            }
            IndexOverlayFactory.overlayHide();
        });
	}
});
