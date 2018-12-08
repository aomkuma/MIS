angular.module('e-homework').controller('UpdateGMController', function($scope, $cookies, $filter, $state, $routeParams, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'management';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }
    $scope.ID = $routeParams.id;
    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session')); 
    $scope.$parent.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));      
    // console.log($scope.$parent.Menu);

    $scope.loadMasterGoalList = function(action, menu_type){
        var params = {'actives' : 'Y', 'menu_type' : menu_type};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.MasterGoalList = result.data.DATA.List;

                if($scope.ID !== undefined && $scope.ID !== null){
                    $scope.loadData('goal-mission/get', $scope.ID);
                }

                // console.log($scope.MasterGoalList);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadData = function(action, id){
        var params = {
            'id' : id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.Data = result.data.DATA.Data;
                $scope.avgList = $scope.Data.goal_mission_avg;
                for(var i = 0; i < $scope.avgList.length; i++){
                    $scope.avgIDList.push({'id':$scope.avgList[i].id});
                    $scope.avgList[i].amount = parseFloat($scope.avgList[i].amount);
                    $scope.avgList[i].price_value = parseFloat($scope.avgList[i].price_value);
                    $scope.totalAmount += $scope.avgList[i].amount;
                    $scope.totalPriceValue += $scope.avgList[i].price_value;
                }
                $scope.totalAmount = parseFloat($scope.totalAmount.toFixed(2));
                $scope.totalPriceValue = parseFloat($scope.totalPriceValue.toFixed(2));
                
                $scope.historyList = $scope.Data.goal_mission_history;

                $scope.Data.amount = parseFloat($scope.Data.amount);
                $scope.Data.price_value = parseFloat($scope.Data.price_value);
                
                //find goal type
                // $scope.findGoalType($scope.Data.goal_id);
            }
            IndexOverlayFactory.overlayHide();
        });

    }

    $scope.save = function(Data, AvgList){
        var params = {'Data' : Data, 'AvgList' : AvgList};
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('goal-mission/update', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                // if($scope.ID !== undefined && $scope.ID !== null){
                    window.location.href = '#/goal-mission/update/' + result.data.DATA.id;
                // }else{
                //     location.reload();    
                // }
                IndexOverlayFactory.overlayHide();
            }
        });
    }

    $scope.findGoalType = function(goal_id){
        for(var i = 0; i < $scope.MasterGoalList.length; i++){
            if($scope.MasterGoalList[i].id == goal_id){
                $scope.goal_type = $scope.MasterGoalList[i].goal_type;
                break;
            }
        }
    }

    $scope.cancelUpdate = function(){
        window.location.href = '#/goal-mission';
    }

    $scope.setGoalType = function(data){
        console.log(data);
        $scope.goal_type = data.goal_type;
    }

    $scope.avgData = function(Data){
        $scope.avgList = [];
        $scope.totalAmount = 0;
        $scope.totalPriceValue = 0;
        var avgAmount = parseFloat(Data.amount) / 12;
        var avgPriceValue = parseFloat(Data.price_value) / 12;
        avgAmount = parseFloat(avgAmount.toFixed(2));
        avgPriceValue = parseFloat(avgPriceValue.toFixed(2));
        var month = 10;
        var year = parseInt(Data.years);
        for(var i = 0; i < 12; i++){

            // Create Date from years
            if(month > 12){
                month = 1;
                year += 1;
            }
            var dateStr = year + '-' + padLeft(""+(month), '00') + '-01';
            // console.log(dateStr);
            // var curDate = new Date(dateStr);
            
            var avgData = {
                'id':($scope.avgIDList[i] === undefined?'':$scope.avgIDList[i].id)
                , 'goal_mission_id':''
                , 'avg_date':dateStr
                , 'amount':avgAmount
                , 'price_value':avgPriceValue
            };

            $scope.avgList.push(avgData);
            month++;
            $scope.totalAmount += avgAmount;
            $scope.totalPriceValue += avgPriceValue;
        }

        $scope.totalAmount = parseFloat($scope.totalAmount.toFixed(2));
        $scope.totalPriceValue = parseFloat($scope.totalPriceValue.toFixed(2));
        // console.log($scope.avgList);
    }

    $scope.getMonthYearText = function(dateStr){
        return getMonthYearText(dateStr);
    }

    $scope.totalAmount = 0;
    $scope.totalPriceValue = 0;
    $scope.YearList = getYearList(20);
    $scope.Data = {
        'id':''
        , 'years':''
        , 'region_id':null
        , 'goal_id':null
        , 'amount':null
        , 'unit':null
        , 'price_value':null
        , 'editable':'Y'
        , 'actives':'Y'
        , 'create_date':''
        , 'update_date':''
    };
    $scope.avgList = [];
    $scope.avgIDList = [];
    $scope.historyList = [];

    // 
    if($scope.ID != null){
        $scope.loadMasterGoalList('master-goal/list', '');
        $scope.loadData('goal-mission/get', $scope.ID);
    }

});