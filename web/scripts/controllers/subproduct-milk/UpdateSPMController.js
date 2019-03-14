angular.module('e-homework').controller('UpdateSPMController', function ($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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
    //  console.log($scope.$parent.Menu);

    $scope.loadList = function (action, id) {
        var params = {
            'id': id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                $scope.List2 = result.data.DATA.List;
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.loadData = function (action, id) {
        var params = {
            'id': id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                $scope.Data = result.data.DATA.Data;

            }
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.loadDatasub = function (action, id) {
        var params = {
            'id': id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                $scope.Data = result.data.DATA.Data;
                console.log();
                $scope.Subdata = {
                    'id': ''
                    , 'name': ''
                    , 'product_milk_id': $scope.ID

                    , 'actives': 'Y'
                    , 'create_date': ''
                    , 'update_date': ''
                };
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function (Subdata) {
        var params = {'Subdata': Subdata};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('subproduct-milk/update', params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                // if($scope.ID !== undefined && $scope.ID !== null){
                // window.location.href = '#/master-goal/update/' + result.data.DATA.id;
                window.location.href = '#/subproduct-milk';
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
        window.location.href = '#/subproduct-milk';
    }
    $scope.goUpdate = function (detailid) {
        window.location.href = '#/subproduct-milk/update/' + detailid + '-xx';
    }


    $scope.Subdata = {
        'id': ''
        , 'name': ''
        , 'product_milk_id': $scope.ID

        , 'actives': 'Y'
        , 'create_date': ''
        , 'update_date': ''
    };

    var size = $scope.ID;

    var cksize = size.split('-');
    console.log(cksize.length);
    // if ($scope.ID !== undefined && $scope.ID !== null) {
    if (cksize.length === 1) {
        console.log('if');
        $scope.loadData('product-milk/get', $scope.ID);
    } else {
        console.log('else');
        $scope.loadDatasub('subproduct-milk/get', cksize[0]);

    }

    $scope.loadList('subproduct-milk/list/byparent', $scope.ID);
});