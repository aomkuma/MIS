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
                    $scope.loadData('cow-group/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/cow-group', params).then(function(result){
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
            'cooperative_id' : $scope.Sperm.cooperative_id
            ,'months' : $scope.Sperm.months
            ,'years' : $scope.Sperm.years
        };
        if(id != null){
            params = {'id':id};
        }else{
            params = {
                'cooperative_id' : $scope.Sperm.cooperative_id
                ,'months' : $scope.Sperm.months
                ,'years' : $scope.Sperm.years
            };
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Sperm = result.data.DATA.Data;
                $scope.Sperm.cooperative_id = parseInt($scope.Sperm.cooperative_id);
                $scope.SpermDetailList = $scope.Sperm.cow-group_detail;
                // load sub dar=iry farming
                // for(var i =0; i < $scope.SpermDetailList.length; i++){
                //     $scope.loadDairyFarming('CHILD', $scope.SpermDetailList[i].dairy_farming_id);
                // }
                IndexOverlayFactory.overlayHide();
            }else{
                if($scope.Sperm.id != ''){
                    $scope.Sperm.id = '';
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Sperm, SpermDetailList){
        
        var params = {'Sperm' : Sperm, 'SpermDetailList' : SpermDetailList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('cow-group/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                if($scope.ID !== undefined && $scope.ID !== null){
                    window.location.href = '#/cow-group/update/' + result.data.DATA.id;
                }else{
                    location.reload();    
                }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/cow-group';
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
        $scope.SpermDetailList = [];
        // $scope.setSperm();
        // $scope.loadData('cow-group/get');
        $scope.SpermDetailList = [
            {
                'id':''
                ,'cow_group_id':1
                ,'cow_type_id':1
                ,'cow_item_id':1
                ,'beginning_period':17
                ,'beginning_period_total_values':1500000
                ,'total_born':1
                ,'total_born_values':10000
                ,'total_movein':1
                ,'total_movein_values':10000
                ,'total_buy':1
                ,'total_buy_values':10000
                ,'total_die':1
                ,'total_die_values':10000
                ,'total_sell':1
                ,'total_sell_values':10000
                ,'total_sell_carcass':1
                ,'total_sell_carcass_values':10000
                ,'total_moveout':1
                ,'total_moveout_values':10000
                ,'total_cutout':1
                ,'total_cutout_values':10000
                ,'last_period':13
                ,'last_period_total_values':980000
            }
        ];
    }

    $scope.addSpermDetail = function(){
        var detail =
            {
                'id':''
                ,'cooperative_id':1
                ,'member_id':2
                ,'total_person':'200'
                ,'total_person_sent':'150'
                ,'total_cow':'400'
                ,'total_cow_beeb':230
                ,'milk_amount':230
                ,'total_values':230
                ,'average_values':230
            }
        ;

        $scope.SpermDetailList.push(detail);
    }

    $scope.addSpermItem = function(index){
        var item = {'id':''
                    , 'cow-group_id':$scope.ID
                    , 'cow-group_detail_id':''
                    , 'item_type':null
                    , 'item_amount':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID};

        $scope.SpermDetailList[index].cow-group_item.push(item);
    }

    $scope.removeDetail = function(id, index){
        if(id == ''){
            $scope.SpermDetailList.splice(index, 1);
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
                HTTPService.clientRequest('cow-group/delete/detail', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.SpermDetailList.splice(index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.removeItem = function(id, parent_index, child_index){
        if(id == ''){
            $scope.SpermDetailList[parent_index].cow-group_item.splice(child_index, 1);
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
                HTTPService.clientRequest('cow-group/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.SpermDetailList[parent_index].cow-group_item.splice(child_index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.setSperm = function(){
        $scope.Sperm = {
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
    $scope.SpermDetailList = [];
    $scope.SpermList = [
        {'id':1, 'name':'ฝูงโค 1'}
        ,{'id':2, 'name':'อาหาร TMR'}
    ];
    $scope.CowGroupList = [
            {'id':1, 'name':'ฝูงโค 1'}
            ,{'id':2, 'name':'ฝูงโค 2'}
            ];
    $scope.CowTypeList = [
            {'id':1, 'name':'ประเภท 1'}
            ,{'id':2, 'name':'ประเภท 2'}
            ];
    $scope.CowItemList = [
            {'id':1, 'name':'โคอายุ 1-12 เดือน'}
            ,{'id':2, 'name':'ประเภท 2'}
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

    $scope.setSperm();
    $scope.loadCooperative();
    // $scope.loadDairyFarming('MAIN', '');
    // $scope.loadDairyFarming('CHILD', '');


});