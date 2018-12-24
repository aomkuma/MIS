angular.module('e-homework').controller('MainGMController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
        console.log($scope.$parent.currentUser);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));   
    $scope.$parent.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));   
    $scope.Approval = false;
    $scope.getUserRole = function(){
        var params = {'UserID' : $scope.currentUser.UserID};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('account-permission/get', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.UserRole = result.data.DATA.Role;
                for(var i =0; i < $scope.UserRole.length; i++){
                    if($scope.UserRole[i].role == '2' && $scope.UserRole[i].actives == 'Y'){
                        $scope.Approval = true;
                    }
                }
                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadMasterGoalList = function(action){
        var params = {'actives' : 'Y'};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.MasterGoalList = result.data.DATA.List;
                $scope.loadList('goal-mission/list');
                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadList = function(action){
        var params = {'condition' : $scope.condition};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.List;
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDate(new Date(date));
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/goal-mission/update/' + id;
    }

    $scope.getGoalType = function(val){
        if(val == 'DBI'){
            return 'ข้อมูลกิจการโคนม';
        }else if(val == 'II'){
            return 'ข้อมูลอุตสาหกรรม';
        }
    }

    $scope.getRegionName = function(region_id){
        for(var i = 0;  i < $scope.$parent.PersonRegion.length; i++){
            if(region_id == $scope.$parent.PersonRegion[i].RegionID){
                return $scope.$parent.PersonRegion[i].RegionName;
            }
        }   
    }

    $scope.getGoalName = function(goal_id){
        for(var i = 0;  i < $scope.MasterGoalList.length; i++){
            if(goal_id == $scope.MasterGoalList[i].id){
                return $scope.MasterGoalList[i].goal_name;
            }
        }   
    }

    $scope.findGoalType = function(goal_id){
        var goalType = '';
        for(var i = 0;  i < $scope.MasterGoalList.length; i++){
            if(goal_id == $scope.MasterGoalList[i].id){
                
                goalType = $scope.getGoalType($scope.MasterGoalList[i].goal_type);
            }
        }   
        return goalType;
    }

    $scope.updateEdit = function(id, editable){
        var params = {'id' : id, 'editable' : editable};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('goal-mission/update/editable', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.loadList('goal-mission/list');
                // console.log($scope.UserList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.numberFormat = function(num){
        if(num == null){
            return '';
        }
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $scope.numberFormatComma = function(num){
        if(num == null){
            return '';
        }
        return parseFloat(num).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // $scope.condition = {'Year' : ''
    //                     , 'Region' : ''
    //                     , 'Goal' : ''
    //                 };
    $scope.YearList = getYearList(20);
    $scope.getUserRole();
    $scope.loadMasterGoalList('master-goal/list');
    

    // console.log($scope.$parent.PersonRegion);
});