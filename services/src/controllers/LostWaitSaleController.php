<?php

namespace App\Controller;

use App\Service\LostWaitSaleService;
use App\Service\MasterGoalService;
use App\Service\MasterLossService;
use App\Service\GoalMissionService;
use App\Service\ProductMilkService;
use App\Service\SubProductMilkService;
use App\Service\ProductMilkDetailService;
use App\Service\UploadLogService;

use PHPExcel;

class LostWaitSaleController extends Controller {

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

            $Data = LostWaitSaleService::loadDataApprove($user_session['UserID']);

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

            // print_r($RegionList);
            // exit;
            if ($condition['DisplayType'] == 'monthly') {
                $Result = $this->getMonthDataList($condition);
            } else if ($condition['DisplayType'] == 'quarter') {
                $Result = $this->getQuarterDataList($condition);
            } else if ($condition['DisplayType'] == 'annually') {
                $Result = $this->getAnnuallyDataList($condition);
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

    public function getMonthDataList($condition) {

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

        // get master goal
        $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียรอจำหน่าย', [], '', $factory_id);
        // echo count($MasterGoalList);
        // exit;
        $Sum_CurrentAmount = 0;
        $Sum_CurrentBaht = 0;
        $Sum_BeforeAmount = 0;
        $Sum_BeforeBaht = 0;

        for ($i = 0; $i < $diffMonth; $i++) {

            // Prepare condition
            $curYear = $condition['YearTo'];
            $beforeYear = $condition['YearTo'] - 1;

            foreach ($MasterGoalList as $k => $v) {

                $master_type_id = $v['id'];

                $monthName = LostWaitSaleController::getMonthName($curMonth);

                $data = [];
                // $data['RegionName'] = $value['RegionName'];
                $data['LostWaitSaleName'] = $v['goal_name'];
                $data['Month'] = $monthName;

                // get cooperative type
                $Current = LostWaitSaleService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                // print_r($Current);exit;
                $data['CurrentAmount'] = floatval($Current['sum_amount']);
                $data['CurrentBaht'] = floatval($Current['sum_baht']);

                $Before = LostWaitSaleService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                $data['BeforeAmount'] = floatval($Before['sum_amount']);
                $data['BeforeBaht'] = floatval($Before['sum_baht']);

                $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
                $data['DiffAmount'] = $DiffAmount;
                if ($data['BeforeAmount'] != 0) {
                    $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
                } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                    $data['DiffAmountPercentage'] = 100;
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
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
            }

            $curMonth++;
        }

        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวม';
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
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getMonthDataListreport($condition) {
        $factory_id = $condition['Factory'];
        $ymFrom = $condition['YearFrom'] - 1 . '-' . str_pad(10, 2, "0", STR_PAD_LEFT);
        $ymTo = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT);
        $toTime = $condition['YearTo'] . '-' . str_pad($condition['MonthTo'], 2, "0", STR_PAD_LEFT) . '-28';
        $fromTime = $condition['YearFrom'] - 1 . '-' . str_pad(10, 2, "0", STR_PAD_LEFT) . '-01';

        $date1 = new \DateTime($toTime);
        $date2 = new \DateTime($fromTime);
        $diff = $date1->diff($date2);
        $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m'));
        if ($diffMonth == 0) {
            $diffMonth = 1;
        } else {
            $diffMonth += 1;
        }

        $DataList = [];
        $DataSummary = [];

        // get master goal
        $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียรอจำหน่าย', [], '', $factory_id);


        $Sum_CurrentAmount = 0;
        $Sum_CurrentBaht = 0;
        $Sum_BeforeAmount = 0;
        $Sum_BeforeBaht = 0;
        foreach ($MasterGoalList as $k => $v) {

            $curYear = $condition['YearTo'] - 1;
            $curMonth = 10;
            $data = [];
            for ($i = 0; $i < $diffMonth; $i++) {
                $master_type_id = $v['id'];

                $monthName = LostWaitSaleController::getMonthName($curMonth);

                //$data = [];
                // $data['RegionName'] = $value['RegionName'];
                $data['LostWaitSaleName'] = $v['goal_name'];
                $data['Month'] = $monthName;

                // get cooperative type
                $Current = LostWaitSaleService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                // print_r($Current);exit;
                $data['CurrentAmount'] += floatval($Current['sum_amount']);
                $data['CurrentBaht'] += floatval($Current['sum_baht']);

                $Before = LostWaitSaleService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
                $data['BeforeAmount'] += floatval($Before['sum_amount']);
                $data['BeforeBaht'] += floatval($Before['sum_baht']);

                $Sum_CurrentAmount += floatval($data['CurrentAmount']);
                $Sum_CurrentBaht += floatval($data['CurrentBaht']);
                $Sum_BeforeAmount += floatval($data['BeforeAmount']);
                $Sum_BeforeBaht += floatval($data['BeforeBaht']);

                $curMonth++;
                if ($curMonth > 12) {
                    $curMonth = 1;
                    $curYear++;
                }
            }
            $DiffAmount = $data['CurrentAmount'] - $data['BeforeAmount'];
            $data['DiffAmount'] = $DiffAmount;
            if ($data['BeforeAmount'] != 0) {
                $data['DiffAmountPercentage'] = (($data['CurrentAmount'] - $data['BeforeAmount']) / $data['BeforeAmount']) * 100;
            } else if (empty($data['BeforeAmount']) && !empty($data['CurrentAmount'])) {
                $data['DiffAmountPercentage'] = 100;
            }


            $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
            $data['DiffBaht'] = $DiffBaht;

            if ($data['BeforeBaht'] != 0) {
                $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
            } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                $data['DiffBahtPercentage'] = 100;
            }


            array_push($DataList, $data);
        }
        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวม';
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
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);




        return ['DataList' => $DataList];
    }

    public function getQuarterDataList($condition, $regions) {

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

        // get master goal
        $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียรอจำหน่าย', [], '', $factory_id);
        $Sum_CurrentAmount = 0;
        $Sum_CurrentBaht = 0;
        $Sum_BeforeAmount = 0;
        $Sum_BeforeBaht = 0;

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


            foreach ($MasterGoalList as $k => $v) {

                $master_type_id = $v['id'];

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

                    $Current = LostWaitSaleService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                    $SumCurrentAmount += floatval($Current['sum_amount']);
                    $SumCurrentBaht += floatval($Current['sum_baht']);

                    $Before = LostWaitSaleService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
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
                $data['RegionName'] = $value['RegionName'];
                $data['LostWaitSaleName'] = $v['goal_name'];
                $data['Quarter'] = $curQuarter . ' (' . (($curQuarter == 1 ? $curYear + 543 + 1 : $curYear + 543)) . ')';

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
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                }

                $data['CreateDate'] = $UpdateDate;
                $data['ApproveDate'] = $ApproveDate;
                if (!empty($ApproveDate)) {
                    if (empty($ApproveComment)) {
                        $data['Status'] = 'อนุมัติ';
                    } else {
                        $data['Status'] = 'ไม่อนุมัติ';
                    }
                }
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'quarter' => $curQuarter
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                $Sum_CurrentAmount += $data['CurrentAmount'];
                $Sum_CurrentBaht += $data['CurrentBaht'];
                $Sum_BeforeAmount += $data['BeforeAmount'];
                $Sum_BeforeBaht += $data['BeforeBaht'];
            }
        }

        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวม';
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
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getAnnuallyDataList($condition, $regions) {

        $factory_id = $condition['Factory'];

        $loop = 1; //intval($condition['YearTo']) - intval($condition['YearFrom']) + 1;
        $curYear = $condition['YearTo'];

        $beforeYear = $calcYear - 1;
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        $DataList = [];
        $DataSummary = [];
        $curYear = $condition['YearTo'];

        // get master goal
        $MasterGoalList = MasterGoalService::getList('Y', 'การสูญเสียรอจำหน่าย', [], '', $factory_id);
        $Sum_CurrentAmount = 0;
        $Sum_CurrentBaht = 0;
        $Sum_BeforeAmount = 0;
        $Sum_BeforeBaht = 0;

        for ($i = 0; $i < $loop; $i++) {

            foreach ($MasterGoalList as $k => $v) {

                $master_type_id = $v['id'];
                $region_id = $value['RegionID'];


                $calcYear = intval($curYear) - 1;

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

                    $Current = LostWaitSaleService::getMainList($curYear, $curMonth, $factory_id, $master_type_id);
                    $SumCurrentAmount += floatval($Current['sum_amount']);
                    $SumCurrentBaht += floatval($Current['sum_baht']);

                    $Before = LostWaitSaleService::getMainList($beforeYear, $curMonth, $factory_id, $master_type_id);
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
                $data['RegionName'] = $value['RegionName'];
                $data['LostWaitSaleName'] = $v['goal_name'];
                $data['Year'] = $curYear + 543;

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
                }


                $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
                $data['DiffBaht'] = $DiffBaht;

                if ($data['BeforeBaht'] != 0) {
                    $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
                } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
                    $data['DiffBahtPercentage'] = 100;
                }

                $data['CreateDate'] = $UpdateDate;
                $data['ApproveDate'] = $ApproveDate;
                if (!empty($ApproveDate)) {
                    if (empty($ApproveComment)) {
                        $data['Status'] = 'อนุมัติ';
                    } else {
                        $data['Status'] = 'ไม่อนุมัติ';
                    }
                }
                $data['Description'] = ['months' => $curMonth
                    , 'years' => $curYear
                    , 'region_id' => $region_id
                ];

                array_push($DataList, $data);

                $DataSummary['SummaryAmount'] = $DataSummary['SummaryAmount'] + $data['CurrentAmount'];
                $DataSummary['SummaryBaht'] = $DataSummary['SummaryBaht'] + $data['CurrentBaht'];

                $Sum_CurrentAmount += $data['CurrentAmount'];
                $Sum_CurrentBaht += $data['CurrentBaht'];
                $Sum_BeforeAmount += $data['BeforeAmount'];
                $Sum_BeforeBaht += $data['BeforeBaht'];
            }
        }

        $data = [];
        // $data['RegionName'] = $value['RegionName'];
        $data['bg_color'] = '#ccc';
        $data['Month'] = 'รวม';
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
        }


        $DiffBaht = $data['CurrentBaht'] - $data['BeforeBaht'];
        $data['DiffBaht'] = $DiffBaht;

        if ($data['BeforeBaht'] != 0) {
            $data['DiffBahtPercentage'] = (($data['CurrentBaht'] - $data['BeforeBaht']) / $data['BeforeBaht']) * 100;
        } else if (empty($data['BeforeBaht']) && !empty($data['CurrentBaht'])) {
            $data['DiffBahtPercentage'] = 100;
        }
        $data['DiffBaht'] = $DiffBaht;

        array_push($DataList, $data);

        return ['DataList' => $DataList, 'Summary' => $DataSummary];
    }

    public function getData($request, $response, $args) {
        try {
            $params = $request->getParsedBody();

            $id = $params['obj']['id'];

            $factory_id = $params['obj']['factory_id'];
            $months = $params['obj']['months'];
            $years = $params['obj']['years'];

            if (!empty($id)) {
                $_Data = LostWaitSaleService::getDataByID($id);
            } else {
                $_Data = LostWaitSaleService::getData($factory_id, $months, $years);
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

            $id = LostWaitSaleService::updateData($_Data);

            foreach ($_Detail as $key => $value) {
                $value['lost_wait_sale_id'] = $id;
                LostWaitSaleService::updateDetailData($value);
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
            $result = LostWaitSaleService::removeDetailData($id);

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
            $result = LostWaitSaleService::updateDataApprove($id, $data);

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

    public function getExcelTemplate($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');
        try {

            $params = $request->getParsedBody();
            // $condition = $params['obj']['condition'];

            $factory_id = $params['obj']['factory_id'];
            $years = $params['obj']['years'];
            $months = $params['obj']['months'];
            $menu_type = 'การสูญเสียรอจำหน่าย';

            $con_year = $years;
            if($months > 9){
                $con_year = $years - 1;
            }
            $avgDate = $con_year . '-'. ($months<10?'0'.$months:$months) . '-01';
            
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();

            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'รายการสูญเสียหลังผลิต');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', 'ประเภทของนม');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', 'ชื่อผลิตภัณฑ์');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', 'ชนิดผลิตภัณฑ์');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', 'เป้าหมายทั้งปี');
            $objPHPExcel->getActiveSheet()->setCellValue('G1', 'เป้าหมายเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('I1', 'การดำเนินงานรายเดือน');

            $objPHPExcel->getActiveSheet()->setCellValue('E2', 'จำนวนหีบ / กล่อง');
            $objPHPExcel->getActiveSheet()->setCellValue('F2', 'บาท');

            $objPHPExcel->getActiveSheet()->setCellValue('G2', 'จำนวนหีบ / กล่อง');
            $objPHPExcel->getActiveSheet()->setCellValue('H2', 'บาท');

            $objPHPExcel->getActiveSheet()->setCellValue('I2', 'จำนวนหีบ / กล่อง');
            $objPHPExcel->getActiveSheet()->setCellValue('J2', 'ลิตร');
            $objPHPExcel->getActiveSheet()->setCellValue('K2', 'บาท');

            $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
            $objPHPExcel->getActiveSheet()->mergeCells('B1:B2');
            $objPHPExcel->getActiveSheet()->mergeCells('C1:C2');
            $objPHPExcel->getActiveSheet()->mergeCells('D1:D2');
            $objPHPExcel->getActiveSheet()->mergeCells('E1:F1');
            $objPHPExcel->getActiveSheet()->mergeCells('G1:H1');
            $objPHPExcel->getActiveSheet()->mergeCells('I1:K1');

            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);

            $item_cnt = 3;
            // Gen item

            // load loss mapping
            $LossMappingList = MasterLossService::getMappingList($factory_id, $menu_type);
            // print_r($LossMappingList);exit;
            foreach ($LossMappingList as $loss_key => $loss_value) {
                $loss_id = $loss_value['loss_id'];
                $loss_name = $loss_value['name'];
                // load product milk
                $ProductMilk = MasterLossService::getProductMilkList($factory_id, $loss_id, $menu_type);
                // print_r($ProductMilk);exit;
                foreach ($ProductMilk as $key => $value) {
                    
                    $product_milk_id = $value['product_milk_id'];
                    $product_milk_name = $value['name'];
                    
                    $SubProductMilk = MasterLossService::getSubProductMilkList($product_milk_id, $factory_id, $loss_id, $menu_type);
                    // print_r($SubProductMilk);exit;
                    foreach ($SubProductMilk as $key1 => $value1) {
                        $subproduct_milk_id = $value1['subproduct_milk_id'];
                        $subproduct_milk_name = $value1['product_character'] . ' ' . $value1['name'];

                        $ProductMilkDetail = MasterLossService::getProductMilkDetailList($subproduct_milk_id, $factory_id, $loss_id, $product_milk_id, $menu_type);
                        // print_r($ProductMilkDetail);exit;
                        foreach ($ProductMilkDetail as $key2 => $value2) {

                            $product_milk_detail_name = $value2['name'] . ' ' .$value2['number_of_package'] . ' ' . $value2['unit'] . ' ' . $value2['amount'] . ' ' . $value2['amount_unit'] . ' ' . $value2['taste'];

                            // find goal values from name
                            $goal_name = $loss_name . ' - ' . $product_milk_name . ' - ' .  $subproduct_milk_name . ' - ' . $product_milk_detail_name;

                            // get goal id by goal name

                            $_MasterGoal = MasterGoalService::getGoalIDByName($goal_name, $menu_type, $factory_id);

                            $GoalMissionData = GoalMissionService::getGoalMissionByGoalName($menu_type, $_MasterGoal['id'], $factory_id, $years);

                            // get goal mission in month
                            // echo $GoalMissionData['id'];exit;
                            $GoalMissionMonthData = GoalMissionService::getAvgMonth($GoalMissionData['id'], $avgDate);
                            // print_r($GoalMissionData);
                            // exit;
                            $objPHPExcel->getActiveSheet()->setCellValue('A' .$item_cnt, $loss_name);
                            $objPHPExcel->getActiveSheet()->setCellValue('B' .$item_cnt, $product_milk_name);
                            $objPHPExcel->getActiveSheet()->setCellValue('C' .$item_cnt, $subproduct_milk_name);
                            $objPHPExcel->getActiveSheet()->setCellValue('D' .$item_cnt, $product_milk_detail_name);
                            $objPHPExcel->getActiveSheet()->setCellValue('E' .$item_cnt, $GoalMissionData['total_amount']);
                            $objPHPExcel->getActiveSheet()->setCellValue('F' .$item_cnt, $GoalMissionData['price_value']);
                            
                            $objPHPExcel->getActiveSheet()->setCellValue('G' .$item_cnt, $GoalMissionMonthData['amount']);
                            $objPHPExcel->getActiveSheet()->setCellValue('H' .$item_cnt, $GoalMissionMonthData['price_value']);
                            
                            $objPHPExcel->getActiveSheet()->setCellValue('I' .$item_cnt, '');
                            $objPHPExcel->getActiveSheet()->setCellValue('J' .$item_cnt, '');
                            $objPHPExcel->getActiveSheet()->setCellValue('K' .$item_cnt, '');
                            // $objPHPExcel->getActiveSheet()->setCellValue('K' .$item_cnt, $goal_name);
                            // $objPHPExcel->getActiveSheet()->setCellValue('L' .$item_cnt, $GoalMissionData['id'] . ' - ' .  $avgDate);
                            // $objPHPExcel->getActiveSheet()->setCellValue('M' .$item_cnt, $menu_type .' - ' . $_MasterGoal['id'].' - ' .  $factory_id.' - ' .  $years);
                            $item_cnt++;
                        }
                    }

                }
            }
           

            $objPHPExcel->getActiveSheet()->getStyle('A1:K2' . $highestRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()
            ->getStyle("A1:K" . $objPHPExcel->getActiveSheet()->getHighestRow())
            ->applyFromArray($this->getDefaultStyle());

            // exit;
            $filename = 'TEMPLATE__loss-wait-sale_' . date('YmdHis') . '.xlsx';
            $filepath = '../../files/files/download/' . $filename;

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            $objWriter->setPreCalculateFormulas();
            $objWriter->save($filepath);
            // exit;
            $this->data_result['DATA'] = 'files/files/download/' . $filename;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function getDefaultStyle(){
        return 
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    )
                    // ,
                    // 'font' => array(
                    //     'name' => 'AngsanaUPC'
                    // )
                );
    }

    public function uploadData($request, $response, $args) {
        // error_reporting(E_ERROR);
        //     error_reporting(E_ALL);
        //     ini_set('display_errors','On');
        $_WEB_FILE_PATH = 'files/files';
        try {
            $params = $request->getParsedBody();
            $_Data = $params['obj']['Data'];
            $factory_id = $_Data['factory_id'];
            foreach ($_Data as $key => $value) {
                if($value == 'null'){
                    $_Data[$key] = '';
                }
            }

            $_FileDate = $params['obj']['FileDate'];

            $user_session = $params['user_session'];

            $id = LostWaitSaleService::updateData($_Data);

            // clear item
            LostWaitSaleService::removeDetailDataByParent($id);            

            $files = $request->getUploadedFiles();
            $f = $files['obj']['AttachFile'];
            $_UploadFile = [];
            if($f != null){
                if($f->getClientFilename() != ''){
                    // Unset old image if exist
                    
                    $ext = pathinfo($f->getClientFilename(), PATHINFO_EXTENSION);
                    $FileName = date('YmdHis').'_'.rand(100000,999999). '.'.$ext;
                    $FilePath = $_WEB_FILE_PATH . '/upload/'.$FileName;
                    
                    $_UploadFile['file_name'] = $f->getClientFilename();
                    $_UploadFile['file_path'] = $FilePath;
                    
                    $f->moveTo('../../' . $FilePath);
                }        
            }

            // read file 
            $file = '../../' . $FilePath;
            $_Detail = $this->readExcelFile($file, $id);

            // print_r($_Detail);
            // exit;
            foreach ($_Detail as $key => $value) {
            //     print_r($value);
            // exit;
                $data = [];
                
                $data['lost_wait_sale_id'] = $value['lost_wait_sale_id'];

                $goal_name = $value['loss_name'] . ' - ' . $value['product_milk'] . ' - ' .  $value['sub_product_milk'] . ' - ' . $value['product_milk_detail'];

                $product_milk_id = $this->getProductInfoType1($value['product_milk'], $factory_id);
                $subproduct_milk_id = $this->getProductInfoType2($value['sub_product_milk'], $product_milk_id);
                $productmilk_detail_id = $this->getProductInfoType3($value['product_milk_detail'], $subproduct_milk_id);

                // echo "$goal_name, $factory_id";exit;
                $data['lost_wait_sale_type'] = $this->getLostWaitSaleType($goal_name, $factory_id );
                $data['package_amount'] = str_replace(',', '', $value['result_package_amount']);
                // exit;
                $value['result_package_amount'] = str_replace(',', '', $value['result_package_amount']);
                $value['result_thb'] = str_replace(',', '', $value['result_thb']);

                // Get product milk detail data
                $ProductMilkDetailData = ProductMilkDetailService::getData($productmilk_detail_id);
                // print_r($ProductMilkDetailData);
                // exit;
                // Calc litre
                // (((3*48)+5)*125)/1,000
                
                if(!empty($value['result_package_amount']) && ($ProductMilkDetailData['unit'] == 'ซีซี' || $ProductMilkDetailData['unit'] == 'มิลลิลิตร')){

                    // $data['amount'] = empty($value['result_amount'])?0:$value['result_amount'];
                    $box = 0;
                    $amount_data = explode('.', ''.$value['result_package_amount']);
                    $amount = $amount_data[0];
                    if(!empty($amount_data[1])){
                        $box = $amount_data[1];
                    }
                    
                    $data['amount'] = ((($amount * $ProductMilkDetailData['amount']) + $box) * $ProductMilkDetailData['number_of_package']) / 1000;
                }else{
                    $data['amount'] = empty($value['result_amount'])?0:$value['result_amount'];
                
                }     
                // exit;
                
                $data['price_value'] = empty($value['result_thb'])?0:$value['result_thb'];
                $data['id'] = '';

                LostWaitSaleService::updateDetailData($data);
            }

            // add log
            $_UploadFile['menu_type'] = 'lost-wait-sale';
            $_UploadFile['file_date'] = $_FileDate;
            $_UploadFile['data_id'] = $id;
            UploadLogService::updateLog($_UploadFile);

            //           
            $this->data_result['DATA']['id'] = $id;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function getLostWaitSaleType($goal_name, $factory_id){
        $data = MasterGoalService::getGoalIDByName($goal_name, 'การสูญเสียรอจำหน่าย', $factory_id);
        return $data['id'];
    }

    private function getProductInfoType1($product_milk, $factory_id){
        return ProductMilkService::getIDByName($product_milk, $factory_id);
    }

    private function getProductInfoType2($sub_product_milk, $production_sale_info_type1){
        return SubProductMilkService::getIDByName($sub_product_milk, $production_sale_info_type1);
    }

    private function getProductInfoType3($product_milk_detail, $production_sale_info_type2){
        return ProductMilkDetailService::getIDByName($product_milk_detail, $production_sale_info_type2);
    }

    private function readExcelFile($file, $lost_wait_sale_id){

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        $field_array = ['loss_name', 'product_milk', 'sub_product_milk', 'product_milk_detail', 'goal_year_amount', 'goal_year_thb',  'goal_month_amount', 'goal_month_thb', 'result_package_amount', 'result_amount', 'result_thb'];
        $cnt_row = 1;

        $ItemList = [];
        foreach ($sheetData as $key => $value) {
            
            if($cnt_row > 2){
                
                $cnt_col = 0;
                $cnt_field = 0;
                $Item = [];
                $Item[ 'lost_wait_sale_id' ] = $lost_wait_sale_id;

                foreach ($value as $k => $v) {
                    // if($cnt_col >= 1 && $cnt_col <= 7){
                        
                        $Item[ $field_array[$cnt_field] ] = $v;
                        $cnt_field++;
                        
                    // }
                    $cnt_col++;
                }
                
                array_push($ItemList, $Item);
                
            }

            $cnt_row++;

        }
        
        return $ItemList;
    }

}
