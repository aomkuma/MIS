angular.module('e-homework').controller('MainController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
    //console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'dairyfarming';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    
    $scope.PersonRegion = angular.fromJson(sessionStorage.getItem('person_region_session'));   
    $scope.loadList = function(action){
        var params = {
            'condition' : $scope.condition
            , 'region' : $scope.PersonRegion
        };
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest(action, params).then(function(result){
            if(result.data.STATUS == 'OK'){
                $scope.List = result.data.DATA.DataList;
                $scope.SummaryData = result.data.DATA.Summary;
                console.log($scope.List);
            }
            IndexOverlayFactory.overlayHide();
        });
    }

    $scope.getThaiDate = function(date){
        // console.log(date);
        return convertDateToFullThaiDateIgnoreTime(new Date(date));
    }

    $scope.goUpdate = function(id){
        window.location.href = '#/cooperative-milk/update/' + id;
    }

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
        // $scope.loadList('cooperative-milk/list/main');
    }


    $scope.viewDetail = function(){
        $scope.ViewType = 'DETAIL';
        console.log($scope.DetailList);
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

    $scope.Header = [];
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
                        'Region':null
                        ,'MonthFrom' : 1//curDate.getMonth()
                        ,'YearFrom': curDate.getFullYear()
                        ,'MonthTo' : 4//curDate.getMonth()
                        ,'YearTo': curDate.getFullYear()
                        ,'QuarterFrom':'1'
                        ,'QuarterTo':'4'
                    };

    $scope.SummaryData = {
                    'SummaryCurrentMineralAmount':'240000'
                    ,'SummaryCurrentMineralIncome':'10245000'
                    ,'SummaryMineralAmountPercentage':'15'
                    ,'SummaryMineralIncomePercentage':'11.21'
                    };

    $scope.ResultYearList = [
                {'years' : (curDate.getFullYear() + 543)}
                ,{'years' : (curDate.getFullYear() + 543) - 1}
            ];

    // $scope.loadList('cooperative-milk/list', '');
    IndexOverlayFactory.overlayHide();

    // Dummy Data
    $scope.List = [
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์'
            ,'TotalMilk':3000
            ,'TotalMilkSent':3000000
            ,'TotalMilkBeeb':2970
            ,'TotalMilkBeebSent':2920000
            ,'TotalMilkAmount':30
            ,'TotalValues':2.1
            ,'AverageValues':900000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'ฟาร์มโคมน 1962'
            ,'TotalMilk':3000
            ,'TotalMilkSent':3000000
            ,'TotalMilkBeeb':2970
            ,'TotalMilkBeebSent':2920000
            ,'TotalMilkAmount':30
            ,'TotalValues':2.1
            ,'AverageValues':900000
            ,'DiffBahtPercentage':2
        }
    ];

    $scope.DetailList = [
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค มิตภาพ จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค ปากช่อง จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค ซับกระดาน จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค พระพุทธบาท จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'อ.ส.ค.ภาคกลาง'
            ,'CooperativeName':'สหกรณ์ไทย-เดนมาร์ค จำกัด'
            ,'MemberName':'สหกรณ์ไทย-เดนมาร์ค ลำพญากลาง จำกัด'
            ,'TotalMilk':2000
            ,'TotalMilkSent':2000000
            ,'TotalMilkBeeb':1980
            ,'TotalMilkBeebSent':1920000
            ,'TotalMilkAmount':20
            ,'TotalValues':2
            ,'AverageValues':800000
            ,'DiffBahtPercentage':2
        }
    ];

    $scope.DetailSummary = [
        {'values':'4000'}
        ,{'values':'9600'}
        ,{'values':'3932'}
        ,{'values':'36000'}
        ,{'values':'3099'}
        ,{'values':'7200'}
        ,{'values':'151500'}
    ];
    // $scope.loadList('cooperative-milk/list/main');

});