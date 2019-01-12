angular.module('e-homework').controller('UpdateISController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));    
    // console.log($scope.$parent.Menu);

    $scope.loadCooperative = function(){
        var params = {'actives':'Y', 'RegionList':$scope.PersonRegion};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('cooperative/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Cooperative = result.data.DATA.List;
                if($scope.ID !== undefined && $scope.ID !== null){
                    $scope.loadData('insemination/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/insemination', params).then(function(result){
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

        $scope.CooperativeName = '';
        $scope.MonthName = '';
        $scope.YearName = '';
        // Get cooperative name
        for(var i=0; i < $scope.Cooperative.length; i++){
            if($scope.Insemination.cooperative_id == $scope.Cooperative[i].id){
                $scope.CooperativeName = $scope.Cooperative[i].cooperative_name;
            }
        }

        for(var i=0; i < $scope.MonthList.length; i++){
            if($scope.Insemination.months == $scope.MonthList[i].monthValue){
                $scope.MonthName = $scope.MonthList[i].monthText;
            }
        }

        for(var i=0; i < $scope.YearList.length; i++){
            if($scope.Insemination.years == $scope.YearList[i].yearText){
                $scope.YearName = $scope.YearList[i].yearValue;
            }
        }
        
        var params = {
            'cooperative_id' : $scope.Insemination.cooperative_id
            ,'months' : $scope.Insemination.months
            ,'years' : $scope.Insemination.years
        };
        if(id != null){
            params = {'id':id};
        }else{
            params = {
                'cooperative_id' : $scope.Insemination.cooperative_id
                ,'months' : $scope.Insemination.months
                ,'years' : $scope.Insemination.years
            };
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Insemination = result.data.DATA.Data;
                $scope.Insemination.cooperative_id = parseInt($scope.Insemination.cooperative_id);
                $scope.InseminationDetailList = $scope.Insemination.insemination_detail;
                // load sub dar=iry farming
                // for(var i =0; i < $scope.InseminationDetailList.length; i++){
                //     $scope.loadDairyFarming('CHILD', $scope.InseminationDetailList[i].dairy_farming_id);
                // }
                IndexOverlayFactory.overlayHide();
            }else{
                if($scope.Insemination.id != ''){
                    $scope.Insemination.id = '';
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Insemination, InseminationDetailList){
        
        var params = {'Data' : Insemination, 'Detail' : InseminationDetailList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('insemination/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                alert('บันทึกสำเร็จ');
                 window.location.href = '#/insemination/update/' + result.data.DATA.id;
                // if($scope.ID !== undefined && $scope.ID !== null){
                   
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/insemination';
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.getThaiDateTime = function(date){
        // console.log(date);
        return convertDateToFullThaiDate(new Date(date));
    }

    $scope.getThaiDateTimeFromString = function(date){
        // console.log(date);
        return convertSQLDateTimeToReportDateTime(date);
    }

    $scope.goSearch = function(){
        $scope.Search = true;
        $scope.InseminationDetailList = [];
        // $scope.setInsemination();
        $scope.loadData('insemination/get');
        
        // $scope.InseminationDetailList = [
        //     {}
        // ];
    }

    $scope.addInseminationDetail = function(){
        var detail = {'id':''
                    , 'insemination_id':$scope.ID
                    , 'dairy_farming_id':null
                    , 'sub_dairy_farming_id':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID
                    , 'insemination_item':[{
                                            'id':''
                                            , 'insemination_id':$scope.ID
                                            , 'insemination_detail_id':''
                                            , 'item_type':null
                                            , 'item_amount':null
                                            , 'create_date':''
                                            , 'update_date':''
                                            , 'create_by':$scope.currentUser.UserID
                                            , 'update_by':$scope.currentUser.UserID
                                        }]
                    };

        $scope.InseminationDetailList.push(detail);
    }

    $scope.addInseminationItem = function(index){
        var item = {'id':''
                    , 'insemination_id':$scope.ID
                    , 'insemination_detail_id':''
                    , 'item_type':null
                    , 'item_amount':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID};

        $scope.InseminationDetailList[index].insemination_item.push(item);
    }

    $scope.removeDetail = function(id, index){
        if(id == ''){
            $scope.InseminationDetailList.splice(index, 1);
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
                HTTPService.clientRequest('insemination/delete/detail', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.InseminationDetailList.splice(index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.removeItem = function(id, parent_index, child_index){
        if(id == ''){
            $scope.InseminationDetailList[parent_index].insemination_item.splice(child_index, 1);
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
                HTTPService.clientRequest('insemination/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.InseminationDetailList[parent_index].insemination_item.splice(child_index, 1);
                    }
                    IndexOverlayFactory.overlayHide();
                });
            });
        }
    }

    $scope.setInsemination = function(){
        $scope.Insemination = {
            'id':''
            , 'cooperative_id':null
            , 'region_id':null
            , 'months':curDate.getMonth() + 1
            , 'years':curDate.getFullYear()
            , 'create_date':''
            , 'update_date':''
        };    
    }
    
    var curDate = new Date();
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    $scope.Search = false;
    $scope.SubDairyFarmingList = [];
    $scope.DairyFarmingList = [];
    $scope.InseminationDetailList = [];

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

    $scope.setInsemination();
    $scope.loadCooperative();
    // $scope.loadDairyFarming('MAIN', '');
    // $scope.loadDairyFarming('CHILD', '');


});