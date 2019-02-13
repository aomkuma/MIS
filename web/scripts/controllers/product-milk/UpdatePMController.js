angular.module('e-homework').controller('UpdatePMController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }
    $scope.ID = $routeParams.id;
    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    
    console.log($scope.$parent.Menu);

    $scope.loadData = function(action, id){
        var params = {
            'id' : id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Data = result.data.DATA.Data;
                
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Data){
        var params = {'Data' : Data};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('product-milk/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // if($scope.ID !== undefined && $scope.ID !== null){
                    // window.location.href = '#/master-goal/update/' + result.data.DATA.id;
                    window.location.href = '#/product-milk';
                // }else{
                //     location.reload();    
                // }
                
            }else{
                alert(result.data.DATA);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/product-milk';
    }

    

    $scope.Data = {
        'id':''
        , 'goal_type':''
        , 'menu_type':null
        , 'goal_name':''
        , 'actives':'Y'
        , 'create_date':''
        , 'update_date':''
    };

    
    if($scope.ID !== undefined && $scope.ID !== null){
        $scope.loadData('master-goal/get', $scope.ID);
    }

});