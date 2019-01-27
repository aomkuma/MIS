angular.module('e-homework').controller('MainPRController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
	
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'personal';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));   

    $scope.loadList = function(action){
        $scope.CurYear = $scope.condition.YearTo + 543;
        $scope.LastYear = $scope.CurYear - 1;
        var params = {
            'condition' : $scope.condition
            //, 'region' : $scope.PersonRegion
        };
       //  console.log(params);
      //  IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.DataList;
                $scope.SummaryData = result.data.DATA.Summary;
                console.log( $scope.List);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
/*
    $scope.loadListDetail = function(action, description){
        $scope.data_description = description;
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
            , 'description' : description
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.DetailList = result.data.DATA.DetailList;
                $scope.CooperativeList = result.data.DATA.CooperativeList;
                // $scope.SummaryData = result.data.DATA.Summary;
                // console.log($scope.List);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.loadListSubDetail = function(action, cooperative_id, description){
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
            , 'description' : description
            , 'cooperative_id' : cooperative_id
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.DetailList = result.data.DATA.SubDetailList;
                $scope.Cooperative = result.data.DATA.Cooperative;
                $scope.Header =  result.data.DATA.MonthNameList;
                // $scope.SummaryData = result.data.DATA.Summary;
                // console.log($scope.List);
            }
            IndexOverlayFactory.overlayHide();
        });
    }
*/
    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/veterinary/update/' + id;
    }

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
        $scope.loadList('personal/list/main');
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
        return num.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    /*

    $scope.viewDetail = function(description){
        $scope.ViewType = 'DETAIL';
        $scope.description = description;
        $scope.loadListDetail('veterinary/list/detail', description);
    }

    $scope.getRegionName = function(region_id){
        switch(region_id){
            case 1 : return 'อ.ส.ค. สำนักงานใหญ่ มวกเหล็ก';
            case 2 : return 'อ.ส.ค. สำนักงานกรุงเทพฯ Office';
            case 3 : return 'อ.ส.ค. สำนักงานภาคกลาง';
            case 4 : return 'อ.ส.ค. ภาคใต้ (ประจวบคีรีขันธ์)';
            case 5 : return 'อ.ส.ค. ภาคตะวันออกเฉียงเหนือ (ขอนแก่น)';
            case 6 : return 'อ.ส.ค. ภาคเหนือตอนล่าง (สุโขทัย)';
            case 7 : return 'อ.ส.ค. ภาคเหนือตอนบน (เชียงใหม่)';
            default : return '';
        }
    }

   */

    $scope.Header = [];
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
                        'DisplayType':'monthly'
                        ,'MonthFrom' : curDate.getMonth() + 1
                        ,'YearFrom': curDate.getFullYear()
                        ,'MonthTo' : curDate.getMonth() + 1
                        ,'YearTo': curDate.getFullYear()
                        ,'QuarterFrom':'1'
                        ,'QuarterTo':'4'
                    };

    $scope.SummaryData = {
                    'SummaryCurrentCow':''
                    ,'SummaryCurrentService':''
                    ,'SummaryCowPercentage':''
                    ,'SummaryServicePercentage':''
                    };

    $scope.ResultYearList = [
                {'years' : (curDate.getFullYear() + 543)}
                ,{'years' : (curDate.getFullYear() + 543) - 1}
            ];
//console.log($scope.condition);
    // $scope.loadList('veterinary/list', '');
    IndexOverlayFactory.overlayHide();
   
  //  setTimeout(function(){
      $scope.loadList('personal/list/main');
        
    //},200);
    

});