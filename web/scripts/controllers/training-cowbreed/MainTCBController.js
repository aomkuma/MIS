angular.module('e-homework').controller('MainTCBController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
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
        window.location.href = '#/training-cowbreed/update/' + id;
    }

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
        $scope.loadList('training-cowbreed/list/main');
    }


    $scope.viewDetail = function(){
        $scope.ViewType = 'DETAIL';
        console.log($scope.DetailList);
    }
    $scope.exportReport = function(data,condition){
       // console.log(DetailList, $scope.data_description);
        // return;
        IndexOverlayFactory.overlayHide();
        var params = {
            'data' : data
           , 'condition' : condition
        }; 
        IndexOverlayFactory.overlayShow();
        HTTPService.clientRequest('training-cowbreed/report', params).then(function(result){
            if(result.data.STATUS == 'OK'){
                window.location.href="../" + result.data.DATA;
            }
            IndexOverlayFactory.overlayHide();
        });
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

    $scope.Header = [];
    $scope.ViewType = 'MAIN';
    $scope.YearList = getYearList(20);
    $scope.MonthList = getMonthList();
    var curDate = new Date();
    $scope.condition = {
                        'Region':null
                        ,'DisplayType' : 'monthly'
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

    // $scope.loadList('training-cowbreed/list', '');
    IndexOverlayFactory.overlayHide();

    // Dummy Data
    $scope.List = [
        {
            'RegionName':'ฝ.สส'
            ,'SpermName':'ผลิตน้ำเชื้อแช่แข็ง'
            ,'CurrentAmount':2000
            ,'CurrentBaht':2000000
            ,'BeforeAmount':1980
            ,'BeforeBaht':1920000
            ,'DiffAmount':20
            ,'DiffAmountPercentage':2
            ,'DiffBaht':800000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'สภต.'
            ,'SpermName':'น้ำเชื้อแช่แข็งผ่านการพิสูจน์แล้ว'
            ,'CurrentAmount':3000
            ,'CurrentBaht':3000000
            ,'BeforeAmount':2970
            ,'BeforeBaht':2920000
            ,'DiffAmount':30
            ,'DiffAmountPercentage':2.1
            ,'DiffBaht':900000
            ,'DiffBahtPercentage':2
        },
        {
            'RegionName':'สภต.'
            ,'SpermName':'น้ำเชื้อแช่แข็งรอการพิสูจน์ '
            ,'CurrentAmount':3000
            ,'CurrentBaht':3000000
            ,'BeforeAmount':2970
            ,'BeforeBaht':2920000
            ,'DiffAmount':30
            ,'DiffAmountPercentage':2.1
            ,'DiffBaht':900000
            ,'DiffBahtPercentage':2
        }
    ];

    $scope.Item = [
        {
            'label':'หลังสูตรการเลียงโคนม'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'การใช้เครื่องรีดนม'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'ฝึกงานนักศึกษา'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                ]
        }
        
    ];

    $scope.ItemUnit = [
        {'label':'ราย'}
        ,{'label':'มูลค่า (บาท)'}
        ,{'label':'ราย'}
        ,{'label':'มูลค่า (บาท)'}
        ,{'label':'ราย'}
        ,{'label':'มูลค่า (บาท)'}
        ,{'label':'บาท'}
        
    ];

    $scope.DetailList = [
        {
            'RegionName':'มิตรภาพ'
            ,'ValueList':[
                {'values':'0'}
                ,{'values':'0'}
                ,{'values':'1'}
                ,{'values':'12000'}
                ,{'values':'10'}
                ,{'values':'2400'}
                ,{'values':'50500'}
            ]
        },
        {
            'RegionName':'บกระดาน'
            ,'ValueList':[
                {'values':'2'}
                ,{'values':'4800'}
                ,{'values':'1'}
                ,{'values':'12000'}
                ,{'values':'10'}
                ,{'values':'2400'}
                ,{'values':'50500'}
            ]
        }
        ,
        {
            'RegionName':'ลำพญาลาง'
            ,'ValueList':[
                {'values':'2'}
                ,{'values':'4800'}
                ,{'values':'1'}
                ,{'values':'12000'}
                ,{'values':'10'}
                ,{'values':'2400'}
                ,{'values':'50500'}
            ]
        }
    ];

    $scope.DetailSummary = [
        {'values':'4'}
        ,{'values':'9600'}
        ,{'values':'3'}
        ,{'values':'36000'}
        ,{'values':'30'}
        ,{'values':'7200'}
        ,{'values':'151500'}
    ];
    $scope.loadList('training-cowbreed/list/main');

});