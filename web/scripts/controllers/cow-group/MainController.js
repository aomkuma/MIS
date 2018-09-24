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
        window.location.href = '#/cow-group/update/' + id;
    }

    $scope.goSearch = function(){
        $scope.ViewType = 'MAIN';
        // $scope.loadList('cow-group/list/main');
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

    // $scope.loadList('cow-group/list', '');
    IndexOverlayFactory.overlayHide();

    // Dummy Data
    $scope.List = [
        {
            'MainItem':'ฝูงโคต้นงวด'
            ,'SubItem':[
                {
                    'SubItem':'ฝูงโคฟาร์มโคนม 1962'
                    ,'CurrentUnit':2000
                    ,'CurrentPercentage':32
                    ,'BeforeUnit':1980
                    ,'BeforePercentage':30
                    ,'DiffUnit':20
                    ,'DiffPercentage':2
                },
                {
                    'SubItem':'ฝูงโคฟาร์มโคเพศเมีย(โคทดแทน)'
                    ,'CurrentUnit':2000
                    ,'CurrentPercentage':32
                    ,'BeforeUnit':1980
                    ,'BeforePercentage':30
                    ,'DiffUnit':20
                    ,'DiffPercentage':2
                },
                {
                    'SubItem':'ฝูงโคฝึกอมรม(โคนมอินทรี)'
                    ,'CurrentUnit':2000
                    ,'CurrentPercentage':32
                    ,'BeforeUnit':1980
                    ,'BeforePercentage':30
                    ,'DiffUnit':20
                    ,'DiffPercentage':2
                }
                ,
                {
                    'SubItem':'ฝูงโคทั้งหมด'
                    ,'CurrentUnit':6000
                    ,'CurrentPercentage':32
                    ,'BeforeUnit':5940
                    ,'BeforePercentage':30
                    ,'DiffUnit':60
                    ,'DiffPercentage':2
                }
            ]
        },
        {
            'MainItem':'ฝูงโคปลายงวด'
            ,'SubItem':[
                {
                    'SubItem':'ฝูงโคฟาร์มโคนม 1962'
                    ,'CurrentUnit':2000
                    ,'CurrentPercentage':32
                    ,'BeforeUnit':1980
                    ,'BeforePercentage':30
                    ,'DiffUnit':20
                    ,'DiffPercentage':2
                },
                {
                    'SubItem':'ฝูงโคฟาร์มโคเพศเมีย(โคทดแทน)'
                    ,'CurrentUnit':2000
                    ,'CurrentPercentage':32
                    ,'BeforeUnit':1980
                    ,'BeforePercentage':30
                    ,'DiffUnit':20
                    ,'DiffPercentage':2
                },
                {
                    'SubItem':'ฝูงโคฝึกอมรม(โคนมอินทรี)'
                    ,'CurrentUnit':2000
                    ,'CurrentPercentage':32
                    ,'BeforeUnit':1980
                    ,'BeforePercentage':30
                    ,'DiffUnit':20
                    ,'DiffPercentage':2
                }
                ,
                {
                    'SubItem':'ฝูงโคทั้งหมด'
                    ,'CurrentUnit':6000
                    ,'CurrentPercentage':32
                    ,'BeforeUnit':5940
                    ,'BeforePercentage':30
                    ,'DiffUnit':60
                    ,'DiffPercentage':2
                }
            ]
        },
    ];

    $scope.Item = [
        {
            'label':'จำหน่ายน้ำแช่แข็งผ่านกระบวนการ'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'จำหน่ายน้ำแช่แข็งไม่ผ่านกระบวนการ'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'จำหน่ายไนโตรเจนเหลว'
            ,'unit' : [
                    {'label':''}
                    ,{'label':''}
                ]
        }
        ,{
            'label':'จำหน่ายวัสดุผสมเทียมและอื่นๆ'
            ,'unit' : [
                    {'label':''}
                ]
        }
    ];

    $scope.ItemUnit = [
        {'label':'หลอด'}
        ,{'label':'มูลค่า (บาท)'}
        ,{'label':'หลอด'}
        ,{'label':'มูลค่า (บาท)'}
        ,{'label':'หลอด'}
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
    // $scope.loadList('cow-group/list/main');

});