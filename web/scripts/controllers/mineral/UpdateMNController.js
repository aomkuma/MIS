angular.module('e-homework').controller('UpdateMNController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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
                    $scope.loadData('mineral/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadFood = function(){
        var params = {'actives':'Y'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('food/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.FoodList = result.data.DATA.List;
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadData = function(action, id){
        var params = {
            'cooperative_id' : $scope.Mineral.cooperative_id
            ,'months' : $scope.Mineral.months
            ,'years' : $scope.Mineral.years
        };
        if(id != null){
            params = {'id':id};
        }else{
            params = {
                'cooperative_id' : $scope.Mineral.cooperative_id
                ,'months' : $scope.Mineral.months
                ,'years' : $scope.Mineral.years
            };
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Mineral = result.data.DATA.Data;
                $scope.Mineral.cooperative_id = parseInt($scope.Mineral.cooperative_id);
                $scope.MineralDetailList = $scope.Mineral.mineral_detail;
                // load sub dar=iry farming
                // for(var i =0; i < $scope.MineralDetailList.length; i++){
                //     $scope.loadDairyFarming('CHILD', $scope.MineralDetailList[i].dairy_farming_id);
                // }
                IndexOverlayFactory.overlayHide();
            }else{
                if($scope.Mineral.id != ''){
                    $scope.Mineral.id = '';
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Mineral, MineralDetailList){
        
        var params = {'Data' : Mineral, 'Detail' : MineralDetailList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('mineral/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){
                    window.location.href = '#/mineral/update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/mineral';
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
        $scope.MineralDetailList = [];
        // $scope.setMineral();
        $scope.loadData('mineral/get');
        // $scope.MineralDetailList = [
        //     {
        //         'id':''
        //         ,'food_id':'1'
        //         ,'amount':'200'
        //         ,'values':'145000'
        //     },
        //     {
        //         'id':''
        //         ,'food_id':'2'
        //         ,'amount':'400'
        //         ,'values':'245000'
        //     }
        // ];
    }

    $scope.addMineralDetail = function(){
        var detail = {'id':''
                    , 'mineral_id':$scope.ID
                    , 'dairy_farming_id':null
                    , 'sub_dairy_farming_id':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID
                    , 'mineral_item':[{
                                            'id':''
                                            , 'mineral_id':$scope.ID
                                            , 'mineral_detail_id':''
                                            , 'item_type':null
                                            , 'item_amount':null
                                            , 'create_date':''
                                            , 'update_date':''
                                            , 'create_by':$scope.currentUser.UserID
                                            , 'update_by':$scope.currentUser.UserID
                                        }]
                    };

        $scope.MineralDetailList.push(detail);
    }

    $scope.addMineralItem = function(index){
        var item = {'id':''
                    , 'mineral_id':$scope.ID
                    , 'mineral_detail_id':''
                    , 'item_type':null
                    , 'item_amount':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID};

        $scope.MineralDetailList[index].mineral_item.push(item);
    }

    $scope.removeDetail = function(id, index){
        if(id == ''){
            $scope.MineralDetailList.splice(index, 1);
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
                HTTPService.clientRequest('mineral/delete/detail', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.MineralDetailList.splice(index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.removeItem = function(id, parent_index, child_index){
        if(id == ''){
            $scope.MineralDetailList[parent_index].mineral_item.splice(child_index, 1);
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
                HTTPService.clientRequest('mineral/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.MineralDetailList[parent_index].mineral_item.splice(child_index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.setMineral = function(){
        $scope.Mineral = {
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
    $scope.MineralDetailList = [];
    $scope.FoodList = [
        {'id':1, 'name':'อาหาร'}
        ,{'id':2, 'name':'แร่ธาตุ'}
    ];

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

    $scope.setMineral();
    $scope.loadCooperative();
    $scope.loadFood();
    

});