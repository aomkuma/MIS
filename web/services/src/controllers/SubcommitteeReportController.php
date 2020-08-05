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
use App\Service\MaterialService;
use App\Service\ProductMilkService;

use PHPExcel;

class SubcommitteeReportController extends Controller {

    protected $logger;
    protected $db;
    protected $total_loss_amount;
    protected $total_loss_amount_percent;

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

            $years = $condition['YearTo'];
            $months = $condition['MonthFrom'];
            $quarters = $condition['QuarterFrom'];
            $time_list = [];

            if ($condition['DisplayType'] == 'annually') {
                $time_list = [
                                [
                                    'year'=>$years - 1,
                                    'month'=>10
                                ],
                                [
                                    'year'=>$years - 1,
                                    'month'=>11
                                ],
                                [
                                    'year'=>$years - 1,
                                    'month'=>12
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>1
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>2
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>3
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>4
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>5
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>6
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>7
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>8
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>9
                                ]
                            ];
            }else if($condition['DisplayType'] == 'monthly'){
                $time_list = [
                                [
                                    'year'=>$months > 9 ? $years - 1 : $years,
                                    'month'=>$months
                                ]
                            ];
            }else{
                if($condition['QuarterFrom'] == 1){
                     $time_list = [
                                [
                                    'year'=>$years - 1,
                                    'month'=>10
                                ],
                                [
                                    'year'=>$years - 1,
                                    'month'=>11
                                ],
                                [
                                    'year'=>$years - 1,
                                    'month'=>12
                                ]
                            ];
                }else if($condition['QuarterFrom'] == 2){
                     $time_list = [
                                [
                                    'year'=>$years,
                                    'month'=>1
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>2
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>3
                                ]
                            ];
                }else if($condition['QuarterFrom'] == 3){
                     $time_list = [
                                [
                                    'year'=>$years,
                                    'month'=>4
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>5
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>6
                                ]
                            ];
                }else if($condition['QuarterFrom'] == 4){
                     $time_list = [
                                [
                                    'year'=>$years,
                                    'month'=>7
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>8
                                ],
                                [
                                    'year'=>$years,
                                    'month'=>9
                                ]
                            ];
                }
            }

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'สรุปรายงานผลการดำเนินงานประจำ ปี ' . ($condition['YearTo'] + 543);
                    break;
                case 'monthly' :$header = 'สรุปรายงานผลการดำเนินงานประจำเดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ปี ' . ($condition['YearTo'] + 543);
                    //  $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $header);
                    break;
                case 'quarter' :$header = 'สรุปรายงานผลการดำเนินงานประจำ ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearTo'] + 543);
                    //   $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $header);
                    break;

                default : $result = null;
            }

            $objPHPExcel = $this->generatesheet1($objPHPExcel, $condition, $time_list, $header);
            $objPHPExcel = $this->generatesheet2($objPHPExcel, $condition, $header);
            $objPHPExcel = $this->generatesheet3($objPHPExcel, $condition, $header);
            $objPHPExcel = $this->generatesheet4($objPHPExcel, $condition, $time_list, $header);
            $objPHPExcel = $this->generatesheet5($objPHPExcel, $condition, $time_list, $header);
//            
//            die();
            // $filename = 'MIS_Report-รายงานรายเดือน' . '_' . date('YmdHis') . '.xlsx';

            // set total loss value is summary sheet
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('A19', '14. ปริมาณสูญเสียทั้งกระบวนการ');
            $objPHPExcel->getActiveSheet()->setCellValue('B19', ' ตัน');
            $objPHPExcel->getActiveSheet()->setCellValue('D19', number_format($this->total_loss_amount, 2, '.', ''));
            $objPHPExcel->getActiveSheet()->setCellValue('A20', '15. % ของการสูญเสีย');
            $objPHPExcel->getActiveSheet()->setCellValue('B20', ' %');
            $objPHPExcel->getActiveSheet()->setCellValue('D20', number_format($this->total_loss_amount_percent, 2, '.', ''));

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

    private function generatesheet1($objPHPExcel, $condition, $time_list, $header) {
        $objPHPExcel->getActiveSheet()->setTitle("สรุป");
        $mastername = ['การบริการสัตวแพทย์', 'การบริการผสมเทียม', 'การผลิตน้ำนมของฟาร์ม อ.ส.ค.', 'ปริมาณการจำหน่ายแร่ธาตุ', 'ปริมาณการจำหน่ายอาหารสัตว์อื่นๆ', 'การฝึกอบรม', 'รายได้จากน้ำเชื้อแช่แข็ง', 'รายได้อื่นๆ จากการจำหน่ายไนโตรเจนเหลวและปัจจัยการเลี้ยงโคนม', 'บริการชมฟาร์มโคนมฯ', 'ปริมาณการรับซื้อน้ำนม', 'ปริมาณน้ำนมดิบเข้ากระบวนการผลิต', 'ปริมาณการผลิตผลิตภัณฑ์นม', 'ปริมาณการจำหน่าย'];

        $row = 0;
        $position = 1;

        if($condition['DisplayType'] == 'annually'){
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'เป้าหมาย');
            // $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ปี ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ปี ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('C4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');

        }else if($condition['DisplayType'] == 'monthly'){

            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'เป้าหมาย');
            // $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('C4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');

        }else{

            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'กิจกรรม');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'หน่วย');
            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] - 1957));
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('C4:E4');
            $objPHPExcel->getActiveSheet()->setCellValue('D5', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', '%/เป้าหมาย');
            

        }
        $index = 0;
        // การบริการสัตวแพทย์
        $index++;
        $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
        $dairy_farming_id = [1,4,20];
        $goal_name = ['ควบคุมโรค','การบริการสัตว์แพทย์','การตรวจทางห้องปฏิบัติการ'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $index. '. การบริการสัตวแพทย์');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ครั้ง');

        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = VeterinaryService::getDetailmonth($y, $m, '','', $dairy_farming_id);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount);
        
        if(!empty($goal_amount)){
            $percent = number_format((($result_amount * 100) / $goal_amount), 2, '.', '');   
            // $percent = ($result_amount * 100) / $goal_amount; 
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // การบริการผสมเทียม
        $row++;
        $index++;
        $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
        $goal_name = ['การบริการผสมเทียม','ทะเบียนประวัติโค'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. การบริการผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ครั้ง');

        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = VeterinaryService::getDetailmonthInsemination($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount);

        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');   
        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // การผลิตน้ำนมของฟาร์ม อ.ส.ค.
        $row++;
        $index++;
        $menu_type = 'ข้อมูลฝูงโค';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. การผลิตน้ำนมของฟาร์ม อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = CowGroupService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');    
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // ปริมาณการจำหน่ายแร่ธาตุ
        $row++;
        $index++;
        $menu_type = 'แร่ธาตุ พรีมิกซ์ และอาหาร';
        $sub_goal_type_arr = ['พรีมิกซ์','แร่ธาตุ'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการจำหน่ายแร่ธาตุ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = MineralService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // ปริมาณการจำหน่ายอาหารสัตว์อื่นๆ
        $row++;
        $index++;
        $menu_type = 'แร่ธาตุ พรีมิกซ์ และอาหาร';
        $sub_goal_type_arr = ['อาหาร'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการจำหน่ายอาหารสัตว์อื่นๆ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = MineralService::getDetailmonthFood($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format(($goal_amount / 1000), 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format(($result_amount / 1000), 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', ''); 
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // การฝึกอบรม
        $row++;
        $index++;
        $menu_type = 'ฝึกอบรม';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. การฝึกอบรม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ราย');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = TrainingCowBreedService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount);
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // รายได้น้ำเชื้อแช่แข็ง
        $row++;
        $index++;
        $menu_type = 'จำหน่ายน้ำเชื้อแช่แข็ง';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. รายได้น้ำเชื้อแช่แข็ง');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ล้านบาท');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['price'];

            $result_data = SpermSaleService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['price']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // รายได้อื่นๆ จากการจำหน่ายไนโตรเจนเหลวและปัจจัยการเลี้ยงโคนม
        $row++;
        $index++;
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. รายได้อื่นๆ จากการจำหน่ายไนโตรเจนเหลวและปัจจัยการเลี้ยงโคนม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ล้านบาท');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $menu_type = 'วัสดุผสมเทียมและเวชภัณฑ์ยาสัตว์';
            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['price'];

            $result_data = MaterialService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['price']; 

            $menu_type = 'ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)';
            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['price'];

            $result_data = CowBreedService::getDetailmonth($y, $m, '','');
            $result_amount += $result_data['price']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // บริการชมฟาร์มโคนมฯ
        $row++;
        $index++;
        $menu_type = 'ท่องเที่ยว';
        $goal_id_list = [391,392,393,320,321,322];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. บริการชมฟาร์มโคนมฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ราย');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuTypeAndGoalID($menu_type, $goal_id_list, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = TravelService::getDetailmonth($y, $m, '',$goal_id_list);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount);
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // ปริมาณการรับซื้อน้ำนม
        $row++;
        $index++;
        $menu_type = 'รับซื้อน้ำนมดิบ (ERP)';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการรับซื้อน้ำนม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = MBIService::getListMBIreoprt2($y, $m);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // ปริมาณการจำหน่ายน้ำนม
        $row++;
        $index++;
        $menu_type = 'จำหน่ายน้ำนมดิบ (ERP)';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการจำหน่ายน้ำนม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = MSIService::getListMSIreoprt2($y, $m);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $goal_amount);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $result_amount);
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // ปริมาณการผลิตผลิตภัณฑ์นม
        $row++;
        $index++;
        $menu_type = 'ข้อมูลการผลิต';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการผลิตผลิตภัณฑ์นม');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = ProductionInfoService::getDetailList2($y, $m);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);

        // ปริมาณการจำหน่าย
        $row++;
        $index++;
        $menu_type = 'ข้อมูลการขาย';
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row),  $index. '. ปริมาณการจำหน่าย');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), ' ตัน');
        
        $goal_amount = 0;
        $result_amount = 0;
        $percent = 0;
        foreach ($time_list as $t_key => $t_value) {

            $y = $t_value['year'];
            $m = $t_value['month'];

            $avg_data = GoalMissionService::getMissionavgByMenuType($menu_type, $y, $m);
            $goal_amount += $avg_data['amount'];

            $result_data = ProductionSaleInfoService::getDetailList2($y, $m);
            $result_amount += $result_data['amount']; 

        }
        
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($goal_amount / 1000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($result_amount / 1000, 2, '.', ''));
        
        if(!empty($goal_amount)){
            $percent = number_format(($result_amount * 100) / $goal_amount, 2, '.', '');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $percent);


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

        $row++;
        $_SESSION["row"] = 6 + $row;
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('B6:E' . (6 + $row + 1))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('A4:E' . (6 + $row + 1 ))->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C6:E' . (6 + $row + 1))
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
        // $mastername = ['สัตวแพทย์', 'การบริการผสมเทียม', 'รายได้จากน้ำเชื้อแช่แข็ง', 'ผลิตน้ำเชื้อแช่แข็ง', 'ปริมาณการจำหน่ายแร่ธาตุ', 'ปัจจัยการเลี้ยงโค', 'การฝึกอบรม'/*, 'รายได้จากน้ำเชื้อแช่แข็ง'*/];
        $mastername = ['การบริการสัตวแพทย์'
                , 'การบริการผสมเทียม'
                , 'การบริการและจัดการฟาร์มสหกรณ์'
                , 'การผลิตน้ำนมของฟาร์ม อ.ส.ค.'
                , 'การจำหน่ายอาหารสัตว์'];


        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 2-3");
        $row = 0;
        $data = [];
        $detail = [];
        /*$detail['beforemonth']['amount'] = 0;
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
        $detail['unit'] = 0;*/
        if ($condition['DisplayType'] == 'monthly') {
            $beforemonth = $condition['MonthFrom'];
            if ($condition['MonthFrom'] == 1) {
                $beforemonth = 12;
                $beforeYear = $condition['YearTo'] - 1;
            } else {
                $beforemonth--;
                $beforeYear = $condition['YearTo'];
            }

            $position = 1;
            $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
            $objPHPExcel->getActiveSheet()->setCellValue('A3', '1.ผลการดำเนินงานด้านกิจการโคนม');

            $objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getMonthName($beforemonth) . ' ' . ($beforeYear + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:A5');
            
            $objPHPExcel->getActiveSheet()->setCellValue('B4',  'เดือน' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('B4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('B5',  'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5',  'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('D5',  '% เป้าหมาย');

            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'ผลงานปีที่ผ่านมา');
            $objPHPExcel->getActiveSheet()->mergeCells('E4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('E5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 542));
            $objPHPExcel->getActiveSheet()->setCellValue('F5', '%เพิ่ม/ลด ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($beforeYear + 543));
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
            
            // การบริการสัตวแพทย์

            $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
            $detail['name'] = 'การบริการสัตวแพทย์';
            $dairy_farming_id = [1,4,20];
            $goal_name = ['ควบคุมโรค','การบริการสัตว์แพทย์','การตรวจทางห้องปฏิบัติการ'];
            // result before selected month 
            $result = VeterinaryService::getDetailmonth($beforeYear, $beforemonth, '', '', $dairy_farming_id);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($beforeYear, $beforemonth, '', '', $dairy_farming_id);
            $detail['beforemonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['collectmonth']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = VeterinaryService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['beforeyear']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }
            
            $result = GoalMissionService::getMissionYearByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['YearTo'] - 1;
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = VeterinaryService::getDetailmonth($loop_year, $loop_month, '', '', $dairy_farming_id);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $result = VeterinaryService::getDetailmonthPrice($loop_year, $loop_month, '', '', $dairy_farming_id);
                $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ครั้ง';
            $detail['unit_price'] = 'ล้านบาท';
            $detail['type_amount'] = 'ปริมาณ';
            $detail['type_price'] = 'มูลค่า';
            array_push($data, $detail);
            $detail = [];

            // การบริการผสมเทียม

            $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
            $detail['name'] = 'การบริการผสมเทียม';
            $dairy_farming_id = [17, 29];
            $goal_name = ['การบริการผสมเทียม','ทะเบียนประวัติโค'];
            // result before selected month 
            $result = VeterinaryService::getDetailmonth($beforeYear, $beforemonth, '', '', $dairy_farming_id);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($beforeYear, $beforemonth, '', '', $dairy_farming_id);
            $detail['beforemonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['collectmonth']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = VeterinaryService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['beforeyear']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['YearTo'] - 1;
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = VeterinaryService::getDetailmonth($loop_year, $loop_month, '', '', $dairy_farming_id);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $result = VeterinaryService::getDetailmonthPrice($loop_year, $loop_month, '', '', $dairy_farming_id);
                $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ครั้ง';
            $detail['unit_price'] = 'ล้านบาท';
            $detail['type_amount'] = 'ปริมาณ';
            $detail['type_price'] = 'มูลค่า';
            array_push($data, $detail);

            $detail = [];

            // การบริการจัดการฟาร์มและสหกรณ์

            $menu_type = 'บริการสัตวแพทย์และผสมเทียม';
            $detail['name'] = 'การบริการจัดการฟาร์มและสหกรณ์';
            $dairy_farming_id = [13];
            $goal_name = ['DIP'];
            // result before selected month 
            $result = VeterinaryService::getDetailmonth($beforeYear, $beforemonth, '', '', $dairy_farming_id);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($beforeYear, $beforemonth, '', '', $dairy_farming_id);
            $detail['beforemonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = VeterinaryService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'], $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['collectmonth']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = VeterinaryService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $result = VeterinaryService::getDetailmonthPrice($condition['YearTo'] - 1, $condition['MonthFrom'], '', '', $dairy_farming_id);
            $detail['beforeyear']['price_value'] = empty($result['amount'])?0:$result['amount'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            $result = GoalMissionService::getMissionYearByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalName($menu_type, $goal_name, $condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['YearTo'] - 1;
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = VeterinaryService::getDetailmonth($loop_year, $loop_month, '', '', $dairy_farming_id);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $result = VeterinaryService::getDetailmonthPrice($loop_year, $loop_month, '', '', $dairy_farming_id);
                $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ครั้ง';
            $detail['unit_price'] = 'ล้านบาท';
            $detail['type_amount'] = 'ปริมาณ';
            $detail['type_price'] = 'มูลค่า';
            array_push($data, $detail);

            $detail = [];

            // การผลิตน้ำนมของฟาร์ม อ.ส.ค.

            $menu_type = 'ข้อมูลฝูงโค';
            $detail['name'] = 'การผลิตน้ำนมของฟาร์ม อ.ส.ค.';
            $dairy_farming_id = [13];
            // result before selected month 
            $result = CowGroupService::getDetailmonth($beforeYear, $beforemonth);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = CowGroupService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = CowGroupService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuType($menu_type, $condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['YearTo'] - 1;
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = CowGroupService::getDetailmonth($loop_year, $loop_month);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ตัน';
            $detail['unit_price'] = 'ล้านบาท';

            $detail['type_amount'] = 'ปริมาณ';
            $detail['type_price'] = 'มูลค่า';
            array_push($data, $detail);

            $detail = [];

            // การจำหน่ายอาหารสัตว์

            $menu_type = 'แร่ธาตุ พรีมิกซ์ และอาหาร';
            $detail['name'] = 'การจำหน่ายอาหารสัตว์ (แร่ธาตุ พรีมิกซ์)';
            $dairy_farming_id = [13];
            $sub_goal_type_arr = ['พรีมิกซ์','แร่ธาตุ'];
            // result before selected month 
            $result = MineralService::getDetailmonth($beforeYear, $beforemonth);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = MineralService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = MineralService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['YearTo'] - 1;
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = MineralService::getDetailmonth($loop_year, $loop_month);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ตัน';
            $detail['unit_price'] = 'ล้านบาท';

            $detail['type_amount'] = 'ปริมาณการจำหน่ายแร่ธาตุ';
            $detail['type_price'] = 'รายได้การจำหน่ายแร่ธาตุ';
            array_push($data, $detail);

            $detail = [];

            // การจำหน่ายอาหารสัตว์

            $menu_type = 'แร่ธาตุ พรีมิกซ์ และอาหาร';
            $detail['name'] = 'การจำหน่ายอาหารสัตว์ (อาหาร)';
            $dairy_farming_id = [13];
            $sub_goal_type_arr = ['อาหาร'];
            // result before selected month 
            $result = MineralService::getDetailmonthFood($beforeYear, $beforemonth);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = MineralService::getDetailmonthFood($condition['YearTo'], $condition['MonthFrom']);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = MineralService::getDetailmonthFood($condition['YearTo'] - 1, $condition['MonthFrom']);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuTypeAndSubGoalType($menu_type, $sub_goal_type_arr, $condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['YearTo'] - 1;
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = MineralService::getDetailmonthFood($loop_year, $loop_month);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }

            $detail['unit_amount'] = 'ตัน';
            $detail['unit_price'] = 'ล้านบาท';

            $detail['type_amount'] = 'ปริมาณการจำหน่ายอาหาร';
            $detail['type_price'] = 'รายได้การจำหน่ายอาหาร';
            array_push($data, $detail);

            $detail = [];
            // การจำหน่ายอาหารสัตว์

            $menu_type = 'ฝึกอบรม';
            $detail['name'] = 'การฝึกอบรม';
            // result before selected month 
            $result = TrainingCowBreedService::getDetailmonth($beforeYear, $beforemonth);
            $detail['beforemonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
            // current month goal 
            $result = GoalMissionService::getMissionavgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
            $detail['target']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];

            // result selected month 
            $result = TrainingCowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
            $detail['collectmonth']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

            if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
            $detail['permonth']['price_value'] += ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];

            $result = TrainingCowBreedService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
            $detail['beforeyear']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

            if(!empty($detail['beforeyear']['amount'])){
                $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
            }else{
                $detail['perbeforeyear']['amount'] = 0;
            }
            
            if(!empty($detail['beforeyear']['price_value'])){
                $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
            }else{
                $detail['perbeforeyear']['price_value'] = 0;
            }

            
            $result = GoalMissionService::getMissionYearByMenuType($menu_type, $condition['YearTo']);
            $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

            $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
            $detail['targetoct']['amount'] = empty($result['amount'])?0:$result['amount'];
            $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];


            $loop_year = $condition['YearTo'] - 1;
            $loop_month = 10;

            while($loop_month != $condition['MonthFrom']){

                $result = TrainingCowBreedService::getDetailmonth($loop_year, $loop_month);
                $detail['collectoct']['amount'] += empty($result['amount'])?0:$result['amount'];
                $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
                $loop_month++;

                if($loop_month > 12){
                    $loop_month = 1;
                    $loop_year += 1;
                }
            }

            if(!empty($detail['targetoct']['amount'])){
                $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['targetoct']['amount'];
            }else{
                $detail['peroct']['amount'] = 0;
            }
            
            if(!empty($detail['targetoct']['price_value'])){
                $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
            }else{
                $detail['peroct']['price_value'] = 0;
            }
            

            $detail['unit_amount'] = 'ราย';
            $detail['unit_price'] = 'ล้านบาท';

            $detail['type_amount'] = 'ปริมาณ';
            $detail['type_price'] = 'รายได้';
            array_push($data, $detail);

        }
// print

        foreach ($data as $key => $itemdata) {
            $index = $position + $key;
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), ($position + $key) . '.' . $itemdata['name']);
            $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
            $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
            $row++;

            if($itemdata['unit_amount'] == 'ตัน'){
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($itemdata['beforemonth']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($itemdata['target']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($itemdata['collectmonth']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($itemdata['beforeyear']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   ' . $itemdata['type_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($itemdata['yeartarget']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($itemdata['targetoct']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($itemdata['collectoct']['amount'] / 1000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['amount']);
            }else{
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   ' . $itemdata['type_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit_amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['amount']);
            }
            
            $row++;
            if($itemdata['unit_price'] == 'ล้านบาท'){

                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($itemdata['beforemonth']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($itemdata['target']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($itemdata['collectmonth']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($itemdata['beforeyear']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   ' . $itemdata['type_price']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit_price']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($itemdata['yeartarget']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($itemdata['targetoct']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($itemdata['collectoct']['price_value'] / 1000000, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['price_value']);

            }else{
                $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $itemdata['beforemonth']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $itemdata['target']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $itemdata['collectmonth']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $itemdata['permonth']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $itemdata['beforeyear']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $itemdata['perbeforeyear']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '   ' . $itemdata['type_price']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '   ' . $itemdata['unit_price']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']['price_value']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']['price_value']);
            }
            

            $row++;
        }

        $detail = [];

        // ปัจจัยการผลิต
        $index++;
        $menu_type = 'จำหน่ายน้ำเชื้อแช่แข็ง';
        $detail['name'] = 'การจำหน่ายปัจจัยการผลิต';
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), ($index) . '.' . $detail['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        // result before selected month 
        $result = SpermSaleService::getDetailmonth($beforeYear, $beforemonth);
        $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        $result = GoalMissionService::getMissionavgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
        $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $result = SpermSaleService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
        $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail['collectmonth']['price_value'] / 1000000, 2, '.', ''));

        if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
        $detail['permonth']['price_value'] = ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail['permonth']['price_value'], 2, '.', ''));

        $result = SpermSaleService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
        $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($detail['beforeyear']['price_value'] / 1000000, 2, '.', ''));
        
        if(!empty($detail['beforeyear']['price_value'])){
            $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
        }else{
            $detail['perbeforeyear']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    รายได้น้ำเชื้อแช่แข็ง');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $result = GoalMissionService::getMissionYearByMenuType($menu_type, $condition['YearTo']);
        $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail['targetoct']['price_value'] / 1000000, 2, '.', ''));
            
        $loop_year = $condition['YearTo'] - 1;
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = SpermSaleService::getDetailmonth($loop_year, $loop_month);
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail['collectoct']['price_value'] / 1000000, 2, '.', ''));
            
        if(!empty($detail['targetoct']['price_value'])){
            $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
        }else{
            $detail['peroct']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail['peroct']['price_value']);
        
        $row++;

        // ไนโตรเจนเหลว
        $detail_nitro = [];
        
        $menu_type = 'วัสดุผสมเทียมและเวชภัณฑ์ยาสัตว์';
        $detail_nitro['name'] = 'ไนโตรเจนเหลว';
        $type_id = [359, 160];

        $result = MaterialService::getDetailmonth($beforeYear, $beforemonth, $type_id);
        $detail_nitro['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];

        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalID($menu_type, $type_id, $condition['YearTo'], $condition['MonthFrom']);
        $detail_nitro['target']['price_value'] = empty($result['price'])?0:$result['price'];

        $result = MaterialService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $type_id);
        $detail_nitro['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];

        $detail_nitro['permonth']['amount'] = ($detail_nitro['collectmonth']['amount'] / $detail_nitro['target']['amount']) * 100;
        $detail_nitro['permonth']['price_value'] += ($detail_nitro['collectmonth']['price_value'] * 100) / $detail_nitro['target']['price_value'];

        $result = MaterialService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $type_id);
        $detail_nitro['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

        if(!empty($detail_nitro['beforeyear']['price_value'])){
            $detail_nitro['perbeforeyear']['price_value'] = (($detail_nitro['collectmonth']['price_value'] - $detail_nitro['beforeyear']['price_value']) * 100) / $detail_nitro['beforeyear']['price_value'];
        }else{
            $detail_nitro['perbeforeyear']['price_value'] = 0;
        }

        $result = GoalMissionService::getMissionYearByMenuTypeAndGoalID($menu_type, $type_id, $condition['YearTo']);
        $detail_nitro['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        
        $loop_year = $condition['YearTo'] - 1;
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = MaterialService::getDetailmonthExcept($loop_year, $loop_month, $type_id);
            $detail_nitro['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalID($menu_type, $type_id, $condition['YearTo'], $condition['MonthFrom']);
        $detail_nitro['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        
        if(!empty($detail_nitro['targetoct']['price_value'])){
            $detail_nitro['peroct']['price_value'] = ($detail_nitro['collectoct']['price_value'] * 100) / $detail_nitro['targetoct']['price_value'];
        }else{
            $detail_nitro['peroct']['price_value'] = 0;
        }




        // ปัจจัยการเลี้ยงโคนม
        $detail_cowbreed = [];
        
        $menu_type = 'วัสดุผสมเทียมและเวชภัณฑ์ยาสัตว์';
        $detail_cowbreed['name'] = 'ปัจจัยการเลี้ยงโคนม';
        $type_id = [359, 160];

        $result = MaterialService::getDetailmonthExcept($beforeYear, $beforemonth, $type_id);
        $detail_cowbreed['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];

        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalIDNotIn($menu_type, $type_id, $condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['target']['price_value'] = empty($result['price'])?0:$result['price'];
        $result = GoalMissionService::getMissionavgByMenuType('ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)', $condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['target']['price_value'] += empty($result['price'])?0:$result['price'];

        $result = MaterialService::getDetailmonthExcept($condition['YearTo'], $condition['MonthFrom'], $type_id);
        $detail_cowbreed['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $result = CowBreedService::getDetailmonth($condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['collectmonth']['price_value'] += empty($result['price'])?0:$result['price'];


        $detail_cowbreed['permonth']['amount'] = ($detail_cowbreed['collectmonth']['amount'] / $detail_cowbreed['target']['amount']) * 100;
        $detail_cowbreed['permonth']['price_value'] += ($detail_cowbreed['collectmonth']['price_value'] * 100) / $detail_cowbreed['target']['price_value'];

        $result = MaterialService::getDetailmonthExcept($condition['YearTo'] - 1, $condition['MonthFrom'], $type_id);
        $detail_cowbreed['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];
        $result = CowBreedService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom']);
        $detail_cowbreed['beforeyear']['price_value'] += empty($result['price'])?0:$result['price'];

        if(!empty($detail_cowbreed['beforeyear']['price_value'])){
            $detail_cowbreed['perbeforeyear']['price_value'] = (($detail_cowbreed['collectmonth']['price_value'] - $detail_cowbreed['beforeyear']['price_value']) * 100) / $detail_cowbreed['beforeyear']['price_value'];
        }else{
            $detail_cowbreed['perbeforeyear']['price_value'] = 0;
        }

        $result = GoalMissionService::getMissionYearByMenuTypeAndGoalIDNotIn($menu_type, $type_id, $condition['YearTo']);
        $detail_cowbreed['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];

        $result = GoalMissionService::getMissionYearByMenuType('ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)', $condition['YearTo']);
        $detail_cowbreed['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        
        $loop_year = $condition['YearTo'] - 1;
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = CowBreedService::getDetailmonth($loop_year, $loop_month);
            $detail_cowbreed['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalIDNotIn($menu_type, $type_id, $condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $result = GoalMissionService::getMissionOctAvgByMenuType('ปัจจัยการเลี้ยงดูโค (เคมีภัณฑ์)', $condition['YearTo'], $condition['MonthFrom']);
        $detail_cowbreed['targetoct']['price_value'] += empty($result['price'])?0:$result['price'];

        if(!empty($detail_cowbreed['targetoct']['price_value'])){
            $detail_cowbreed['peroct']['price_value'] = ($detail_cowbreed['collectoct']['price_value'] * 100) / $detail_cowbreed['targetoct']['price_value'];
        }else{
            $detail_cowbreed['peroct']['price_value'] = 0;
        }

        //$index++;
        
        // $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), 'รายได้อื่นๆ');
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        // $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        // $row++;
        // result before selected month 
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format(($detail_nitro['beforemonth']['price_value'] + $detail_cowbreed['beforemonth']['price_value']) / 1000000, 2, '.', ''));
            
        // current month goal 
        
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format(($detail_nitro['target']['price_value'] + $detail_cowbreed['target']['price_value']) / 1000000, 2, '.', ''));

        // result selected month 
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format(($detail_nitro['collectmonth']['price_value'] + $detail_cowbreed['collectmonth']['price_value']) / 1000000, 2, '.', ''));

        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail_cowbreed['permonth']['price_value'], 2, '.', ''));
        
        
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format(($detail_nitro['beforeyear']['price_value'] + $detail_cowbreed['beforeyear']['price_value']) / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail_nitro['perbeforeyear']['price_value'] + $detail_cowbreed['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    รายได้อื่นๆ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format(($detail_nitro['yeartarget']['price_value'] + $detail_cowbreed['yeartarget']['price_value']) / 1000000, 2, '.', ''));

        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format(($detail_nitro['targetoct']['price_value'] + $detail_cowbreed['targetoct']['price_value']) / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format(($detail_nitro['collectoct']['price_value'] + $detail_cowbreed['collectoct']['price_value']) / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail_nitro['peroct']['price_value'] + $detail_cowbreed['peroct']['price_value']);
        
        $row++;


        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $detail_nitro['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        // $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        // $row++;
        // result before selected month 
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail_nitro['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail_nitro['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail_nitro['collectmonth']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail_nitro['permonth']['price_value'], 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($detail_nitro['beforeyear']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail_nitro['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    ไนโตรเจนเหลว');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail_nitro['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail_nitro['targetoct']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail_nitro['collectoct']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail_nitro['peroct']['price_value']);
        
        $row++;

        //$index++;

        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), $detail_cowbreed['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        // $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        // $row++;
        // result before selected month 
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail_cowbreed['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail_cowbreed['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail_cowbreed['collectmonth']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail_cowbreed['permonth']['price_value'], 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), number_format($detail_cowbreed['beforeyear']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail_cowbreed['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '        ปัจจัยการเลี้ยงโคนม');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail_cowbreed['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail_cowbreed['targetoct']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail_cowbreed['collectoct']['price_value'] / 1000000, 2, '.', ''));
        
        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail_cowbreed['peroct']['price_value']);
        
        $row++;

        $detail = [];

        // ท่องเที่ยวฟาร์มโคนมไทย - เดนมาร์ค
        $index++;
        $menu_type = 'ท่องเที่ยว';
        $goal_id_list = [391,392,393,320,321,322];
        $detail['name'] = 'ท่องเที่ยวฟาร์มโคนมไทย - เดนมาร์ค';
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), ($index) . '.' . $detail['name']);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . (6 + $row))->getFont()->setBold(true);
        $row++;
        // result before selected month 
        $result = TravelService::getDetailmonth($beforeYear, $beforemonth, $goal_id_list);
        $detail['beforemonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), $detail['beforemonth']['price_value']);
            
        // current month goal 
        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['YearTo'], $condition['MonthFrom']);
        $detail['target']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), $detail['target']['price_value']);

        // result selected month 
        $result = TravelService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $goal_id_list);
        $detail['collectmonth']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), $detail['collectmonth']['price_value']);

        if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }


        $detail['permonth']['price_value'] = ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $detail['permonth']['price_value']);

        $result = TravelService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $goal_id_list);
        $detail['beforeyear']['price_value'] = empty($result['amount'])?0:$result['amount'];

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $detail['beforeyear']['price_value']);
        
        if(!empty($detail['beforeyear']['price_value'])){
            $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
        }else{
            $detail['perbeforeyear']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), $detail['perbeforeyear']['price_value']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    บริการชมฟาร์มโคนม');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ราย');
            
        $result = GoalMissionService::getMissionYearByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['YearTo']);
        $detail['yeartarget']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $detail['yeartarget']['price_value']);

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['amount'])?0:$result['amount'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $detail['targetoct']['price_value']);
            
        $loop_year = $condition['YearTo'] - 1;
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = TravelService::getDetailmonth($loop_year, $loop_month, $goal_id_list);
            $detail['collectoct']['price_value'] += empty($result['amount'])?0:$result['amount'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $detail['collectoct']['price_value']);
            
        if(!empty($detail['targetoct']['price_value'])){
            $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
        }else{
            $detail['peroct']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail['peroct']['price_value']);
        
        $row++;

        // result before selected month 
        $result = TravelService::getDetailmonth($beforeYear, $beforemonth, $goal_id_list);
        $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['YearTo'], $condition['MonthFrom']);
        $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $result = TravelService::getDetailmonth($condition['YearTo'], $condition['MonthFrom'], $goal_id_list);
        $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail['collectmonth']['price_value'] / 1000000, 2, '.', ''));

        if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
        $detail['permonth']['price_value'] = ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), $detail['permonth']['price_value']);

        $result = TravelService::getDetailmonth($condition['YearTo'] - 1, $condition['MonthFrom'], $goal_id_list);
        $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $detail['beforeyear']['price_value']);
        
        if(!empty($detail['beforeyear']['price_value'])){
            $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
        }else{
            $detail['perbeforeyear']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), number_format($detail['perbeforeyear']['price_value'] / 1000000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    รายได้เข้าชมฟาร์มโคนม');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $result = GoalMissionService::getMissionYearByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['YearTo']);
        $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalID($menu_type, $goal_id_list, $condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail['targetoct']['price_value'] / 1000000, 2, '.', ''));
            
        $loop_year = $condition['YearTo'] - 1;
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = TravelService::getDetailmonth($loop_year, $loop_month, $goal_id_list);
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail['collectoct']['price_value'] / 1000000, 2, '.', ''));
            
        if(!empty($detail['targetoct']['price_value'])){
            $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
        }else{
            $detail['peroct']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail['peroct']['price_value']);
        
        $row++;

        // result before selected month 
        $result = TravelService::getDetailmonthExcept($beforeYear, $beforemonth, $goal_id_list);
        $detail['beforemonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (6 + $row), number_format($detail['beforemonth']['price_value'] / 1000000, 2, '.', ''));
            
        // current month goal 
        $result = GoalMissionService::getMissionavgByMenuTypeAndGoalIDNotIn($menu_type, $goal_id_list, $condition['YearTo'], $condition['MonthFrom']);
        $detail['target']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (6 + $row), number_format($detail['target']['price_value'] / 1000000, 2, '.', ''));

        // result selected month 
        $result = TravelService::getDetailmonthExcept($condition['YearTo'], $condition['MonthFrom'], $goal_id_list);
        $detail['collectmonth']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (6 + $row), number_format($detail['collectmonth']['price_value'] / 1000000, 2, '.', ''));

        if($detail['target']['amount'] > 0){
                $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] * 100) / $detail['target']['amount'];
            }else{
                $detail['permonth']['amount'] = 0;
            }
        
        $detail['permonth']['price_value'] = ($detail['collectmonth']['price_value'] * 100) / $detail['target']['price_value'];
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (6 + $row), number_format($detail['permonth']['price_value'], 2, '.', ''));


        $result = TravelService::getDetailmonthExcept($condition['YearTo'] - 1, $condition['MonthFrom'], $goal_id_list);
        $detail['beforeyear']['price_value'] = empty($result['price'])?0:$result['price'];

        $objPHPExcel->getActiveSheet()->setCellValue('E' . (6 + $row), $detail['beforeyear']['price_value']);
        
        if(!empty($detail['beforeyear']['price_value'])){
            $detail['perbeforeyear']['price_value'] = (($detail['collectmonth']['price_value'] - $detail['beforeyear']['price_value']) * 100) / $detail['beforeyear']['price_value'];
        }else{
            $detail['perbeforeyear']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('F' . (6 + $row), number_format($detail['perbeforeyear']['price_value'] / 1000000, 2, '.', ''));
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (6 + $row), '    รายได้กิจกรรมอื่นๆ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), '    ล้านบาท');
            
        $result = GoalMissionService::getMissionYearByMenuTypeAndGoalIDNotIn($menu_type, $goal_id_list, $condition['YearTo']);
        $detail['yeartarget']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), number_format($detail['yeartarget']['price_value'] / 1000000, 2, '.', ''));

        $result = GoalMissionService::getMissionOctAvgByMenuTypeAndGoalIDNotIn($menu_type, $goal_id_list, $condition['YearTo'], $condition['MonthFrom']);
        $detail['targetoct']['price_value'] = empty($result['price'])?0:$result['price'];
        $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), number_format($detail['targetoct']['price_value'] / 1000000, 2, '.', ''));
            
        $loop_year = $condition['YearTo'] - 1;
        $loop_month = 10;

        while($loop_month != $condition['MonthFrom']){

            $result = TravelService::getDetailmonthExcept($loop_year, $loop_month, $goal_id_list);
            $detail['collectoct']['price_value'] += empty($result['price'])?0:$result['price'];
            $loop_month++;

            if($loop_month > 12){
                $loop_month = 1;
                $loop_year += 1;
            }
        }

        $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), number_format($detail['collectoct']['price_value'] / 1000000, 2, '.', ''));
            
        if(!empty($detail['targetoct']['price_value'])){
            $detail['peroct']['price_value'] = ($detail['collectoct']['price_value'] * 100) / $detail['targetoct']['price_value'];
        }else{
            $detail['peroct']['price_value'] = 0;
        }

        $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $detail['peroct']['price_value']);
        
        $row++;

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
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . (6 + $row))->getFont()->setSize(14);
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
            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
            $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'เป้าหมายทั้งปี ');
            $objPHPExcel->getActiveSheet()->mergeCells('I4:I5');
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');

            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' - ' . $this->getMonthName(9) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');
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
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการจำหน่ายน้ำนม', $type);
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
            $data = [];
////////รับซื้อ

            $moumission = MouService::getMission($condition['YearTo'], $condition['MonthFrom']);
            $mbi = MBIService::getactualMBIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforembi = MBIService::getactualMBIDetail($year, $beforemonth);
            $beforeyearmbi = MBIService::getactualMBIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);
            $moumissionyear = MouService::getMissionyear($condition['YearTo']);
            $detail['name'] = '1.การรับซื้อน้ำนม';
            $detail['unit'] = 'ตัน';
            $detail['detailname'] = '1.1 การรับซื้อน้ำนม ';
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


            $mbi = MBIService::getactualMBIDetailByVendor($condition['YearTo'], $condition['MonthFrom'], 'สหกรณ์');
            $beforembi = MBIService::getactualMBIDetailByVendor($year, $beforemonth, 'สหกรณ์');
            $beforeyearmbi = MBIService::getactualMBIDetailByVendor($condition['YearTo'] - 1, $condition['MonthFrom'], 'สหกรณ์');

            $detail['unit'] = 'ตัน';
            $detail['detailname'] = '     - รับซื้อน้ำนมจากสหกรณ์ทั้งหมด';
            $detail['beforemonth'] = $beforembi['sum_amount'] / 1000;
            $detail['target'] = 0;
            $detail['collectmonth'] = $mbi['sum_amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmbi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $mbioct = MBIService::getactualMBIDetailByVendor($condition['YearTo'] - $yearlist[$key], $ml, 'สหกรณ์');
                $detail['collectoct'] += $mbioct['sum_amount'] / 1000;
                $detail['targetoct'] = 0;

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



            $mbi = MBIService::getactualMBIDetailByVendor($condition['YearTo'], $condition['MonthFrom'], 'ศูนย์');
            $beforembi = MBIService::getactualMBIDetailByVendor($year, $beforemonth, 'ศูนย์');
            $beforeyearmbi = MBIService::getactualMBIDetailByVendor($condition['YearTo'] - 1, $condition['MonthFrom'], 'ศูนย์');

            $detail['unit'] = 'ตัน';
            $detail['detailname'] = '     - รับซื้อน้ำนมจากศูนย์รับน้ำนม อ.ส.ค.';
            $detail['beforemonth'] = $beforembi['sum_amount'] / 1000;
            $detail['target'] = 0;
            $detail['collectmonth'] = $mbi['sum_amount'] / 1000;
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $beforeyearmbi['sum_amount'] / 1000;
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = 0;
            $detail['peroct'] = 0;

            foreach ($monthList as $key => $ml) {
                $mbioct = MBIService::getactualMBIDetailByVendor($condition['YearTo'] - $yearlist[$key], $ml, 'ศูนย์');
                $detail['collectoct'] += $mbioct['sum_amount'] / 1000;
                $detail['targetoct'] = 0;

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
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการจำหน่ายน้ำนม', $type);
            $mission = GoalMissionService::getMission($mastes[0]['id'], 3, $condition['YearTo']);
            $beforeavg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $beforemonth);
            $avg = GoalMissionService::getMissionavg($mission[0]['id'], $condition['YearTo'], $condition['MonthFrom']);


            $msi = MSIService::getactualMSIDetail($condition['YearTo'], $condition['MonthFrom']);
            $beforemsi = MSIService::getactualMSIDetail($year, $beforemonth);
            $beforeyearmsi = MSIService::getactualMSIDetail($condition['YearTo'] - 1, $condition['MonthFrom']);

            $detail['detailname'] = '1.2 การจำหน่ายน้ำนม ';
            $detail['unit'] = 'ตัน';
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


            $detail['detailname'] = '1.3 น้ำนมคงเหลือ ';
            $detail['unit'] = 'ตัน';
            $detail['beforemonth'] = $data[0]['beforemonth'] - $data[1]['beforemonth'];
            $detail['target'] = 0;
            $detail['collectmonth'] = $data[0]['collectmonth'] - $data[1]['collectmonth'];
            $detail['permonth'] = 0;
            $detail['beforeyear'] = $data[0]['beforeyear'] - $data[1]['beforeyear'];
            $detail['perbeforeyear'] = 0;
            $detail['yeartarget'] = 0;
            $detail['targetoct'] = 0;
            $detail['collectoct'] = $data[0]['collectoct'] - $data[1]['collectoct'];
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
            $detail['detailname'] = '2.1 น้ำนมดิบเข้ากระบวนการผลิต';
            $detail['unit'] = 'ตัน';
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
            $detail['detailname'] = '3.1  ผลิตภัณฑ์ที่ผลิตได้';
            $detail['unit'] = 'ตัน';
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
                $data_fac = ProductionInfoController::getMonthreportforsubcom($condition, 1);
                array_push($detail, $data_fac);
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
            $mastes = MasterGoalService::getList('Y', 'ข้อมูลการจำหน่ายน้ำนม', $type);
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
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['unit']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']);
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
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['unit']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']);
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
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (6 + $row), $itemdata['unit']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (6 + $row), $itemdata['yeartarget']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . (6 + $row), $itemdata['targetoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . (6 + $row), $itemdata['collectoct']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . (6 + $row), $itemdata['peroct']);
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
          $row+=7;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'หมายเหตู');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), 'เป้าหมายจากแผนปฏิบัติงานปี ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $row . ':D' . $row);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), 'ข้อมูล ณ วันที่ ' . ($condition['date'] ));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $row . ':D' . $row);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row . ':D' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generatesheet4($objPHPExcel, $condition, $time_list, $header) {
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $objPHPExcel->createSheet(3);
        $objPHPExcel->setActiveSheetIndex(3);
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 5");
        $FactoryList = FactoryService::getList();
        $row = 6;
        $detail = [];
        $detail2 = [];
        $this->logger->info('begin log sheet 4');

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

            // foreach ($FactoryList as $id) {
            //     $data_fac = ProductionInfoController::getMonthreportforsubcom($condition, $id['id']);
            //     array_push($detail, $data_fac);
            //     $data2 = ProductionSaleInfoController::getMonthreportforsubcom($condition, $id['id']);
            //     array_push($detail2, $data2);
            // }

        } else if ($condition['DisplayType'] == 'monthly') {

            $this->logger->info('type monthly');
        
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
            /*
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));

            $objPHPExcel->getActiveSheet()->mergeCells('B4:B5');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'ผลการดำเนินงานสะสม');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('C5', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('D5', '%เป้าหมาย ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            */

            $objPHPExcel->getActiveSheet()->setCellValue('B4',  'เดือน' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearTo'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('B4:D4');
            $objPHPExcel->getActiveSheet()->setCellValue('B5',  'เป้าหมาย');
            $objPHPExcel->getActiveSheet()->setCellValue('C5',  'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->setCellValue('D5',  '% เป้าหมาย');

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
            $objPHPExcel->getActiveSheet()->setCellValue('J4', 'เป้าหมาย ' . $this->getMonthName(10) . ' ' . ($condition['YearFrom'] + 542) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('J4:J5');
            $objPHPExcel->getActiveSheet()->setCellValue('K4', 'ผลการดำเนินงานสะสม ');
            $objPHPExcel->getActiveSheet()->mergeCells('K4:L4');

            $objPHPExcel->getActiveSheet()->setCellValue('K5', $this->getMonthName(10) . ' ' . ($condition['YearFrom'] + 542) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->setCellValue('L5', '%/เป้าหมายสะสม');

            $condition['MonthTo'] = $condition['MonthFrom'];


            // foreach ($FactoryList as $id) {
            //     $data_fac = ProductionInfoController::getMonthreportforsubcom($condition, $id['id']);
            //     array_push($detail, $data_fac);
            //     $data2 = ProductionSaleInfoController::getMonthreportforsubcom($condition, $id['id']);
            //     array_push($detail2, $data2);
            // }
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


            /*foreach ($FactoryList as $id) {
                $data_fac = ProductionInfoController::getQreportforsubcom($condition, $id['id']);
                array_push($detail, $data_fac);
                $data2 = ProductionSaleInfoController::getQreportforsubcom($condition, $id['id']);
                array_push($detail2, $data2);
            }*/
        }

        // ผลิต
        $menu_type = 'ข้อมูลการผลิต';

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '1. การผลิต');
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
        $row++;
        
        $List = [];
        foreach ($FactoryList as $factory_k => $factory_v) {
            
            $FacList = [];
            $FacList['beforemonth']['amount'] = 0;
            $FacList['target']['amount'] = 0;
            $FacList['collectmonth']['amount'] = 0;
            $FacList['permonth']['amount'] = 0;
            $FacList['beforeyear']['amount'] = 0;
            $FacList['perbeforeyear']['amount'] = 0;
            $FacList['activity']['amount'] = $factory_v['factory_name'];
            $FacList['unit']['amount'] = 'ตัน';
            $FacList['yeartarget']['amount'] = 0;
            $FacList['yearocttarget']['amount'] = 0;
            $FacList['collectoct']['amount'] = 0;
            $FacList['peroct']['amount'] = 0;
            $FacList['detail'] = [];

            $ProductMilkList = ProductMilkService::getList('Y', '', '', $factory_v['id']);

            foreach ($ProductMilkList as $prod_k => $prod_v) {

                $detail = [];
                $detail['activity']['amount'] = $prod_v['name'];
                $detail['unit']['amount'] = 'ตัน';

                foreach ($time_list as $time_k => $time_v) {

                    $before_month = $time_v['month'];
                    $year = $time_v['year'];

                    if($condition['DisplayType'] == 'quarter'){
                        $before_month -= 3;
                        if($before_month == -2){
                            $before_month = 10;
                            $year -= 1;
                        }else if($before_month == -1){
                            $before_month = 11;
                            $year -= 1;
                        }else if($before_month == 0){
                            $before_month = 12;
                            $year -= 1;
                        }
                    }else if($condition['DisplayType'] == 'annually'){
                        $year -= 1;
                    }
                 
                    $result = ProductionInfoService::getDetail($year, $before_month, $factory_v['id'], $prod_v['id']);
                    $detail['beforemonth']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionavgByMenuType($menu_type, $time_v['year'], $time_v['month']);
                    $detail['target']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = ProductionInfoService::getDetail($time_v['year'], $time_v['month'], $factory_v['id'], $prod_v['id']);
                    $detail['collectmonth']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $detail['permonth']['amount'] = 0;

                    $result = ProductionInfoService::getDetail($time_v['year'] - 1, $time_v['month'], $factory_v['id'], $prod_v['id']);
                    $detail['beforeyear']['amount'] = 0;

                    $result = GoalMissionService::getMissionYearByMenuType($menu_type, $time_v['year']);
                    $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $time_v['year'], $time_v['month']);
                    $detail['yearocttarget']['amount'] = empty($result['amount'])?0:$result['amount'];

                    $loop_year = $time_v['year'] - 1;
                    $loop_month = 10;

                    while($loop_month != $time_v['month']){

                        $result = ProductionInfoService::getDetail($loop_year, $loop_month, $factory_v['id'], $prod_v['id']);
                        $detail['collectoct']['amount'] = empty($result['amount'])?0:$result['amount'];
                        $loop_month++;

                        if($loop_month > 12){
                            $loop_month = 1;
                            $loop_year += 1;
                        }
                    }
        
                }

                if(!empty($detail['collectmonth']['amount'])){
                    $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] / $detail['target']['amount']) * 100;
                }else{
                    $detail['permonth']['amount'] = 0;
                }

                if(!empty($detail['beforeyear']['amount'])){
                    $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                }else{
                    $detail['perbeforeyear']['amount'] = 0;
                }

                if(!empty($detail['yearocttarget']['amount'])){
                    $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['yearocttarget']['amount'];
                }else{
                    $detail['peroct']['amount'] = 0;
                }

                $FacList['beforemonth']['amount'] += $detail['beforemonth']['amount'];
                $FacList['target']['amount'] += $detail['target']['amount'];
                $FacList['collectmonth']['amount'] += $detail['collectmonth']['amount'];
                $FacList['beforeyear']['amount'] += $detail['beforeyear']['amount'];
                $FacList['yeartarget']['amount'] += $detail['yeartarget']['amount'];
                $FacList['yearocttarget']['amount'] += $detail['yearocttarget']['amount'];
                $FacList['collectoct']['amount'] += $detail['collectoct']['amount'];
                
                array_push($FacList['detail'], $detail);

            }

            if(!empty($FacList['collectmonth']['amount'])){
                $FacList['permonth']['amount'] = ($FacList['collectmonth']['amount'] / $FacList['target']['amount']) * 100;
            }else{
                $FacList['permonth']['amount'] = 0;
            }

            if(!empty($FacList['beforeyear']['amount'])){
                $FacList['perbeforeyear']['amount'] = (($FacList['collectmonth']['amount'] - $FacList['beforeyear']['amount']) * 100) / $FacList['beforeyear']['amount'];
            }else{
                $FacList['perbeforeyear']['amount'] = 0;
            }

            if(!empty($FacList['yearocttarget']['amount'])){
                $FacList['peroct']['amount'] = ($FacList['collectoct']['amount'] * 100) / $FacList['yearocttarget']['amount'];
            }else{
                $FacList['peroct']['amount'] = 0;
            }

            array_push($List, $FacList);
            
        }

        $Summary = [];
        $Summary['beforemonth']['amount'] = 0;
        $Summary['target']['amount'] = 0;
        $Summary['collectmonth']['amount'] = 0;
        $Summary['permonth']['amount'] = 0;
        $Summary['beforeyear']['amount'] = 0;
        $Summary['perbeforeyear']['amount'] = 0;
        $Summary['activity']['amount'] = 'รวม';
        $Summary['unit']['amount'] = 'ตัน';
        $Summary['yeartarget']['amount'] = 0;
        $Summary['yearocttarget']['amount'] = 0;
        $Summary['collectoct']['amount'] = 0;
        $Summary['peroct']['amount'] = 0;
        
        $TotalByProd = [];
        $TotalByProd['milk1'] = [];
        $TotalByProd['milk1']['beforemonth']['amount'] = 0;
        $TotalByProd['milk1']['target']['amount'] = 0;
        $TotalByProd['milk1']['collectmonth']['amount'] = 0;
        $TotalByProd['milk1']['permonth']['amount'] = 0;
        $TotalByProd['milk1']['beforeyear']['amount'] = 0;
        $TotalByProd['milk1']['perbeforeyear']['amount'] = 0;
        $TotalByProd['milk1']['activity']['amount'] = 'นมพานิชย์';
        $TotalByProd['milk1']['unit']['amount'] = 'ตัน';
        $TotalByProd['milk1']['yeartarget']['amount'] = 0;
        $TotalByProd['milk1']['yearocttarget']['amount'] = 0;
        $TotalByProd['milk1']['collectoct']['amount'] = 0;
        $TotalByProd['milk1']['peroct']['amount'] = 0;

        $TotalByProd['milk2'] = [];
        $TotalByProd['milk2']['beforemonth']['amount'] = 0;
        $TotalByProd['milk2']['target']['amount'] = 0;
        $TotalByProd['milk2']['collectmonth']['amount'] = 0;
        $TotalByProd['milk2']['permonth']['amount'] = 0;
        $TotalByProd['milk2']['beforeyear']['amount'] = 0;
        $TotalByProd['milk2']['perbeforeyear']['amount'] = 0;
        $TotalByProd['milk2']['activity']['amount'] = 'นมโรงเรียน';
        $TotalByProd['milk2']['unit']['amount'] = 'ตัน';
        $TotalByProd['milk2']['yeartarget']['amount'] = 0;
        $TotalByProd['milk2']['yearocttarget']['amount'] = 0;
        $TotalByProd['milk2']['collectoct']['amount'] = 0;
        $TotalByProd['milk2']['peroct']['amount'] = 0;

        $cnt = 1;
        foreach ($List as $l_key => $l_value) {

            $Summary['beforemonth']['amount'] += $l_value['beforemonth']['amount'];
            $Summary['target']['amount'] += $l_value['target']['amount'];
            $Summary['collectmonth']['amount'] += $l_value['collectmonth']['amount'];
            $Summary['beforeyear']['amount'] += $l_value['beforeyear']['amount'];
            $Summary['yeartarget']['amount'] += $l_value['yeartarget']['amount'];
            $Summary['yearocttarget']['amount'] += $l_value['yearocttarget']['amount'];
            $Summary['collectoct']['amount'] += $l_value['collectoct']['amount'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $l_value['beforemonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $l_value['target']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $l_value['collectmonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $l_value['permonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $l_value['beforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $l_value['perbeforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '   1.' . $cnt . ' ' . $l_value['activity']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $l_value['unit']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $l_value['yeartarget']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $l_value['yearocttarget']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $l_value['collectoct']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $l_value['peroct']['amount']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
            $row++;

            foreach ($l_value['detail'] as $d_key => $d_value) {

                if($d_value['activity']['amount'] == 'นมพาณิชย์'){

                    $TotalByProd['milk1']['beforemonth']['amount'] += $d_value['beforemonth']['amount'];
                    $TotalByProd['milk1']['target']['amount'] += $d_value['target']['amount'];
                    $TotalByProd['milk1']['collectmonth']['amount'] += $d_value['collectmonth']['amount'];
                    $TotalByProd['milk1']['beforeyear']['amount'] += $d_value['beforeyear']['amount'];
                    $TotalByProd['milk1']['yeartarget']['amount'] += $d_value['yeartarget']['amount'];
                    $TotalByProd['milk1']['yearocttarget']['amount'] += $d_value['yearocttarget']['amount'];
                    $TotalByProd['milk1']['collectoct']['amount'] += $d_value['collectoct']['amount'];

                }else if($d_value['activity']['amount'] == 'นมโรงเรียน'){

                    $TotalByProd['milk2']['beforemonth']['amount'] += $d_value['beforemonth']['amount'];
                    $TotalByProd['milk2']['target']['amount'] += $d_value['target']['amount'];
                    $TotalByProd['milk2']['collectmonth']['amount'] += $d_value['collectmonth']['amount'];
                    $TotalByProd['milk2']['beforeyear']['amount'] += $d_value['beforeyear']['amount'];
                    $TotalByProd['milk2']['yeartarget']['amount'] += $d_value['yeartarget']['amount'];
                    $TotalByProd['milk2']['yearocttarget']['amount'] += $d_value['yearocttarget']['amount'];
                    $TotalByProd['milk2']['collectoct']['amount'] += $d_value['collectoct']['amount'];

                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $d_value['beforemonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $d_value['target']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $d_value['collectmonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $d_value['permonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $d_value['beforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $d_value['perbeforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         - '.$d_value['activity']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $d_value['unit']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $d_value['yeartarget']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $d_value['yearocttarget']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $d_value['collectoct']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $d_value['peroct']['amount']);
                $row++;

            }

            $cnt++;

        }

        if(!empty($Summary['collectmonth']['amount'])){
            $Summary['permonth']['amount'] = ($Summary['collectmonth']['amount'] / $Summary['target']['amount']) * 100;
        }else{
            $Summary['permonth']['amount'] = 0;
        }

        if(!empty($Summary['beforeyear']['amount'])){
            $Summary['perbeforeyear']['amount'] = (($Summary['collectmonth']['amount'] - $Summary['beforeyear']['amount']) * 100) / $Summary['beforeyear']['amount'];
        }else{
            $Summary['perbeforeyear']['amount'] = 0;
        }

        if(!empty($Summary['yearocttarget']['amount'])){
            $Summary['peroct']['amount'] = ($Summary['collectoct']['amount'] * 100) / $Summary['yearocttarget']['amount'];
        }else{
            $Summary['peroct']['amount'] = 0;
        }





        if(!empty($TotalByProd['milk1']['collectmonth']['amount'])){
            $TotalByProd['milk1']['permonth']['amount'] = ($TotalByProd['milk1']['collectmonth']['amount'] / $TotalByProd['milk1']['target']['amount']) * 100;
        }else{
            $TotalByProd['milk1']['permonth']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk1']['beforeyear']['amount'])){
            $TotalByProd['milk1']['perbeforeyear']['amount'] = (($Summary['collectmonth']['amount'] - $Summary['beforeyear']['amount']) * 100) / $Summary['beforeyear']['amount'];
        }else{
            $TotalByProd['milk1']['perbeforeyear']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk1']['yearocttarget']['amount'])){
            $TotalByProd['milk1']['peroct']['amount'] = ($TotalByProd['milk1']['collectoct']['amount'] * 100) / $TotalByProd['milk1']['yearocttarget']['amount'];
        }else{
            $TotalByProd['milk1']['peroct']['amount'] = 0;
        }





        if(!empty($TotalByProd['milk2']['collectmonth']['amount'])){
            $TotalByProd['milk2']['permonth']['amount'] = ($TotalByProd['milk2']['collectmonth']['amount'] / $TotalByProd['milk2']['target']['amount']) * 100;
        }else{
            $TotalByProd['milk2']['permonth']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk2']['beforeyear']['amount'])){
            $TotalByProd['milk2']['perbeforeyear']['amount'] = (($TotalByProd['milk2']['collectmonth']['amount'] - $TotalByProd['milk2']['beforeyear']['amount']) * 100) / $TotalByProd['milk2']['beforeyear']['amount'];
        }else{
            $TotalByProd['milk2']['perbeforeyear']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk2']['yearocttarget']['amount'])){
            $TotalByProd['milk2']['peroct']['amount'] = ($TotalByProd['milk2']['collectoct']['amount'] * 100) / $TotalByProd['milk2']['yearocttarget']['amount'];
        }else{
            $TotalByProd['milk2']['peroct']['amount'] = 0;
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $Summary['beforemonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $Summary['target']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $Summary['collectmonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $Summary['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $Summary['beforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $Summary['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '   '. $Summary['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $Summary['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $Summary['yeartarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $Summary['yearocttarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $Summary['collectoct']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $Summary['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $TotalByProd['milk1']['beforemonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $TotalByProd['milk1']['target']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $TotalByProd['milk1']['collectmonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $TotalByProd['milk1']['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $TotalByProd['milk1']['beforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $TotalByProd['milk1']['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         '. $TotalByProd['milk1']['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $TotalByProd['milk1']['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $TotalByProd['milk1']['yeartarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $TotalByProd['milk1']['yearocttarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $TotalByProd['milk1']['collectoct']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $TotalByProd['milk1']['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $TotalByProd['milk2']['beforemonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $TotalByProd['milk2']['target']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $TotalByProd['milk2']['collectmonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $TotalByProd['milk2']['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $TotalByProd['milk2']['beforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $TotalByProd['milk2']['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         '. $TotalByProd['milk2']['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $TotalByProd['milk2']['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $TotalByProd['milk2']['yeartarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $TotalByProd['milk2']['yearocttarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $TotalByProd['milk2']['collectoct']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $TotalByProd['milk2']['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        // ขาย
        $menu_type = 'ข้อมูลการขาย';

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '2. การจำหน่ายผลิตภัณฑ์นม (ตัน)');
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
        $row++;
        
        $List = [];
        foreach ($FactoryList as $factory_k => $factory_v) {
            
            $FacList = [];
            $FacList['beforemonth']['amount'] = 0;
            $FacList['target']['amount'] = 0;
            $FacList['collectmonth']['amount'] = 0;
            $FacList['permonth']['amount'] = 0;
            $FacList['beforeyear']['amount'] = 0;
            $FacList['perbeforeyear']['amount'] = 0;
            $FacList['activity']['amount'] = $factory_v['factory_name'];
            $FacList['unit']['amount'] = 'ตัน';
            $FacList['yeartarget']['amount'] = 0;
            $FacList['yearocttarget']['amount'] = 0;
            $FacList['collectoct']['amount'] = 0;
            $FacList['peroct']['amount'] = 0;
            $FacList['detail'] = [];

            $ProductMilkList = ProductMilkService::getList('Y', '', '', $factory_v['id']);

            foreach ($ProductMilkList as $prod_k => $prod_v) {

                $detail = [];
                $detail['activity']['amount'] = $prod_v['name'];
                $detail['unit']['amount'] = 'ตัน';

                foreach ($time_list as $time_k => $time_v) {

                    $before_month = $time_v['month'];
                    $year = $time_v['year'];

                    if($condition['DisplayType'] == 'quarter'){
                        $before_month -= 3;
                        if($before_month == -2){
                            $before_month = 10;
                            $year -= 1;
                        }else if($before_month == -1){
                            $before_month = 11;
                            $year -= 1;
                        }else if($before_month == 0){
                            $before_month = 12;
                            $year -= 1;
                        }
                    }else if($condition['DisplayType'] == 'annually'){
                        $year -= 1;
                    }
                 
                    $result = ProductionSaleInfoService::getDetail($year, $before_month, $factory_v['id'], $prod_v['id']);
                    $detail['beforemonth']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionavgByMenuType($menu_type, $time_v['year'], $time_v['month']);
                    $detail['target']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $result = ProductionSaleInfoService::getDetail($time_v['year'], $time_v['month'], $factory_v['id'], $prod_v['id']);
                    $detail['collectmonth']['amount'] += empty($result['amount'])?0:$result['amount'];

                    $detail['permonth']['amount'] = 0;

                    $result = ProductionSaleInfoService::getDetail($time_v['year'] - 1, $time_v['month'], $factory_v['id'], $prod_v['id']);
                    $detail['beforeyear']['amount'] = 0;

                    $result = GoalMissionService::getMissionYearByMenuType($menu_type, $time_v['year']);
                    $detail['yeartarget']['amount'] = empty($result['amount'])?0:$result['amount'];

                    $result = GoalMissionService::getMissionOctAvgByMenuType($menu_type, $time_v['year'], $time_v['month']);
                    $detail['yearocttarget']['amount'] = empty($result['amount'])?0:$result['amount'];

                    $loop_year = $time_v['year'] - 1;
                    $loop_month = 10;

                    while($loop_month != $time_v['month']){

                        $result = ProductionSaleInfoService::getDetail($loop_year, $loop_month, $factory_v['id'], $prod_v['id']);
                        $detail['collectoct']['amount'] = empty($result['amount'])?0:$result['amount'];
                        $loop_month++;

                        if($loop_month > 12){
                            $loop_month = 1;
                            $loop_year += 1;
                        }
                    }
        
                }

                if(!empty($detail['collectmonth']['amount'])){
                    $detail['permonth']['amount'] = ($detail['collectmonth']['amount'] / $detail['target']['amount']) * 100;
                }else{
                    $detail['permonth']['amount'] = 0;
                }

                if(!empty($detail['beforeyear']['amount'])){
                    $detail['perbeforeyear']['amount'] = (($detail['collectmonth']['amount'] - $detail['beforeyear']['amount']) * 100) / $detail['beforeyear']['amount'];
                }else{
                    $detail['perbeforeyear']['amount'] = 0;
                }

                if(!empty($detail['yearocttarget']['amount'])){
                    $detail['peroct']['amount'] = ($detail['collectoct']['amount'] * 100) / $detail['yearocttarget']['amount'];
                }else{
                    $detail['peroct']['amount'] = 0;
                }

                $FacList['beforemonth']['amount'] += $detail['beforemonth']['amount'];
                $FacList['target']['amount'] += $detail['target']['amount'];
                $FacList['collectmonth']['amount'] += $detail['collectmonth']['amount'];
                $FacList['beforeyear']['amount'] += $detail['beforeyear']['amount'];
                $FacList['yeartarget']['amount'] += $detail['yeartarget']['amount'];
                $FacList['yearocttarget']['amount'] += $detail['yearocttarget']['amount'];
                $FacList['collectoct']['amount'] += $detail['collectoct']['amount'];
                
                array_push($FacList['detail'], $detail);

            }

            if(!empty($FacList['collectmonth']['amount'])){
                $FacList['permonth']['amount'] = ($FacList['collectmonth']['amount'] / $FacList['target']['amount']) * 100;
            }else{
                $FacList['permonth']['amount'] = 0;
            }

            if(!empty($FacList['beforeyear']['amount'])){
                $FacList['perbeforeyear']['amount'] = (($FacList['collectmonth']['amount'] - $FacList['beforeyear']['amount']) * 100) / $FacList['beforeyear']['amount'];
            }else{
                $FacList['perbeforeyear']['amount'] = 0;
            }

            if(!empty($FacList['yearocttarget']['amount'])){
                $FacList['peroct']['amount'] = ($FacList['collectoct']['amount'] * 100) / $FacList['yearocttarget']['amount'];
            }else{
                $FacList['peroct']['amount'] = 0;
            }

            array_push($List, $FacList);
            
        }

        $Summary = [];
        $Summary['beforemonth']['amount'] = 0;
        $Summary['target']['amount'] = 0;
        $Summary['collectmonth']['amount'] = 0;
        $Summary['permonth']['amount'] = 0;
        $Summary['beforeyear']['amount'] = 0;
        $Summary['perbeforeyear']['amount'] = 0;
        $Summary['activity']['amount'] = 'รวม';
        $Summary['unit']['amount'] = 'ตัน';
        $Summary['yeartarget']['amount'] = 0;
        $Summary['yearocttarget']['amount'] = 0;
        $Summary['collectoct']['amount'] = 0;
        $Summary['peroct']['amount'] = 0;
        
        $TotalByProd = [];
        $TotalByProd['milk1'] = [];
        $TotalByProd['milk1']['beforemonth']['amount'] = 0;
        $TotalByProd['milk1']['target']['amount'] = 0;
        $TotalByProd['milk1']['collectmonth']['amount'] = 0;
        $TotalByProd['milk1']['permonth']['amount'] = 0;
        $TotalByProd['milk1']['beforeyear']['amount'] = 0;
        $TotalByProd['milk1']['perbeforeyear']['amount'] = 0;
        $TotalByProd['milk1']['activity']['amount'] = 'นมพานิชย์';
        $TotalByProd['milk1']['unit']['amount'] = 'ตัน';
        $TotalByProd['milk1']['yeartarget']['amount'] = 0;
        $TotalByProd['milk1']['yearocttarget']['amount'] = 0;
        $TotalByProd['milk1']['collectoct']['amount'] = 0;
        $TotalByProd['milk1']['peroct']['amount'] = 0;

        $TotalByProd['milk2'] = [];
        $TotalByProd['milk2']['beforemonth']['amount'] = 0;
        $TotalByProd['milk2']['target']['amount'] = 0;
        $TotalByProd['milk2']['collectmonth']['amount'] = 0;
        $TotalByProd['milk2']['permonth']['amount'] = 0;
        $TotalByProd['milk2']['beforeyear']['amount'] = 0;
        $TotalByProd['milk2']['perbeforeyear']['amount'] = 0;
        $TotalByProd['milk2']['activity']['amount'] = 'นมโรงเรียน';
        $TotalByProd['milk2']['unit']['amount'] = 'ตัน';
        $TotalByProd['milk2']['yeartarget']['amount'] = 0;
        $TotalByProd['milk2']['yearocttarget']['amount'] = 0;
        $TotalByProd['milk2']['collectoct']['amount'] = 0;
        $TotalByProd['milk2']['peroct']['amount'] = 0;

        $cnt = 1;
        foreach ($List as $l_key => $l_value) {

            $Summary['beforemonth']['amount'] += $l_value['beforemonth']['amount'];
            $Summary['target']['amount'] += $l_value['target']['amount'];
            $Summary['collectmonth']['amount'] += $l_value['collectmonth']['amount'];
            $Summary['beforeyear']['amount'] += $l_value['beforeyear']['amount'];
            $Summary['yeartarget']['amount'] += $l_value['yeartarget']['amount'];
            $Summary['yearocttarget']['amount'] += $l_value['yearocttarget']['amount'];
            $Summary['collectoct']['amount'] += $l_value['collectoct']['amount'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $l_value['beforemonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $l_value['target']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $l_value['collectmonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $l_value['permonth']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $l_value['beforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $l_value['perbeforeyear']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '   2.' . $cnt . ' ' . $l_value['activity']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $l_value['unit']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $l_value['yeartarget']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $l_value['yearocttarget']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $l_value['collectoct']['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $l_value['peroct']['amount']);
            $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
            $row++;

            foreach ($l_value['detail'] as $d_key => $d_value) {

                if($d_value['activity']['amount'] == 'นมพาณิชย์'){

                    $TotalByProd['milk1']['beforemonth']['amount'] += $d_value['beforemonth']['amount'];
                    $TotalByProd['milk1']['target']['amount'] += $d_value['target']['amount'];
                    $TotalByProd['milk1']['collectmonth']['amount'] += $d_value['collectmonth']['amount'];
                    $TotalByProd['milk1']['beforeyear']['amount'] += $d_value['beforeyear']['amount'];
                    $TotalByProd['milk1']['yeartarget']['amount'] += $d_value['yeartarget']['amount'];
                    $TotalByProd['milk1']['yearocttarget']['amount'] += $d_value['yearocttarget']['amount'];
                    $TotalByProd['milk1']['collectoct']['amount'] += $d_value['collectoct']['amount'];

                }else if($d_value['activity']['amount'] == 'นมโรงเรียน'){

                    $TotalByProd['milk2']['beforemonth']['amount'] += $d_value['beforemonth']['amount'];
                    $TotalByProd['milk2']['target']['amount'] += $d_value['target']['amount'];
                    $TotalByProd['milk2']['collectmonth']['amount'] += $d_value['collectmonth']['amount'];
                    $TotalByProd['milk2']['beforeyear']['amount'] += $d_value['beforeyear']['amount'];
                    $TotalByProd['milk2']['yeartarget']['amount'] += $d_value['yeartarget']['amount'];
                    $TotalByProd['milk2']['yearocttarget']['amount'] += $d_value['yearocttarget']['amount'];
                    $TotalByProd['milk2']['collectoct']['amount'] += $d_value['collectoct']['amount'];

                }

                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $d_value['beforemonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $d_value['target']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $d_value['collectmonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $d_value['permonth']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $d_value['beforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $d_value['perbeforeyear']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         - '.$d_value['activity']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $d_value['unit']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $d_value['yeartarget']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $d_value['yearocttarget']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $d_value['collectoct']['amount']);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $d_value['peroct']['amount']);
                $row++;

            }

            $cnt++;

        }

        if(!empty($Summary['collectmonth']['amount'])){
            $Summary['permonth']['amount'] = ($Summary['collectmonth']['amount'] / $Summary['target']['amount']) * 100;
        }else{
            $Summary['permonth']['amount'] = 0;
        }

        if(!empty($Summary['beforeyear']['amount'])){
            $Summary['perbeforeyear']['amount'] = (($Summary['collectmonth']['amount'] - $Summary['beforeyear']['amount']) * 100) / $Summary['beforeyear']['amount'];
        }else{
            $Summary['perbeforeyear']['amount'] = 0;
        }

        if(!empty($Summary['yearocttarget']['amount'])){
            $Summary['peroct']['amount'] = ($Summary['collectoct']['amount'] * 100) / $Summary['yearocttarget']['amount'];
        }else{
            $Summary['peroct']['amount'] = 0;
        }





        if(!empty($TotalByProd['milk1']['collectmonth']['amount'])){
            $TotalByProd['milk1']['permonth']['amount'] = ($TotalByProd['milk1']['collectmonth']['amount'] / $TotalByProd['milk1']['target']['amount']) * 100;
        }else{
            $TotalByProd['milk1']['permonth']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk1']['beforeyear']['amount'])){
            $TotalByProd['milk1']['perbeforeyear']['amount'] = (($Summary['collectmonth']['amount'] - $Summary['beforeyear']['amount']) * 100) / $Summary['beforeyear']['amount'];
        }else{
            $TotalByProd['milk1']['perbeforeyear']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk1']['yearocttarget']['amount'])){
            $TotalByProd['milk1']['peroct']['amount'] = ($TotalByProd['milk1']['collectoct']['amount'] * 100) / $TotalByProd['milk1']['yearocttarget']['amount'];
        }else{
            $TotalByProd['milk1']['peroct']['amount'] = 0;
        }





        if(!empty($TotalByProd['milk2']['collectmonth']['amount'])){
            $TotalByProd['milk2']['permonth']['amount'] = ($TotalByProd['milk2']['collectmonth']['amount'] / $TotalByProd['milk2']['target']['amount']) * 100;
        }else{
            $TotalByProd['milk2']['permonth']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk2']['beforeyear']['amount'])){
            $TotalByProd['milk2']['perbeforeyear']['amount'] = (($TotalByProd['milk2']['collectmonth']['amount'] - $TotalByProd['milk2']['beforeyear']['amount']) * 100) / $TotalByProd['milk2']['beforeyear']['amount'];
        }else{
            $TotalByProd['milk2']['perbeforeyear']['amount'] = 0;
        }

        if(!empty($TotalByProd['milk2']['yearocttarget']['amount'])){
            $TotalByProd['milk2']['peroct']['amount'] = ($TotalByProd['milk2']['collectoct']['amount'] * 100) / $TotalByProd['milk2']['yearocttarget']['amount'];
        }else{
            $TotalByProd['milk2']['peroct']['amount'] = 0;
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $Summary['beforemonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $Summary['target']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $Summary['collectmonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $Summary['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $Summary['beforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $Summary['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '   '. $Summary['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $Summary['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $Summary['yeartarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $Summary['yearocttarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $Summary['collectoct']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $Summary['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $TotalByProd['milk1']['beforemonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $TotalByProd['milk1']['target']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $TotalByProd['milk1']['collectmonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $TotalByProd['milk1']['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $TotalByProd['milk1']['beforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $TotalByProd['milk1']['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         '. $TotalByProd['milk1']['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $TotalByProd['milk1']['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $TotalByProd['milk1']['yeartarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $TotalByProd['milk1']['yearocttarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $TotalByProd['milk1']['collectoct']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $TotalByProd['milk1']['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $TotalByProd['milk2']['beforemonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $TotalByProd['milk2']['target']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $TotalByProd['milk2']['collectmonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $TotalByProd['milk2']['permonth']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $TotalByProd['milk2']['beforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $TotalByProd['milk2']['perbeforeyear']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, '         '. $TotalByProd['milk2']['activity']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $TotalByProd['milk2']['unit']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $TotalByProd['milk2']['yeartarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $TotalByProd['milk2']['yearocttarget']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, $TotalByProd['milk2']['collectoct']['amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $TotalByProd['milk2']['peroct']['amount']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':L' . ($row))->getFont()->setBold(true);
        $row++;

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
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A4:L' . ($row))->getFont()->setSize(14);
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

        return $objPHPExcel;

    }

    private function generatesheet5($objPHPExcel, $condition, $time_list, $header){
        $objPHPExcel->createSheet(4);
        $objPHPExcel->setActiveSheetIndex(4);
        $monthList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $yearlist = [1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $FactoryList = FactoryService::getList();
        $objPHPExcel->getActiveSheet()->setTitle("หน้า 6");
        // $objPHPExcel->getActiveSheet()->setCellValue('A2', $header);
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2.2 การสูญเสียน้ำนมสดและผลิตภัณฑ์นมของสำนักงานภาค');

        $data_produce = [];
        $data = [];
        $detail2 = [];
        $detail3 = [];
        $divide_value = 1000;

        if ($condition['DisplayType'] == 'monthly') {
            $objPHPExcel->getActiveSheet()->setCellValue('A4', 'ผลการดำเนินงาน เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('A4:F4');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'สะสม ' . $this->getMonthName(10) . ' - ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
            $objPHPExcel->getActiveSheet()->mergeCells('I4:N4');
            foreach ($FactoryList as $key => $fac) {
                // echo $condition['YearTo']. $condition['MonthFrom'].  $fac['id'];
                $produce = ProductionInfoService::getDetailListByFac($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $dataIN = LostInProcessService::getMainListreport($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $dataOUT = LostOutProcessService::getMainListreport($condition['YearTo'], $condition['MonthFrom'], $fac['id']);
                $dataWAIT = LostWaitSaleService::getMainListreport($condition['YearTo'], $condition['MonthFrom'], $fac['id']);

                array_push($data_produce, $produce);
                array_push($data, $dataIN);
                // print_r($dataIN);exit;
                array_push($data2, $dataOUT);
                array_push($data3, $dataWAIT);
            }
            foreach ($FactoryList as $key => $fac) {
                $detail = [];
                $detail2 = [];
                $detail3 = [];
                foreach ($monthList as $key => $ml) {

                    $produce = ProductionInfoService::getDetailListByFac($condition['YearTo'] - $yearlist[$key], $ml, $fac['id']);
                    $produce['amount'] += $produce['amount'];
                    $produce['sum_baht'] += $produce['sum_baht'];

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

                array_push($data_produce, $produce);
                array_push($data, $detail);
                array_push($data2, $detail2);
                array_push($data3, $detail3);
            }
        }

        $sumam = ($data[0]['sum_amount'] + $data[1]['sum_amount'] + $data[2]['sum_amount'] + $data[3]['sum_amount'] + $data[4]['sum_amount']) / $divide_value;
        $sumba = ($data[0]['sum_baht'] + $data[1]['sum_baht'] + $data[2]['sum_baht'] + $data[3]['sum_baht'] + $data[4]['sum_baht']) / 1000000;
        $bsumam = ($data[5]['sum_amount'] + $data[6]['sum_amount'] + $data[7]['sum_amount'] + $data[8]['sum_amount'] + $data[9]['sum_amount']) / $divide_value;
        $bsumba = ($data[5]['sum_baht'] + $data[6]['sum_baht'] + $data[7]['sum_baht'] + $data[8]['sum_baht'] + $data[9]['sum_baht']) / 1000000;
        $sumam2 = ($data2[0]['sum_amount'] + $data2[1]['sum_amount'] + $data2[2]['sum_amount'] + $data2[3]['sum_amount'] + $data2[4]['sum_amount']) / $divide_value;
        $sumba2 = ($data2[0]['sum_baht'] + $data2[1]['sum_baht'] + $data2[2]['sum_baht'] + $data2[3]['sum_baht'] + $data2[4]['sum_baht']) / 1000000;
        $bsumam2 = ($data2[5]['sum_amount'] + $data2[6]['sum_amount'] + $data2[7]['sum_amount'] + $data2[8]['sum_amount'] + $data2[9]['sum_amount']) / $divide_value;
        $bsumba2 = ($data2[5]['sum_baht'] + $data2[6]['sum_baht'] + $data2[7]['sum_baht'] + $data2[8]['sum_baht'] + $data2[9]['sum_baht']) / 1000000;
        $sumam3 = ($data3[0]['sum_amount'] + $data3[1]['sum_amount'] + $data3[2]['sum_amount'] + $data3[3]['sum_amount'] + $data3[4]['sum_amount']) / $divide_value;
        $sumba3 = ($data3[0]['sum_baht'] + $data3[1]['sum_baht'] + $data3[2]['sum_baht'] + $data3[3]['sum_baht'] + $data3[4]['sum_baht']) / 1000000;
        $bsumam3 = ($data3[5]['sum_amount'] + $data3[6]['sum_amount'] + $data3[7]['sum_amount'] + $data3[8]['sum_amount'] + $data3[9]['sum_amount']) / $divide_value;
        $bsumba3 = ($data3[5]['sum_baht'] + $data3[6]['sum_baht'] + $data3[7]['sum_baht'] + $data3[8]['sum_baht'] + $data3[9]['sum_baht']) / 1000000;

        $objPHPExcel->getActiveSheet()->setCellValue('G4', 'กิจกรรม ');
        $objPHPExcel->getActiveSheet()->mergeCells('G4:G5');
        $objPHPExcel->getActiveSheet()->setCellValue('H4', 'หน่วย ');
        $objPHPExcel->getActiveSheet()->mergeCells('H4:H5');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', "สภก.\nสระบุรี");
        $objPHPExcel->getActiveSheet()->setCellValue('B5', "สภต.\nปราณบุรี");
        $objPHPExcel->getActiveSheet()->setCellValue('C5', "สภอ.\nขอนแก่น");
        $objPHPExcel->getActiveSheet()->setCellValue('D5', "สภน.\nสุโขทัย");
        $objPHPExcel->getActiveSheet()->setCellValue('E5', "สภน.\nเชียงใหม่");
        $objPHPExcel->getActiveSheet()->setCellValue('F5', "รวม");
        $objPHPExcel->getActiveSheet()->setCellValue('I5', "สภก.\nสระบุรี");
        $objPHPExcel->getActiveSheet()->setCellValue('J5', "สภต.\nปราณบุรี");
        $objPHPExcel->getActiveSheet()->setCellValue('K5', "สภอ.\nขอนแก่น");
        $objPHPExcel->getActiveSheet()->setCellValue('L5', "สภน.\nสุโขทัย");
        $objPHPExcel->getActiveSheet()->setCellValue('M5', "สภน.\nเชียงใหม่");
        $objPHPExcel->getActiveSheet()->setCellValue('N5', 'รวม');

        // A5 - N5
        $objPHPExcel->getActiveSheet()
                ->getStyle('A5:N5')
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ),
                        )
        );

        $row = 6;
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '1.การสูญเสีย');
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row))->getFont()->setBold(true);
        $row++;

        
        
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data[0]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data[1]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data[2]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data[3]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data[4]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '   1.1 สูญเสียในกระบวนการผลิต ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data[5]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data[6]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data[7]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data[8]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data[9]['sum_amount'] / $divide_value);
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

        // print_r($data_produce);exit;
        $asri = ($data[0]['sum_amount'] / $data_produce[0]['amount']) * 100;
        $apri = ($data[1]['sum_amount'] / $data_produce[1]['amount']) * 100;
        $akkn = ($data[2]['sum_amount'] / $data_produce[2]['amount']) * 100;
        $asti = ($data[3]['sum_amount'] / $data_produce[3]['amount']) * 100;
        $acnx = ($data[4]['sum_amount'] / $data_produce[4]['amount']) * 100;
        $sumAin = (
                    ($data[0]['sum_amount'] + $data[1]['sum_amount'] + $data[2]['sum_amount'] + $data[3]['sum_amount'] + $data[4]['sum_amount']) /
                     ($data_produce[0]['amount'] + $data_produce[1]['amount'] + $data_produce[2]['amount'] + $data_produce[3]['amount'] + $data_produce[4]['amount'])
                ) * 100;

        $sri = ($data[5]['sum_amount'] / $data_produce[5]['amount']) * 100;
        $pri = ($data[6]['sum_amount'] / $data_produce[6]['amount']) * 100;
        $kkn = ($data[7]['sum_amount'] / $data_produce[7]['amount']) * 100;
        $sti = ($data[8]['sum_amount'] / $data_produce[8]['amount']) * 100;
        $cnx = ($data[9]['sum_amount'] / $data_produce[9]['amount']) * 100;
        $sumOCTin = (
                    ($data[5]['sum_amount'] + $data[6]['sum_amount'] + $data[7]['sum_amount'] + $data[8]['sum_amount'] + $data[9]['sum_amount']) /
                     ($data_produce[5]['amount'] + $data_produce[6]['amount'] + $data_produce[7]['amount'] + $data_produce[8]['amount'] + $data_produce[9]['amount'])
                ) * 100;


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
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data2[0]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data2[1]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data2[2]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data2[3]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data2[4]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam2);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '    1.2 สูญเสียหลังกระบวนการผลิต  ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data2[5]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data2[6]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data2[7]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data2[8]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data2[9]['sum_amount'] / $divide_value);
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

        $asri2 = ($data2[0]['sum_amount'] / $data_produce[0]['amount']) * 100;
        $apri2 = ($data2[1]['sum_amount'] / $data_produce[1]['amount']) * 100;
        $akkn2 = ($data2[2]['sum_amount'] / $data_produce[2]['amount']) * 100;
        $asti2 = ($data2[3]['sum_amount'] / $data_produce[3]['amount']) * 100;
        $acnx2 = ($data2[4]['sum_amount'] / $data_produce[4]['amount']) * 100;
        $sumAout = (
                    ($data2[0]['sum_amount'] + $data2[1]['sum_amount'] + $data2[2]['sum_amount'] + $data2[3]['sum_amount'] + $data2[4]['sum_amount']) /
                     ($data_produce[0]['amount'] + $data_produce[1]['amount'] + $data_produce[2]['amount'] + $data_produce[3]['amount'] + $data_produce[4]['amount'])
                ) * 100;

        $sri2 = ($data2[5]['sum_amount'] / $data_produce[5]['amount']) * 100;
        $pri2 = ($data2[6]['sum_amount'] / $data_produce[6]['amount']) * 100;
        $kkn2 = ($data2[7]['sum_amount'] / $data_produce[7]['amount']) * 100;
        $sti2 = ($data2[8]['sum_amount'] / $data_produce[8]['amount']) * 100;
        $cnx2 = ($data2[9]['sum_amount'] / $data_produce[9]['amount']) * 100;
        $sumOCTout = (
                    ($data2[5]['sum_amount'] + $data2[6]['sum_amount'] + $data2[7]['sum_amount'] + $data2[8]['sum_amount'] + $data2[9]['sum_amount']) /
                     ($data_produce[5]['amount'] + $data_produce[6]['amount'] + $data_produce[7]['amount'] + $data_produce[8]['amount'] + $data_produce[9]['amount'])
                ) * 100;

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
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $data3[0]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $data3[1]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $data3[2]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $data3[3]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $data3[4]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '   1.3 สูญเสียระหว่างรอจำหน่าย ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $data3[5]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), $data3[6]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), $data3[7]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), $data3[8]['sum_amount'] / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), $data3[9]['sum_amount'] / $divide_value);
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

        $asri3 = ($data3[0]['sum_amount'] / $data_produce[0]['amount']) * 100;
        $apri3 = ($data3[1]['sum_amount'] / $data_produce[1]['amount']) * 100;
        $akkn3 = ($data3[2]['sum_amount'] / $data_produce[2]['amount']) * 100;
        $asti3 = ($data3[3]['sum_amount'] / $data_produce[3]['amount']) * 100;
        $acnx3 = ($data3[4]['sum_amount'] / $data_produce[4]['amount']) * 100;
        $sumAw = (
                    ($data3[0]['sum_amount'] + $data3[1]['sum_amount'] + $data3[2]['sum_amount'] + $data3[3]['sum_amount'] + $data3[4]['sum_amount']) /
                     ($data_produce[0]['amount'] + $data_produce[1]['amount'] + $data_produce[2]['amount'] + $data_produce[3]['amount'] + $data_produce[4]['amount'])
                ) * 100;

        $sri3 = ($data3[5]['sum_amount'] / $data_produce[5]['amount']) * 100;
        $pri3 = ($data3[6]['sum_amount'] / $data_produce[6]['amount']) * 100;
        $kkn3 = ($data3[7]['sum_amount'] / $data_produce[7]['amount']) * 100;
        $sti3 = ($data3[8]['sum_amount'] / $data_produce[8]['amount']) * 100;
        $cnx3 = ($data3[9]['sum_amount'] / $data_produce[9]['amount']) * 100;
        $sumOCTw = (
                    ($data3[5]['sum_amount'] + $data3[6]['sum_amount'] + $data3[7]['sum_amount'] + $data3[8]['sum_amount'] + $data3[9]['sum_amount']) /
                     ($data_produce[5]['amount'] + $data_produce[6]['amount'] + $data_produce[7]['amount'] + $data_produce[8]['amount'] + $data_produce[9]['amount'])
                ) * 100;
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
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($data[0]['sum_amount'] + $data2[0]['sum_amount'] + $data3[0]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), ($data[1]['sum_amount'] + $data2[1]['sum_amount'] + $data3[1]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ($data[2]['sum_amount'] + $data2[2]['sum_amount'] + $data3[2]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), ($data[3]['sum_amount'] + $data2[3]['sum_amount'] + $data3[3]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), ($data[4]['sum_amount'] + $data2[4]['sum_amount'] + $data3[4]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumam + $sumam2 + $sumam3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '   1.4 สูญเสียทั้งกระบวนการผลิต  ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ตัน');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), ($data[5]['sum_amount'] + $data2[5]['sum_amount'] + $data3[5]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), ($data[6]['sum_amount'] + $data2[6]['sum_amount'] + $data3[6]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), ($data[7]['sum_amount'] + $data2[7]['sum_amount'] + $data3[7]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), ($data[8]['sum_amount'] + $data2[8]['sum_amount'] + $data3[8]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), ($data[9]['sum_amount'] + $data2[9]['sum_amount'] + $data3[9]['sum_amount']) / $divide_value);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . ($row), $bsumam + $bsumam2 + $bsumam3);
        $this->total_loss_amount = $bsumam + $bsumam2 + $bsumam3;

        $row++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), ($data[0]['sum_baht'] + $data2[0]['sum_baht'] + $data3[0]['sum_baht']) / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), ($data[1]['sum_baht'] + $data2[1]['sum_baht'] + $data3[1]['sum_baht']) / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), ($data[2]['sum_baht'] + $data2[2]['sum_baht'] + $data3[2]['sum_baht']) / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), ($data[3]['sum_baht'] + $data2[3]['sum_baht'] + $data3[3]['sum_baht']) / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), ($data[4]['sum_baht'] + $data2[4]['sum_baht'] + $data3[4]['sum_baht']) / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $sumba + $sumba2 + $sumba3);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), '           มูลค่า ');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), 'ล้านบาท');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), ($data[5]['sum_baht'] + $data2[5]['sum_baht'] + $data3[5]['sum_baht']) / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . ($row), ($data[6]['sum_baht'] + $data2[6]['sum_baht'] + $data3[6]['sum_baht']) / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . ($row), ($data[7]['sum_baht'] + $data2[7]['sum_baht'] + $data3[7]['sum_baht']) / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . ($row), ($data[8]['sum_baht'] + $data2[8]['sum_baht'] + $data3[8]['sum_baht']) / 1000000);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . ($row), ($data[9]['sum_baht'] + $data2[9]['sum_baht'] + $data3[9]['sum_baht']) / 1000000);
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
        $this->total_loss_amount_percent = $sumOCTin + $sumOCTout + $sumOCTw;
        $row++;


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

        return $objPHPExcel;        

    }


}