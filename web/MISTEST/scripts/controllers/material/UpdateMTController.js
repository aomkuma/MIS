angular.module('e-homework').controller('UpdateMTController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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

    $scope.page_type = 'material';
    $scope.getMenu = function(action, menu_type){
        var params = {'menu_type' : menu_type};
        HTTPService.clientRequest(action, params).then(function(result){
            console.log(result);
            $scope.MenuName = result.data.DATA.Menu;
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.getMenu('menu/get/type' ,$scope.page_type);
    
    $scope.getUserRole = function(){
        var params = {'UserID' : $scope.currentUser.UserID};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('account-permission/get', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.UserRole = result.data.DATA.Role;
                for(var i =0; i < $scope.UserRole.length; i++){
                    if($scope.UserRole[i].role == '1' && $scope.UserRole[i].actives == 'Y'){
                        $scope.Maker = true;
                    }
                }
                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
    $scope.Maker = false;
    $scope.getUserRole();

    $scope.loadCooperative = function(){
        var params = {'actives':'Y', 'RegionList':$scope.PersonRegion};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('cooperative/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Cooperative = result.data.DATA.List;
                if($scope.ID !== undefined && $scope.ID !== null){
                    $scope.loadData('material/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/material', params).then(function(result){
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

    $scope.loadMasterGoalList = function(){
        var params = {'actives':'Y', 'menu_type' : 'วัสดุผสมเทียมและเวชภัณฑ์ยาสัตว์'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('master-goal/list', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.MasterGoalList = result.data.DATA.List;
                IndexOverlayFactory.overlayHide();
            }
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
                $scope.SpermDetailList = $scope.Sperm.material_detail;
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
            $scope.CooperativeName = '';
            $scope.MonthName = '';
            $scope.YearName = '';
            // Get cooperative name
            for(var i=0; i < $scope.Cooperative.length; i++){
                if($scope.Sperm.cooperative_id == $scope.Cooperative[i].id){
                    $scope.CooperativeName = $scope.Cooperative[i].cooperative_name;
                }
            }

            for(var i=0; i < $scope.MonthList.length; i++){
                if($scope.Sperm.months == $scope.MonthList[i].monthValue){
                    $scope.MonthName = $scope.MonthList[i].monthText;
                }
            }

            for(var i=0; i < $scope.YearList.length; i++){
                if($scope.Sperm.years == $scope.YearList[i].yearText){
                    $scope.YearName = $scope.YearList[i].yearValue;
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.save = function(Sperm, SpermDetailList){
        $scope.Saving = true;
        var params = {'Data' : Sperm, 'Detail' : SpermDetailList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('material/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/material';///update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/material';
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
        console.log(date);
        if(date != undefined && date != null && date != ''){
            return convertSQLDateTimeToReportDateTime(date);
        }
    }

    $scope.goSearch = function(){
        $scope.Search = true;
        $scope.SpermDetailList = [];
        // $scope.setSperm();
        $scope.loadData('material/get');
        $scope.$parent.getGoalByMenu('วัสดุผสมเทียมและอื่นๆ', $scope.Sperm.years, $scope.Sperm.months);
        // $scope.SpermDetailList = [
        //     {
        //         'id':''
        //         ,'material_id':'1'
        //         ,'amount':'2'
        //         ,'amount_unit':'1'
        //         ,'values':'400'
        //     },
        //     {
        //         'id':''
        //         ,'material_id':'2'
        //         ,'amount':'4'
        //         ,'amount_unit':'1'
        //         ,'values':'1600'
        //     }
        // ];
    }

    $scope.addSpermDetail = function(){
        var detail = {'id':''
                    , 'material_id':$scope.ID
                    , 'dairy_farming_id':null
                    , 'sub_dairy_farming_id':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID
                    , 'material_item':[{
                                            'id':''
                                            , 'material_id':$scope.ID
                                            , 'material_detail_id':''
                                            , 'item_type':null
                                            , 'item_amount':null
                                            , 'create_date':''
                                            , 'update_date':''
                                            , 'create_by':$scope.currentUser.UserID
                                            , 'update_by':$scope.currentUser.UserID
                                        }]
                    };

        $scope.SpermDetailList.push(detail);
    }

    $scope.addSpermItem = function(index){
        var item = {'id':''
                    , 'material_id':$scope.ID
                    , 'material_detail_id':''
                    , 'item_type':null
                    , 'item_amount':null
                    , 'create_date':''
                    , 'update_date':''
                    , 'create_by':$scope.currentUser.UserID
                    , 'update_by':$scope.currentUser.UserID};

        $scope.SpermDetailList[index].material_item.push(item);
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
                HTTPService.clientRequest('material/delete/detail', params).then(function(result){
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
            $scope.SpermDetailList[parent_index].material_item.splice(child_index, 1);
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
                HTTPService.clientRequest('material/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.SpermDetailList[parent_index].material_item.splice(child_index, 1);
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
            , 'region_id':$scope.PersonRegion[0].RegionID
            , 'months':curDate.getMonth() + 1
            , 'years':curDate.getFullYear()
            , 'create_date':''
            , 'update_date':''
        };    
    }
    
    $scope.approve = function(Data, OrgType){
        $scope.alertMessage = 'ต้องการอนุมัติข้อมูลนี้ ใช่หรือไม่ ?';
        var modalInstance = $uibModal.open({
            animation : false,
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

            var params = {'id' : Data.id, 'OrgType' : OrgType, 'ApproveStatus' : 'approve'};
            HTTPService.uploadRequest('material/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/material/update/' + Data.id;
                }else{
                    alert(result.data.DATA);
                }
                IndexOverlayFactory.overlayHide();
            });
        });
    }

    $scope.ApproveComment = '';
    $scope.reject = function(Data, OrgType){
        $scope.alertMessage = 'ไม่ต้องการอนุมัติข้อมูลนี้ ใช่หรือไม่ ?';
        $scope.ApproveComment = '';
        var modalInstance = $uibModal.open({
            animation : false,
            templateUrl : 'reject_dialog.html',
            size : 'md',
            scope : $scope,
            backdrop : 'static',
            controller : 'ModalDialogReturnFromOKBtnCtrl',
            resolve : {
                params : function() {
                    return {};
                } 
            },
        });

        modalInstance.result.then(function (valResult) {
            console.log(valResult);
            var params = {'id' : Data.id, 'OrgType' : OrgType, 'ApproveStatus' : 'reject', 'ApproveComment' : valResult};
            HTTPService.uploadRequest('material/update/approve', params).then(function(result){
                console.log(result);
                if(result.data.STATUS == 'OK'){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/material/update/' + Data.id;
                }else{
                    alert(result.data.DATA);
                }
                IndexOverlayFactory.overlayHide();
            });
        });
    }
    
    var curDate = new Date();

    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    $scope.Search = false;
    $scope.SubDairyFarmingList = [];
    $scope.DairyFarmingList = [];
    $scope.SpermDetailList = [];
    $scope.SpermList = [
        {'id':1, 'name':'น้ำเชื้อแช่แข็งผ่านการพิสูจน์แล้ว'}
        ,{'id':2, 'name':'น้ำเชื้อแช่แข็งรอการพิสูจน์'}
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
    $scope.loadMasterGoalList();
    // $scope.loadDairyFarming('MAIN', '');
    // $scope.loadDairyFarming('CHILD', '');


});