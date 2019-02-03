angular.module('e-homework').controller('HomeController', function($scope, $cookies, $filter, $state, $uibModal, HTTPService, IndexOverlayFactory) {
	//console.log('Hello !');
    $scope.DEFAULT_LANGUAGE = 'TH';
    $scope.menu_selected = 'home';
    var $user_session = sessionStorage.getItem('user_session');
    
    if($user_session != null){
        $scope.$parent.currentUser = angular.fromJson($user_session);
    }else{
       window.location.replace('#/guest/logon');
    }

    $scope.$parent.Menu = angular.fromJson(sessionStorage.getItem('menu_session'));    

    var ctx = null;
    var myChart = null;
    $scope.loadData1 = function(data){
        
        if(myChart!=null){
            myChart.destroy();
        }
        IndexOverlayFactory.overlayShow();
        var params = {'condition':$scope.condition, 'DataType' : 'DBI'};
        HTTPService.clientRequest('chart/main/dbi', params).then(function(result){
            IndexOverlayFactory.overlayHide();
            $scope.Chart1 = result.data;

            ctx = document.getElementById("myChart").getContext('2d');
            myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels:  $scope.Chart1.LABEL/*["บริการสัตว์แพทย์", "ผสมเทียม", "แร่ธาตุ", "อาหารสัตว์", "ผลิตน้ำเชื้อแช่แข็ง", "จำหน่ายน้ำเชื้อแช่แข็ง", "วัสดุผสมเทียมและอื่นๆ", "ปัจจัยการการเลี้ยงโค", "ฝึกอบรม", "ท่องเที่ยว", "สหกรณ์และปริมาณน้ำนม", "ข้อมูลฝูงโค"]*/,
                    datasets: [
                    {
                        label: 'ข้อมูลปัจจุบัน (บาท)',
                        backgroundColor: '#FFB6B6',
                        stack: 'Stack0',
                        data: $scope.Chart1.DATA.Price
                        /*
                        [
                            7000000,
                            7000000,
                            700000,
                            7000000,
                            6000000,
                            7000000,
                            6000000,
                            1000000,
                            1500000,
                            3500000,
                            700000,
                            9500000,
                        ]
                        */
                    },
                    {
                        label: 'เป้าหมาย (บาท)',
                        backgroundColor: '#C2FFB6',
                        stack: 'Stack0',
                        data: $scope.Chart1.GOAL.Price
                        /*
                        [
                            3000000,
                            9000000,
                            1000000,
                            1000000,
                            7000000,
                            7000000,
                            7000000,
                            3000000,
                            2000000,
                            5000000,
                            1000000,
                            1000000,
                        ]
                        */
                    }, {
                        label: 'ข้อมูลปัจจุบัน (จำนวน)',
                        backgroundColor: '#CDF9F3',
                        stack: 'Stack1',
                        data: $scope.Chart1.DATA.Amount
                        /*
                        [
                            500000,
                            900000,
                            500000,
                            500000,
                            1000000,
                            500000,
                            500000,
                            500000,
                            500000,
                            1500000,
                            500000,
                            500000,
                        ]
                        */
                    }, {
                        label: 'เป้าหมาย (จำนวน)',
                        backgroundColor: '#F9EFCD',
                        stack: 'Stack1',
                        data: $scope.Chart1.GOAL.Amount
                        /*
                        [
                            620000,
                            620000,
                            820000,
                            720000,
                            620000,
                            620000,
                            920000,
                            620000,
                            1020000,
                            620000,
                            620000,
                            620000,
                        ]
                        */
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: 'ข้อมูลให้บริการและกิจการโคนม'
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false
                    },
                    responsive: true,
                    scales: {
                        xAxes: [{
                            stacked: true,
                        }],
                        yAxes: [{
                            stacked: true,
                            ticks: {
                                beginAtZero:true
                            }
                        }]
                    }
                }
            });
        });
    }

    $scope.loadData2 = function(data){
        var params = {'DataType' : 'II'};
        HTTPService.clientRequest('chart/main', params).then(function(result){
            $scope.Chart2 = result.data.DATA;
        });
    }

    $scope.makeDate = function(){
        var date = new Date();
        return convertDateToFullThaiDateIgnoreTime(date);
    }

    var date = new Date();
    $scope.condition = {'Year':date.getFullYear(), 'Year1':date.getFullYear()};
    $scope.YearList = getYearList(20);

    // Create chart
    

    var ctx1 = document.getElementById("myChart1").getContext('2d');
    var myChart1 = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: ["ข้อมูลการผลิต", "ข้อมูลการขาย", "ข้อมูลรับซื้อและจำหน่ายน้ำนม", "การสูญเสียในกระบวนการ", "การสูญเสียนอกกระบวนการ", "การสูญเสียรอจำหน่าย", "การสูญเสียในการะบวนการขนส่ง"],
            datasets: [{
                label: 'ข้อมูลปัจจุบัน (บาท)',
                backgroundColor: '#FFDDB6',
                stack: 'Stack0',
                data: [
                    12000000,
                    11000000,
                    11000000,
                    11000000,
                    11000000,
                    11000000,
                    11000000,
                ]
            }, {
                label: 'เป้าหมาย (บาท)',
                backgroundColor: '#B6B9FF',
                stack: 'Stack0',
                data: [
                    10000000,
                    20000000,
                    20000000,
                    20000000,
                    20000000,
                    20000000,
                    20000000,
                ]
            }, {
                label: 'ข้อมูลปัจจุบัน (จำนวน)',
                backgroundColor: '#FFBCBC',
                stack: 'Stack1',
                data: [
                    12000000,
                    11000000,
                    11000000,
                    11000000,
                    11000000,
                    11000000,
                    11000000,
                ]
            },{
                label: 'เป้าหมาย (จำนวน)',
                backgroundColor: '#F9CDF6',
                stack: 'Stack1',
                data: [
                    12000000,
                    11000000,
                    11000000,
                    11000000,
                    11000000,
                    11000000,
                    11000000,
                ]
            }]
        },
        options: {
            title: {
                display: true,
                text: 'ข้อมูลให้บริการและกิจการโคนม'
            },
            tooltips: {
                mode: 'index',
                intersect: false
            },
            responsive: true,
            scales: {
                xAxes: [{
                    stacked: true,
                }],
                yAxes: [{
                    stacked: true,
                    ticks: {
                        beginAtZero:true
                    }
                }]
            }
        }
    });

    $scope.loadData1();

});