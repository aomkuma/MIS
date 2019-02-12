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
                    break;
                case 'monthly' :$header = 'สรุปรายงานผลการดำเนินงานประจำเดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ปี ' . ($condition['YearTo'] + 543);
                    $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
                    break;
                case 'quarter' :$header = 'สรุปรายงานผลการดำเนินงานประจำ ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543);
                    $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $header);
                    $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
                    break;

                default : $result = null;
            }

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

        $mastername = ['สัตวแพท', 'ผสมเทียม', 'ผลิตน้ำนม', 'ผลิตน้ำเชื้อแช่แข็ง', 'แร่ธาตุ พรีมิกซ์ และอาหาร', 'ปัจจัยการเลี้ยงโค', 'ฝึกอบรม', 'จำหน่ายน้ำเชื้อแช่แข็ง'];




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
            $objPHPExcel->getActiveSheet()->setCellValue('C5', ($condition['YearFrom'] - 1957));
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
                            case 'สัตวแพท' :
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
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), ($position + $key) . '.' . $item);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setSize(14);
                $objPHPExcel->getActiveSheet()->getStyle('A' . (6 + $row))->getFont()->setBold(true);
                $row++;
//                $detail['data'] = [];
                foreach ($mastes as $keyitem => $itemmaster) {
                    $subposition = 1;
                    $mission = GoalMissionService::getMission($itemmaster['id'], 3, $condition['YearTo']);

                    $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);
                    switch ($itemmaster['menu_type']) {
                        case 'ผสมเทียม' :
                            $actually = InseminationService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], 3);
                            break;
                        case 'สัตวแพท' :
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

                        default : $result = null;
                    }

                    $detail2['mission'] = $itemmaster['goal_name'];
                    $detail2['unit'] = $mission[0]['unit'];
                    $detail2['target'] = $avg[0]['amount'];
                    $detail2['actual'] = $actually['amount'];
                    if ($detail2['target'] > 0) {
                        $detail2['percen'] = ($detail2['actual'] * 100) / $detail2['target'];
                    } else {
                        $detail2['percen'] = 0;
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), '  ' . ($position + $key) . '.' . ($subposition + $keyitem) . ' ' . $detail2['mission']);
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
                    // array_push($detail['data'], $detail2);
                }
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

            foreach ($mastername as $item) {
                $mastes = MasterGoalService::getList('Y', $item);
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
                            case 'สัตวแพท' :
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


        foreach ($data as $key => $itemdata) {
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
        $objPHPExcel->getActiveSheet()->getStyle('A4:E' . (6 + $row - 1))->applyFromArray(
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

            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item);
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
                        $detail['beforemonth']['price_value'] += $beforeavg[0]['price_value']/ 1000000;
                        $detail['target']['amount'] += $avg[0]['amount'];
                        $detail['target']['price_value'] += $avg[0]['price_value']/ 1000000;
                        $detail['collectmonth']['amount'] += $actually['amount'];
                        $detail['collectmonth']['price_value'] += $actually['price']/ 1000000;
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

            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item);
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
                    $detail['beforemonth']['price_value'] += $beforeavg[0]['price_value']/ 1000000;
                    $detail['target']['amount'] += $avg[0]['amount'];
                    $detail['target']['price_value'] += $avg[0]['price_value']/ 1000000;
                    $detail['collectmonth']['amount'] += $actually['amount'];
                    $detail['collectmonth']['price_value'] += $actually['price']/ 1000000;
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
                    $detail['beforeyear']['price_value'] += $beforeactually['price']/ 1000000;;
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
                        $detail['collectoct']['price_value'] += $actually['price']/ 1000000;;
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
            } else if ($condition['QuarterFrom'] == 3) {
                $montharr = [4, 5, 6];
                $beforemontharr = [1, 2, 3];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6];
            } else if ($condition['QuarterFrom'] == 4) {
                $montharr = [7, 8, 9];
                $beforemontharr = [4, 5, 6];
                $loop = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
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


            foreach ($mastername as $key => $item) {
                $mastes = MasterGoalService::getList('Y', $item);
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
                        $detail['beforemonth']['price_value'] += $beforeavg[0]['price_value']/ 1000000;;
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
                        $detail['target']['price_value'] += $avg[0]['price_value']/ 1000000;;
                        $detail['collectmonth']['amount'] += $actually['amount'];
                        $detail['collectmonth']['price_value'] += $actually['price']/ 1000000;;
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
                        $detail['beforeyear']['price_value'] += $beforeactually['price']/ 1000000;;
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
                        $detail['yeartarget']['price_value'] += $mission[0]['price_value']/ 1000000;;

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
                            $detail['collectoct']['price_value'] += $actually['price']/ 1000000;;
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
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   '.$itemdata['unit']);
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

}
