angular.module('e-homework').controller('UpdatePMDController', function ($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');

    if ($user_session != null) {
        $scope.$parent.currentUser = angular.fromJson($user_session);
    } else {
        window.location.replace('#/guest/logon');
    }
    $scope.ID = $routeParams.id;

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));


    $scope.loadData = function (action, id) {
        var params = {
            'id': id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function (result) {
            console.log(result.data.DATA.Data.subid);
            if (result.data.STATUS == 'OK') {
                $scope.Data = result.data.DATA.Data;
                $scope.Subdata = {
                    'id': result.data.DATA.Data.id
                    , 'name': result.data.DATA.Data.name
                    , 'sub_product_milk_id': result.data.DATA.Data.subid

                    , 'actives': result.data.DATA.Data.actices
                   
                };
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function (Subdata) {
        var params = {'Subdata': Subdata};
        console.log(params);
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('product-milk-detail/update', params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                // if($scope.ID !== undefined && $scope.ID !== null){
                // window.location.href = '#/master-goal/update/' + result.data.DATA.id;
                window.location.href = '#/product-milk-detail';
                // }else{
                //     location.reload();    
                // }

            } else {
                alert(result.data.DATA);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.cancelUpdate = function () {
        window.location.href = '#/product-milk-detail';
    }



    var size = $scope.ID;

    var cksize = size.split('-');
 
    if ($scope.ID !== undefined && $scope.ID !== null && cksize.length === 1) {
        console.log('if');
        $scope.loadData('subproduct-milk/get', $scope.ID);
        $scope.Subdata = {
            'id': ''
            , 'name': ''
            , 'sub_product_milk_id': $scope.ID

            , 'actives': 'Y'
            , 'create_date': ''
            , 'update_date': ''
        };
    } else {
         console.log('else');
        $scope.loadData('product-milk-detail/get', $scope.ID);
    }


});