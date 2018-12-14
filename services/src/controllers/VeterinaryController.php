<?php

namespace App\Controller;

use App\Service\VeterinaryService;
use App\Service\CooperativeService;
use App\Service\DairyFarmingService;

class VeterinaryController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public static function getLastDayOfMonth($time) {
        return $date = date("t", strtotime($time . '-' . '01'));

        // return date("t", $last_day_timestamp);
    }

    public static function getMonthName($month) {
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
            case 11 : $monthTxt = 'พฤษจิกายน';
                break;
            case 12 : $monthTxt = 'ธันวาคม';
                break;
        }
        return $monthTxt;
    }

    public function getMainList($request, $response, $args) {
        try {
            error_reporting(E_ERROR);
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $condition = $params['obj']['condition'];
            $regions = $params['obj']['region'];

            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDataList($condition, $regions);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDataList($condition, $regions);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDataList($condition, $regions);
            }
            $DataList = $Result['DataList'];
            $Summary = $Result['Summary'];
            // print_r($DataList);
            // exit;

            $this->data_result['DATA']['DataList'] = $DataList;
            $this->data_result['DATA']['Summary'] = $Summary;
//            print_r($this->data_result);
//            die();
            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getMonthDataList($condition, $regions) {
        $ymFrom = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';// . VeterinaryController::getLastDayOfMonth($ymTo);
        //exit;
        $fromTime = $condition['YearTo'] . '-' . str_pad($condition['MonthFrom'], 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);

        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);

        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        // exit;
        // if($ymFrom != $ymTo){
        //     $diffMonth += 1;
        // }
        $curMonth = $condition['MonthFrom'];
        $DataList = [];
        $DataSummary = [];
        $DataSummary['SummaryCurrentCow'] = 0;
        $DataSummary['SummaryBeforeCow'] = 0;
        $DataSummary['SummaryCowPercentage'] = 0;
        $DataSummary['SummaryCurrentService'] = 0;
        $DataSummary['SummaryBeforeService'] = 0;
        $DataSummary['SummaryServicePercentage'] = 0;
        if ($diffMonth == 0) {
            $diffMonth = 1;
        }else{
            $diffMonth += 1;
        }
        for ($i = 0; $i < $diffMonth; $i++) {

            // Prepare condition
            $curYear = $condition['YearTo'];
            $beforeYear = $condition['YearTo'] - 1;
            // Loop User Regions
            foreach ($regions as $key => $value) {

                $region_id = $value['RegionID'];
                $monthName = VeterinaryController::getMonthName($curMonth);

                $data = [];
                $data['RegionName'] = $value['RegionName'] . ' (สหกรณ์)';
                $data['Month'] = $monthName;
                $data['Quarter'] = ($i + 1);
                $data['Year'] = ($curYear);
                // get cooperative type
                $farm_type = 'Cooperative';
                $item_type = 'โคนม';
                $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                $data['CurrentCowData'] = floatval($CurrentCowData['sum_amount']);
                $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                $data['BeforeCowData'] = floatval($BeforeCowData['sum_amount']);

                $item_type = 'ค่าบริการ';
                $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                $data['CurrentServiceData'] = floatval($CurrentServiceData['sum_amount']);
                $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                $data['BeforeServiceData'] = floatval($BeforeServiceData['sum_amount']);

                $diffCowData = $data['CurrentCowData'] - $data['BeforeCowData'];
                $data['DiffCowData'] = $diffCowData;

                if($data['BeforeCowData'] != 0){
                    $data['DiffCowDataPercentage'] = ($data['CurrentCowData'] / $data['BeforeCowData']) * 100;
                }else{
                    $data['DiffCowDataPercentage'] = 0;
                }
                

                $diffServiceData = $data['CurrentServiceData'] - $data['BeforeServiceData'];
                $data['DiffServiceData'] = $diffServiceData;
                

                if($data['BeforeServiceData'] != 0){
                    $data['DiffServiceDataPercentage'] = ($data['CurrentServiceData'] / $data['BeforeServiceData']) * 100;
                }else{
                    $data['DiffServiceDataPercentage'] = 0;
                }
                
                $data['CreateDate'] = $CurrentCowData['update_date'];
                $data['ApproveDate'] = '';
                $data['Status'] = '';
                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'months' => $curMonth
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = 0;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = 0;

                array_push($DataList, $data);

                #### End of cooperative 
                // get lab type
                $data = [];
                $data['RegionName'] = $value['RegionName'] . ' (ห้องปฏิบัติการ)';
                $data['Month'] = $monthName;
                $data['Quarter'] = ($i + 1);
                $data['Year'] = ($curYear);
                $farm_type = 'lab';
                $item_type = 'โคนม';
                $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                $data['CurrentCowData'] = floatval($CurrentCowData['sum_amount']);
                $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                $data['BeforeCowData'] = floatval($BeforeCowData['sum_amount']);

                $item_type = 'ค่าบริการ';
                $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                $data['CurrentServiceData'] = floatval($CurrentServiceData['sum_amount']);
                $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                $data['BeforeServiceData'] = floatval($BeforeServiceData['sum_amount']);

                $diffCowData = floatval($data['CurrentCowData']) - floatval($data['BeforeCowData']);
                $data['DiffCowData'] = $diffCowData;

                if(floatval($data['BeforeCowData']) != 0){
                    $data['DiffCowDataPercentage'] = floatval($data['CurrentCowData']) / floatval($data['BeforeCowData'] * 100);
                }else{
                    $data['DiffCowDataPercentage'] = 0;
                }
                // if (is_nan($data['DiffCowDataPercentage'])) {
                //     $data['DiffCowDataPercentage'] = 0;
                // }
                $diffServiceData = $data['CurrentServiceData'] - $data['BeforeServiceData'];
                $data['DiffServiceData'] = $diffServiceData;

                if($data['BeforeServiceData'] != 0){
                    $data['DiffServiceDataPercentage'] = floatval($data['CurrentServiceData']) / floatval($data['BeforeServiceData'] * 100);
                }else{
                    $data['DiffServiceDataPercentage'] = 0;
                }
                // if (is_nan($data['DiffServiceDataPercentage'])) {
                //     $data['DiffServiceDataPercentage'] = 0;
                // }
                $data['CreateDate'] = $CurrentCowData['update_date'];
                $data['ApproveDate'] = '';
                $data['Status'] = '';
                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'months' => $curMonth
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];
                array_push($DataList, $data);

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = 0;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = 0;
            }
            $curMonth++;
        }
      
        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    private function getQuarterDataList($condition, $regions) {

        // get loop to query
        $diffYear = ($condition['YearTo'] - $condition['YearFrom']) + 1;
        $cnt = 0;
        $loop = 0;
        $j = $condition['QuarterFrom'];

        for ($i = 0; $i < $diffYear; $i++) {
            if ($cnt == $diffYear) {
                for ($k = 0; $k < $condition['QuarterTo']; $k++) {
                    $loop++;
                }
            } else {

                if ($i > 0) {
                    $j = 0;
                }

                if ($diffYear == 1) {
                    $length = $condition['QuarterTo'];
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
            $curYear = intval($condition['YearFrom']) - 1;
            $beforeYear = $curYear - 1;
        } else {
            $curYear = intval($condition['YearFrom']);
            $beforeYear = $curYear - 1;
        }

        $DataList = [];
        $DataSummary = [];

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

            // Loop User Regions
            foreach ($regions as $key => $value) {

                $region_id = $value['RegionID'];
                $Co_SumCurrentCowData = 0;
                $Co_SumBeforeCowData = 0;
                $Co_SumCurrentServiceData = 0;
                $Co_SumBeforeServiceData = 0;

                $Lab_SumCurrentCowData = 0;
                $Lab_SumBeforeCowData = 0;
                $Lab_SumCurrentServiceData = 0;
                $Lab_SumBeforeServiceData = 0;
                // loop get quarter sum data
                for ($j = 0; $j < count($monthList); $j++) {
                    $curMonth = $monthList[$j];
                    $farm_type = 'Cooperative';
                    $item_type = 'โคนม';
                    $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumCurrentCowData += floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumBeforeCowData += floatval($BeforeCowData['sum_amount']);

                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumCurrentServiceData += floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumBeforeServiceData += floatval($BeforeServiceData['sum_amount']);

                    $farm_type = 'lab';
                    $item_type = 'โคนม';
                    $CurrentCowData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumCurrentCowData += floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumBeforeCowData += floatval($BeforeCowData['sum_amount']);

                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($curYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumCurrentServiceData += floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumBeforeServiceData += floatval($BeforeServiceData['sum_amount']);
                }

                // Cooperative
                $data = [];
                $data['RegionName'] = $value['RegionName'] . ' (สหกรณ์)';
                $data['Quarter'] = ($curQuarter) . ' (' . ($curQuarter == 1 ? $curYear + 543 + 1 : $curYear + 543) . ')';
                $data['CurrentCowData'] = $Co_SumCurrentCowData;
                $data['BeforeCowData'] = $Co_SumBeforeCowData;
                $data['CurrentServiceData'] = $Co_SumCurrentServiceData;
                $data['BeforeServiceData'] = $Co_SumBeforeServiceData;
                $data['DiffCowData'] = $Co_SumCurrentCowData - $Co_SumBeforeCowData;
                $data['DiffCowDataPercentage'] = 0;
                $data['DiffServiceData'] = $Co_SumCurrentServiceData - $Co_SumBeforeServiceData;
                $data['DiffServiceDataPercentage'] = 0;
                $data['CreateDate'] = $CurrentCowData['update_date'];
                $data['ApproveDate'] = '';
                $data['Status'] = '';
                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'quarter' => $curQuarter
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = 0;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = 0;

                array_push($DataList, $data);

                // Lab
                $data['RegionName'] = $value['RegionName'] . ' (ห้องปฏิบัติการ)';
                $data['Quarter'] = ($curQuarter) . ' (' . (($curQuarter == 1 ? $curYear + 543 + 1 : $curYear + 543)) . ')';
                $data['CurrentCowData'] = $Lab_SumCurrentCowData;
                $data['BeforeCowData'] = $Lab_SumBeforeCowData;
                $data['CurrentServiceData'] = $Lab_SumCurrentServiceData;
                $data['BeforeServiceData'] = $Lab_SumBeforeServiceData;
                $data['DiffCowData'] = $Lab_SumCurrentCowData - $Lab_SumBeforeCowData;
                $data['DiffCowDataPercentage'] = 0;
                $data['DiffServiceData'] = $Lab_SumCurrentServiceData - $Lab_SumBeforeServiceData;
                $data['DiffServiceDataPercentage'] = 0;
                $data['CreateDate'] = $CurrentCowData['update_date'];
                $data['ApproveDate'] = '';
                $data['Status'] = '';
                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'quarter' => $curQuarter
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];
                array_push($DataList, $data);

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = 0;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = 0;
            }

            $curQuarter++;
            if ($curQuarter > 4) {
                $curQuarter = 1;
            }
        }

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    private function getAnnuallyDataList($condition, $regions) {

        $loop = intval($condition['YearTo']) - intval($condition['YearFrom']) + 1;
        $curYear = $condition['YearFrom'];

        $beforeYear = $calcYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        $DataList = [];
        $DataSummary = [];

        for ($i = 0; $i < $loop; $i++) {
            foreach ($regions as $key => $value) {

                $curYear = $condition['YearFrom'];
                $calcYear = intval($curYear) - 1;

                $region_id = $value['RegionID'];
                $Co_SumCurrentCowData = 0;
                $Co_SumBeforeCowData = 0;
                $Co_SumCurrentServiceData = 0;
                $Co_SumBeforeServiceData = 0;
                $Co_UpdateDate = '';

                $Lab_SumCurrentCowData = 0;
                $Lab_SumBeforeCowData = 0;
                $Lab_SumCurrentServiceData = 0;
                $Lab_SumBeforeServiceData = 0;
                $Lab_UpdateDate = '';

                for ($j = 0; $j < 12; $j++) {

                    $curMonth = $monthList[$j];

                    if (intval($curMonth) == 1) {
                        $calcYear++;
                        $beforeYear = $calcYear - 1;
                    }

                    $farm_type = 'Cooperative';
                    $item_type = 'โคนม';
                    // echo "$calcYear, $curMonth, $region_id, $farm_type, $item_type\n";
                    $CurrentCowData = VeterinaryService::getMainList($calcYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumCurrentCowData += floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumBeforeCowData += floatval($BeforeCowData['sum_amount']);

                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($calcYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumCurrentServiceData += floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Co_SumBeforeServiceData += floatval($BeforeServiceData['sum_amount']);

                    if (!empty($CurrentCowData['update_date'])) {
                        $Co_UpdateDate = $CurrentCowData['update_date'];
                    }

                    $farm_type = 'lab';
                    $item_type = 'โคนม';
                    echo "$calcYear, $curMonth, $region_id\n";
                    $CurrentCowData = VeterinaryService::getMainList($calcYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumCurrentCowData += floatval($CurrentCowData['sum_amount']);
                    $BeforeCowData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumBeforeCowData += floatval($BeforeCowData['sum_amount']);

                    $item_type = 'ค่าบริการ';
                    $CurrentServiceData = VeterinaryService::getMainList($calcYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumCurrentServiceData += floatval($CurrentServiceData['sum_amount']);
                    $BeforeServiceData = VeterinaryService::getMainList($beforeYear, $curMonth, $region_id, $farm_type, $item_type);
                    $Lab_SumBeforeServiceData += floatval($BeforeServiceData['sum_amount']);

                    if (!empty($CurrentCowData['update_date'])) {
                        $Lab_UpdateDate = $CurrentCowData['update_date'];
                    }
                }

                // Cooperative
                $data = [];
                $data['RegionName'] = $value['RegionName'] . ' (สหกรณ์)';
                $data['Year'] = $curYear + 543;
                $data['CurrentCowData'] = $Co_SumCurrentCowData;
                $data['BeforeCowData'] = $Co_SumBeforeCowData;
                $data['CurrentServiceData'] = $Co_SumCurrentServiceData;
                $data['BeforeServiceData'] = $Co_SumBeforeServiceData;
                $data['DiffCowData'] = $Co_SumCurrentCowData - $Co_SumBeforeCowData;
                $data['DiffCowDataPercentage'] = 0;
                $data['DiffServiceData'] = $Co_SumCurrentServiceData - $Co_SumBeforeServiceData;
                $data['DiffServiceDataPercentage'] = 0;
                $data['CreateDate'] = $Co_UpdateDate;
                $data['ApproveDate'] = '';
                $data['Status'] = '';
                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = 0;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = 0;

                array_push($DataList, $data);

                // Lab
                $data['RegionName'] = $value['RegionName'] . ' (ห้องปฏิบัติการ)';
                $data['Year'] = $curYear + 543;
                $data['CurrentCowData'] = $Lab_SumCurrentCowData;
                $data['BeforeCowData'] = $Lab_SumBeforeCowData;
                $data['CurrentServiceData'] = $Lab_SumCurrentServiceData;
                $data['BeforeServiceData'] = $Lab_SumBeforeServiceData;
                $data['DiffCowData'] = $Lab_SumCurrentCowData - $Lab_SumBeforeCowData;
                $data['DiffCowDataPercentage'] = 0;
                $data['DiffServiceData'] = $Lab_SumCurrentServiceData - $Lab_SumBeforeServiceData;
                $data['DiffServiceDataPercentage'] = 0;
                $data['CreateDate'] = $Lab_UpdateDate;
                $data['ApproveDate'] = '';
                $data['Status'] = '';
                $data['Description'] = ['farm_type' => $farm_type
                    , 'item_type' => $item_type
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];
                array_push($DataList, $data);

                $DataSummary['SummaryCurrentCow'] = $DataSummary['SummaryCurrentCow'] + $data['CurrentCowData'];
                $DataSummary['SummaryBeforeCow'] = $DataSummary['SummaryBeforeCow'] + $data['BeforeCowData'];
                $DataSummary['SummaryCowPercentage'] = 0;
                $DataSummary['SummaryCurrentService'] = $DataSummary['SummaryCurrentService'] + $data['CurrentServiceData'];
                $DataSummary['SummaryBeforeService'] = $DataSummary['SummaryBeforeService'] + $data['BeforeServiceData'];
                $DataSummary['SummaryServicePercentage'] = 0;
            }

            $curYear++;
        }
        // exit;
        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getDetailList($request, $response, $args) {
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();

            $description = $params['obj']['description'];
            $condition = $params['obj']['condition'];
            $regions = $params['obj']['region'];

            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDetailList($condition, $regions, $description);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDetailList($condition, $regions, $description);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDetailList($condition, $regions, $description);
            }



            $this->data_result['DATA']['DetailList'] = $Result['DetailList'];
            $this->data_result['DATA']['CooperativeList'] = $Result['CooperativeList'];

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function getMonthDetailList($condition, $regions, $description) {
        $years = $description['years'];
        $months = $description['months'];
        $region_id = $description['region_id'];
        $costList = [
            ['item' => 'สมาขิก', 'unit' => 'ราย']
            , ['item' => 'โคนม', 'unit' => 'ตัว']
            , ['item' => 'ค่าวัสดุ', 'unit' => 'บาท']
            , ['item' => 'ค่าบริการ', 'unit' => 'บาท']
        ];

        // get cooperative list
        $CooperativeList = CooperativeService::getListByRegion($region_id);
        // get dairy farming
        $DairyFarming = DairyFarmingService::getList('Y');

        $DetailList = [];
        foreach ($DairyFarming as $key => $value) {
            // get child
            $dairy_farming_id = $value['id'];
            $SubDairyFarming = DairyFarmingService::getChildList($dairy_farming_id, 'Y');
            if (!empty($SubDairyFarming)) {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                array_push($DetailList, $data);
                foreach ($SubDairyFarming as $_key => $_value) {

                    $data = [];
                    $data['DairyFarmingName'] = $_value['child_name'];
                    $data['BGColor'] = '#BBECA9';
                    $data['Data'] = [];
                    // array_push($DetailList, $data);

                    $sub_dairy_farming_id = $_value['id'];
                    foreach ($costList as $cost_key => $cost_value) {

                        $item_type = $cost_value['item'];
                        $unit = $cost_value['unit'];

                        // prepare data
                        $sub_data = [];
                        $sub_data['ItemName'] = $item_type;
                        $sub_data['Unit'] = $unit;
                        $sub_data['Dataset'] = [];
                        $SumAmount = 0;
                        $amount_data = [];
                        foreach ($CooperativeList as $co_key => $co_value) {
                            $cooperative_id = $co_value['id'];
                            $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);
                            $amount_data[]['Amount'] = floatval($AmountList['sum_amount']);
                            $SumAmount += floatval($AmountList['sum_amount']);
                        }
                        $sub_data['Dataset'] = $amount_data;
                        $sub_data['Summary'] = $SumAmount;
                        array_push($data['Data'], $sub_data);
                    }


                    array_push($DetailList, $data);
                }
            } else {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                $data['Data'] = [];
                foreach ($costList as $cost_key => $cost_value) {

                    $item_type = $cost_value['item'];
                    $unit = $cost_value['unit'];

                    // prepare data
                    $sub_data = [];
                    $sub_data['ItemName'] = $item_type;
                    $sub_data['Unit'] = $unit;
                    $sub_data['Dataset'] = [];
                    $SumAmount = 0;
                    $amount_data = [];
                    $cnt = 0;
                    foreach ($CooperativeList as $co_key => $co_value) {
                        $cooperative_id = $co_value['id'];
                        $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id);
                        $amount_data[$cnt]['Amount'] = floatval($AmountList['sum_amount']);
                        $SumAmount += floatval($AmountList['sum_amount']);
                        $cnt++;
                    }
                    $sub_data['Dataset'] = $amount_data;
                    $sub_data['Summary'] = $SumAmount;
                    array_push($data['Data'], $sub_data);
                }

                array_push($DetailList, $data);
            }
        }

        return ['DetailList' => $DetailList, 'CooperativeList' => $CooperativeList];
    }

    private function getQuarterDetailList($condition, $regions, $description) {
        $years = $description['years'];
        $quarter = $description['quarter'];
        $region_id = $description['region_id'];
        $costList = [
            ['item' => 'สมาขิก', 'unit' => 'ราย']
            , ['item' => 'โคนม', 'unit' => 'ตัว']
            , ['item' => 'ค่าวัสดุ', 'unit' => 'บาท']
            , ['item' => 'ค่าบริการ', 'unit' => 'บาท']
        ];
        if ($quarter == 1) {
            $monthList = [10, 11, 12];
        } else if ($quarter == 2) {
            $monthList = [1, 2, 3];
        } else if ($quarter == 3) {
            $monthList = [4, 5, 6];
        } else if ($quarter == 4) {
            $monthList = [7, 8, 9];
        }

        // get cooperative list
        $CooperativeList = CooperativeService::getListByRegion($region_id);
        // get dairy farming
        $DairyFarming = DairyFarmingService::getList('Y');

        $DetailList = [];
        foreach ($DairyFarming as $key => $value) {
            // get child
            $dairy_farming_id = $value['id'];
            $SubDairyFarming = DairyFarmingService::getChildList($dairy_farming_id, 'Y');
            if (!empty($SubDairyFarming)) {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                array_push($DetailList, $data);
                foreach ($SubDairyFarming as $_key => $_value) {

                    $data = [];
                    $data['DairyFarmingName'] = $_value['child_name'];
                    $data['BGColor'] = '#BBECA9';
                    $data['Data'] = [];
                    // array_push($DetailList, $data);

                    $sub_dairy_farming_id = $_value['id'];
                    foreach ($costList as $cost_key => $cost_value) {

                        $item_type = $cost_value['item'];
                        $unit = $cost_value['unit'];

                        // prepare data
                        $sub_data = [];
                        $sub_data['ItemName'] = $item_type;
                        $sub_data['Unit'] = $unit;
                        $sub_data['Dataset'] = [];
                        $SumAmount = 0;
                        $amount_data = [];
                        foreach ($CooperativeList as $co_key => $co_value) {
                            $cooperative_id = $co_value['id'];
                            $totalAmount = 0;
                            for ($i = 0; $i < count($monthList); $i++) {
                                $months = $monthList[$i];
                                $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);
                                $totalAmount += floatval($AmountList['sum_amount']);
                            }
                            $amount_data[]['Amount'] = $totalAmount;
                            $SumAmount += $totalAmount;
                        }
                        $sub_data['Dataset'] = $amount_data;
                        $sub_data['Summary'] = $SumAmount;
                        array_push($data['Data'], $sub_data);
                    }


                    array_push($DetailList, $data);
                }
            } else {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                $data['Data'] = [];
                foreach ($costList as $cost_key => $cost_value) {

                    $item_type = $cost_value['item'];
                    $unit = $cost_value['unit'];

                    // prepare data
                    $sub_data = [];
                    $sub_data['ItemName'] = $item_type;
                    $sub_data['Unit'] = $unit;
                    $sub_data['Dataset'] = [];
                    $SumAmount = 0;
                    $amount_data = [];
                    foreach ($CooperativeList as $co_key => $co_value) {
                        $cooperative_id = $co_value['id'];
                        $totalAmount = 0;
                        for ($i = 0; $i < count($monthList); $i++) {
                            $months = $monthList[$i];
                            $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id);
                            $totalAmount += floatval($AmountList['sum_amount']);
                        }
                        $amount_data[]['Amount'] = $totalAmount;
                        $SumAmount += $totalAmount;
                    }
                    $sub_data['Dataset'] = $amount_data;
                    $sub_data['Summary'] = $SumAmount;
                    array_push($data['Data'], $sub_data);
                }

                array_push($DetailList, $data);
            }
        }

        return ['DetailList' => $DetailList, 'CooperativeList' => $CooperativeList];
    }

    private function getAnnuallyDetailList($condition, $regions, $description) {
        $years = $description['years'];
        $quarter = $description['quarter'];
        $region_id = $description['region_id'];
        $costList = [
            ['item' => 'สมาชิก', 'unit' => 'ราย']
            , ['item' => 'โคนม', 'unit' => 'ตัว']
            , ['item' => 'ค่าวัสดุ', 'unit' => 'บาท']
            , ['item' => 'ค่าบริการ', 'unit' => 'บาท']
        ];

        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        // get cooperative list
        $CooperativeList = CooperativeService::getListByRegion($region_id);
        // get dairy farming
        $DairyFarming = DairyFarmingService::getList('Y');

        $DetailList = [];
        foreach ($DairyFarming as $key => $value) {
            // get child
            $dairy_farming_id = $value['id'];
            $SubDairyFarming = DairyFarmingService::getChildList($dairy_farming_id, 'Y');
            if (!empty($SubDairyFarming)) {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                array_push($DetailList, $data);
                foreach ($SubDairyFarming as $_key => $_value) {

                    $data = [];
                    $data['DairyFarmingName'] = $_value['child_name'];
                    $data['BGColor'] = '#BBECA9';
                    $data['Data'] = [];
                    // array_push($DetailList, $data);

                    $sub_dairy_farming_id = $_value['id'];
                    foreach ($costList as $cost_key => $cost_value) {

                        $item_type = $cost_value['item'];
                        $unit = $cost_value['unit'];

                        // prepare data
                        $sub_data = [];
                        $sub_data['ItemName'] = $item_type;
                        $sub_data['Unit'] = $unit;
                        $sub_data['Dataset'] = [];
                        $SumAmount = 0;
                        $amount_data = [];
                        foreach ($CooperativeList as $co_key => $co_value) {
                            $cooperative_id = $co_value['id'];
                            $totalAmount = 0;
                            for ($i = 0; $i < count($monthList); $i++) {
                                $months = $monthList[$i];
                                $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);
                                $totalAmount += floatval($AmountList['sum_amount']);
                            }
                            $amount_data[]['Amount'] = $totalAmount;
                            $SumAmount += $totalAmount;
                        }
                        $sub_data['Dataset'] = $amount_data;
                        $sub_data['Summary'] = $SumAmount;
                        array_push($data['Data'], $sub_data);
                    }

                    array_push($DetailList, $data);
                }
            } else {
                $data = [];
                $data['DairyFarmingName'] = $value['dairy_farming_name'];
                $data['BGColor'] = '#B6CCFF';
                $data['Data'] = [];
                foreach ($costList as $cost_key => $cost_value) {

                    $item_type = $cost_value['item'];
                    $unit = $cost_value['unit'];

                    // prepare data
                    $sub_data = [];
                    $sub_data['ItemName'] = $item_type;
                    $sub_data['Unit'] = $unit;
                    $sub_data['Dataset'] = [];
                    $SumAmount = 0;
                    $amount_data = [];
                    foreach ($CooperativeList as $co_key => $co_value) {
                        $cooperative_id = $co_value['id'];
                        $totalAmount = 0;
                        for ($i = 0; $i < count($monthList); $i++) {
                            $months = $monthList[$i];
                            $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id);
                            $totalAmount += floatval($AmountList['sum_amount']);
                        }
                        $amount_data[]['Amount'] = $totalAmount;
                        $SumAmount += $totalAmount;
                    }
                    $sub_data['Dataset'] = $amount_data;
                    $sub_data['Summary'] = $SumAmount;
                    array_push($data['Data'], $sub_data);
                }

                array_push($DetailList, $data);
            }
        }

        return ['DetailList' => $DetailList, 'CooperativeList' => $CooperativeList];
    }

    public function getSubDetailList($request, $response, $args) {
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();

            $description = $params['obj']['description'];
            $condition = $params['obj']['condition'];
            $regions = $params['obj']['region'];
            $cooperative_id = $params['obj']['cooperative_id'];

            $years = $description['years'];
            $quarter = $description['quarter'];
            $region_id = $description['region_id'];

            $costList = [
                ['item' => 'สมาขิก', 'unit' => 'ราย']
                , ['item' => 'โคนม', 'unit' => 'ตัว']
                , ['item' => 'ค่าวัสดุ', 'unit' => 'บาท']
                , ['item' => 'ค่าบริการ', 'unit' => 'บาท']
            ];

            if ($condition['DisplayType'] == 'annually') {
                $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            } else {
                $quarter = $description['quarter'];
                if ($quarter == 1) {
                    $monthList = [10, 11, 12];
                } else if ($quarter == 2) {
                    $monthList = [1, 2, 3];
                } else if ($quarter == 3) {
                    $monthList = [4, 5, 6];
                } else if ($quarter == 4) {
                    $monthList = [7, 8, 9];
                }
            }

            // get cooperative by id
            $Cooperative = CooperativeService::getData($cooperative_id);

            // get dairy farming
            $DairyFarming = DairyFarmingService::getList('Y');

            $DetailList = [];
            foreach ($DairyFarming as $key => $value) {
                // get child
                $dairy_farming_id = $value['id'];
                $SubDairyFarming = DairyFarmingService::getChildList($dairy_farming_id, 'Y');
                if (!empty($SubDairyFarming)) {
                    $data = [];
                    $data['DairyFarmingName'] = $value['dairy_farming_name'];
                    $data['BGColor'] = '#B6CCFF';
                    array_push($DetailList, $data);
                    foreach ($SubDairyFarming as $_key => $_value) {

                        $data = [];
                        $data['DairyFarmingName'] = $_value['child_name'];
                        $data['BGColor'] = '#BBECA9';
                        $data['Data'] = [];
                        // array_push($DetailList, $data);

                        $sub_dairy_farming_id = $_value['id'];
                        foreach ($costList as $cost_key => $cost_value) {

                            $item_type = $cost_value['item'];
                            $unit = $cost_value['unit'];

                            // prepare data
                            $sub_data = [];
                            $sub_data['ItemName'] = $item_type;
                            $sub_data['Unit'] = $unit;
                            $sub_data['Dataset'] = [];
                            $SumAmount = 0;
                            $amount_data = [];
                            for ($i = 0; $i < count($monthList); $i++) {
                                $months = $monthList[$i];
                                $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);

                                $amount_data[]['Amount'] = floatval($AmountList['sum_amount']);
                                $SumAmount += floatval($AmountList['sum_amount']);
                            }

                            $sub_data['Dataset'] = $amount_data;
                            $sub_data['Summary'] = $SumAmount;
                            array_push($data['Data'], $sub_data);
                        }

                        array_push($DetailList, $data);
                    }
                } else {
                    $data = [];
                    $data['DairyFarmingName'] = $value['dairy_farming_name'];
                    $data['BGColor'] = '#B6CCFF';
                    $data['Data'] = [];
                    foreach ($costList as $cost_key => $cost_value) {

                        $item_type = $cost_value['item'];
                        $unit = $cost_value['unit'];

                        // prepare data
                        $sub_data = [];
                        $sub_data['ItemName'] = $item_type;
                        $sub_data['Unit'] = $unit;
                        $sub_data['Dataset'] = [];
                        $amount_data = [];
                        $SumAmount = 0;
                        for ($i = 0; $i < count($monthList); $i++) {
                            $months = $monthList[$i];
                            $AmountList = VeterinaryService::getDetailList($years, $months, $region_id, $cooperative_id, $item_type, $dairy_farming_id, $sub_dairy_farming_id);

                            $amount_data[]['Amount'] = floatval($AmountList['sum_amount']);
                            $SumAmount += floatval($AmountList['sum_amount']);
                        }
                        $sub_data['Dataset'] = $amount_data;
                        $sub_data['Summary'] = $SumAmount;
                        array_push($data['Data'], $sub_data);
                    }

                    array_push($DetailList, $data);
                }
            }

            // gen month name
            $MonthNameList = [];
            for ($i = 0; $i < count($monthList); $i++) {
                $MonthNameList[]['month'] = $this->getMonthName($monthList[$i]);
            }

            $this->data_result['DATA']['SubDetailList'] = $DetailList;
            $this->data_result['DATA']['Cooperative'] = $Cooperative;
            $this->data_result['DATA']['MonthNameList'] = $MonthNameList;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function getList($request, $response, $args) {
        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $actives = $params['obj']['actives'];

            // group by region first
            $Regions = VeterinaryService::getRegionList();
            // Loop get by region
            $DataList = [];
            foreach ($Regions as $key => $value) {
                $_List = VeterinaryService::getList($value['region_id'], $actives);
                $value['Data'] = $_List;
                array_push($DataList, $value);
                // $Regions['Data'][] = $_List;
            }


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

            $cooperative_id = $params['obj']['cooperative_id'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = VeterinaryService::getDataByID($id);
            } else {
                $_Data = VeterinaryService::getData($cooperative_id, $months, $years);
            }

            $this->data_result['DATA']['Data'] = $_Data;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function updateData($request, $response, $args) {

        try {
            // error_reporting(E_ERROR);
            // error_reporting(E_ALL);
            // ini_set('display_errors','On');
            $params = $request->getParsedBody();
            $_Veterinary = $params['obj']['Veterinary'];
            $_VeterinaryDetailList = $params['obj']['VeterinaryDetailList'];
            unset($_Veterinary['veterinary_detail']);

            // get region from cooperative id
            $Cooperative = CooperativeService::getData($_Veterinary['cooperative_id']);
            $_Veterinary['region_id'] = $Cooperative['region_id'];

            // print_r($Veterinary);exit;
            $id = VeterinaryService::updateData($_Veterinary);

            // update veterinary detail & item
            foreach ($_VeterinaryDetailList as $key => $value) {
                $_VeterinaryItemList = $value['veterinary_item'];

                $DairyFarming = DairyFarmingService::getData($value['dairy_farming_id']);
                $value['farm_type'] = $DairyFarming['dairy_farming_type'];
                $value['veterinary_id'] = $id;
                unset($value['veterinary_item']);
                $veterinary_detail_id = VeterinaryService::updateDetailData($value);

                // update item
                foreach ($_VeterinaryItemList as $_key => $_value) {
                    $_value['veterinary_id'] = $id;
                    $_value['veterinary_detail_id'] = $veterinary_detail_id;
                    $veterinary_item_id = VeterinaryService::updateItemData($_value);
                }
            }

            $this->data_result['DATA']['id'] = $id;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function removeData($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $id = $params['obj']['id'];
            $result = VeterinaryService::removeData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function removeDetailData($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $id = $params['obj']['id'];
            $result = VeterinaryService::removeDetailData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function removeItemData($request, $response, $args) {
        try {

            $params = $request->getParsedBody();
            $id = $params['obj']['id'];
            $result = VeterinaryService::removeItemData($id);

            $this->data_result['DATA']['result'] = $result;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

}
