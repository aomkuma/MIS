angular.module('e-homework').controller('UpdateController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'dairyfarming';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }
    $scope.ID = $routeParams.id;
    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    
    // console.log($scope.$parent.Menu);

    $scope.loadCooperative = function(){
        var params = {'actives':'Y'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('cooperative/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Cooperative = result.data.DATA.List;
                if($scope.ID !== undefined && $scope.ID !== null){
                    $scope.loadData('veterinary/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/veterinary', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                if(type == 'MAIN'){
                    $scope.DairyFarmingList = result.data.DATA.List;
                }else{
                    $scope.SubDairyFarmingList = result.data.DATA.List;
                }
                // $scope.Cooperative = result.data.DATA.List;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadData = function(action, id){
        var params = {
            'cooperative_id' : $scope.Veterinary.cooperative_id
            ,'months' : $scope.Veterinary.months
            ,'years' : $scope.Veterinary.years
        };
        if(id != null){
            params = {'id':id};
        }else{
            params = {
                'cooperative_id' : $scope.Veterinary.cooperative_id
                ,'months' : $scope.Veterinary.months
                ,'years' : $scope.Veterinary.years
            };
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Veterinary = result.data.DATA.Data;
                $scope.Veterinary.cooperative_id = parseInt($scope.Veterinary.cooperative_id);
                $scope.VeterinaryDetailList = $scope.Veterinary.veterinary_detail;
                // load sub dar=iry farming
                // for(var i =0; i < $scope.VeterinaryDetailList.length; i++){
                //     $scope.loadDairyFarming('CHILD', $scope.VeterinaryDetailList[i].dairy_farming_id);
                // }
                IndexOverlayFactory.overlayHide();
            }else{
                if($scope.Veterinary.id != ''){
                    $scope.Veterinary.id = '';
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Veterinary, VeterinaryDetailList){
        
        var params = {'Veterinary' : Veterinary, 'VeterinaryDetailList' : VeterinaryDetailList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('veterinary/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                if($scope.ID !== undefined && $scope.ID !== null){
                    window.location.href = '#/veterinary/update/' + result.data.DATA.id;
                }else{
                    location.reload();    
                }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/veterinary';
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.getThaiDateTime = function(date){
        // console.log(date);
        return convertDateToFullThaiDate(new Date(date));
    }

    $scope.goSearch = function(){
        $scope.Search = true;
        $scope.VeterinaryDetailList = [];
        // $scope.setVeterinary();
        $scope.loadData('veterinary/get');

    }

    $scope.addVeterinaryDetail = function(){
        var detail = {'id':''
                    , 'veterinary_id':$scope.ID
                    , 'dairy_farming_id':null
                    , 'sub_dairy_farming_id':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID
                    , 'veterinary_item':[{
                                            'id':''
                                            , 'veterinary_id':$scope.ID
                                            , 'veterinary_detail_id':''
                                            , 'item_type':null
                                            , 'item_amount':null
                                            , 'create_date':''
                                            , 'update_date':''
                                            , 'create_by':$scope.currentUser.UserID
                                            , 'update_by':$scope.currentUser.UserID
                                        }]
                    };

        $scope.VeterinaryDetailList.push(detail);
    }

    $scope.addVeterinaryItem = function(index){
        var item = {'id':''
                    , 'veterinary_id':$scope.ID
                    , 'veterinary_detail_id':''
                    , 'item_type':null
                    , 'item_amount':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID};

        $scope.VeterinaryDetailList[index].veterinary_item.push(item);
    }

    $scope.removeDetail = function(id, index){
        if(id == ''){
            $scope.VeterinaryDetailList.splice(index, 1);
        }else{
        $scope.alertMessage = 'ข้อมูลจะถูกลบจากระบบทันที<br>ต้องการลบรายการนี้ ใช่หรือไม่ ?';
            var modalInstance = $uibModal.open({
                animation : true,
                templateUrl : 'views/dialog_confirm.html',
                size : 'sm',
                scope : $scope,
                backdrop : 'static',
                controller : 'ModalDialogCtrl',
                resolve : {
                    params : function() {
                        return {};
                    } 
                },
            });

            modalInstance.result.then(function (valResult) {
                IndexOverlayFactory.overlayShow();
                var params = {'id' : id};
                HTTPService.clientRequest('veterinary/delete/detail', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.VeterinaryDetailList.splice(index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.removeItem = function(id, parent_index, child_index){
        if(id == ''){
            $scope.VeterinaryDetailList[parent_index].veterinary_item.splice(child_index, 1);
        }else{

            $scope.alertMessage = 'ข้อมูลจะถูกลบจากระบบทันที<br>ต้องการลบรายการนี้ ใช่หรือไม่ ?';
            var modalInstance = $uibModal.open({
                animation : true,
                templateUrl : 'views/dialog_confirm.html',
                size : 'sm',
                scope : $scope,
                backdrop : 'static',
                controller : 'ModalDialogCtrl',
                resolve : {
                    params : function() {
                        return {};
                    } 
                },
            });

            modalInstance.result.then(function (valResult) {
                IndexOverlayFactory.overlayShow();
                var params = {'id' : id};
                HTTPService.clientRequest('veterinary/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.VeterinaryDetailList[parent_index].veterinary_item.splice(child_index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.setVeterinary = function(){
        $scope.Veterinary = {
            'id':''
            , 'cooperative_id':null
            , 'region_id':null
            , 'months':null
            , 'years':null
            , 'create_date':''
            , 'update_date':''
        };    
    }
    

    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    $scope.Search = false;
    $scope.SubDairyFarmingList = [];
    $scope.DairyFarmingList = [];
    $scope.VeterinaryDetailList = [];

    $scope.popup1 = {
        opened: false
    };
    $scope.open1 = function() {
        $scope.popup1.opened = true;
    };

    $scope.popup2 = {
        opened: false
    };
    $scope.open2 = function() {
        $scope.popup2.opened = true;
    };

    $scope.setVeterinary();
    $scope.loadCooperative();
    $scope.loadDairyFarming('MAIN', '');
    $scope.loadDairyFarming('CHILD', '');


});