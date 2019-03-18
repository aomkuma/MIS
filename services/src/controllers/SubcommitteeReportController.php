<?php

namespace App\Controller;

use App\Service\FoodService;
use App\Service\MasterGoalService;
use App\Service\SpermService;
use App\Service\GoalMissionService;
use App\Service\TravelService;
use App\Service\CowBreedService;
use App\Service\CowGroupService;
use App\Service\TrainingCowBreedService;
use App\Service\InseminationService;
use App\Service\VeterinaryService;
use App\Service\MineralService;
use App\Service\SpermSaleService;
use App\Service\MouService;
use App\Service\MBIService;
use App\Service\MSIService;
use App\Service\ProductionInfoService;
use App\Service\ProductionSaleInfoService;
use App\Service\LostInProcessService;
use App\Service\LostOutProcessService;
use App\Service\LostWaitSaleService;
use App\Service\FactoryService;
use PHPExcel;

class SubcommitteeReportController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    private function getMonthshName($month) {
        switch ($month) {
            case 1 : $monthTxt = 'ม.ค.';
                break;
            case 2 : $monthTxt = 'ก.พ.';
                break;
            case 3 : $monthTxt = 'มี.ค.';
                break;
            case 4 : $monthTxt = 'ม.ย.';
                break;
            case 5 : $monthTxt = 'พ.ค.';
                break;
            case 6 : $monthTxt = 'มิ.ย.';
                break;
            case 7 : $monthTxt = 'ก.ค.';
                break;
            case 8 : $monthTxt = 'ส.ค.';
                break;
            case 9 : $monthTxt = 'ก.ย.';
                break;
            case 10 : $monthTxt = 'ต.ค.';
                break;
            case 11 : $monthTxt = 'พ.ย.';
                break;
            case 12 : $monthTxt = 'ธ.ค.';
                break;
        }
        return $monthTxt;
    }

    private function getMonthName($month) {
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

    public function exportsubreportExcel($request, $response) {
        try {
            $obj = $request->getParsedBody();
            $condition = $obj['obj']['condition'];


            $objPHPExcel = new PHPExcel();

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'สรุปรายงานผลการดำเนินงานประจำ ปี ' . ($condition['YearFrom'] + 543);
                    $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet3($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet4($objPHPExcel, $condition, $header);
                    //   $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $header);
                    break;
                case 'monthly' :$header = 'สรุปรายงานผลการดำเนินงานประจำเดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ปี ' . ($condition['YearTo'] + 543);
                    $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet3($objPHPExcel, $condition, $header);

                    $objPHPExcel = $this->generatesheet4($objPHPExcel, $condition, $header);
                    //  $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $header);
                    break;
                case 'quarter' :$header = 'สรุปรายงานผลการดำเนินงานประจำ ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543);
                    $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet3($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet4($objPHPExcel, $condition, $header);
                    //   $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $header);
                    break;

                default : $result = null;
            }
//            die();
            // $filename = 'MIS_Report-รายงานรายเดือน' . '_' . date('YmdHis') . '.xlsx';
            $filename = 'MIS_Report-Subcommittee_' . '_' . date('YmdHis') . '.xlsx';
            $filepath = '../../files/files/download/' . $filename;

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            $objWriter->setPreCalculateFormulas();


            $objWriter->save($filepath);

            $this->data_result['DATA'] = 'files/files/download/' . $filename;

            return $this->returnResponse(200, $this->data_result, $response);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    private function generatesheet1($objPHPExcel, $condition, $header) {
        $objPHPExcel->getActiveSheet()->setTitle("สรุป");

        $mastername = ['บริการสัตวแพทย์', 'ผสมเทียม', 'ผลิตน้ำนม', 'ผลิตน้ำเชื้อแช่แข็ง', 'แร่ธาตุ พรีมิกซ์ และอาหาร', 'ปัจจัยการเลี้ยงโค', 'ฝึกอบรม', 'จำหน่ายน้ำเชื้อแช่แข็ง', 'ข้อมูลการผลิต', 'ข้อมูลการขาย', 'ข้อมูลรับซื้อน้ำนม', 'ข้อมูลจำหน่ายน้ำนม'];




        if ($condition['DisplayType'] == 'annually') {
            $year = $condition['YearFrom'];
            $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row = 0;
            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ปี ' . ($condition['YearFrom'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ผลการดำเนินงานปี ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ปี ' . ($condition['YearFrom'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');
            $data = [];

            foreach ($mastername as $item) {
                $mastes = MasterGoalService::getList('Y', $item);
                $detail['name'] = $item;

                $detail['data'] = [];
                foreach ($mastes as $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearFrom']);
                    $detail2['target'] = 0;
                    $detail2['actual'] = 0;
                    $detail2['percen'] = 0;
                    foreach ($monthList as $i => $monthloop) {
                        $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year - $yearlist[$i], $monthloop);
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($year - $yearlist[$i], $monthloop, 3);
                                break;
                            case 'บริการสัตวแพทย์' :
                                $actually = VeterinaryService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id']);

                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($year - $yearlist[$i], $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'ข้อมูลการผลิต' :
                                $actually = ProductionInfoService::getDetailList2($year - $yearlist[$i], $monthloop);
                                break;
                            case 'ข้อมูลการขาย' :
                                $actually = ProductionSaleInfoService::getDetailList2($year - $yearlist[$i], $monthloop);
                                break;
                            case 'ข้อมูลรับซื้อน้ำนม' :
                                $actually = MBIService::getListMBIreoprt2($year - $yearlist[$i], $monthloop);
                                break;
                            case 'ข้อมูลจำหน่ายน้ำนม' :
                                $actually = MSIService::getListMSIreoprt2($year - $yearlist[$i], $monthloop);
                                break;

                            default : $result = null;
                        }

                        $detail2['mission'] = $itemmaster['goal_name'];
                        $detail2['unit'] = $mission[0]['unit'];
                        $detail2['target'] += $avg[0]['amount'];
                        $detail2['actual'] += $actually['amount'];
                        if ($detail2['target'] > 0) {
                            $detail2['percen'] += ($detail2['actual'] * 100) / $detail2['target'];
                        } else {
                            $detail2['percen'] += 0;
                        }
                    }
                    array_push($detail['data'], $detail2);
                    //       
                }
                array_push($data, $detail);
            }
        } else if ($condition['DisplayType'] == 'monthly') {
            $row = 0;
            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ผลการดำเนินงานเดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');


            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item);
                // $detail['name'] = $item;
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), ($position + $key) . '. ' . $item);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setBold(true);
                // $row++;
//                $detail['data'] = [];
                foreach ($mastes as $keyitem => $itemmaster) {
                    $subposition = 1;
                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearTo']);

                    $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);
                    switch ($itemmaster['menu_type']) {
                        case 'ผสมเทียม' :
                            $actually = InseminationService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], 3);
                            break;
                        case 'บริการสัตวแพทย์' :
                            $actually = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ผลิตน้ำเชื้อแช่แข็ง' :
                            $actually = SpermService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ท่องเที่ยว' :
                            $actually = TravelService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id']);

                            break;
                        case 'ปัจจัยการเลี้ยงโค' :
                            $actually = CowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ข้อมูลฝูงโค' :
                            $actually = CowGroupService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            break;
                        case 'ฝึกอบรม' :
                            $actually = TrainingCowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            break;
                        case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                            $actually = MineralService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                            $actually = SpermSaleService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            break;
                        case 'ข้อมูลการผลิต' :
                            $actually = ProductionInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
                            break;
                        case 'ข้อมูลการขาย' :
                            $actually = ProductionSaleInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
                            break;
                        case 'ข้อมูลรับซื้อน้ำนม' :
                            $actually = MBIService::getListMBIreoprt2($condition['YearTo'], $condition['MonthFrom']);
                            break;
                        case 'ข้อมูลจำหน่ายน้ำนม' :
                            $actually = MSIService::getListMSIreoprt2($condition['YearTo'], $condition['MonthFrom']);
                            break;

                        default : $result = null;
                    }

                    // $detail2['mission'] += $itemmaster['goal_name'];
                    $detail2['unit'] = $mission[0]['unit'];
                    $detail2['target'] += $avg[0]['amount'];
                    $detail2['actual'] += $actually['amount'];

                    // array_push($detail['data'], $detail2);
                }
                if ($detail2['target'] > 0) {
                    $detail2['percen'] = ($detail2['actual'] * 100) / $detail2['target'];
                } else {
                    $detail2['percen'] = 0;
                }
//                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '  ' . ($position + $key) . '.' . ($subposition + $keyitem) . ' ' . $detail2['mission']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $detail2['unit']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $detail2['target']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $detail2['actual']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $detail2['percen']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('B' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('C' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('D' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('E' . (6 + $row))->getFont()->setSize(14);
                $row++;
                //       array_push($data, $detail);
            }
        } else {
            $year = $condition['YearFrom'];

            if ($condition['QuarterFrom'] == 1) {
                $year--;
            }
            if ($condition['QuarterFrom'] == 1) {
                $monthList = [10, 11, 12];
            } else if ($condition['QuarterFrom'] == 2) {
                $monthList = [1, 2, 3];
            } else if ($condition['QuarterFrom'] == 3) {
                $monthList = [4, 5, 6];
            } else if ($condition['QuarterFrom'] == 4) {
                $monthList = [7, 8, 9];
            }
            $row = 0;
            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'ผลการดำเนินงานไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('D4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');
            $data = [];
            $type['goal_type'] = DBI;
            foreach ($mastername as $item) {
                $mastes = MasterGoalService::getList('Y', $item, $type);
                $detail['name'] = $item;

                $detail['data'] = [];
                foreach ($mastes as $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearFrom']);
                    $detail2['target'] = 0;
                    $detail2['actual'] = 0;
                    $detail2['percen'] = 0;
                    foreach ($monthList as $monthloop) {
                        $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $monthloop);
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($year, $monthloop, 3);
                                break;
                            case 'บริการสัตวแพทย์' :
                                $actually = VeterinaryService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($year, $monthloop, $itemmaster['id']);

                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($year, $monthloop, $itemmaster['id'], 3);
                                break;
                            case 'ข้อมูลการผลิต' :
                                $actually = ProductionInfoService::getDetailList2($year, $monthloop);
                                break;
                            case 'ข้อมูลการขาย' :
                                $actually = ProductionSaleInfoService::getDetailList2($year, $monthloop);
                                break;
                            case 'ข้อมูลรับซื้อน้ำนม' :
                                $actually = MBIService::getListMBIreoprt2($year, $monthloop);
                                break;
                            case 'ข้อมูลจำหน่ายน้ำนม' :
                                $actually = MSIService::getListMSIreoprt2($year, $monthloop);
                                break;

                            default : $result = null;
                        }

                        $detail2['mission'] = $itemmaster['goal_name'];
                        $detail2['unit'] = $mission[0]['unit'];
                        $detail2['target'] += $avg[0]['amount'];
                        $detail2['actual'] += $actually['amount'];
                        if ($detail2['target'] > 0) {
                            $detail2['percen'] += ($detail2['actual'] * 100) / $detail2['target'];
                        } else {
                            $detail2['percen'] += 0;
                        }
                    }
                    array_push($detail['data'], $detail2);
                    //       
                }
                array_push($data, $detail);
            }
        }
        session_start();

        foreach ($data as $key => $itemdata) {
            $_SESSION["postion"] = ($position + $key);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), ($position + $key) . '.' . $itemdata['name']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setBold(true);
            $row++;
            $subposition = 1;
            foreach ($itemdata['data'] as $key2 => $item2) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '  ' . ($position + $key) . '.' . ($subposition + $key2) . ' ' . $item2['mission']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $item2['unit']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $item2['target']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $item2['actual']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $item2['percen']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('B' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('C' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('D' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('E' . (6 + $row))->getFont()->setSize(14);
                $row++;
            }
        }

        // header style

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);

        $objPHPExcel->getActiveSheet()->getStyle('A4:E5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E5')->getFont()->setSize(16);


        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:E5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );

        $_SESSION["row"] = 6 + $row;
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('B6:E' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E' . (6 + $row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C6:E' . (6 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E' . (6 + $row + 1))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    ),
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generatesheet2($objPHPExcel, $condition, $header) {
        $mastername = ['สัตวแพท', 'ผสมเทียม', 'ผลิตน้ำนม', 'ผลิตน้ำเชื้อแช่แข็ง', 'แร่ธาตุ พรีมิกซ์ และอาหาร', 'ปัจจัยการเลี้ยงโค', 'ฝึกอบรม', 'จำหน่ายน้ำเชื้อแช่แข็ง'];
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 2-3");
        $row = 0;
        if ($condition['DisplayType'] == 'annually') {
            $position = 1;


            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '1.ผลการดำเนินงานด้านกิจการโคนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ปี ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ปี' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');
            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');
            $data = [];
            $type['goal_type'] = DBI;
            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item, $type);
                $detail['name'] = $item;

                $detail['beforemonth']['amount'] = 0;
                $detail['target']['amount'] = 0;
                $detail['collectmonth']['amount'] = 0;
                $detail['permonth']['amount'] = 0;
                $detail['beforeyear']['amount'] = 0;
                $detail['perbeforeyear']['amount'] = 0;
                $detail['yeartarget']['amount'] = 0;
                $detail['targetoct']['amount'] = 0;
                $detail['collectoct']['amount'] = 0;
                $detail['peroct']['amount'] = 0;
                $detail['beforemonth']['price_value'] = 0;
                $detail['target']['price_value'] = 0;
                $detail['collectmonth']['price_value'] = 0;
                $detail['permonth']['price_value'] = 0;
                $detail['beforeyear']['price_value'] = 0;
                $detail['perbeforeyear']['price_value'] = 0;
                $detail['yeartarget']['price_value'] = 0;
                $detail['targetoct']['price_value'] = 0;
                $detail['collectoct']['price_value'] = 0;
                $detail['peroct']['price_value'] = 0;
                $detail['unit'] = 0;
                foreach ($mastes as $keyitem => $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearFrom']);
                    $detail['unit'] = $mission[0]['unit'];
                    $detail['yeartarget']['amount'] += $mission[0]['amount'];
                    $detail['yeartarget']['price_value'] += $mission[0]['price_value'] / 1000000;

                    $detail['targetoct']['amount'] += $mission[0]['amount'];
                    $detail['targetoct']['price_value'] += $mission[0]['price_value'] / 1000000;
                    if ($detail['targetoct']['amount'] > 0) {
                        $detail['peroct']['amount'] += ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
                    } else {
                        $detail['peroct']['amount'] += 0;
                    }
                    if ($detail['targetoct']['price_value'] > 0) {
                        $detail['peroct']['price_value'] += ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
                    } else {
                        $detail['peroct']['price_value'] += 0;
                    }
                    foreach ($monthList as $key => $ml) {

                        $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearFrom'] - $yearlist[$key], $ml);

                        $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearFrom'] - $yearlist[$key], $ml);
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, 3);
                                $beforeactually = InseminationService::getDetailmonth(($condition['YearFrom'] - $yearlist[$key]) - 1, $ml, 3);

                                break;
                            case 'สัตวแพท' :
                                $actually = VeterinaryService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = VeterinaryService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = SpermService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id']);
                                $beforeactually = TravelService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id']);

                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = CowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = CowGroupService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = TrainingCowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = MineralService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                $beforeactually = SpermSaleService::getDetailmonth($condition['YearFrom'] - $yearlist[$key] - 1, $ml, $itemmaster['id'], 3);


                                break;

                            default : $result = null;
                        }
                        $detail['beforemonth']['amount'] += $beforeavg[0]['amount'];
                        $detail['beforemonth']['price_value'] += $beforeavg[0]['price_value'] / 1000000;
                        $detail['target']['amount'] += $avg[0]['amount'];
                        $detail['target']['price_value'] += $avg[0]['price_value'] / 1000000;
                        $detail['collectmonth']['amount'] += $actually['amount'];
                        $detail['collectmonth']['price_value'] += $actually['price'] / 1000000;
                        if ($detail['target']['amount'] > 0) {
                            $detail['permonth']['amount'] += ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
                        } else {
                            $detail['permonth']['amount'] += 0;
                        }
                        if ($detail['target']['price_value'] > 0) {
                            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
                        } else {
                            $detail['permonth']['price_value'] += 0;
                        }
//
                        $detail['beforeyear']['amount'] += $beforeactually['amount'];
                        $detail['beforeyear']['price_value'] += $beforeactually['price'];
                        if ($detail['beforeyear']['amount'] > 0) {
                            $detail['perbeforeyear']['amount'] += (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                        } else {
                            $detail['perbeforeyear']['amount'] += 0;
                        }
                        if ($detail['beforeyear']['price_value'] > 0) {
                            $detail['perbeforeyear']['price_value'] += (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
                        } else {
                            $detail['perbeforeyear']['price_value'] += 0;
                        }


                        $detail['collectoct']['amount'] += $actually['amount'];
                        $detail['collectoct']['price_value'] += $actually['price'] / 1000000;
                    }
                }
                array_push($data, $detail);
            }
        } else if ($condition['DisplayType'] == 'monthly') {
            $beforemonth = $condition['MonthFrom'];
            if ($condition['MonthFrom'] == 1) {
                $beforemonth = 12;
            } else {
                $beforemonth--;
            }

            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '1.ผลการดำเนินงานด้านกิจการโคนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getMonthName($beforemonth) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');
            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');
            $data = [];
            $type['goal_type'] = DBI;
            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item, $type);
                $detail['name'] = $item;

                $detail['beforemonth']['amount'] = 0;
                $detail['target']['amount'] = 0;
                $detail['collectmonth']['amount'] = 0;
                $detail['permonth']['amount'] = 0;
                $detail['beforeyear']['amount'] = 0;
                $detail['perbeforeyear']['amount'] = 0;
                $detail['yeartarget']['amount'] = 0;
                $detail['targetoct']['amount'] = 0;
                $detail['collectoct']['amount'] = 0;
                $detail['peroct']['amount'] = 0;
                $detail['beforemonth']['price_value'] = 0;
                $detail['target']['price_value'] = 0;
                $detail['collectmonth']['price_value'] = 0;
                $detail['permonth']['price_value'] = 0;
                $detail['beforeyear']['price_value'] = 0;
                $detail['perbeforeyear']['price_value'] = 0;
                $detail['yeartarget']['price_value'] = 0;
                $detail['targetoct']['price_value'] = 0;
                $detail['collectoct']['price_value'] = 0;
                $detail['peroct']['price_value'] = 0;
                $detail['unit'] = 0;
                foreach ($mastes as $keyitem => $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearTo']);

                    $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);

                    $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);
                    switch ($itemmaster['menu_type']) {
                        case 'ผสมเทียม' :
                            $actually = InseminationService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], 3);
                            $beforeactually = InseminationService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], 3);

                            break;
                        case 'สัตวแพท' :
                            $actually = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = VeterinaryService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ผลิตน้ำเชื้อแช่แข็ง' :
                            $actually = SpermService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = SpermService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ท่องเที่ยว' :
                            $actually = TravelService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id']);
                            $beforeactually = TravelService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id']);

                            break;
                        case 'ปัจจัยการเลี้ยงโค' :
                            $actually = CowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = CowBreedService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ข้อมูลฝูงโค' :
                            $actually = CowGroupService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = CowGroupService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'ฝึกอบรม' :
                            $actually = TrainingCowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = TrainingCowBreedService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                            $actually = MineralService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = MineralService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);

                            break;
                        case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                            $actually = SpermSaleService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $itemmaster['id'], 3);
                            $beforeactually = SpermSaleService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $itemmaster['id'], 3);


                            break;

                        default : $result = null;
                    }
                    $detail['beforemonth']['amount'] += $beforeavg[0]['amount'];
                    $detail['beforemonth']['price_value'] += $beforeavg[0]['price_value'] / 1000000;
                    $detail['target']['amount'] += $avg[0]['amount'];
                    $detail['target']['price_value'] += $avg[0]['price_value'] / 1000000;
                    $detail['collectmonth']['amount'] += $actually['amount'];
                    $detail['collectmonth']['price_value'] += $actually['price'] / 1000000;
                    if ($detail['target']['amount'] > 0) {
                        $detail['permonth']['amount'] += ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
                    } else {
                        $detail['permonth']['amount'] += 0;
                    }
                    if ($detail['target']['price_value'] > 0) {
                        $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
                    } else {
                        $detail['permonth']['price_value'] += 0;
                    }
//
                    $detail['beforeyear']['amount'] += $beforeactually['amount'];
                    $detail['beforeyear']['price_value'] += $beforeactually['price'] / 1000000;
                    ;
                    if ($detail['beforeyear']['amount'] > 0) {
                        $detail['perbeforeyear']['amount'] += (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                    } else {
                        $detail['perbeforeyear']['amount'] += 0;
                    }
                    if ($detail['beforeyear']['price_value'] > 0) {
                        $detail['perbeforeyear']['price_value'] += (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
                    } else {
                        $detail['perbeforeyear']['price_value'] += 0;
                    }

                    $detail['unit'] = $mission[0]['unit'];
                    $detail['yeartarget']['amount'] += $mission[0]['amount'];
                    $detail['yeartarget']['price_value'] += $mission[0]['price_value'];
                    foreach ($monthList as $key => $ml) {
                        $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);
                        $detail['targetoct']['amount'] += $octavg[0]['amount'];
                        $detail['targetoct']['price_value'] += $octavg[0]['price_value'];
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, 3);
                                break;
                            case 'สัตวแพท' :
                                $actually = VeterinaryService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id']);

                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($condition['YearTo'] - $yearlist[$key], $condition['MonthFrom'], $itemmaster['id'], 3);
                                break;

                            default : $result = null;
                        }
                        $detail['collectoct']['amount'] += $actually['amount'];
                        $detail['collectoct']['price_value'] += $actually['price'] / 1000000;
                        ;
                        if ($ml == $condition['MonthFrom']) {
                            break;
                        }
                    }
                    if ($detail['targetoct']['amount'] > 0) {
                        $detail['peroct']['amount'] += ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
                    } else {
                        $detail['peroct']['amount'] += 0;
                    }
                    if ($detail['targetoct']['price_value'] > 0) {
                        $detail['peroct']['price_value'] += ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
                    } else {
                        $detail['peroct']['price_value'] += 0;
                    }





                    // array_push($detail['data'], $detail2);
                }
                array_push($data, $detail);
            }
        } else {
            $beforeQuarter = $condition['QuarterFrom'];
            $year = $condition['YearFrom'];
            $beforeyear = $condition['YearFrom'];
            $loop = [10, 11, 12];
            if ($condition['QuarterFrom'] == 1) {
                $montharr = [10, 11, 12];
                $beforemontharr = [7, 8, 9];
                $year--;
                $beforeyear--;
                $beforeQuarter = 4;
            } else if ($condition['QuarterFrom'] == 2) {
                $montharr = [1, 2, 3];
                $beforemontharr = [10, 11, 12];
                $beforeyear--;
                $loop = [10, 11, 12, 1, 2, 3];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 3) {
                $montharr = [4, 5, 6];
                $beforemontharr = [1, 2, 3];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 4) {
                $montharr = [7, 8, 9];
                $beforemontharr = [4, 5, 6];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                $beforeQuarter--;
            }

            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '1.ผลการดำเนินงานด้านกิจการโคนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ไตรมาสที่ ' . $beforeQuarter . ' ' . ($year + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');
            $objPHPExcel->getActiveSheet()->setCellValue('K5', 'ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');
            $data = [];
            $type['goal_type'] = DBI;

            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item, $type);
                $detail['name'] = $item;

                $detail['beforemonth']['amount'] = 0;
                $detail['target']['amount'] = 0;
                $detail['collectmonth']['amount'] = 0;
                $detail['permonth']['amount'] = 0;
                $detail['beforeyear']['amount'] = 0;
                $detail['perbeforeyear']['amount'] = 0;
                $detail['yeartarget']['amount'] = 0;
                $detail['targetoct']['amount'] = 0;
                $detail['collectoct']['amount'] = 0;
                $detail['peroct']['amount'] = 0;
                $detail['beforemonth']['price_value'] = 0;
                $detail['target']['price_value'] = 0;
                $detail['collectmonth']['price_value'] = 0;
                $detail['permonth']['price_value'] = 0;
                $detail['beforeyear']['price_value'] = 0;
                $detail['perbeforeyear']['price_value'] = 0;
                $detail['yeartarget']['price_value'] = 0;
                $detail['targetoct']['price_value'] = 0;
                $detail['collectoct']['price_value'] = 0;
                $detail['peroct']['price_value'] = 0;
                $detail['unit'] = 0;
                foreach ($mastes as $keyitem => $itemmaster) {

                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearFrom']);

                    foreach ($beforemontharr as $bm) {
                        $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $beforeyear, $bm);
                        $detail['beforemonth']['amount'] += $beforeavg[0]['amount'];
                        $detail['beforemonth']['price_value'] += $beforeavg[0]['price_value'] / 1000000;
                    }

                    foreach ($montharr as $ma) {
                        $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $ma);
                        switch ($itemmaster['menu_type']) {
                            case 'ผสมเทียม' :
                                $actually = InseminationService::getDetailmonth($year, $ma, 3);
                                $beforeactually = InseminationService::getDetailmonth($year - 1, $ma, 3);
                                break;
                            case 'สัตวแพท' :
                                $actually = VeterinaryService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = VeterinaryService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                $actually = SpermService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = SpermService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'ท่องเที่ยว' :
                                $actually = TravelService::getDetailmonth($year, $ma, $itemmaster['id']);
                                $beforeactually = TravelService::getDetailmonth($year - 1, $ma, $itemmaster['id']);
                                break;
                            case 'ปัจจัยการเลี้ยงโค' :
                                $actually = CowBreedService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = CowBreedService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'ข้อมูลฝูงโค' :
                                $actually = CowGroupService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = CowGroupService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'ฝึกอบรม' :
                                $actually = TrainingCowBreedService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = TrainingCowBreedService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;
                            case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                $actually = MineralService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = MineralService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);

                                break;
                            case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                $actually = SpermSaleService::getDetailmonth($year, $ma, $itemmaster['id'], 3);
                                $beforeactually = SpermSaleService::getDetailmonth($year - 1, $ma, $itemmaster['id'], 3);
                                break;

                            default : $result = null;
                        }

                        $detail['target']['amount'] += $avg[0]['amount'];
                        $detail['target']['price_value'] += $avg[0]['price_value'] / 1000000;
                        ;
                        $detail['collectmonth']['amount'] += $actually['amount'];
                        $detail['collectmonth']['price_value'] += $actually['price'] / 1000000;
                        ;
                        if ($detail['target']['amount'] > 0) {
                            $detail['permonth']['amount'] += ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
                        } else {
                            $detail['permonth']['amount'] += 0;
                        }
                        if ($detail['target']['price_value'] > 0) {
                            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
                        } else {
                            $detail['permonth']['price_value'] += 0;
                        }
                        $detail['beforeyear']['amount'] += $beforeactually['amount'];
                        $detail['beforeyear']['price_value'] += $beforeactually['price'] / 1000000;
                        ;
                        if ($detail['beforeyear']['amount'] > 0) {
                            $detail['perbeforeyear']['amount'] += (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                        } else {
                            $detail['perbeforeyear']['amount'] += 0;
                        }
                        if ($detail['beforeyear']['price_value'] > 0) {
                            $detail['perbeforeyear']['price_value'] += (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
                        } else {
                            $detail['perbeforeyear']['price_value'] += 0;
                        }

                        $detail['unit'] = $mission[0]['unit'];
                        $detail['yeartarget']['amount'] += $mission[0]['amount'];
                        $detail['yeartarget']['price_value'] += $mission[0]['price_value'] / 1000000;


                        foreach ($loop as $key => $ml) {
                            $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);
                            $detail['targetoct']['amount'] += $octavg[0]['amount'];
                            $detail['targetoct']['price_value'] += $octavg[0]['price_value'];
                            switch ($itemmaster['menu_type']) {
                                case 'ผสมเทียม' :
                                    $actually = InseminationService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, 3);
                                    break;
                                case 'สัตวแพท' :
                                    $actually = VeterinaryService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                    break;
                                case 'ผลิตน้ำเชื้อแช่แข็ง' :
                                    $actually = SpermService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                    break;
                                case 'ท่องเที่ยว' :
                                    $actually = TravelService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id']);

                                    break;
                                case 'ปัจจัยการเลี้ยงโค' :
                                    $actually = CowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                    break;
                                case 'ข้อมูลฝูงโค' :
                                    $actually = CowGroupService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                    break;
                                case 'ฝึกอบรม' :
                                    $actually = TrainingCowBreedService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);
                                    break;
                                case 'แร่ธาตุ พรีมิกซ์ และอาหาร' :
                                    $actually = MineralService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $ml, $itemmaster['id'], 3);

                                    break;
                                case 'จำหน่ายน้ำเชื้อแช่แข็ง' :
                                    $actually = SpermSaleService::getDetailmonth($condition['YearFrom'] - $yearlist[$key], $condition['MonthFrom'], $itemmaster['id'], 3);
                                    break;

                                default : $result = null;
                            }
                            $detail['collectoct']['amount'] += $actually['amount'];
                            $detail['collectoct']['price_value'] += $actually['price'] / 1000000;
                            ;
                        }
                        if ($detail['targetoct']['amount'] > 0) {
                            $detail['peroct']['amount'] += ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
                        } else {
                            $detail['peroct']['amount'] += 0;
                        }
                        if ($detail['targetoct']['price_value'] > 0) {
                            $detail['peroct']['price_value'] += ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
                        } else {
                            $detail['peroct']['price_value'] += 0;
                        }
                    }
                }
                array_push($data, $detail);
            }
        }
// print

        foreach ($data as $key => $itemdata) {
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), ($position + $key) . '.' . $itemdata['name']);
            $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
            $row++;

            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   จำนวน');
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['amount']);
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   รายได้');
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ล้านบาท');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['price_value']);
            $row++;
        }

        // header style

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:L2');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(14);

        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setSize(16);


        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:L5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('B6:L' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . (6 + $row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6:L' . (6 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . (6 + $row - 1 ))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    ),
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesheet3($objPHPExcel, $condition, $header) {
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $objPHPExcel->createSheet(2);
        $objPHPExcel->setActiveSheetIndex(2);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 4");
        $row = 0;
        if ($condition['DisplayType'] == 'annually') {
            $position = 1;


            //  $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. ผลการดำเนินงานด้านอุตสาหกรรมนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ปี ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ปี' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('J4:K4');

            $objPHPExcel->getActiveSheet()->setCellValue('J5', $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('K5', '%/เป้าหมายสะสม');
            $data = [];

            ////////รับซื้อ

            $moumission = MouService::getMission($condition['YearFrom'], $condition['MonthFrom']);
            $mbi = MBIService::getactualMBIDetail($condition['YearFrom'], $condition['MonthFrom']);

            $beforeyearmbi = MBIService::getactualMBIDetail($condition['YearFrom'] - 1, $condition['MonthFrom']);
            $moumissionyear = MouService::getMissionyear($condition['YearFrom']);
            $detail['name'] = '1.การรับซื้อน้ำนม';
            $detail['detailname'] = '1 การรับซื้อน้ำนม (ตัน) ';
            $detail['beforemonth'] = 0;
            $detail['target'] = $moumissionyear['amount'] / 1000;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmbi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $moumissionyear['amount'] / 1000;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

//        


            foreach ($monthList as $key => $ml) {
                $beforembi = MBIService::getactualMBIDetail($condition['YearFrom'] - $yearlist[$key] - 1, $ml);
                $detail['beforemonth'] += $beforembi['sum_amount'] / 1000;
                $mbi = MBIService::getactualMBIDetail($condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectmonth'] += $mbi['sum_amount'] / 1000;
                $mbioct = MBIService::getactualMBIDetail($condition['YearFrom'] - $yearlist[$key], $ml);
                $mouoct = MouService::getMission($condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectoct'] += $mbioct['sum_amount'] / 1000;
                $detail['targetoct'] += $mouoct['amount'] / 1000;
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////จำหน่าย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การจำหน่ายน้ำนม';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลจำหน่ายน้ำนม', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $msi = MSIService::getactualMSIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforemsi = MSIService::getactualMSIDetail($year, $beforemonth);
            $beforeyearmsi = MSIService::getactualMSIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);

            $detail['detailname'] = '2 การจำหน่ายน้ำนม (ตัน) ';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;



            $detail['beforemonth'] = $beforeyearmsi['sum_amount'] / 1000;

            $detail['target'] = $mission[0]['amount'];
            $detail['collectmonth'] = $msi['sum_amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmsi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);

                $msioct = MSIService::getactualMSIDetail($condition['YearTo'] - $yearlist[$key], $ml);

                $detail['collectoct'] += $msioct['sum_amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];

//                if ($ml == $condition['MonthFrom']) {
//                    break;
//                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////
            /////ข้อมูลการผลิต
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            //   $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            //  $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $data2 = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.1 น้ำนมดิบเข้ากระบวนการผลิต (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            $detail['beforemonth'] = $beforeyearpro['amount'] / 1000;

            $detail['target'] = $mission[0]['amount'];
            $detail['collectmonth'] = $pro['amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;


            foreach ($monthList as $key => $ml) {
                $beforepro = ProductionInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key] - 1, $ml);
                $detail['beforemonth'] += $beforepro['amount'] / 1000;
                $pro = ProductionInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectmonth'] += $pro['amount'] / 1000;
                $prooct = ProductionInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key], $ml);
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg['amount'] / 1000;
            }

            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data2, $detail);
            /////ข้อมูลการขาย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการขาย', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
//            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
//            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionSaleInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionSaleInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionSaleInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $data3 = [];
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.1  ผลิตภัณฑ์ที่ผลิตได้ (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;
            $detail['beforemonth'] = $beforeyearpro['amount'] / 1000;

            $detail['target'] = $mission[0]['amount'];
            $detail['collectmonth'] = $pro['amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $beforepro = ProductionSaleInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key] - 1, $ml);
                $detail['beforemonth'] += $beforepro['amount'] / 1000;
                $pro = ProductionSaleInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectmonth'] += $pro['amount'] / 1000;
                $prooct = ProductionSaleInfoService::getDetailList2($condition['YearFrom'] - $yearlist[$key], $ml);
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearFrom'] - $yearlist[$key], $ml);
                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg['amount'] / 1000;
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data3, $detail);
        } else if ($condition['DisplayType'] == 'monthly') {
            $beforemonth = $condition['MonthFrom'];
            $year = $condition['YearTo'];
            if ($condition['MonthFrom'] == 1) {
                $beforemonth = 12;
                $year--;
            } else {
                $beforemonth--;
            }

            $position = 1;
            //   $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. ผลการดำเนินงานด้านอุตสาหกรรมนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getMonthName($beforemonth) . ' ' . ($year + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('J4:K4');

            $objPHPExcel->getActiveSheet()->setCellValue('J5', $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('K5', '%/เป้าหมายสะสม');
            $data = [];
////////รับซื้อ

            $moumission = MouService::getMission($condition['YearTo'], $condition['MonthFrom']);
            $mbi = MBIService::getactualMBIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforembi = MBIService::getactualMBIDetail($year, $beforemonth);
            $beforeyearmbi = MBIService::getactualMBIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);
            $moumissionyear = MouService::getMissionyear($condition['YearTo']);
            $detail['name'] = '1.การรับซื้อน้ำนม';
            $detail['detailname'] = '1 การรับซื้อน้ำนม (ตัน) ';
            $detail['beforemonth'] = $beforembi['sum_amount'] / 1000;
            $detail['target'] = $moumission['amount'] / 1000;
            $detail['collectmonth'] = $mbi['sum_amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmbi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $moumissionyear['amount'] / 1000;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $mbioct = MBIService::getactualMBIDetail($condition['YearTo'] - $yearlist[$key], $ml);
                $mouoct = MouService::getMission($condition['YearTo'] - $yearlist[$key], $ml);
                $detail['collectoct'] += $mbioct['sum_amount'] / 1000;
                $detail['targetoct'] += $mouoct['amount'] / 1000;

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////จำหน่าย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การจำหน่ายน้ำนม';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลจำหน่ายน้ำนม', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $msi = MSIService::getactualMSIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforemsi = MSIService::getactualMSIDetail($year, $beforemonth);
            $beforeyearmsi = MSIService::getactualMSIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);

            $detail['detailname'] = '2 การจำหน่ายน้ำนม (ตัน) ';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;



            $detail['beforemonth'] = $beforemsi['sum_amount'] / 1000;

            $detail['target'] = $avg[0]['amount'];
            $detail['collectmonth'] = $msi['sum_amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmsi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);

                $msioct = MSIService::getactualMSIDetail($condition['YearTo'] - $yearlist[$key], $ml);

                $detail['collectoct'] += $msioct['sum_amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////ข้อมูลการผลิต
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $data2 = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.1 น้ำนมดิบเข้ากระบวนการผลิต (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            $detail['beforemonth'] = $beforepro['amount'] / 1000;

            $detail['target'] = $avg[0]['amount'];
            $detail['collectmonth'] = $pro['amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;
            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);

                $prooct = ProductionInfoService::getDetailList2($condition['YearTo'] - $yearlist[$key], $ml);

                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data2, $detail);
            /////ข้อมูลการขาย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการขาย', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $pro = ProductionSaleInfoService::getDetailList2($condition['YearTo'], $condition['MonthFrom']);
            $beforepro = ProductionSaleInfoService::getDetailList2($year, $beforemonth);
            $beforeyearpro = ProductionSaleInfoService::getDetailList2($condition['YearTo'] - 1, $condition['MonthFrom']);

            $data3 = [];
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.1  ผลิตภัณฑ์ที่ผลิตได้ (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            $detail['beforemonth'] = $beforepro['amount'] / 1000;

            $detail['target'] = $avg[0]['amount'];
            $detail['collectmonth'] = $pro['amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $mission[0]['amount'];
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;
            foreach ($monthList as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'] - $yearlist[$key], $ml);

                $prooct = ProductionSaleInfoService::getDetailList2($condition['YearTo'] - $yearlist[$key], $ml);

                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];

                if ($ml == $condition['MonthFrom']) {
                    break;
                }
            }
            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data3, $detail);
            /////tb2
            $condition['MonthTo'] = $condition['MonthFrom'];
            $FactoryList = FactoryService::getList();
            $detail = [];
            foreach ($FactoryList as $id) {
                $data = ProductionInfoController::getMonthreportforsubcom($condition, 1);
                array_push($detail, $data);
            }
            //   print_r($detail);
        } else {
            $beforeQuarter = $condition['QuarterFrom'];
            $year = $condition['YearFrom'];
            $beforeyear = $condition['YearFrom'];
            $loop = [10, 11, 12];
            if ($condition['QuarterFrom'] == 1) {
                $montharr = [10, 11, 12];
                $beforemontharr = [7, 8, 9];
                $year--;
                $beforeyear--;
                $beforeQuarter = 4;
            } else if ($condition['QuarterFrom'] == 2) {
                $montharr = [1, 2, 3];
                $beforemontharr = [10, 11, 12];
                $beforeyear--;
                $loop = [10, 11, 12, 1, 2, 3];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 3) {
                $montharr = [4, 5, 6];
                $beforemontharr = [1, 2, 3];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 4) {
                $montharr = [7, 8, 9];
                $beforemontharr = [4, 5, 6];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                $beforeQuarter--;
            }

            $position = 1;
            // $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. ผลการดำเนินงานด้านอุตสาหกรรมนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ไตรมาสที่ ' . $beforeQuarter . ' ' . ($year + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');

            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมาย ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('J4:K4');
            $objPHPExcel->getActiveSheet()->setCellValue('J5', 'ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('K5', '%/เป้าหมายสะสม');
            $data = [];




            foreach ($beforemontharr as $bm) {
                $beforembi = MBIService::getactualMBIDetail($year, $bm);
                $detail['beforemonth'] += $beforembi['sum_amount'] / 1000;
            }
            foreach ($montharr as $ma) {
                $moumission = MouService::getMission($year, $ma);
                $detail['target'] += $moumission['amount'] / 1000;
                $mbi = MBIService::getactualMBIDetail($year, $ma);
                $detail['collectmonth'] += $mbi['sum_amount'] / 1000;
                $beforeyearmbi = MBIService::getactualMBIDetail($year - 1, $ma);
                $detail['beforeyear'] += $beforeyearmbi['sum_amount'] / 1000;
            }

            foreach ($loop as $key => $ml) {
                $mbioct = MBIService::getactualMBIDetail($year - $yearlist[$key], $ml);
                $mouoct = MouService::getMission($year - $yearlist[$key], $ml);
                $detail['collectoct'] += $mbioct['sum_amount'] / 1000;
                $detail['targetoct'] += $mouoct['amount'] / 1000;
            }



            $moumissionyear = MouService::getMissionyear($condition['YearFrom']);
            $detail['name'] = '1.การรับซื้อน้ำนม';
            $detail['detailname'] = '1 การรับซื้อน้ำนม (ตัน) ';



            $detail['permonth'] = 0;

            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = $moumissionyear['amount'] / 1000;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;




            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////จำหน่าย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การจำหน่ายน้ำนม';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลจำหน่ายน้ำนม', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearFrom']);


            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);


            $detail['detailname'] = '2 การจำหน่ายน้ำนม (ตัน) ';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($beforemontharr as $bm) {
                $beforemsi = MSIService::getactualMSIDetail($year, $bm);
                $detail['beforemonth'] += $beforemsi['sum_amount'] / 1000;
            }
            foreach ($montharr as $ma) {

                $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $ma);
                $detail['target'] = $avg[0]['amount'];
                $msi = MSIService::getactualMSIDetail($year, $ma);
                $detail['collectmonth'] = $msi['sum_amount'] / 1000;
                $beforeyearmsi = MSIService::getactualMSIDetail($year - 1, $ma);
                $detail['beforeyear'] = $beforeyearmsi['sum_amount'] / 1000;
            }

            foreach ($loop as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $year - $yearlist[$key], $ml);

                $msioct = MSIService::getactualMSIDetail($year - $yearlist[$key], $ml);

                $detail['collectoct'] += $msioct['sum_amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];
            }


            $detail['yeartarget'] = $mission[0]['amount'];



            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data, $detail);
            /////
            /////ข้อมูลการผลิต
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการผลิต', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearFrom']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $data2 = [];
            $detail['name'] = '2. การผลิต';
            $detail['detailname'] = '2.1 น้ำนมดิบเข้ากระบวนการผลิต (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($beforemontharr as $bm) {
                $beforepro = ProductionInfoService::getDetailList2($year, $bm);
                $detail['beforemonth'] += $beforepro['amount'] / 1000;
            }
            foreach ($montharr as $ma) {

                $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $ma);
                $detail['target'] = $avg[0]['amount'];

                $pro = ProductionInfoService::getDetailList2($year, $ma);
                $detail['collectmonth'] = $pro['amount'] / 1000;
                $beforeyearpro = ProductionInfoService::getDetailList2($year - 1, $ma);
                $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            }

            foreach ($loop as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $year - $yearlist[$key], $ml);

                $prooct = ProductionInfoService::getDetailList2($year - $yearlist[$key], $ml);

                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];
            }


            $detail['yeartarget'] = $mission[0]['amount'];



            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data2, $detail);
            /////
            /////ข้อมูลการขาย
            $type['goal_type'] = 'II';
            $type['keyword'] = 'การรับซื้อน้ำนมเข้ากระบวนการ';
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการขาย', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearFrom']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $data3 = [];
            $detail['name'] = '3. การจำหน่าย';
            $detail['detailname'] = '3.1 ผลิตภัณฑ์ที่ผลิตได้ (ตัน)';
            $detail['beforemonth'] = 0;
            $detail['target'] = 0;
            $detail['collectmonth'] = 0;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = 0;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($beforemontharr as $bm) {
                $beforepro = ProductionSaleInfoService::getDetailList2($year, $bm);
                $detail['beforemonth'] += $beforepro['amount'] / 1000;
            }
            foreach ($montharr as $ma) {

                $avg = GoalMissionService::getMissionavg($mission[0]['id'], $year, $ma);
                $detail['target'] = $avg[0]['amount'];

                $pro = ProductionSaleInfoService::getDetailList2($year, $ma);
                $detail['collectmonth'] = $pro['amount'] / 1000;
                $beforeyearpro = ProductionSaleInfoService::getDetailList2($year - 1, $ma);
                $detail['beforeyear'] = $beforeyearpro['amount'] / 1000;
            }

            foreach ($loop as $key => $ml) {
                $octavg = GoalMissionService::getMissionavg($mission[0]['id'], $year - $yearlist[$key], $ml);

                $prooct = ProductionSaleInfoService::getDetailList2($year - $yearlist[$key], $ml);

                $detail['collectoct'] += $prooct['amount'] / 1000;
                $detail['targetoct'] += $octavg[0]['amount'];
            }


            $detail['yeartarget'] = $mission[0]['amount'];



            if ($detail['target'] > 0) {
                $detail['permonth'] = ($detail['collectmonth'] * 100) / $detail['target'];
            }
            if ($detail['beforeyear'] > 0) {
                $detail['perbeforeyear'] = (($detail['collectmonth'] - $detail['beforeyear']) * 100) / $detail['beforeyear'];
            }
            if ($detail['targetoct'] > 0) {
                $detail['peroct'] = ($detail['collectoct'] * 100) / $detail['targetoct'];
            }
            array_push($data3, $detail);
            /////
        }
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $data[0]['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        foreach ($data as $key => $itemdata) {


            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $itemdata['detailname']);

            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['peroct']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $data2[0]['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        foreach ($data2 as $key => $itemdata) {


            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $itemdata['detailname']);

            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['peroct']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $data3[0]['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        foreach ($data3 as $key => $itemdata) {


            $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $itemdata['detailname']);

            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['peroct']);
            $row++;
        }
        // header style

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:L2');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(14);

        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setSize(16);


        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:K5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('B6:L' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . (6 + $row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6:L' . (6 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:K' . (6 + $row - 1 ))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    ),
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesheet4($objPHPExcel, $condition, $header) {
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $objPHPExcel->createSheet(3);
        $objPHPExcel->setActiveSheetIndex(3);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 5");
        $FactoryList = FactoryService::getList();
        $row = 6;
        $detail = [];
        $detail2 = [];
        if ($condition['DisplayType'] == 'annually') {
            $position = 1;


            // $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2.1 ผลการดำเนินงานด้านอุตสาหกรรมนม รายภาค');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ปี ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ปี' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . ($condition['YearFrom'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');

            $objPHPExcel->getActiveSheet()->setCellValue('J5', $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('K5', '%/เป้าหมายสะสม');
            $condition['MonthFrom'] = 10;
            $condition['MonthTo'] = 9;
            foreach ($FactoryList as $id) {
                $data = ProductionInfoController::getMonthreportforsubcom($condition, $id['id']);
                array_push($detail, $data);
                $data2 = ProductionSaleInfoController::getMonthreportforsubcom($condition, $id['id']);
                array_push($detail2, $data2);
            }
        } else if ($condition['DisplayType'] == 'monthly') {
            $beforemonth = $condition['MonthFrom'];
            $year = $condition['YearTo'];
            if ($condition['MonthFrom'] == 1) {
                $beforemonth = 12;
                $year--;
            } else {
                $beforemonth--;
            }

            $position = 1;
            //  $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2.1 ผลการดำเนินงานด้านอุตสาหกรรมนม รายภาค');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getMonthName($beforemonth) . ' ' . ($year + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');

            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');

            $condition['MonthTo'] = $condition['MonthFrom'];


            foreach ($FactoryList as $id) {
                $data = ProductionInfoController::getMonthreportforsubcom($condition, $id['id']);
                array_push($detail, $data);
                $data2 = ProductionSaleInfoController::getMonthreportforsubcom($condition, $id['id']);
                array_push($detail2, $data2);
            }
//            print_r($detail);
        } else {
            $beforeQuarter = $condition['QuarterFrom'];
            $year = $condition['YearFrom'];
            $beforeyear = $condition['YearFrom'];
            
              if ($condition['QuarterFrom'] == 1) {
                $montharr = [10, 11, 12];
                $beforemontharr = [7, 8, 9];
                $year--;
                $beforeyear--;
                $beforeQuarter = 4;
            } else if ($condition['QuarterFrom'] == 2) {
                $montharr = [1, 2, 3];
                $beforemontharr = [10, 11, 12];
                $beforeyear--;
                $loop = [10, 11, 12, 1, 2, 3];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 3) {
                $montharr = [4, 5, 6];
                $beforemontharr = [1, 2, 3];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6];
                $beforeQuarter--;
            } else if ($condition['QuarterFrom'] == 4) {
                $montharr = [7, 8, 9];
                $beforemontharr = [4, 5, 6];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                $beforeQuarter--;
            }

            $position = 1;
            //    $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '2.1 ผลการดำเนินงานด้านอุตสาหกรรมนม รายภาค');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ไตรมาสที่ ' . $beforeQuarter . ' ' . ($year + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
            $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');
            $objPHPExcel->getActiveSheet()->setCellValue('K5', 'ไตรมาสที่1 - ' . 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');


            foreach ($FactoryList as $id) {
                $data = ProductionInfoController::getQreportforsubcom($condition, $id['id']);
                array_push($detail, $data);
                $data2 = ProductionSaleInfoController::getQreportforsubcom($condition, $id['id']);
                array_push($detail2, $data2);
            }
        }
//        print_r($beforemontharr);
//        การผลิต
        $summary = $detail[0]['DataList']['product'];
        $total['BeforemonthAmount'] = 0;
        $total['target'] = 0;
        $total['CurrentAmount'] = 0;
        $total['BeforeyearAmount'] = 0;
        $total['Yeartarget'] = 0;
        $total['targetoct'] = 0;
        $total['collectoct'] = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '1.การผลิต');
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
        $row++;
        foreach ($detail as $keydetail => $m) {
            $total['BeforemonthAmount'] += $m['DataList']['BeforemonthAmount'];
            $total['target'] += $m['DataList']['target'];
            $total['CurrentAmount'] += $m['DataList']['CurrentAmount'];
            $total['BeforeyearAmount'] += $m['DataList']['BeforeyearAmount'];
            $total['Yeartarget'] += $m['DataList']['Yeartarget'];
            $total['targetoct'] += $m['DataList']['targetoct'];
            $total['collectoct'] += $m['DataList']['collectoct'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $m['DataList']['BeforemonthAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $m['DataList']['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $m['DataList']['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $m['DataList']['percentTarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $m['DataList']['BeforeyearAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $m['DataList']['percentDiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $m['DataList']['factory_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $m['DataList']['Yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $m['DataList']['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $m['DataList']['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $m['DataList']['percentoct']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('H' . ($row))
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                            )
            );
            $row++;

//               
            foreach ($m['DataList']['product'] as $key => $itemdata) {
                if ($keydetail > 0) {
                    $summary[$key]['BeforemonthAmount'] += $itemdata['BeforemonthAmount'];
                    $summary[$key]['target'] += $itemdata['target'];
                    $summary[$key]['CurrentAmount'] += $itemdata['CurrentAmount'];
                    $summary[$key]['BeforeyearAmount'] += $itemdata['BeforeyearAmount'];
                    $summary[$key]['Yeartarget'] += $itemdata['Yeartarget'];
                    $summary[$key]['targetoct'] += $itemdata['targetoct'];
                    $summary[$key]['collectoct'] += $itemdata['collectoct'];
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $itemdata['BeforemonthAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $itemdata['target']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $itemdata['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $itemdata['percentTarget']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $itemdata['BeforeyearAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $itemdata['percentDiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '    - ' . $itemdata['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $itemdata['Yeartarget']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $itemdata['targetoct']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $itemdata['collectoct']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $itemdata['percentoct']);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('H' . ($row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            ),
                                )
                );
                $row++;
            }
        }
        if ($total['target'] > 0) {
            $total['percentTarget'] = ($total['CurrentAmount'] * 100) / $total['target'];
        } else {
            $total['percentTarget'] = 100;
        }
        if ($total['BeforeyearAmount'] > 0) {
            $total['percentDiffAmount'] = (($total['CurrentAmount'] - $total['BeforeyearAmount']) * 100) / $total['BeforeyearAmount'];
        } else {
            $total['percentDiffAmount'] = 100;
        }
        if ($total['targetoct'] > 0) {
            $total['percentoct'] = ($total['collectoct'] * 100) / $total['targetoct'];
        } else {
            $total['percentoct'] = 100;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $total['BeforemonthAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $total['target']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $total['CurrentAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $total['percentTarget']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $total['BeforeyearAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $total['percentDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $total['Yeartarget']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $total['targetoct']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $total['collectoct']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $total['percentoct']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('H' . ($row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );
        $objPHPExcel->getActiveSheet()
                ->getStyle('G' . ($row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );
        $row++;

        foreach ($summary as $sum) {
            if ($sum['target'] > 0) {
                $sum['percentTarget'] = ($sum['CurrentAmount'] * 100) / $sum['target'];
            }
            if ($sum['BeforeyearAmount'] > 0) {
                $sum['percentDiffAmount'] = (($sum['CurrentAmount'] - $sum['BeforeyearAmount']) * 100) / $sum['BeforeyearAmount'];
            }
            if ($sum['targetoct'] > 0) {
                $sum['percentoct'] = ($sum['collectoct'] * 100) / $sum['targetoct'];
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $sum['BeforemonthAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $sum['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $sum['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $sum['percentTarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $sum['BeforeyearAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sum['percentDiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '  - ' . $sum['ProductionInfoName']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $sum['Yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $sum['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $sum['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $sum['percentoct']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('H' . ($row))
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                            )
            );
            $row++;
        }
        //        การจำหน่าบ
        $summary2 = $detail2[0]['DataList']['product'];
        $total2['BeforemonthAmount'] = 0;
        $total2['target'] = 0;
        $total2['CurrentAmount'] = 0;
        $total2['BeforeyearAmount'] = 0;
        $total2['Yeartarget'] = 0;
        $total2['targetoct'] = 0;
        $total2['collectoct'] = 0;
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '2. การจำหน่ายผลิตภัณฑ์นม (ตัน)');
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
        $row++;
        foreach ($detail2 as $keydetail => $m) {
            $total2['BeforemonthAmount'] += $m['DataList']['BeforemonthAmount'];
            $total2['target'] += $m['DataList']['target'];
            $total2['CurrentAmount'] += $m['DataList']['CurrentAmount'];
            $total2['BeforeyearAmount'] += $m['DataList']['BeforeyearAmount'];
            $total2['Yeartarget'] += $m['DataList']['Yeartarget'];
            $total2['targetoct'] += $m['DataList']['targetoct'];
            $total2['collectoct'] += $m['DataList']['collectoct'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $m['DataList']['BeforemonthAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $m['DataList']['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $m['DataList']['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $m['DataList']['percentTarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $m['DataList']['BeforeyearAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $m['DataList']['percentDiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $m['DataList']['factory_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $m['DataList']['Yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $m['DataList']['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $m['DataList']['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $m['DataList']['percentoct']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('H' . ($row))
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                            )
            );
            $row++;

//               
            foreach ($m['DataList']['product'] as $key => $itemdata) {
                if ($keydetail > 0) {
                    $summary2[$key]['BeforemonthAmount'] += $itemdata['BeforemonthAmount'];
                    $summary2[$key]['target'] += $itemdata['target'];
                    $summary2[$key]['CurrentAmount'] += $itemdata['CurrentAmount'];
                    $summary2[$key]['BeforeyearAmount'] += $itemdata['BeforeyearAmount'];
                    $summary2[$key]['Yeartarget'] += $itemdata['Yeartarget'];
                    $summary2[$key]['targetoct'] += $itemdata['targetoct'];
                    $summary2[$key]['collectoct'] += $itemdata['collectoct'];
                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $itemdata['BeforemonthAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $itemdata['target']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $itemdata['CurrentAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $itemdata['percentTarget']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $itemdata['BeforeyearAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $itemdata['percentDiffAmount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '    - ' . $itemdata['ProductionInfoName']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');
                $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $itemdata['Yeartarget']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $itemdata['targetoct']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $itemdata['collectoct']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $itemdata['percentoct']);
                $objPHPExcel->getActiveSheet()
                        ->getStyle('H' . ($row))
                        ->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                            ),
                                )
                );
                $row++;
            }
        }
        if ($total2['target'] > 0) {
            $total2['percentTarget'] = ($total2['CurrentAmount'] * 100) / $total2['target'];
        } else {
            $total2['percentTarget'] = 100;
        }
        if ($total2['BeforeyearAmount'] > 0) {
            $total2['percentDiffAmount'] = (($total2['CurrentAmount'] - $total2['BeforeyearAmount']) * 100) / $total2['BeforeyearAmount'];
        } else {
            $total2['percentDiffAmount'] = 100;
        }
        if ($total2['targetoct'] > 0) {
            $total2['percentoct'] = ($total2['collectoct'] * 100) / $total2['targetoct'];
        } else {
            $total2['percentoct'] = 100;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $total2['BeforemonthAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $total2['target']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $total2['CurrentAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $total2['percentTarget']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $total2['BeforeyearAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $total2['percentDiffAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $total2['Yeartarget']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $total2['targetoct']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $total2['collectoct']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $total2['percentoct']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('H' . ($row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );
        $objPHPExcel->getActiveSheet()
                ->getStyle('G' . ($row))
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );
        $row++;

        foreach ($summary as $sum) {
            if ($sum['target'] > 0) {
                $sum['percentTarget'] = ($sum['CurrentAmount'] * 100) / $sum['target'];
            }
            if ($sum['BeforeyearAmount'] > 0) {
                $sum['percentDiffAmount'] = (($sum['CurrentAmount'] - $sum['BeforeyearAmount']) * 100) / $sum['BeforeyearAmount'];
            }
            if ($sum['targetoct'] > 0) {
                $sum['percentoct'] = ($sum['collectoct'] * 100) / $sum['targetoct'];
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $sum['BeforemonthAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $sum['target']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $sum['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $sum['percentTarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $sum['BeforeyearAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sum['percentDiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '  - ' . $sum['ProductionInfoName']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $sum['Yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $sum['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $sum['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $sum['percentoct']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
                    ->getStyle('H' . ($row))
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        ),
                            )
            );
            $row++;
        }
        // header style

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:L2');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(14);

        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L5')->getFont()->setSize(16);


        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:L5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('B6:L' . ($row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . ($row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6:L' . ($row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . ( $row - 1 ))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    ),
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $header, $detail, $total);
        return $objPHPExcel;
    }

    private function generatesheet5($objPHPExcel, $condition, $header, $detailsheet4, $total) {
        $objPHPExcel->createSheet(4);
        $objPHPExcel->setActiveSheetIndex(4);
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $FactoryList = FactoryService::getList();
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 6");
        // $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2.2 การสูญเสียน้ำนมสดและผลิตภัณฑ์นมของสำนักงานภาค');
        $data = [];
        $data2 = [];
        $data3 = [];



        if ($condition['DisplayType'] == 'annually') {
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ผลการดำเนินงาน ปี ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'สะสม ' . $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:N4');
            foreach ($FactoryList as $key => $fac) {

                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($monthList as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
            foreach ($FactoryList as $key => $fac) {

                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($monthList as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
        } else if ($condition['DisplayType'] == 'monthly') {
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ผลการดำเนินงาน เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'สะสม ' . $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:N4');
            foreach ($FactoryList as $key => $fac) {

                $dataIN = LostInProcessService::getMainListreport($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'], $condition['MonthFrom'], $fac['id']);

                array_push($data, $dataIN);
                array_push($data2, $dataOUT);
                array_push($data3, $dataWAIT);
            }
            foreach ($FactoryList as $key => $fac) {
                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($monthList as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];


                    if ($ml == $condition['MonthFrom']) {
                        break;
                    }
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
        } else {
            $year = $condition['YearFrom'];


            if ($condition['QuarterFrom'] == 1) {
                $montharr = [10, 11, 12];
                $year--;
                $loop = [10, 11, 12];
            } else if ($condition['QuarterFrom'] == 2) {
                $montharr = [1, 2, 3];

                $loop = [10, 11, 12, 1, 2, 3];
            } else if ($condition['QuarterFrom'] == 3) {
                $montharr = [4, 5, 6];

                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6];
            } else if ($condition['QuarterFrom'] == 4) {
                $montharr = [7, 8, 9];

                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ผลการดำเนินงาน ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'สะสม ไตรมาสที่ 1 - ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:N4');
            foreach ($FactoryList as $key => $fac) {

                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($montharr as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($year, $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($year, $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($year, $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
            foreach ($FactoryList as $key => $fac) {

                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($loop as $key => $ml) {

                    $dataIN = LostInProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail['sum_amount'] += $dataIN['sum_amount'];
                    $detail['sum_baht'] += $dataIN['sum_baht'];
                    $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail2['sum_amount'] += $dataOUT['sum_amount'];
                    $detail2['sum_baht'] += $dataOUT['sum_baht'];

                    $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $detail3['sum_amount'] += $dataWAIT['sum_amount'];
                    $detail3['sum_baht'] += $dataWAIT['sum_baht'];
                }
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
        }




        $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
        $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
        $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', 'สภก.');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', 'สภต.');
        $objPHPExcel->getActiveSheet()->setCellValue('C5', 'สภอ.');
        $objPHPExcel->getActiveSheet()->setCellValue('D5', 'สภน.');
        $objPHPExcel->getActiveSheet()->setCellValue('E5', 'สภน.');
        $objPHPExcel->getActiveSheet()->setCellValue('F5', 'รวม');
        $objPHPExcel->getActiveSheet()->setCellValue('I5', 'สภก.');
        $objPHPExcel->getActiveSheet()->setCellValue('J5', 'สภต.');
        $objPHPExcel->getActiveSheet()->setCellValue('K5', 'สภอ.');
        $objPHPExcel->getActiveSheet()->setCellValue('L5', 'สภน.');
        $objPHPExcel->getActiveSheet()->setCellValue('M5', 'สภน.');
        $objPHPExcel->getActiveSheet()->setCellValue('N5', 'รวม');
        ///actuarper
        $asri = 0;
        $apri = 0;
        $akkn = 0;
        $asti = 0;
        $acnx = 0;

        $asri2 = 0;
        $apri2 = 0;
        $akkn2 = 0;
        $asti2 = 0;
        $acnx2 = 0;

        $asri3 = 0;
        $apri3 = 0;
        $akkn3 = 0;
        $asti3 = 0;
        $acnx3 = 0;


        if ($detailsheet4[0]['DataList']['CurrentAmount'] > 0) {
            $asri = ($data[0]['sum_amount'] * 100) / $detailsheet4[0]['DataList']['CurrentAmount'];
            $asri2 = ($data2[0]['sum_amount'] * 100) / $detailsheet4[0]['DataList']['CurrentAmount'];
            $asri3 = ($data3[0]['sum_amount'] * 100) / $detailsheet4[0]['DataList']['CurrentAmount'];
        }
        if ($detail[1]['DataList']['collectoct'] > 0) {
            $apri = ($data[1]['sum_amount'] * 100) / $detailsheet4[1]['DataList']['CurrentAmount'];
            $apri2 = ($data2[1]['sum_amount'] * 100) / $detailsheet4[1]['DataList']['CurrentAmount'];
            $apri3 = ($data3[1]['sum_amount'] * 100) / $detailsheet4[1]['DataList']['CurrentAmount'];
        }
        if ($detail[2]['DataList']['collectoct'] > 0) {
            $akkn = ($data[2]['sum_amount'] * 100) / $detailsheet4[2]['DataList']['CurrentAmount'];
            $akkn2 = ($data2[2]['sum_amount'] * 100) / $detailsheet4[2]['DataList']['CurrentAmount'];
            $akkn3 = ($data3[2]['sum_amount'] * 100) / $detailsheet4[2]['DataList']['CurrentAmount'];
        }
        if ($detail[3]['DataList']['collectoct'] > 0) {
            $asri = ($data[3]['sum_amount'] * 100) / $detailsheet4[3]['DataList']['CurrentAmount'];
            $asri2 = ($data2[3]['sum_amount'] * 100) / $detailsheet4[3]['DataList']['CurrentAmount'];
            $asri3 = ($data3[3]['sum_amount'] * 100) / $detailsheet4[3]['DataList']['CurrentAmount'];
        }
        if ($detail[4]['DataList']['collectoct'] > 0) {
            $acnx = ($data[4]['sum_amount'] * 100) / $detailsheet4[4]['DataList']['CurrentAmount'];
            $acnx2 = ($data2[4]['sum_amount'] * 100) / $detailsheet4[4]['DataList']['CurrentAmount'];
            $acnx3 = ($data3[4]['sum_amount'] * 100) / $detailsheet4[4]['DataList']['CurrentAmount'];
        }
//////
///collectoctper
        $sri = 0;
        $pri = 0;
        $kkn = 0;
        $sti = 0;
        $cnx = 0;

        $sri2 = 0;
        $pri2 = 0;
        $kkn2 = 0;
        $sti2 = 0;
        $cnx2 = 0;

        $sri3 = 0;
        $pri3 = 0;
        $kkn3 = 0;
        $sti3 = 0;
        $cnx3 = 0;
        if ($detail[0]['DataList']['collectoct'] > 0) {
            $sri = ($data[5]['sum_amount'] * 100) / $detail[0]['DataList']['collectoct'];
            $sri2 = ($data2[5]['sum_amount'] * 100) / $detail[0]['DataList']['collectoct'];
            $sri3 = ($data3[5]['sum_amount'] * 100) / $detail[0]['DataList']['collectoct'];
        }
        if ($detail[1]['DataList']['collectoct'] > 0) {
            $pri = ($data[6]['sum_amount'] * 100) / $detail[1]['DataList']['collectoct'];
            $pri2 = ($data2[6]['sum_amount'] * 100) / $detail[1]['DataList']['collectoct'];
            $pri3 = ($data3[6]['sum_amount'] * 100) / $detail[1]['DataList']['collectoct'];
        }
        if ($detail[2]['DataList']['collectoct'] > 0) {
            $kkn = ($data[7]['sum_amount'] * 100) / $detail[2]['DataList']['collectoct'];
            $kkn2 = ($data2[7]['sum_amount'] * 100) / $detail[2]['DataList']['collectoct'];
            $kkn3 = ($data3[7]['sum_amount'] * 100) / $detail[2]['DataList']['collectoct'];
        }
        if ($detail[3]['DataList']['collectoct'] > 0) {
            $sri = ($data[8]['sum_amount'] * 100) / $detail[3]['DataList']['collectoct'];
            $sri2 = ($data2[8]['sum_amount'] * 100) / $detail[3]['DataList']['collectoct'];
            $sri3 = ($data3[8]['sum_amount'] * 100) / $detail[3]['DataList']['collectoct'];
        }
        if ($detail[4]['DataList']['collectoct'] > 0) {
            $cnx = ($data[9]['sum_amount'] * 100) / $detail[4]['DataList']['collectoct'];
            $cnx2 = ($data2[9]['sum_amount'] * 100) / $detail[4]['DataList']['collectoct'];
            $cnx3 = ($data3[9]['sum_amount'] * 100) / $detail[4]['DataList']['collectoct'];
        }
//////
        $sumam = $data[0]['sum_amount'] + $data[1]['sum_amount'] + $data[2]['sum_amount'] + $data[3]['sum_amount'] + $data[4]['sum_amount'];
        $sumba = ($data[0]['sum_baht'] + $data[1]['sum_baht'] + $data[2]['sum_baht'] + $data[3]['sum_baht'] + $data[4]['sum_baht']) / 1000000;
        $bsumam = $data[5]['sum_amount'] + $data[6]['sum_amount'] + $data[7]['sum_amount'] + $data[8]['sum_amount'] + $data[9]['sum_amount'];
        $bsumba = ($data[5]['sum_baht'] + $data[6]['sum_baht'] + $data[7]['sum_baht'] + $data[8]['sum_baht'] + $data[9]['sum_baht']) / 1000000;
        $sumam2 = $data2[0]['sum_amount'] + $data2[1]['sum_amount'] + $data2[2]['sum_amount'] + $data2[3]['sum_amount'] + $data2[4]['sum_amount'];
        $sumba2 = ($data2[0]['sum_baht'] + $data2[1]['sum_baht'] + $data2[2]['sum_baht'] + $data2[3]['sum_baht'] + $data2[4]['sum_baht']) / 1000000;
        $bsumam2 = $data2[5]['sum_amount'] + $data2[6]['sum_amount'] + $data2[7]['sum_amount'] + $data2[8]['sum_amount'] + $data2[9]['sum_amount'];
        $bsumba2 = ($data2[5]['sum_baht'] + $data2[6]['sum_baht'] + $data2[7]['sum_baht'] + $data2[8]['sum_baht'] + $data2[9]['sum_baht']) / 1000000;
        $sumam3 = $data3[0]['sum_amount'] + $data3[1]['sum_amount'] + $data3[2]['sum_amount'] + $data3[3]['sum_amount'] + $data3[4]['sum_amount'];
        $sumba3 = ($data3[0]['sum_baht'] + $data3[1]['sum_baht'] + $data3[2]['sum_baht'] + $data3[3]['sum_baht'] + $data3[4]['sum_baht']) / 1000000;
        $bsumam3 = $data3[5]['sum_amount'] + $data3[6]['sum_amount'] + $data3[7]['sum_amount'] + $data3[8]['sum_amount'] + $data3[9]['sum_amount'];
        $bsumba3 = ($data3[5]['sum_baht'] + $data3[6]['sum_baht'] + $data3[7]['sum_baht'] + $data3[8]['sum_baht'] + $data3[9]['sum_baht']) / 1000000;

        $sumAin = 0;
        $sumAout = 0;
        $sumAw = 0;
        if ($total['CurrentAmount'] > 0) {
            $sumAin = ($sumam * 100) / $total['CurrentAmount'];
            $sumAout = ($sumam2 * 100) / $total['CurrentAmount'];
            $sumAw = ($sumam3 * 100) / $total['CurrentAmount'];
        }
        $sumOCTin = 0;
        $sumOCTout = 0;
        $sumOCTw = 0;
        if ($total['collectoct'] > 0) {
            $sumOCTin = ($sumam * 100) / $total['collectoct'];
            $sumOCTout = ($sumam2 * 100) / $total['collectoct'];
            $sumOCTw = ($sumam3 * 100) / $total['collectoct'];
        }
        ////////       
        $row = 6;
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '1.การสูญเสีย');
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[0]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[1]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[2]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[3]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[4]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '   1.1 สูญเสียในกระบวนการผลิต ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[5]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[6]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[7]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[8]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data[9]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumam);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[0]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[1]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[2]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[3]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[4]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumba);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           มูลค่า ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ล้านบาท');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[5]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[6]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[7]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[8]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data[9]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumba);
        $row++;



        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $asri);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $apri);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $akkn);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $asti);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $acnx);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumAin);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           %สูญเสีย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $sri);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $pri);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $kkn);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $sti);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $cnx);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $sumOCTin);
        $row++;
////
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data2[0]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data2[1]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data2[2]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data2[3]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data2[4]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam2);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '    1.2 สูญเสียหลังกระบวนการผลิต  ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data2[5]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data2[6]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data2[7]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data2[8]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data2[9]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumam2);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data2[0]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data2[1]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data2[2]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data2[3]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data2[4]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumba2);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           มูลค่า ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ล้านบาท');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data2[5]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data2[6]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data2[7]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data2[8]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data2[9]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumba2);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $asri2);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $apri2);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $akkn2);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $asti2);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $acnx2);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumAout);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           %สูญเสีย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $sri2);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $pri2);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $kkn2);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $sti2);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $cnx2);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $sumOCTout);
        $row++;
////
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data3[0]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data3[1]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data3[2]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data3[3]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data3[4]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '   1.3 สูญเสียระหว่างรอจำหน่าย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data3[5]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data3[6]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data3[7]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data3[8]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data3[9]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumam3);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data3[0]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data3[1]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data3[2]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data3[3]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data3[4]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumba3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           มูลค่า ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ล้านบาท');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data3[5]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data3[6]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data3[7]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data3[8]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data3[9]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumba3);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $asri3);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $apri3);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $akkn3);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $asti3);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $acnx3);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumAw);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           %สูญเสีย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $sri3);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $pri3);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $kkn3);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $sti3);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $cnx3);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $sumOCTw);
        $row++;
////
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[0]['sum_amount'] + $data2[0]['sum_amount'] + $data3[0]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[1]['sum_amount'] + $data2[1]['sum_amount'] + $data3[1]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[2]['sum_amount'] + $data2[2]['sum_amount'] + $data3[2]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[3]['sum_amount'] + $data2[3]['sum_amount'] + $data3[3]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[4]['sum_amount'] + $data2[4]['sum_amount'] + $data3[4]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam + $sumam2 + $sumam3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '   1.4 สูญเสียทั้งกระบวนการผลิต  ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[5]['sum_amount'] + $data2[5]['sum_amount'] + $data3[5]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[6]['sum_amount'] + $data2[6]['sum_amount'] + $data3[6]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[7]['sum_amount'] + $data2[7]['sum_amount'] + $data3[7]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[8]['sum_amount'] + $data2[8]['sum_amount'] + $data3[8]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data[9]['sum_amount'] + $data2[9]['sum_amount'] + $data3[9]['sum_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumam + $bsumam2 + $bsumam3);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[0]['sum_baht'] + $data2[0]['sum_baht'] + $data3[0]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[1]['sum_baht'] + $data2[1]['sum_baht'] + $data3[1]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[2]['sum_baht'] + $data2[2]['sum_baht'] + $data3[2]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[3]['sum_baht'] + $data2[3]['sum_baht'] + $data3[3]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[4]['sum_baht'] + $data2[4]['sum_baht'] + $data3[4]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumba + $sumba2 + $sumba3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           มูลค่า ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ล้านบาท');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[5]['sum_baht'] + $data2[5]['sum_baht'] + $data3[5]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[6]['sum_baht'] + $data2[6]['sum_baht'] + $data3[6]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[7]['sum_baht'] + $data2[7]['sum_baht'] + $data3[7]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[8]['sum_baht'] + $data2[8]['sum_baht'] + $data3[8]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data[9]['sum_baht'] + $data2[9]['sum_baht'] + $data3[9]['sum_baht'] / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumba + $bsumba2 + $bsumba3);
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $asri + $asri2 + $asri3);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $apri + $apri2 + $apri3);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $akkn + $akkn2 + $akkn3);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $asti + $asti2 + $asti3);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $acnx + $acnx2 + $acnx3);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumAin + $sumAout + $sumAw);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           %สูญเสีย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), '');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $sri + $sri2 + $sri3);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $pri + $pri2 + $pri3);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $kkn + $kkn2 + $kkn3);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $sti + $sti2 + $sti3);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $cnx + $cnx2 + $cnx3);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $sumOCTin + $sumOCTout + $sumOCTw);
        $row++;
////
        // header style

        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:L2');
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(16);

        $objPHPExcel->getActiveSheet()->getStyle('A4:N5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:N5')->getFont()->setSize(14);


        $objPHPExcel->getActiveSheet()
                ->getStyle('A4:N5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);

        $objPHPExcel->getActiveSheet()->getStyle('A4:N' . ($row ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A6:N' . ($row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A4:N' . ($row - 1 ))->applyFromArray(
                array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => (\PHPExcel_Style_Border::BORDER_THIN)
                        )
                    ),
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );

////
        //  $postion = $_SESSION["postion"];
        $hrow = $_SESSION["row"];
        session_destroy();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($hrow), '  ' . 'ปริมาณสูญเสียทั้งกระบวนการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($hrow), 'ตัน');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($hrow), '-');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($hrow), $bsumam + $bsumam2 + $bsumam3);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($hrow), '-');

        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($hrow + 1), '  ' . '% ของการสูญเสียของทั้ง 3 กระบวนการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($hrow + 1), '%');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($hrow + 1), '-');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($hrow + 1), '');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($hrow + 1), '-');

        $objPHPExcel->getActiveSheet()->getStyle('A' . ($hrow) . ':E' . ($hrow + 1))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($hrow) . ':E' . ($hrow + 1))->getFont()->setBold(true);
////

        return $objPHPExcel;
    }

}
