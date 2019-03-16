angular.module('e-homework').controller('MainPMController', function ($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');

    if ($user_session != null) {
        $scope.$parent.currentUser = angular.fromJson($user_session);
    } else {
        window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));

    $scope.loadList = function (action,id) {
        var params = {'facid': id};
      console.log(params);
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                $scope.List = result.data.DATA.List;
                
            }
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.loadFactoryList = function () {

        //  IndexOverlayFactory.overlayShow();
        var params = {'region': $scope.PersonRegion};
        HTTPService.clientRequest('factory/list', params).then(function (result) {
            if (result.data.STATUS == 'OK') {
                $scope.FactoryList = result.data.DATA.DataList;
                // console.log($scope.List);
                $scope.Data.factory_id = $scope.FactoryList[0].id;

            }
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.getThaiDate = function (date) {
        // console.log(date);
        return convertDateToFullThaiDate(new Date(date));
    }

    $scope.goUpdate = function (id) {
        window.location.href = '#/product-milk/update/' + id;
    }

    $scope.goAddsub = function (id) {
        window.location.href = '#/subproduct-milk/update/' + id;
    }
    $scope.goSearch = function (id) {
       
       $scope.loadList('product-milk/list', id);
    }
    $scope.Data = {
        'id': ''

    };
    $scope.loadFactoryList();

    $scope.loadList('product-milk/list', '');


});