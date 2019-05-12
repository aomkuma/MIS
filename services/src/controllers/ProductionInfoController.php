<?php

namespace App\Controller;

use App\Service\ProductionInfoService;
use App\Service\MasterGoalService;
use App\Service\GoalMissionService;
use App\Service\ProductMilkService;
use App\Service\SubProductMilkService;
use App\Service\ProductMilkDetailService;
use App\Service\FactoryService;

class ProductionInfoController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function getLastDayOfMonth($time) {
        return $date = date("t", strtotime($time . '-' . '01'));

        // return date("t", $last_day_timestamp);
    }

    public function getMonthName($month) {
        switch ($month) {
            case 1 : $monthTxt = 'มกราคม';
                break;
            case 2 : $monthTxt = 'กุมภาพันธ์';
                break;
            case 3 : $monthTxt = 'มีนาคม';
                break;
            case 4 : $monthTxt = 'เมษายน';
                break;
            case 5 : $monthTxt = 'พฤษภาคม';
                break;
            case 6 : $monthTxt = 'มิถุนายน';
                break;
            case 7 : $monthTxt = 'กรกฎาคม';
                break;
            case 8 : $monthTxt = 'สิงหาคม';
                break;
            case 9 : $monthTxt = 'กันยายน';
                break;
            case 10 : $monthTxt = 'ตุลาคม';
                break;
            case 11 : $monthTxt = 'พฤศจิกายน';
                break;
            case 12 : $monthTxt = 'ธันวาคม';
                break;
        }
        return $monthTxt;
    }

    public function loadDataApprove($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $user_session = $params['user_session'];

            $Data = ProductionInfoService::loadDataApprove($user_session['UserID']);

            $this->data_result['DATA']['DataList'] = $Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMainList($request, $response, $args) {
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $condition = $params['obj']['condition'];
            $region = $params['obj']['region'];
            $RegionList = [];
            foreach ($region as $key => $value) {
                $RegionList[] = $value['RegionID'];
            }

            // print_r($RegionList);
            // exit;
            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDataList($condition, $RegionList);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDataList($condition, $RegionList);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDataList($condition, $RegionList);
            }
            $DataList = $Result['DataList'];
            $Summary = $Result['Summary'];
            // print_r($DataList);
            // exit;

            $this->data_result['DATA']['DataList'] = $DataList;
            $this->data_result['DATA']['Summary'] = $Summary;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMonthDatasubproductreport($condition) {



        $ymFrom = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }


        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        $DataList = [];
        $DataSummary = [];

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList();
        foreach ($ProducMilkList as $value) {
            $Datapro['productname'] = $value['name'];
            $Datapro['item'] = [];
            $Datapro['sumCurrentAmount'] = 0;
            $Datapro['sumBeforeAmount'] = 0;
            $Datapro['sumDiffAmount'] = 0;
            $Datapro['sumDiffAmountPercentage'] = 0;
            $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);
            foreach ($SubProductMilkList as $k => $v) {
                $curMonth = $condition['MonthFrom'];
                $curYear = $condition['YearFrom'];
                $data = [];
                $data['subProductionInfoName'] = $v['name'];
                for ($i = 0; $i < $diffMonth; $i++) {

                    $monthName = ProductionInfoController::getMonthName($curMonth);
                    $data['Month'] = $monthName;


                    // get ProductMilkDetailService
                    $ProductMilkDetailList = ProductMilkDetailService::getListByParent($v['id']);

                    foreach ($ProductMilkDetailList as $k1 => $v1) {

                        $master_type_id = $v1['id'];

                        $Current = ProductionInfoService::getDetailListsub($curYear, $curMonth, $master_type_id);
                        //   print_r($Current);
                        $data['CurrentAmount'] += floatval($Current['sum_amount']);
//                            $data['CurrentBaht'] += floatval($Current['sum_baht']);

                        $Before = ProductionInfoService::getDetailListsub($curYear - 1, $curMonth, $master_type_id);
                        $data['BeforeAmount'] += floatval($Before['sum_amount']);
//                            $data['BeforeBaht'] += floatval($Before['sum_baht']);

                        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                        $data['DiffAmount'] = $DiffAmount;



//                            $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
//                            $data['DiffBaht'] += $DiffBaht;
//                            if ($data['BeforeBaht'] != 0) {
//                                $data['DiffBahtPercentage'] += (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
//                            } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
//                                $data['DiffBahtPercentage'] = 100;
//                            }

                        $data['CreateDate'] = $Current['update_date'];
                        $data['ApproveDate'] = $Current['office_approve_date'];
//                        if (!empty($Current['office_approve_id'])) {
//                            if (empty($Current['office_approve_comment'])) {
//                                $data['Status'] = 'อนุมัติ';
//                            } else {
//                                $data['Status'] = 'ไม่อนุมัติ';
//                            }
//                        }
                        $data['Description'] = ['months' => $curMonth
                            , 'years' => $curYear
                        ];

                        //  array_push($DataList, $data);
                        $Datapro['sumCurrentAmount'] += floatval($Current['sum_amount']);
                        $Datapro['sumBeforeAmount'] += floatval($Before['sum_amount']);

                        $DataSummary['sumCurrentAmount'] += floatval($Current['sum_amount']);
                        $DataSummary['sumBeforeAmount'] += floatval($Before['sum_amount']);

                        $Datapro['sumDiffAmount'] = 0;
                        $Datapro['sumDiffAmountPercentage'] = 0;
                    }
                    $Datapro['sumDiffAmount'] = $Datapro['sumCurrentAmount'] - $Datapro['sumBeforeAmount'];


                    if ($Datapro['sumBeforeAmount'] != 0) {
                        $Datapro['sumDiffAmountPercentage'] += (( $Datapro['sumCurrentAmount'] - $Datapro['sumBeforeAmount']) / $Datapro['sumBeforeAmount']) * 100;
                    } else if (empty($Datapro['sumBeforeAmount']) && !empty($Datapro['sumCurrentAmount'])) {
                        $Datapro['sumDiffAmountPercentage'] = 100;
                    }
                    $curMonth++;
                    if ($curMonth > 12) {
                        $curMonth = 1;
                        $curYear++;
                    }
                }
//                $ck=[$date1,$date2,$diffMonth];
                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] += (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                }
                array_push($Datapro['item'], $data);
            }
            array_push($DataList, $Datapro);
        }
        $DataSummary['sumDiffAmount'] = $DataSummary['sumCurrentAmount'] - $DataSummary['sumBeforeAmount'];


        if ($DataSummary['sumBeforeAmount'] != 0) {
            $DataSummary['sumDiffAmountPercentage'] += (( $DataSummary['sumCurrentAmount'] - $DataSummary['sumBeforeAmount']) / $Datapro['sumBeforeAmount']) * 100;
        } else if (empty($DataSummary['sumBeforeAmount']) && !empty($DataSummary['sumCurrentAmount'])) {
            $DataSummary['sumDiffAmountPercentage'] = 100;
        }
        // print_r($DataList);
        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getMonthDataListreport($condition, $fac_id) {

        $factory_id = $fac_id;
        $ymFrom = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        $DataList = [];
        $DataSummary = [];

        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList();

        // get Factory
        $FactoryList = FactoryService::getData($factory_id);
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        for ($i = 0; $i < $diffMonth; $i++) {


            // loop by factory
            foreach ($FactoryList as $key1 => $value1) {

                $data = [];
                // $data['bg_color'] = '#ccc';
                $DataList['factory_name'] = $value1['factory_name'];
                $DataList['product'] = [];
                //  array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;


                foreach ($ProducMilkList as $key => $value) {

                    $datapro = [];

                    $datapro['ProductionInfoName'] = $value['name'];
                    $datapro['sub'] = [];
                    $datapro['summary'] = [];
                    // array_push($DataList, $data);
                    // get SubProductMilkService
                    $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

                    // Prepare condition


                    $Sum_CurrentAmount = 0;
                    $Sum_CurrentBaht = 0;
                    $Sum_BeforeAmount = 0;
                    $Sum_BeforeBaht = 0;

                    $factory_id = $value1['id'];

                    foreach ($SubProductMilkList as $k => $v) {

                        $data = [];
                        // $data['bg_color'] = '#ccc';
                        $data['subProductionInfoName'] = $v['name'];
                        $data['Month'] = $monthName;
                        $data['CurrentAmount'] = 0;
                        $data['BeforeAmount'] = 0;
                        $data['DiffAmount'] = 0;
                        $data['DiffAmountPercentage'] = 0;
                        // $data['detail'] = [];
                        //array_push($DataList, $data);
                        $monthName = ProductionInfoController::getMonthName($curMonth);
                        // get ProductMilkDetailService
                        $ProductMilkDetailList = ProductMilkDetailService::getListByParent($v['id']);
                        //    print_r($ProductMilkDetailList);
                        foreach ($ProductMilkDetailList as $k1 => $v1) {

                            $master_type_id = $v1['id'];

//                            $monthName = ProductionInfoController::getMonthName($curMonth);
                            //   $data = [];
//                            $data['subProductionInfoName'] = $v['name'];
//                            $data['Month'] = $monthName;
//                            // $data['RegionName'] = $value['RegionName'];
//                            $data['ProductionInfoName'] = $v1['name'];
//                            $data['Month'] = $monthName;
                            // get cooperative type

                            $Current = ProductionInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                            // print_r($Current);exit;
                            $data['CurrentAmount'] += floatval($Current['sum_amount']);
//                          

                            $Before = ProductionInfoService::getDetailList($curYear - 1, $curMonth, $factory_id, $master_type_id);
                            $data['BeforeAmount'] += floatval($Before['sum_amount']);
//                            $data['BeforeBaht'] += floatval($Before['sum_baht']);

                            $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                            //  print_r($DiffAmount);
                            $data['DiffAmount'] = $DiffAmount;



//                         

                            $data['CreateDate'] = $Current['update_date'];

                            $data['Description'] = ['months' => $curMonth
                                , 'years' => $curYear
                                , 'factory_id' => $factory_id
                            ];

                            //  array_push($DataList, $data);

                            $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + floatval($Current['sum_amount']);
                            $DataSummary['SummaryBefore'] = $DataSummary['SummaryBefore'] + floatval($Before['sum_amount']);

                            $Sum_CurrentAmount += floatval($Current['sum_amount']);
                            //$Sum_CurrentBaht += $data['CurrentBaht'];
                            $Sum_BeforeAmount += floatval($Before['sum_amount']);
                            //  $Sum_BeforeBaht += $data['BeforeBaht'];

                            $Total_CurrentAmount += floatval($Current['sum_amount']);
                            $Total_CurrentBaht += $data['CurrentBaht'];
                            $Total_BeforeAmount += floatval($Before['sum_amount']);
                            $Total_BeforeBaht += $data['BeforeBaht'];

                            $GrandTotal_CurrentAmount += floatval($Current['sum_amount']);
                            $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                            $GrandTotal_BeforeAmount += floatval($Before['sum_amount']);
                            $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                            //  array_push($datapro['sub'], $data);
                        }
                        if ($data['BeforeAmount'] != 0) {
                            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                            $data['DiffAmountPercentage'] = 100;
                        }
                        array_push($datapro['sub'], $data);
                    }


                    $data = [];
                    $data['bg_color'] = '#AFE1FA';
                    $data['ProductionInfoName'] = 'รวม';

                    $data['CurrentAmount'] = $Sum_CurrentAmount;


                    $data['BeforeAmount'] = $Sum_BeforeAmount;


                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $data['CurrentAmount'] - $data['BeforeAmount'];
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    }


//                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
//                    $data['DiffBaht'] = $DiffBaht;
//
//                    if ($data['BeforeBaht'] != 0) {
//                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
//                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
//                        $data['DiffBahtPercentage'] = 100;
//                    }
                    array_push($datapro['summary'], $data);
                    array_push($DataList['product'], $datapro);
                }
            }

            $curMonth++;
            if ($curMonth > 12) {
                $curMonth = 1;
                $curYear++;
            }
        }

        return ['DataList' => $DataList, 'Summary' => $DataSummary, 'Detail' => $Datadetail];
    }

    public function getMonthDataList($condition, $RegionList) {

        $factory_id = $condition['Factory'];
        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }
        $curMonth = $condition['MonthFrom'];
        $DataList = [];
        $DataSummary = [];

        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList('', '', [], $factory_id);

        // get Factory
        // $FactoryList = FactoryService::getList($RegionList, $factory_id);
        $curYear = $condition['YearTo'];
        $beforeYear = $condition['YearTo'] - 1;

        for ($i = 0; $i < $diffMonth; $i++) {

            foreach ($ProducMilkList as $key => $value) {

                $data = [];
                $data['bg_color'] = '#ccc';
                $data['ProductionInfoName'] = $value['name'];
                $data['show_button'] = 'Y';
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'factory_id' => $factory_id
                    , 'DisplayType' => $condition['DisplayType']
                ];
                array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;

                // loop by factory
                // foreach ($FactoryList as $key1 => $value1) {
                // $data = [];
                // $data['bg_color'] = '#ccc';
                // $data['ProductionInfoName'] = $value1['factory_name'];
                // array_push($DataList, $data);
                // get SubProductMilkService
                $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

                // Prepare condition

                $Sum_CurrentAmount = 0;
                $Sum_CurrentBaht = 0;
                $Sum_BeforeAmount = 0;
                $Sum_BeforeBaht = 0;

                // $factory_id = $value1['id'];

                foreach ($SubProductMilkList as $k => $v) {

                    $master_type_id = $v['id'];

                    $monthName = ProductionInfoController::getMonthName($curMonth);

                    $data = [];
                    // $data['RegionName'] = $value['RegionName'];
                    $data['ProductionInfoName'] = $v['name'];
                    $data['Month'] = $monthName;

                    // get cooperative type
                    // echo "$curYear, $curMonth, $factory_id, $master_type_id";exit;
                    $Current = ProductionInfoService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                    // print_r($Current);exit;
                    $data['CurrentAmount'] = floatval($Current['sum_amount']);
                    $data['CurrentBaht'] = floatval($Current['sum_baht']);

                    $Before = ProductionInfoService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                    $data['BeforeAmount'] = floatval($Before['sum_amount']);
                    $data['BeforeBaht'] = floatval($Before['sum_baht']);

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;

                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }

                    $data['CreateDate'] = $Current['update_date'];
                    $data['ApproveDate'] = $Current['office_approve_date'];
                    if (!empty($Current['office_approve_id'])) {
                        if (empty($Current['office_approve_comment'])) {
                            $data['Status'] = 'อนุมัติ';
                        } else {
                            $data['Status'] = 'ไม่อนุมัติ';
                        }
                    }
                    $data['Description'] = ['months' => $curMonth
                        , 'years' => $curYear
                        , 'factory_id' => $factory_id
                    ];

                    array_push($DataList, $data);

                    $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                    $Sum_CurrentAmount += $data['CurrentAmount'];
                    $Sum_CurrentBaht += $data['CurrentBaht'];
                    $Sum_BeforeAmount += $data['BeforeAmount'];
                    $Sum_BeforeBaht += $data['BeforeBaht'];

                    $Total_CurrentAmount += $data['CurrentAmount'];
                    $Total_CurrentBaht += $data['CurrentBaht'];
                    $Total_BeforeAmount += $data['BeforeAmount'];
                    $Total_BeforeBaht += $data['BeforeBaht'];

                    $GrandTotal_CurrentAmount += $data['CurrentAmount'];
                    $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                    $GrandTotal_BeforeAmount += $data['BeforeAmount'];
                    $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                    // }

                    // $data = [];
                    // $data['bg_color'] = '#AFE1FA';
                    // $data['ProductionInfoName'] = 'รวม';

                    // $data['CurrentAmount'] = $Sum_CurrentAmount;
                    // $data['CurrentBaht'] = $Sum_CurrentBaht;

                    // $data['BeforeAmount'] = $Sum_BeforeAmount;
                    // $data['BeforeBaht'] = $Sum_BeforeBaht;

                    // $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    // $data['DiffAmount'] = $DiffAmount;
                    // if ($data['BeforeAmount'] != 0) {
                    //     $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    // } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    //     $data['DiffAmountPercentage'] = 100;
                    // } else {
                    //     $data['DiffAmountPercentage'] = 0;
                    // }


                    // $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    // $data['DiffBaht'] = $DiffBaht;

                    // if ($data['BeforeBaht'] != 0) {
                    //     $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    // } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    //     $data['DiffBahtPercentage'] = 100;
                    // } else {
                    //     $data['DiffBahtPercentage'] = 0;
                    // }
                    // array_push($DataList, $data);
                }
                // $data = [];
                // $data['bg_color'] = '#A0DEFD';
                // $data['ProductionInfoName'] = 'รวมทั้งสิ้น';
                // $data['CurrentAmount'] = $Total_CurrentAmount;
                // $data['CurrentBaht'] = $Total_CurrentBaht;
                // $data['BeforeAmount'] = $Total_BeforeAmount;
                // $data['BeforeBaht'] = $Total_BeforeBaht;
                // $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                // $data['DiffAmount'] = $DiffAmount;
                // if ($data['BeforeAmount'] != 0) {
                //     $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                // } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                //     $data['DiffAmountPercentage'] = 100;
                // }
                // $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                // $data['DiffBaht'] = $DiffBaht;
                // if ($data['BeforeBaht'] != 0) {
                //     $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                // } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                //     $data['DiffBahtPercentage'] = 100;
                // }
                // array_push($DataList, $data);
            }

            $curMonth++;
        }

        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#87D0F5';
        $data['Month'] = 'รวมทุกรายการ';
        $data['CurrentAmount'] = $GrandTotal_CurrentAmount;
        $data['CurrentBaht'] = $GrandTotal_CurrentBaht;
        $data['BeforeAmount'] = $GrandTotal_BeforeAmount;
        $data['BeforeBaht'] = $GrandTotal_BeforeBaht;

        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        } else {
            $data['DiffAmountPercentage'] = 0;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        } else {
            $data['DiffBahtPercentage'] = 0;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getQuarterDataList($condition, $RegionList) {

        $factory_id = $condition['Factory'];
        // get loop to query
        // $loop = intval($condition['YearTo'] . $condition['QuarterTo']) - intval($condition['YearFrom'] . $condition['QuarterFrom']) + 1;
        $diffYear = 1; //($condition['YearTo'] - $condition['YearFrom']) + 1;
        $cnt = 0;
        $loop = 0;
        $j = $condition['QuarterFrom'];

        for ($i = 0; $i < $diffYear; $i++) {
            if ($cnt == $diffYear) {
                for ($k = 0; $k < $condition['QuarterFrom']; $k++) {
                    $loop++;
                }
            } else {

                if ($i > 0) {
                    $j = 0;
                }

                if ($diffYear == 1) {
                    $length = $condition['QuarterFrom'];
                } else {
                    $length = 4;
                }
                for (; $j < $length; $j++) {
                    $loop++;
                }
            }
            $cnt++;
        }
        $loop++;
        $curQuarter = intval($condition['QuarterFrom']);

        if (intval($curQuarter) == 1) {
            $curYear = intval($condition['YearTo']) - 1;
            $beforeYear = $curYear - 1;
        } else {
            $curYear = intval($condition['YearTo']);
            $beforeYear = $curYear - 1;
        }

        $DataList = [];
        $DataSummary = [];

        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList('', '', [], $factory_id);

        for ($i = 0; $i < $loop; $i++) {

            if ($i > 0 && $curQuarter == 2) {
                $curYear++;
                $beforeYear = $curYear - 1;
            }

            // find month in quarter
            if ($curQuarter == 1) {
                $monthList = [10, 11, 12];
            } else if ($curQuarter == 2) {
                $monthList = [1, 2, 3];
            } else if ($curQuarter == 3) {
                $monthList = [4, 5, 6];
            } else if ($curQuarter == 4) {
                $monthList = [7, 8, 9];
            }

            foreach ($ProducMilkList as $key => $value) {

                $data = [];
                $data['bg_color'] = '#ccc';
                $data['ProductionInfoName'] = $value['name'];
                $data['show_button'] = 'Y';
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'quarter' => $curQuarter
                    , 'factory_id' => $factory_id
                    , 'DisplayType' => $condition['DisplayType']
                ];
                array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;

                // loop by factory
                // foreach ($FactoryList as $key1 => $value1) {
                // $data = [];
                // $data['bg_color'] = '#ccc';
                // $data['ProductionInfoName'] = $value1['factory_name'];
                // array_push($DataList, $data);
                // get SubProductMilkService
                $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

                // Prepare condition

                $Sum_CurrentAmount = 0;
                $Sum_CurrentBaht = 0;
                $Sum_BeforeAmount = 0;
                $Sum_BeforeBaht = 0;

                // $factory_id = $value1['id'];

                foreach ($SubProductMilkList as $k => $v) {

                    $master_type_id = $v['id'];

                    $monthName = ProductionInfoController::getMonthName($curMonth);

                    // get cooperative type
                    $SumCurrentAmount = 0;
                    $SumCurrentBaht = 0;
                    $SumBeforeAmount = 0;
                    $SumBeforeBaht = 0;
                    $UpdateDate = '';
                    $ApproveDate = '';
                    $ApproveComment = '';
                    // loop get quarter sum data

                    for ($j = 0; $j < count($monthList); $j++) {

                        $curMonth = $monthList[$j];
                        $Current = ProductionInfoService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                        // print_r($Current);exit;
                        $SumCurrentAmount += floatval($Current['sum_amount']);
                        $SumCurrentBaht += floatval($Current['sum_baht']);

                        $Before = ProductionInfoService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                        $SumBeforeAmount += floatval($Before['sum_amount']);
                        $SumBeforeBaht += floatval($Before['sum_baht']);

                        if (!empty($Current['update_date'])) {
                            $UpdateDate = $Current['update_date'];
                        }
                        if (!empty($Current['office_approve_id'])) {
                            $ApproveDate = $Current['office_approve_date'];
                        }
                        if (!empty($Current['office_approve_comment'])) {
                            $ApproveComment = $Current['office_approve_comment'];
                        }
                    }

                    $data = [];
                    // $data['RegionName'] = $value['RegionName'];
                    $data['ProductionInfoName'] = $v['name'];
                    $data['Quarter'] = $curQuarter;

                    $data['CurrentAmount'] = $SumCurrentAmount;
                    $data['CurrentBaht'] = $SumCurrentBaht;

                    $data['BeforeAmount'] = $SumBeforeAmount;
                    $data['BeforeBaht'] = $SumBeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;

                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }

                    $data['CreateDate'] = $Current['update_date'];
                    $data['ApproveDate'] = $Current['office_approve_date'];
                    if (!empty($Current['office_approve_id'])) {
                        if (empty($Current['office_approve_comment'])) {
                            $data['Status'] = 'อนุมัติ';
                        } else {
                            $data['Status'] = 'ไม่อนุมัติ';
                        }
                    }
                    $data['Description'] = ['months' => $curMonth
                        , 'years' => $curYear
                        , 'factory_id' => $factory_id
                    ];

                    array_push($DataList, $data);

                    $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                    $Sum_CurrentAmount += $data['CurrentAmount'];
                    $Sum_CurrentBaht += $data['CurrentBaht'];
                    $Sum_BeforeAmount += $data['BeforeAmount'];
                    $Sum_BeforeBaht += $data['BeforeBaht'];

                    $Total_CurrentAmount += $data['CurrentAmount'];
                    $Total_CurrentBaht += $data['CurrentBaht'];
                    $Total_BeforeAmount += $data['BeforeAmount'];
                    $Total_BeforeBaht += $data['BeforeBaht'];

                    $GrandTotal_CurrentAmount += $data['CurrentAmount'];
                    $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                    $GrandTotal_BeforeAmount += $data['BeforeAmount'];
                    $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                    // }

                    $data = [];
                    $data['bg_color'] = '#AFE1FA';
                    $data['ProductionInfoName'] = 'รวม';

                    $data['CurrentAmount'] = $Sum_CurrentAmount;
                    $data['CurrentBaht'] = $Sum_CurrentBaht;

                    $data['BeforeAmount'] = $Sum_BeforeAmount;
                    $data['BeforeBaht'] = $Sum_BeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }
                    array_push($DataList, $data);
                }
            }

            $curMonth++;
        }

        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#87D0F5';
        $data['Quarter'] = 'รวมทุกรายการ';
        $data['CurrentAmount'] = $GrandTotal_CurrentAmount;
        $data['CurrentBaht'] = $GrandTotal_CurrentBaht;
        $data['BeforeAmount'] = $GrandTotal_BeforeAmount;
        $data['BeforeBaht'] = $GrandTotal_BeforeBaht;

        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        } else {
            $data['DiffAmountPercentage'] = 0;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        } else {
            $data['DiffBahtPercentage'] = 0;
        }

        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getAnnuallyDataList($condition, $RegionList) {

        $factory_id = $condition['Factory'];

        $loop = 1; //intval($condition['YearTo']) - intval($condition['YearFrom']) + 1;
        $curYear = $condition['YearTo'];

        $beforeYear = $curYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        $DataList = [];
        $DataSummary = [];
        $curYear = $condition['YearTo'];

        // get master goal
        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;

        // get ProductMilkService
        $ProducMilkList = ProductMilkService::getList('', '', [], $factory_id);

        for ($i = 0; $i < $loop; $i++) {

            foreach ($ProducMilkList as $key => $value) {

                $data = [];
                $data['bg_color'] = '#ccc';
                $data['ProductionInfoName'] = $value['name'];
                $data['show_button'] = 'Y';
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'factory_id' => $factory_id
                    , 'DisplayType' => $condition['DisplayType']
                ];
                array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;

                // loop by factory
                // foreach ($FactoryList as $key1 => $value1) {
                // $data = [];
                // $data['bg_color'] = '#ccc';
                // $data['ProductionInfoName'] = $value1['factory_name'];
                // array_push($DataList, $data);
                // get SubProductMilkService
                $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

                // Prepare condition

                $Sum_CurrentAmount = 0;
                $Sum_CurrentBaht = 0;
                $Sum_BeforeAmount = 0;
                $Sum_BeforeBaht = 0;

                // $factory_id = $value1['id'];

                foreach ($SubProductMilkList as $k => $v) {

                    $master_type_id = $v['id'];

                    $monthName = ProductionInfoController::getMonthName($curMonth);

                    // get cooperative type
                    $SumCurrentAmount = 0;
                    $SumCurrentBaht = 0;
                    $SumBeforeAmount = 0;
                    $SumBeforeBaht = 0;
                    $UpdateDate = '';
                    $ApproveDate = '';
                    $ApproveComment = '';
                    // loop get quarter sum data

                    for ($j = 0; $j < 12; $j++) {

                        $curMonth = $monthList[$j];
                        $Current = ProductionInfoService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                        // print_r($Current);exit;
                        $SumCurrentAmount += floatval($Current['sum_amount']);
                        $SumCurrentBaht += floatval($Current['sum_baht']);

                        $Before = ProductionInfoService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                        $SumBeforeAmount += floatval($Before['sum_amount']);
                        $SumBeforeBaht += floatval($Before['sum_baht']);

                        if (!empty($Current['update_date'])) {
                            $UpdateDate = $Current['update_date'];
                        }
                        if (!empty($Current['office_approve_id'])) {
                            $ApproveDate = $Current['office_approve_date'];
                        }
                        if (!empty($Current['office_approve_comment'])) {
                            $ApproveComment = $Current['office_approve_comment'];
                        }
                    }

                    $data = [];
                    // $data['RegionName'] = $value['RegionName'];
                    $data['ProductionInfoName'] = $v['name'];
                    // $data['Quarter'] = $curQuarter;

                    $data['CurrentAmount'] = $SumCurrentAmount;
                    $data['CurrentBaht'] = $SumCurrentBaht;

                    $data['BeforeAmount'] = $SumBeforeAmount;
                    $data['BeforeBaht'] = $SumBeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;

                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }

                    $data['CreateDate'] = $Current['update_date'];
                    $data['ApproveDate'] = $Current['office_approve_date'];
                    if (!empty($Current['office_approve_id'])) {
                        if (empty($Current['office_approve_comment'])) {
                            $data['Status'] = 'อนุมัติ';
                        } else {
                            $data['Status'] = 'ไม่อนุมัติ';
                        }
                    }
                    $data['Description'] = ['months' => $curMonth
                        , 'years' => $curYear
                        , 'factory_id' => $factory_id
                    ];

                    array_push($DataList, $data);

                    $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                    $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                    $Sum_CurrentAmount += $data['CurrentAmount'];
                    $Sum_CurrentBaht += $data['CurrentBaht'];
                    $Sum_BeforeAmount += $data['BeforeAmount'];
                    $Sum_BeforeBaht += $data['BeforeBaht'];

                    $Total_CurrentAmount += $data['CurrentAmount'];
                    $Total_CurrentBaht += $data['CurrentBaht'];
                    $Total_BeforeAmount += $data['BeforeAmount'];
                    $Total_BeforeBaht += $data['BeforeBaht'];

                    $GrandTotal_CurrentAmount += $data['CurrentAmount'];
                    $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                    $GrandTotal_BeforeAmount += $data['BeforeAmount'];
                    $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                    // }

                    $data = [];
                    $data['bg_color'] = '#AFE1FA';
                    $data['ProductionInfoName'] = 'รวม';

                    $data['CurrentAmount'] = $Sum_CurrentAmount;
                    $data['CurrentBaht'] = $Sum_CurrentBaht;

                    $data['BeforeAmount'] = $Sum_BeforeAmount;
                    $data['BeforeBaht'] = $Sum_BeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }
                    array_push($DataList, $data);
                }
            }

            $curMonth++;
        }

        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#87D0F5';
        $data['ProductionInfoName'] = 'รวมทุกรายการ';
        $data['CurrentAmount'] = $GrandTotal_CurrentAmount;
        $data['CurrentBaht'] = $GrandTotal_CurrentBaht;
        $data['BeforeAmount'] = $GrandTotal_BeforeAmount;
        $data['BeforeBaht'] = $GrandTotal_BeforeBaht;

        $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
        $data['DiffAmount'] = $DiffAmount;
        if ($data['BeforeAmount'] != 0) {
            $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
        } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
            $data['DiffAmountPercentage'] = 100;
        } else {
            $data['DiffAmountPercentage'] = 0;
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        } else {
            $data['DiffBahtPercentage'] = 0;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getMainListDetail($request, $response, $args) {

        try {

            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $condition = $params['obj']['condition'];

            if ($condition['DisplayType'] == 'monthly') {
                $monthList = [$condition['months']];
            } else if ($condition['DisplayType'] == 'quarter') {

                $curQuarter = intval($condition['quarter']);
                if ($curQuarter == 1) {
                    $monthList = [10, 11, 12];
                } else if ($curQuarter == 2) {
                    $monthList = [1, 2, 3];
                } else if ($curQuarter == 3) {
                    $monthList = [4, 5, 6];
                } else if ($curQuarter == 4) {
                    $monthList = [7, 8, 9];
                }
            } else if ($condition['DisplayType'] == 'annually') {
                $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            }

            $factory_id = $condition['factory_id'];
            // $ymFrom = $condition['years'] . '-' . str_pad($condition['months'], 2, "0", STR_PAD_LEFT);
            // $ymTo = $condition['years'] . '-' . str_pad($condition['months'], 2, "0", STR_PAD_LEFT);
            // $toTime = $condition['years'] . '-' . str_pad($condition['months'], 2, "0", STR_PAD_LEFT) . '-28';
            // $fromTime = $condition['years'] . '-' . str_pad($condition['months'], 2, "0", STR_PAD_LEFT) . '-01';
            // $date1 = new \DateTime($toTime);
            // $date2 = new \DateTime($fromTime);
            // $diff = $date1->diff($date2);
            // $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
            // if ($diffMonth == 0) {
            //     $diffMonth = 1;
            // } else {
            //     $diffMonth += 1;
            // }
            $curMonth = $condition['months'];
            $curYear = $condition['years'];
            $DataList = [];
            $DataSummary = [];

            $GrandTotal_CurrentAmount = 0;
            $GrandTotal_CurrentBaht = 0;
            $GrandTotal_BeforeAmount = 0;
            $GrandTotal_BeforeBaht = 0;

            // get ProductMilkService
            $ProducMilkList = ProductMilkService::getList('', '', [], $factory_id);

            // get Factory
            $FactoryList = FactoryService::getData($factory_id);

            // for ($i = 0; $i < $diffMonth; $i++) {
            // loop by factory
            foreach ($FactoryList as $key1 => $value1) {

                $data = [];
                $data['bg_color'] = '#ccc';
                $data['ProductionInfoName'] = $value1['factory_name'];
                $data['show_button'] = 'Y';
                array_push($DataList, $data);

                $Total_CurrentAmount = 0;
                $Total_CurrentBaht = 0;
                $Total_BeforeAmount = 0;
                $Total_BeforeBaht = 0;


                foreach ($ProducMilkList as $key => $value) {

                    $data = [];
                    $data['bg_color'] = '#ccc';
                    $data['ProductionInfoName'] = $value['name'];
                    array_push($DataList, $data);

                    // get SubProductMilkService
                    $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);

                    // Prepare condition


                    $Sum_CurrentAmount = 0;
                    $Sum_CurrentBaht = 0;
                    $Sum_BeforeAmount = 0;
                    $Sum_BeforeBaht = 0;

                    // $factory_id = $value1['id'];

                    foreach ($SubProductMilkList as $k => $v) {

                        $data = [];
                        $data['bg_color'] = '#ccc';
                        $data['ProductionInfoName'] = $v['name'];
                        array_push($DataList, $data);

                        // get ProductMilkDetailService
                        $ProductMilkDetailList = ProductMilkDetailService::getListByParent($v['id']);

                        foreach ($ProductMilkDetailList as $k1 => $v1) {

                            $SumCurrentAmount = 0;
                            $SumCurrentBaht = 0;
                            $SumBeforeAmount = 0;
                            $SumBeforeBaht = 0;
                            $master_type_id = $v1['id'];

                            $monthName = ProductionInfoController::getMonthName($curMonth);

                            for ($j = 0; $j < count($monthList); $j++) {

                                $curMonth = $monthList[$j];
                                $Current = ProductionInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                                // print_r($Current);exit;
                                $SumCurrentAmount += floatval($Current['sum_amount']);
                                $SumCurrentBaht += floatval($Current['sum_baht']);

                                $Before = ProductionInfoService::getDetailList($beforeYear, $curMonth, $factory_id, $master_type_id);
                                $SumBeforeAmount += floatval($Before['sum_amount']);
                                $SumBeforeBaht += floatval($Before['sum_baht']);
                            }

                            $data = [];
                            // $data['RegionName'] = $value['RegionName'];
                            $data['ProductionInfoName'] = $v1['name'];
                            $data['Month'] = $monthName;
                            $data['Quarter'] = $curQuarter;
                            $data['Year'] = ($curYear + 543);

                            $data['CurrentAmount'] = $SumCurrentAmount;
                            $data['CurrentBaht'] = $SumCurrentBaht;

                            $data['BeforeAmount'] = $SumBeforeAmount;
                            $data['BeforeBaht'] = $SumBeforeBaht;

                            // $data = [];
                            // $data['RegionName'] = $value['RegionName'];
                            // $data['ProductionInfoName'] = $v1['name'];
                            // $data['Month'] = $monthName;
                            // get cooperative type
                            // $Current = ProductionInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                            // // print_r($Current);exit;
                            // $data['CurrentAmount'] = floatval($Current['sum_amount']);
                            // $data['CurrentBaht'] = floatval($Current['sum_baht']);
                            // $Before = ProductionInfoService::getDetailList($beforeYear, $curMonth, $factory_id, $master_type_id);
                            // $data['BeforeAmount'] = floatval($Before['sum_amount']);
                            // $data['BeforeBaht'] = floatval($Before['sum_baht']);

                            $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                            $data['DiffAmount'] = $DiffAmount;
                            if ($data['BeforeAmount'] != 0) {
                                $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                            } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                                $data['DiffAmountPercentage'] = 100;
                            } else {
                                $data['DiffAmountPercentage'] = 0;
                            }


                            $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                            $data['DiffBaht'] = $DiffBaht;

                            if ($data['BeforeBaht'] != 0) {
                                $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                            } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                                $data['DiffBahtPercentage'] = 100;
                            } else {
                                $data['DiffBahtPercentage'] = 0;
                            }

                            $data['CreateDate'] = $Current['update_date'];
                            $data['ApproveDate'] = $Current['office_approve_date'];
                            if (!empty($Current['office_approve_id'])) {
                                if (empty($Current['office_approve_comment'])) {
                                    $data['Status'] = 'อนุมัติ';
                                } else {
                                    $data['Status'] = 'ไม่อนุมัติ';
                                }
                            }
                            $data['Description'] = ['months' => $curMonth
                                , 'years' => $curYear
                                , 'factory_id' => $factory_id
                            ];

                            array_push($DataList, $data);

                            $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                            $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                            $Sum_CurrentAmount += $data['CurrentAmount'];
                            $Sum_CurrentBaht += $data['CurrentBaht'];
                            $Sum_BeforeAmount += $data['BeforeAmount'];
                            $Sum_BeforeBaht += $data['BeforeBaht'];

                            $Total_CurrentAmount += $data['CurrentAmount'];
                            $Total_CurrentBaht += $data['CurrentBaht'];
                            $Total_BeforeAmount += $data['BeforeAmount'];
                            $Total_BeforeBaht += $data['BeforeBaht'];

                            $GrandTotal_CurrentAmount += $data['CurrentAmount'];
                            $GrandTotal_CurrentBaht += $data['CurrentBaht'];
                            $GrandTotal_BeforeAmount += $data['BeforeAmount'];
                            $GrandTotal_BeforeBaht += $data['BeforeBaht'];
                        }
                    }

                    $data = [];
                    $data['bg_color'] = '#AFE1FA';
                    $data['ProductionInfoName'] = 'รวม';

                    $data['CurrentAmount'] = $Sum_CurrentAmount;
                    $data['CurrentBaht'] = $Sum_CurrentBaht;

                    $data['BeforeAmount'] = $Sum_BeforeAmount;
                    $data['BeforeBaht'] = $Sum_BeforeBaht;

                    $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                    $data['DiffAmount'] = $DiffAmount;
                    if ($data['BeforeAmount'] != 0) {
                        $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                    } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                        $data['DiffAmountPercentage'] = 100;
                    } else {
                        $data['DiffAmountPercentage'] = 0;
                    }


                    $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                    $data['DiffBaht'] = $DiffBaht;

                    if ($data['BeforeBaht'] != 0) {
                        $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                    } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                        $data['DiffBahtPercentage'] = 100;
                    } else {
                        $data['DiffBahtPercentage'] = 0;
                    }
                    array_push($DataList, $data);
                }
                $data = [];
                $data['bg_color'] = '#A0DEFD';
                $data['ProductionInfoName'] = 'รวมทั้งสิ้น';

                $data['CurrentAmount'] = $Total_CurrentAmount;
                $data['CurrentBaht'] = $Total_CurrentBaht;

                $data['BeforeAmount'] = $Total_BeforeAmount;
                $data['BeforeBaht'] = $Total_BeforeBaht;

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                $data['DiffAmount'] = $DiffAmount;
                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                } else {
                    $data['DiffAmountPercentage'] = 0;
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                } else {
                    $data['DiffBahtPercentage'] = 0;
                }
                array_push($DataList, $data);
            }

            $curMonth++;
            // }



            $this->data_result['DATA']['DataList'] = $DataList;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getData($request, $response, $args) {
        try {
            $params = $request->getParsedBody();

            $id = $params['obj']['id'];

            $factory_id = $params['obj']['factory_id'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = ProductionInfoService::getDataByID($id);
            } else {
                $_Data = ProductionInfoService::getData($factory_id, $months, $years);
            }

            $this->data_result['DATA']['Data'] = $_Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateData($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');
        $URL = '127.0.0.1';
        try {
            $params = $request->getParsedBody();
            $_Data = $params['obj']['Data'];

            $user_session = $params['user_session'];

            $OrgID = $user_session['OrgID'];

            $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['OrgID' => $OrgID, 'Type' => 'OWNER']);
            $HeaderData = json_decode(trim($HeaderData), TRUE);
            // print_r($HeaderData);exit;
            if ($HeaderData['data']['DATA']['Header']['OrgType'] == 'DEPARTMENT') {
                $_Data['dep_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['dep_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            } else if ($HeaderData['data']['DATA']['Header']['OrgType'] == 'DIVISION') {
                $_Data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['division_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            } else if ($HeaderData['data']['DATA']['Header']['OrgType'] == 'OFFICE') {
                $_Data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                $data['office_approve_name'] = $HeaderData['data']['DATA']['Header']['FirstName'] . ' ' . $HeaderData['data']['DATA']['Header']['LastName'];
            }

            $_Detail = $params['obj']['Detail'];

            // get region from cooperative id
            // $Cooperative = CooperativeService::getData($_Data['cooperative_id']);
            // $_Data['region_id'] = $Cooperative['region_id'];
            // print_r($_Data);
            // exit();

            $id = ProductionInfoService::updateData($_Data);

            foreach ($_Detail as $key => $value) {
                $value['production_info_id'] = $id;
                ProductionInfoService::updateDetailData($value);
            }

            //           
            $this->data_result['DATA']['id'] = $id;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function removeDetailData($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $id = $params['obj']['id'];
            $result = ProductionInfoService::removeDetailData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateDataApprove($request, $response, $args) {
        // $URL = '172.23.10.224';
        $URL = '127.0.0.1';
        try {
            $params = $request->getParsedBody();
            $user_session = $params['user_session'];
            $id = $params['obj']['id'];
            $ApproveStatus = $params['obj']['ApproveStatus'];
            $ApproveComment = $params['obj']['ApproveComment'];
            $OrgType = $params['obj']['OrgType'];
            $approval_id = $user_session['UserID'];
            $OrgID = $user_session['OrgID'];

            if ($ApproveStatus == 'approve') {
                // http post to dpo database to retrieve division's header
                $HeaderData = $this->do_post_request('http://' . $URL . '/dportal/dpo/public/mis/get/org/header/', "POST", ['UserID' => $approval_id, 'OrgID' => $OrgID]);
                $HeaderData = json_decode(trim($HeaderData), TRUE);

                $data = [];
                $ApproveComment = '';

                if ($OrgType == 'dep') {
                    $data['dep_approve_date'] = date('Y-m-d H:i:s');
                    $data['dep_approve_comment'] = $ApproveComment;
                    $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                    $data['division_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                } else if ($OrgType == 'division') {
                    $data['division_approve_date'] = date('Y-m-d H:i:s');
                    $data['division_approve_comment'] = $ApproveComment;
                    $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];

                    $data['office_approve_id'] = $HeaderData['data']['DATA']['Header']['UserID'];
                } else if ($OrgType == 'office') {
                    $data['office_approve_date'] = date('Y-m-d H:i:s');
                    $data['office_approve_comment'] = $ApproveComment;
                    $data['office_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                }
            } else if ($ApproveStatus == 'reject') {

                if ($OrgType == 'dep') {
                    $data['dep_approve_date'] = date('Y-m-d H:i:s');
                    $data['dep_approve_comment'] = $ApproveComment;
                    $data['dep_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                } else if ($OrgType == 'division') {
                    $data['dep_approve_date'] = NULL;
                    $data['dep_approve_comment'] = NULL;

                    $data['division_approve_id'] = NULL;
                    $data['division_approve_date'] = date('Y-m-d H:i:s');
                    $data['division_approve_comment'] = $ApproveComment;
                    $data['division_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                } else if ($OrgType == 'office') {

                    $data['dep_approve_date'] = NULL;
                    $data['dep_approve_comment'] = NULL;

                    $data['division_approve_id'] = NULL;
                    $data['division_approve_date'] = NULL;
                    $data['division_approve_comment'] = NULL;

                    $data['office_approve_id'] = NULL;
                    $data['office_approve_date'] = date('Y-m-d H:i:s');
                    $data['office_approve_comment'] = $ApproveComment;
                    $data['office_approve_name'] = $user_session['FirstName'] . ' ' . $user_session['LastName'];
                }
            }

            // print_r($data );
            // exit;
            $result = ProductionInfoService::updateDataApprove($id, $data);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function do_post_request($url, $method, $data = [], $optional_headers = null) {
        $params = array('http' => array(
                'method' => $method,
                'content' => http_build_query($data)
        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            print_r($fp);
            return array("STATUS" => 'ERROR', "MSG" => "ERROR :: Problem with $url");
            //throw new Exception("Problem with $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            print_r($response);
            return array("STATUS" => 'ERROR', "MSG" => "ERROR :: Problem reading data from $url");
            //            throw new Exception("Problem reading data from $url");
        }

        return $response;
    }

    public function getMonthreportforsubcom($condition, $fac_id) {
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $factory_id = $fac_id;
        $ymFrom = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        $DataList = [];
        $DataSummary = [];
        $beforemonth = $condition['MonthFrom'] - 1;
        $year = $condition['YearFrom'];
        if ($curMonth == 12) {
            $beforemonth = 1;
            $year--;
        }
        $GrandTotal_CurrentAmount = 0;
        $GrandTotal_CurrentBaht = 0;
        $GrandTotal_BeforeAmount = 0;
        $GrandTotal_BeforeBaht = 0;



        // get Factory
        $FactoryList = FactoryService::getData($factory_id);
        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        if ($diffMonth == 12) {
            $curYear--;
        }
        for ($i = 0; $i < $diffMonth; $i++) {
            // loop by factory

            foreach ($FactoryList as $key1 => $value1) {
                // get ProductMilkService
                // $ProducMilkList = ProductMilkService::getList('', '', '', $value1['id']);
                $ProducMilkList = ProductMilkService::getList('', '', '', '');

                $DataList['factory_name'] = $value1['factory_name'];
                $DataList['product'] = [];
                $DataList['BeforemonthAmount'] = 0;
                $DataList['target'] = 0;
                $DataList['CurrentAmount'] = 0;
                $DataList['percentTarget'] = 100;
                $DataList['BeforeyearAmount'] = 0;
                $DataList['percentDiffAmount'] = 100;
                $DataList['ProductionInfoName'] = $value['name'];
                $DataList['Yeartarget'] = 0;
                $DataList['targetoct'] = 0;
                $DataList['collectoct'] = 0;
                $DataList['percentoct'] = 100;

                $datapro = [];
                foreach ($ProducMilkList as $key => $value) {
                    $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);
                    $factory_id = $value1['id'];
                    $datapro['BeforemonthAmount'] = 0;
                    $datapro['target'] = 0;
                    $datapro['CurrentAmount'] = 0;
                    $datapro['percentTarget'] = 100;
                    $datapro['BeforeyearAmount'] = 0;
                    $datapro['percentDiffAmount'] = 100;
                    $datapro['ProductionInfoName'] = $value['name'];
                    $datapro['Yeartarget'] = 0;
                    $datapro['targetoct'] = 0;
                    $datapro['collectoct'] = 0;
                    $datapro['percentoct'] = 100;
                    $type['goal_type'] = 'II';
                    $type['keyword'] = $value['name'];
                    $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
                    $mission = GoalMissionService::getMission($mastes[0]['id'], $value1['region_id'], $condition['YearFrom']);
                    $datapro['Yeartarget'] = $mission[0]['amount'] / 1000;
                    $DataList['Yeartarget'] += $mission[0]['amount'] / 1000;
                    $avg = GoalMissionService::getMissionavg($mission[0]['id'], $curYear, $curMonth);
                    $datapro['target'] = $avg['amount'] / 1000;
                    $DataList['target'] += $avg['amount'] / 1000;
                    foreach ($SubProductMilkList as $k => $v) {
                        $ProductMilkDetailList = ProductMilkDetailService::getListByParent2($v['id'], $factory_id);
                        foreach ($ProductMilkDetailList as $k1 => $v1) {
                            $master_type_id = $v1['id'];
                            $BeforemonthAmount = ProductionInfoService::getDetailList($year, $beforemonth, $factory_id, $master_type_id);
                            $Current = ProductionInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                            $Before = ProductionInfoService::getDetailList($curYear - 1, $curMonth, $factory_id, $master_type_id);
                            $datapro['BeforemonthAmount'] += floatval($BeforemonthAmount['sum_amount'] / 1000);
                            $datapro['CurrentAmount'] += floatval($Current['sum_amount'] / 1000);
                            $datapro['BeforeyearAmount'] += floatval($Before['sum_amount'] / 1000);
                            $DataList['BeforemonthAmount'] += floatval($BeforemonthAmount['sum_amount'] / 1000);
                            $DataList['CurrentAmount'] += floatval($Current['sum_amount'] / 1000);
                            $DataList['BeforeyearAmount'] += floatval($Before['sum_amount'] / 1000);
                            foreach ($monthList as $key => $ml) {
                                $coct = ProductionInfoService::getDetailList($condition['YearTo'] - $yearlist[$key], $ml, $factory_id, $master_type_id);
                                $avgoct = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);
                                $datapro['collectoct'] += $coct['sum_amount'] / 1000;
                                $datapro['targetoct'] += $avgoct['amount'] / 1000;
                                $DataList['collectoct'] += $coct['sum_amount'] / 1000;
                                $DataList['targetoct'] += $avgoct['amount'] / 1000;
                                if ($ml == $condition['MonthFrom']) {
                                    break;
                                }
                            }
                        }
                    }
                    if ($datapro['target'] > 0) {
                        $datapro['percentTarget'] = ($datapro['CurrentAmount'] * 100) / $datapro['target'];
                    }
                    if ($datapro['BeforeyearAmount'] > 0) {
                        $datapro['percentDiffAmount'] = (($datapro['CurrentAmount'] - $datapro['BeforeyearAmount']) * 100) / $datapro['BeforeyearAmount'];
                    }
                    if ($datapro['targetoct'] > 0) {
                        $datapro['percentoct'] = ($datapro['collectoct'] * 100) / $datapro['targetoct'];
                    }
                    array_push($DataList['product'], $datapro);
                    if ($DataList['target'] > 0) {
                        $DataList['percentTarget'] = ($DataList['CurrentAmount'] * 100) / $DataList['target'];
                    }
                    if ($DataList['BeforeyearAmount'] > 0) {
                        $DataList['percentDiffAmount'] = (($DataList['CurrentAmount'] - $DataList['BeforeyearAmount']) * 100) / $datapro['BeforeyearAmount'];
                    }
                    if ($DataList['targetoct'] > 0) {
                        $DataList['percentoct'] = ($DataList['collectoct'] * 100) / $DataList['targetoct'];
                    }
                }
            }

            $curMonth++;
            if ($curMonth > 12) {
                $curMonth = 1;
                $curYear++;
            }
        }

        return ['DataList' => $DataList];
    }

    public function getQreportforsubcom($condition, $fac_id) {
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $factory_id = $fac_id;
        $ymFrom = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';


        $curMonth = $condition['MonthFrom'];
        $curYear = $condition['YearFrom'];
        $DataList = [];
        $DataSummary = [];
        $beforemonth = $condition['MonthFrom'] - 1;


        if ($condition['QuarterFrom'] == 1) {
            $curMonth = 10;
            $beforemonth = 7;
            $curYear--;
            $beforeyear = $year;
        } else if ($condition['QuarterFrom'] == 2) {
            $curMonth = 1;
            $beforemonth = 10;
            $beforeyear--;

            $beforeQuarter--;
        } else if ($condition['QuarterFrom'] == 3) {

            $curMonth = 4;
            $beforemonth = 1;
            $beforeyear--;
        } else if ($condition['QuarterFrom'] == 4) {

            $curMonth = 7;
            $beforemonth = 4;
            $beforeyear--;
        }



        // get Factory
        $FactoryList = FactoryService::getData($factory_id);
//        $curMonth = $condition['MonthFrom'];
//        $curYear = $condition['YearFrom'];

        for ($i = 0; $i < 3; $i++) {
            // loop by factory

            foreach ($FactoryList as $key1 => $value1) {
                // get ProductMilkService
                // $ProducMilkList = ProductMilkService::getList('', '', '', $value1['id']);
                $ProducMilkList = ProductMilkService::getList('', '', '', '');

                $DataList['factory_name'] = $value1['factory_name'];
                $DataList['product'] = [];
                $DataList['BeforemonthAmount'] = 0;
                $DataList['target'] = 0;
                $DataList['CurrentAmount'] = 0;
                $DataList['percentTarget'] = 100;
                $DataList['BeforeyearAmount'] = 0;
                $DataList['percentDiffAmount'] = 100;
                $DataList['ProductionInfoName'] = $value['name'];
                $DataList['Yeartarget'] = 0;
                $DataList['targetoct'] = 0;
                $DataList['collectoct'] = 0;
                $DataList['percentoct'] = 100;

                $datapro = [];
                foreach ($ProducMilkList as $key => $value) {
                    $SubProductMilkList = SubProductMilkService::getListByProductMilk($value['id']);
                    $factory_id = $value1['id'];
                    $datapro['BeforemonthAmount'] = 0;
                    $datapro['target'] = 0;
                    $datapro['CurrentAmount'] = 0;
                    $datapro['percentTarget'] = 100;
                    $datapro['BeforeyearAmount'] = 0;
                    $datapro['percentDiffAmount'] = 100;
                    $datapro['ProductionInfoName'] = $value['name'];
                    $datapro['Yeartarget'] = 0;
                    $datapro['targetoct'] = 0;
                    $datapro['collectoct'] = 0;
                    $datapro['percentoct'] = 100;
                    $type['goal_type'] = 'II';
                    $type['keyword'] = $value['name'];
                    $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
                    $mission = GoalMissionService::getMission($mastes[0]['id'], $value1['region_id'], $condition['YearFrom']);
                    $datapro['Yeartarget'] = $mission[0]['amount'] / 1000;
                    $DataList['Yeartarget'] += $mission[0]['amount'] / 1000;
                    $avg = GoalMissionService::getMissionavg($mission[0]['id'], $curYear, $curMonth);
                    $datapro['target'] = $avg['amount'] / 1000;
                    $DataList['target'] += $avg['amount'] / 1000;
                    foreach ($SubProductMilkList as $k => $v) {
                        $ProductMilkDetailList = ProductMilkDetailService::getListByParent2($v['id'], $factory_id);
                        foreach ($ProductMilkDetailList as $k1 => $v1) {
                            $master_type_id = $v1['id'];
                            $BeforemonthAmount = ProductionInfoService::getDetailList($beforeyear, $beforemonth, $factory_id, $master_type_id);
                            $Current = ProductionInfoService::getDetailList($curYear, $curMonth, $factory_id, $master_type_id);
                            $Before = ProductionInfoService::getDetailList($curYear-1, $curMonth, $factory_id, $master_type_id);
                            $datapro['BeforemonthAmount'] += floatval($BeforemonthAmount['sum_amount'] / 1000);
                            $datapro['CurrentAmount'] += floatval($Current['sum_amount'] / 1000);
                            $datapro['BeforeyearAmount'] += floatval($Before['sum_amount'] / 1000);
                            $DataList['BeforemonthAmount'] += floatval($BeforemonthAmount['sum_amount'] / 1000);
                            $DataList['CurrentAmount'] += floatval($Current['sum_amount'] / 1000);
                            $DataList['BeforeyearAmount'] += floatval($Before['sum_amount'] / 1000);
                            foreach ($monthList as $key => $ml) {
                                $coct = ProductionInfoService::getDetailList($condition['YearTo'] - $yearlist[$key], $ml, $factory_id, $master_type_id);
                                $avgoct = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);
                                $datapro['collectoct'] += $coct['sum_amount'] / 1000;
                                $datapro['targetoct'] += $avgoct['amount'] / 1000;
                                $DataList['collectoct'] += $coct['sum_amount'] / 1000;
                                $DataList['targetoct'] += $avgoct['amount'] / 1000;
                                if ($ml == $condition['MonthFrom']) {
                                    break;
                                }
                            }
                        }
                    }
                    if ($datapro['target'] > 0) {
                        $datapro['percentTarget'] = ($datapro['CurrentAmount'] * 100) / $datapro['target'];
                    }
                    if ($datapro['BeforeyearAmount'] > 0) {
                        $datapro['percentDiffAmount'] = (($datapro['CurrentAmount'] - $datapro['BeforeyearAmount']) * 100) / $datapro['BeforeyearAmount'];
                    }
                    if ($datapro['targetoct'] > 0) {
                        $datapro['percentoct'] = ($datapro['collectoct'] * 100) / $datapro['targetoct'];
                    }
                    array_push($DataList['product'], $datapro);
                    if ($DataList['target'] > 0) {
                        $DataList['percentTarget'] = ($DataList['CurrentAmount'] * 100) / $DataList['target'];
                    }
                    if ($DataList['BeforeyearAmount'] > 0) {
                        $DataList['percentDiffAmount'] = (($DataList['CurrentAmount'] - $DataList['BeforeyearAmount']) * 100) / $datapro['BeforeyearAmount'];
                    }
                    if ($DataList['targetoct'] > 0) {
                        $DataList['percentoct'] = ($DataList['collectoct'] * 100) / $DataList['targetoct'];
                    }
                }
            }
            $beforemonth++;
            $curMonth++;
            if ($curMonth > 12) {
                $curMonth = 1;
                $curYear++;
            }
        }

        return ['DataList' => $DataList];
    }

}
