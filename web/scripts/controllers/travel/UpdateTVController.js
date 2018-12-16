angular.module('e-homework').controller('UpdateTVController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
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
                    $scope.loadData('travel/get', $scope.ID);
                }
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadDairyFarming = function(type, parent_id){
        var params = {'type':type, 'parent_id' : parent_id};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('dairy-farming/list/travel', params).then(function(result){
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
        var params = {'actives':'Y', 'menu_type' : 'ท่องเที่ยว'};
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
            'months' : $scope.Sperm.months
            ,'years' : $scope.Sperm.years
        };
        if(id != null){
            params = {'id':id};
        }else{
            params = {
                'months' : $scope.Sperm.months
                ,'years' : $scope.Sperm.years
            };
        }
        
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK' && result.data.DATA.Data != null){
                $scope.Sperm = result.data.DATA.Data;
                // $scope.Sperm.cooperative_id = parseInt($scope.Sperm.cooperative_id);
                if($scope.Sperm.travel_detail !== null){
                    $scope.SpermDetailList = $scope.Sperm.travel_detail;
                }
                // load sub dar=iry farming
                for(var i =0; i < $scope.SpermDetailList.length; i++){
                    if($scope.SpermDetailList[i].travel_date != null && $scope.SpermDetailList[i].travel_date != undefined && $scope.SpermDetailList[i].travel_date != ''){
                        $scope.SpermDetailList[i].travel_date = makeDate($scope.SpermDetailList[i].travel_date);
                    }
                }
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
        for(var i =0; i < SpermDetailList.length; i++){
            if(SpermDetailList.travel_date != null && SpermDetailList.travel_date != undefined && SpermDetailList.travel_date != ''){
                SpermDetailList.travel_date = makeSQLDate(SpermDetailList.travel_date);
            }
        }
        var params = {'Data' : Sperm, 'Detail' : SpermDetailList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('travel/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                //alert('save success');
                // if($scope.ID !== undefined && $scope.ID !== null){
                    alert('บันทึกสำเร็จ');
                    window.location.href = '#/travel/update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/travel';
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
        $scope.loadData('travel/get');

        $scope.MonthName = '';
        $scope.YearName = '';

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
        // $scope.SpermDetailList = [
        //     {
        //         'id':''
        //         ,'travel_id':'1'
        //         ,'amount':'2'
        //         ,'price':'200'
        //         ,'values':'400'
        //         ,'organize':'โรงเรียน'
        //         ,'travel_date':'2018-09-01'
        //         ,'Pay':{
        //                 'adult':'100'
        //                 ,'child':'50'
        //                 ,'student':'70'
        //             }
        //         ,'Price':{
        //                 'adult':'150'
        //                 ,'child':'100'
        //                 ,'student':'120'
        //             }
        //         ,'Except':{
        //                 'adult':'2'
        //                 ,'child':'4'
        //                 ,'student':'3'
        //             }
        //     }
        // ];
    }

    $scope.addSpermDetail = function(){

        var detail = {
                'id':''
                ,'travel_id':''
                ,'amount':''
                ,'price':''
                ,'values':''
                ,'organize':''
                ,'travel_date':''
                ,'open_date':''
                ,'Pay':{
                        'adult':''
                        ,'child':''
                        ,'student':''
                    }
                ,'Price':{
                        'adult':''
                        ,'child':''
                        ,'student':''
                    }
                ,'Except':{
                        'adult':''
                        ,'child':''
                        ,'student':''
                    }
            };

        $scope.SpermDetailList.push(detail);
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
                HTTPService.clientRequest('travel/delete/detail', params).then(function(result){
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
            $scope.SpermDetailList[parent_index].travel_item.splice(child_index, 1);
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
                HTTPService.clientRequest('travel/delete/item', params).then(function(result){
                    if(result.data.STATUS == 'OK'){
                        $scope.SpermDetailList[parent_index].travel_item.splice(child_index, 1);
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
            , 'days':$scope.currentDay.getDate()
            , 'months':($scope.currentDay.getMonth() + 1)
            , 'years':$scope.currentDay.getFullYear()
            , 'create_date':''
            , 'update_date':''
        };    
    }
    
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    $scope.DayList = getDayList();
    $scope.Search = false;
    $scope.SubDairyFarmingList = [];
    $scope.DairyFarmingList = [];
    $scope.SpermDetailList = [];
    $scope.SpermList = [
        {'id':'1', 'name':'น้ำเชื้อแช่แข็งผ่านการพิสูจน์แล้ว'}
        ,{'id':'2', 'name':'น้ำเชื้อแช่แข็งรอการพิสูจน์'}
    ];
    $scope.DatePoupObj = [];
    $scope.popup1 = {
        opened: false
    };
    $scope.openDateObj = function(index) {
        $scope.SpermDetailList[index].open_date = true;
    };

    $scope.currentDay = new Date();

    $scope.setSperm();
    $scope.loadCooperative();
    $scope.loadMasterGoalList();
    // $scope.loadDairyFarming('MAIN', '');
    // $scope.loadDairyFarming('CHILD', '');


});