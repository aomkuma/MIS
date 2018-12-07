<?php

namespace App\Controller;

use App\Service\FoodService;
use App\Service\MasterGoalService;
use App\Service\SpermService;
use App\Service\GoalMissionService;
use App\Service\TravelService;
use PHPExcel;

class ReportController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function exportReport($request, $response, $args) {
        try {
            $params = $request->getParsedBody();
            $actives = $params['obj']['actives'];
            $_List = FoodService::getList($actives);

            $this->data_result['DATA']['List'] = $_List;

            return $this->returnResponse(200, $this->data_result, $response, false);
        } catch (\Exception $e) {
            return $this->returnSystemErrorResponse($this->logger, $this->data_result, $e, $response);
        }
    }

    public function exportVeterinaryExcel($request, $response) {
        // error_reporting(E_ERROR);
        // error_reporting(E_ALL);
        // ini_set('display_errors','On');           
        try {
            $obj = $request->getParsedBody();

            $condition = $obj['obj']['condition'];
            $cooperative = $obj['obj']['CooperativeList'];
            $data = $obj['obj']['DetailList'];
            $description = $obj['obj']['data_description'];
            //$summary = $obj['summary'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ปี ' . ($condition['YearFrom'] + 543);
                    $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header);
                    break;
                case 'monthly' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม เดือน ' . $this->getMonthName($description['months']) . ' ปี ' . ($description['years'] + 543);
                    $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header);
                    break;
                case 'quarter' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ไตรมาสที่ ' . $description['quarter'] . ' ปี ' . ($condition['YearFrom'] + 543);
                    $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header);
                    break;

                default : $result = null;
            }

            $filename = 'Veterinary-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateVeterinaryExcel($objPHPExcel, $condition, $data, $cooperative, $header) {

        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('B2:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'หน่วย');

        $objPHPExcel->getActiveSheet()->setCellValue('D2', 'แผนกส่งเสริการเลี้ยงโคนมภาคกลาง');
        foreach ($cooperative as $key => $value) {

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, 3, $value['cooperative_name']);
        }
        $highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $highestColumm++;
        $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . '3', 'รวม');



        $con_row = 4;
        foreach ($data as $valuedata) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, $valuedata['DairyFarmingName']);

            $styleArrayname = array(
                'font' => array(
                    'bold' => true,
                    'size' => 16,
                    'name' => 'AngsanaUPC'
            ));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->applyFromArray($styleArrayname);
            $con_row++;

            if ($valuedata['Data'] != '' || !is_null($valuedata['Data'])) {
                $sum = array_fill(0, sizeof($valuedata['Data'][0]['Dataset']), 0);
                $sumary = 0;

                foreach ($valuedata['Data'] as $item) {

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, $item['ItemName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $con_row, $item['Unit']);
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    foreach ($item['Dataset'] as $key => $itemdata) {

                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, $con_row, $itemdata['Amount']);

                        if ($item['Unit'] == 'บาท') {

                            $sum[$key] += $itemdata['Amount'];
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . $con_row, $item['Summary']);
                    if ($item['Unit'] == 'บาท') {

                        $sumary += $item['Summary'];
                    }
                    $con_row++;
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, 'รวมจำนวนเงิน');
                $styleArraysum = array(
                    'font' => array(
                        'bold' => true,
                        'size' => 16,
                        'name' => 'AngsanaUPC'
                ));
                $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->applyFromArray($styleArraysum);
                // $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row . ':' . $highestColumm . $con_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'F2DCDB')
                            )
                        )
                );

                foreach ($sum as $key => $sumarys) {

                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, $con_row, $sumarys);
                }

                $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . $con_row, $sumary);
                $con_row++;
            }
        }

        $styleArray = array(
//             'borders' => array(
//                'allborders' => array(
//                    'style' => (\PHPExcel_Style_Border::BORDER_THIN)
//                )
//            ),
            'font' => array(
                'name' => 'AngsanaUPC',
                'size' => '16'
        ));
//        $objPHPExcel->getDefaultStyle()
//                ->applyFromArray($styleArray);
        // header style
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColumm . '1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A' . $con_row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A2:B3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:B3')->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('D2:' . $highestColumm . '2');
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setSize(12);

        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getFont()->setSize(12);

        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B3:' . $highestColumm . $con_row)->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle($highestColumm . '3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A2:" . $highestColumm . "3")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D:' . $highestColumm)->setWidth(10);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . '3')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . ($con_row - 1))->applyFromArray(
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

    public function exportMineralExcel($request, $response) {
        // error_reporting(E_ERROR);
        // error_reporting(E_ALL);
        // ini_set('display_errors','On');           
        try {
            $obj = $request->getParsedBody();

            $condition = $obj['obj']['condition'];
            $item = $obj['obj']['Item'];
            $itemunit = $obj['obj']['ItemUnit'];
            $data = $obj['obj']['DetailList'];
            $description = $obj['obj']['data_description'];
            //$summary = $obj['summary'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ปี ' . ($condition['YearFrom'] + 543);
                    $objPHPExcel = $this->generateMineralExcel($objPHPExcel, $condition, $data, $header, $item, $itemunit);
                    break;
                case 'monthly' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม เดือน ' . $this->getMonthName($description['months']) . ' ปี ' . ($description['years'] + 543);
                    $objPHPExcel = $this->generateMineralExcel($objPHPExcel, $condition, $data, $header, $item, $itemunit);
                    break;
                case 'quarter' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ไตรมาสที่ ' . $description['quarter'] . ' ปี ' . ($condition['YearFrom'] + 543);
                    $objPHPExcel = $this->generateMineralExcel($objPHPExcel, $condition, $data, $header, $item, $itemunit);
                    break;

                default : $result = null;
            }

            $filename = 'Mineral-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateMineralExcel($objPHPExcel, $condition, $data, $header, $item, $itemunit) {

        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('B2:C3');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'หน่วย');

        $objPHPExcel->getActiveSheet()->setCellValue('D2', 'แผนกส่งเสริการเลี้ยงโคนมภาคกลาง');
        foreach ($data as $key => $value) {

            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, 3, $value['RegionName']);
        }
        $highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $highestColumm++;
        $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . '3', 'รวม');



        $con_row = 4;

        $sizesum = sizeof($data) + 1;
        $sum = array_fill(0, $sizesum, 0);
        $index = 1;
        $indexkg = 0;
        $sum = [];
        foreach ($item as $key => $valueitem) {
            $sumrow = 0;
            $sumkg = 0;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, $valueitem['label']);
            if ($itemunit[$key + $key]['label'] == 'กิโลกรัม') {
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $con_row, 'กก.');
                $objPHPExcel->getActiveSheet()->getStyle('B' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $con_row, $itemunit[$key + $key]['label']);
            }

            foreach ($data as $keydata => $valueitemsdata) {
                $sumrow += $valueitemsdata['ValueList'][$index]['values'];
                $sumkg += $valueitemsdata['ValueList'][$indexkg]['values'];
                $sum[$keydata] += $valueitemsdata['ValueList'][$index]['values'];
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $keydata, $con_row, $valueitemsdata['ValueList'][$index]['values']);
            }
            $sum[sizeof($data)] += $sumrow;
            //   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $key, $con_row, $data['ValueList'][$key + $key]['values']);
            $objPHPExcel->getActiveSheet()->setCellValue($highestColumm . $con_row, $sumrow);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $con_row, $sumkg);
            $con_row++;
            $index += 2;
            $indexkg += 2;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $con_row, 'รวมจำนวนเงิน');
        $styleArraysum = array(
            'font' => array(
                'bold' => true,
                'size' => 16,
                'name' => 'AngsanaUPC'
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $con_row)->applyFromArray($styleArraysum);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $con_row, 'บาท');

        $objPHPExcel->getActiveSheet()->getStyle('B' . $con_row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        foreach ($sum as $keysum => $sums) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3 + $keysum, $con_row, $sums);
        }
        // header style
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColumm . '1');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A' . $con_row)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A2:B3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A2:B3')->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('D2:' . $highestColumm . '2');
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setSize(12);

        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getFont()->setSize(12);

        $objPHPExcel->getActiveSheet()->getStyle('D3:' . $highestColumm . '3')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B3:' . $highestColumm . $con_row)->getFont()->setSize(12);
        $objPHPExcel->getActiveSheet()->getStyle($highestColumm . '3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A2:" . $highestColumm . "3")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D:' . $highestColumm)->setWidth(10);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . '3')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColumm . ($con_row ))->applyFromArray(
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

    public function exportSpermExcel($request, $response) {
        // error_reporting(E_ERROR);
        // error_reporting(E_ALL);
        // ini_set('display_errors','On');           
        try {
            $obj = $request->getParsedBody();
            $mastesgoallist = MasterGoalService::getList('Y');
            //  print_r($mastesgoallist->toArray());

            $condition = $obj['obj']['condition'];
//            $cooperative = $obj['obj']['CooperativeList'];
            $data = $obj['obj']['data'];
//            $description = $obj['obj']['data_description'];

            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ปี ' . ($data['Description']['years'] + 543);
                    $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType']);
                    break;
                case 'monthly' : $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
                    $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType']);
                    break;
                case 'quarter' :$header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม ไตรมาสที่ ' . $data['Quarter'] . ' ปี ' . ($data['Description']['years'] + 543);
                    $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType']);
                    break;

                default : $result = null;
            }
//            $header = 'ฝ่ายวิจัยและพัฒนาการเลี้ยงโคนม เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
//            $objPHPExcel = $this->generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data);
            $filename = 'Sperm-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateSpermExcel($objPHPExcel, $mastesgoallist, $header, $data, $type) {

        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);



        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'การผลิตน้ำเชื้อแช่แข็ง');

        $objPHPExcel->getActiveSheet()->setCellValue('A3', 'กิจกรรม/ผลิตภัณฑ์/สินค้า/บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', 'หน่วย');
        $objPHPExcel->getActiveSheet()->setCellValue('C3', 'เป้าหมายทั้งปี');

        $row = 0;
        $goal = GoalMissionService::getyearGoal($data['Description']['region_id'], $data['Description']['years']);


        if ($type == 'annually') {
            //  $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ผลการดำเนินงานประจำปี');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'เปรียบเทียบเป้าหมาย');
            foreach ($goal as $key => $value) {

                $mastesgoallist = MasterGoalService::getData($value['goal_id']);
                $spmonth = SpermService::getDetailyear($data['Description']['years'], $value['goal_id'], $data['Description']['region_id']);
                if (sizeof($spmonth) > 0) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), $mastesgoallist['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        มูลค่า' . $mastesgoallist['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                    ///goal
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), $value['unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $value['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $value['price_value']);
//                $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), round($value['amount'] , 2));
//                $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), round($value['price_value'] , 2));
/// month
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), $spmonth['amount']);

                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), $spmonth['price']);
//compare
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $spmonth['amount'] - round($value['amount'], 2));

                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $spmonth['price'] - round($value['price_value'], 2));
                    $row += 2;
                }
            }
        } else if ($type == 'monthly') {

            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำเดือน');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            foreach ($goal as $key => $value) {
                $mastesgoallist = MasterGoalService::getData($value['goal_id']);
                $spmonth = SpermService::getDetailmonth($data['Description']['years'], $data['Description']['months'], $value['goal_id'], $data['Description']['region_id']);

                if (sizeof($spmonth) > 0) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), $mastesgoallist['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        มูลค่า' . $mastesgoallist['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                    ///goal
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), $value['unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $value['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $value['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), round($value['amount'] / 12, 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), round($value['price_value'] / 12, 2));
/// month
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $spmonth['amount']);

                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $spmonth['price']);
//compare
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $spmonth['amount'] - round($value['amount'] / 12, 2));

                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $spmonth['price'] - round($value['price_value'] / 12, 2));
                    $row += 2;
                }
            }
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'เป้าหมาย ประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('E3', 'ผลการดำเนินงานประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'เปรียบเทียบเป้าหมาย');
            foreach ($goal as $key => $value) {
                $mastesgoallist = MasterGoalService::getData($value['goal_id']);
                $spmonth = SpermService::getDetailquar($data['Description']['years'], $value['goal_id'], $data['Description']['region_id'], $data['Quarter']);
                if (sizeof($spmonth) > 0) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (4 + $row), $mastesgoallist['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . (5 + $row), '        มูลค่า' . $mastesgoallist['goal_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (5 + $row), 'บาท');
                    ///goal
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . (4 + $row), $value['unit']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (4 + $row), $value['amount']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . (5 + $row), $value['price_value']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (4 + $row), round($value['amount'] / 3, 2));
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . (5 + $row), round($value['price_value'] / 3, 2));
/// month
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (4 + $row), $spmonth['amount']);

                    $objPHPExcel->getActiveSheet()->setCellValue('E' . (5 + $row), $spmonth['price']);
//compare
                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (4 + $row), $spmonth['amount'] - round($value['amount'] / 3, 2));

                    $objPHPExcel->getActiveSheet()->setCellValue('F' . (5 + $row), $spmonth['price'] - round($value['price_value'] / 3, 2));
                    $row += 2;
                }
            }
        }


        $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        $highestColum = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColum . '1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:' . $highestColum . '2');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2')->getFont()->setSize(18);


        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A4:A' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4:A' . $highestRow)->getFont()->setSize(14);

        $objPHPExcel->getActiveSheet()->getStyle('B4:' . $highestColum . $highestRow)->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->getStyle('B4:B' . $highestRow)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $highestColum . '3')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)->applyFromArray(
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

    public function exportTravelExcel($request, $response) {

        try {
            $obj = $request->getParsedBody();
            $mastesgoallist = MasterGoalService::getList('Y');

            //  $condition = $obj['obj']['condition'];
            // $data = $obj['obj']['data'];
            $data['Description']['years'] = 2018;
            $condition['DisplayType'] = 'annually';
            $data['Description']['months'] = 1;
            $data['Quarter'] = 1;
            $data['Description']['region_id'] = 3;
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

            $catch_result = \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

            $objPHPExcel = new PHPExcel();

            switch ($condition['DisplayType']) {
                case 'annually' :$header = 'รายงานผลการดำเนินงาน ฝ่ายท่องเที่ยวเชิงเกษตร ปี ' . ($data['Description']['years'] + 543);
                    $objPHPExcel = $this->generateTravelExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType']);
                    break;
                case 'monthly' : $header = 'รายงานผลการดำเนินงาน ฝ่ายท่องเที่ยวเชิงเกษตร เดือน ' . $this->getMonthName($data['Description']['months']) . ' ปี ' . ($data['Description']['years'] + 543);
                    $objPHPExcel = $this->generateTravelExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType']);
                    break;
                case 'quarter' :$header = 'รายงานผลการดำเนินงาน ฝ่ายท่องเที่ยวเชิงเกษตร ไตรมาสที่ ' . $data['Quarter'] . ' ปี ' . ($data['Description']['years'] + 543);
                    $objPHPExcel = $this->generateTravelExcel($objPHPExcel, $mastesgoallist, $header, $data, $condition['DisplayType']);
                    break;

                default : $result = null;
            }

            $filename = 'Travel-' . $condition['DisplayType'] . '_' . date('YmdHis') . '.xlsx';
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

    private function generateTravelExcel($objPHPExcel, $mastesgoallist, $header, $data, $type) {

        $objPHPExcel->getActiveSheet()->setCellValue('A1', $header);
        $objPHPExcel->getActiveSheet()->setCellValue('A2', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:A4');
        $objPHPExcel->getActiveSheet()
                ->getStyle("A2:A4")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $objPHPExcel->getActiveSheet()->setCellValue('B2', 'เป้าหมายทั้งปี');
        $objPHPExcel->getActiveSheet()->mergeCells('B2:C3');
        $objPHPExcel->getActiveSheet()
                ->getStyle("B2:C3")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->setCellValue('A5', 'รายได้จากการท่องเที่ยว');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '  - บุคคลทั่วไป (ผู้ใหญ่)');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '  - บุคคลทั่วไป (เด็ก)');
        $objPHPExcel->getActiveSheet()->setCellValue('A8', '  - นักศึกษา');
        $objPHPExcel->getActiveSheet()->setCellValue('A9', '  - จำนวนผู้เข้าชมที่ยกเว้น');
        $objPHPExcel->getActiveSheet()->setCellValue('A10', 'รวมรายได้ทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A6:A9')->getFont()->setSize(16);
        $row = 0;

        $mastesgoaladult = MasterGoalService::getmisiontravel('ท่องเที่ยวผู้ใหญ่');
        $missionad = GoalMissionService::getGoaltravel($mastesgoaladult[0]['id'], $data['Description']['region_id'], $data['Description']['years']);
        $mastesgoalchild = MasterGoalService::getmisiontravel('ท่องเที่ยวเด็ก');
        $missionch = GoalMissionService::getGoaltravel($mastesgoalchild[0]['id'], $data['Description']['region_id'], $data['Description']['years']);
        $mastesgoalstudent = MasterGoalService::getmisiontravel('ท่องเที่ยวนักศึกษา');
        $missionst = GoalMissionService::getGoaltravel($mastesgoalstudent[0]['id'], $data['Description']['region_id'], $data['Description']['years']);



        $tvmonth = TravelService::getDetailmonth($data['Description']['years'], $data['Description']['months'], $data['Description']['region_id']);
        if ($type == 'annually') {
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'มูลค่า (บาท)');

          

            $objPHPExcel->getActiveSheet()->setCellValue('D2', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->mergeCells('D2:E2');

            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('F2', 'เปรียบเทียบ % เป้าหมาย');
            $objPHPExcel->getActiveSheet()->mergeCells('F2:G2');

            $objPHPExcel->getActiveSheet()->setCellValue('F4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'มูลค่า (บาท)');
            $objPHPExcel->getActiveSheet()->setCellValue('D3', 'ประจำปี ' . substr($data['Description']['years'] + 543, 2, 5));
            $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("D3:E3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ประจำปี');
            $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("F2:G3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
//            ///goal

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6), $missionad[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6), $missionad[0]['price_value']);


            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7), $missionch[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7), $missionch[0]['price_value']);


            $objPHPExcel->getActiveSheet()->setCellValue('B' . (8), $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (8), $missionst[0]['price_value']);


            $objPHPExcel->getActiveSheet()->setCellValue('D6', $tvmonth['apay']);
            $objPHPExcel->getActiveSheet()->setCellValue('E6', $tvmonth['p_adult']);
            $objPHPExcel->getActiveSheet()->setCellValue('D7', $tvmonth['cpay']);
            $objPHPExcel->getActiveSheet()->setCellValue('E7', $tvmonth['p_child']);
            $objPHPExcel->getActiveSheet()->setCellValue('D8', $tvmonth['spay']);
            $objPHPExcel->getActiveSheet()->setCellValue('E8', $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('D9', $tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);


////compare
            $objPHPExcel->getActiveSheet()->setCellValue('F6', $tvmonth['apay'] - round($missionad[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('G6', $tvmonth['p_adult'] - round($missionad[0]['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F7', $tvmonth['cpay'] - round($missionch[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('G7', $tvmonth['p_child'] - round($missionch[0]['price_value'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F8', $tvmonth['spay'] - round($missionst[0]['amount'], 2));
            $objPHPExcel->getActiveSheet()->setCellValue('G8', $tvmonth['p_student'] - round($missionst[0]['price_value'], 2));


//  sum
            $objPHPExcel->getActiveSheet()->setCellValue('B10', $missionad[0]['amount'] + $missionch[0]['amount'] + $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C10', $missionad[0]['price_value'] + $missionch[0]['price_value'] + $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D10', $tvmonth['apay'] + $tvmonth['cpay'] + $tvmonth['spay']+$tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);
            $objPHPExcel->getActiveSheet()->setCellValue('E10', $tvmonth['p_adult'] + $tvmonth['p_child'] + $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('F10', ($tvmonth['apay'] - round($missionad[0]['amount'], 2)) + ( $tvmonth['cpay'] - round($missionch[0]['amount'], 2)) + ($tvmonth['spay'] - round($missionst[0]['amount'], 2)));
            $objPHPExcel->getActiveSheet()->setCellValue('G10', ($tvmonth['p_adult'] - round($missionad[0]['price_value'], 2)) + ($tvmonth['p_child'] - round($missionch[0]['price_value'], 2)) + ($tvmonth['p_student'] - round($missionst[0]['price_value'], 2)));
        } else if ($type == 'monthly') {
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('F2', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->mergeCells('F2:G2');

            $objPHPExcel->getActiveSheet()->setCellValue('F4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('H2', 'เปรียบเทียบ % เป้าหมาย');
            $objPHPExcel->getActiveSheet()->mergeCells('H2:I2');

            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'มูลค่า (บาท)');
            $objPHPExcel->getActiveSheet()->setCellValue('D2', 'เป้าหมายประจำเดือน');
            $objPHPExcel->getActiveSheet()->mergeCells('D2:E3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("D2:E3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ประจำเดือน ' . $this->getMonthshName($data['Description']['months']) . ' ' . substr($data['Description']['years'] + 543, 2, 5));
            $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("F2:G3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('H3', 'ประจำเดือน');
            $objPHPExcel->getActiveSheet()->mergeCells('H3:I3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("H2:I3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
//            ///goal

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6), $missionad[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6), $missionad[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6), round($missionad[0]['amount'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6), round($missionad[0]['price_value'] / 12, 2));

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7), $missionch[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7), $missionch[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7), round($missionch[0]['amount'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7), round($missionch[0]['price_value'] / 12, 2));

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (8), $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (8), $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (8), round($missionst[0]['amount'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (8), round($missionst[0]['price_value'] / 12, 2));
///// month
            $objPHPExcel->getActiveSheet()->setCellValue('F6', $tvmonth['apay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G6', $tvmonth['p_adult']);
            $objPHPExcel->getActiveSheet()->setCellValue('F7', $tvmonth['cpay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G7', $tvmonth['p_child']);
            $objPHPExcel->getActiveSheet()->setCellValue('F8', $tvmonth['spay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G8', $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('F9', $tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);


////compare
            $objPHPExcel->getActiveSheet()->setCellValue('H6', $tvmonth['apay'] - round($missionad[0]['amount'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I6', $tvmonth['p_adult'] - round($missionad[0]['price_value'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H7', $tvmonth['cpay'] - round($missionch[0]['amount'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I7', $tvmonth['p_child'] - round($missionch[0]['price_value'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H8', $tvmonth['spay'] - round($missionst[0]['amount'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I8', $tvmonth['p_student'] - round($missionst[0]['price_value'] / 12, 2));


//  sum
            $objPHPExcel->getActiveSheet()->setCellValue('B10', $missionad[0]['amount'] + $missionch[0]['amount'] + $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C10', $missionad[0]['price_value'] + $missionch[0]['price_value'] + $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D10', round($missionad[0]['amount'] / 12, 2) + round($missionch[0]['amount'] / 12, 2) + round($missionst[0]['amount'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E10', round($missionad[0]['price_value'] / 12, 2) + round($missionch[0]['price_value'] / 12, 2) + round($missionst[0]['price_value'] / 12, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F10', $tvmonth['apay'] + $tvmonth['cpay'] + $tvmonth['spay']+$tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);
            $objPHPExcel->getActiveSheet()->setCellValue('G10', $tvmonth['p_adult'] + $tvmonth['p_child'] + $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('H10', ($tvmonth['apay'] - round($missionad[0]['amount'] / 12, 2)) + ( $tvmonth['cpay'] - round($missionch[0]['amount'] / 12, 2)) + ($tvmonth['spay'] - round($missionst[0]['amount'] / 12, 2)));
            $objPHPExcel->getActiveSheet()->setCellValue('I10', ($tvmonth['p_adult'] - round($missionad[0]['price_value'] / 12, 2)) + ($tvmonth['p_child'] - round($missionch[0]['price_value'] / 12, 2)) + ($tvmonth['p_student'] - round($missionst[0]['price_value'] / 12, 2)));
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('B4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('C4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('D4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('E4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('F2', 'ผลการดำเนินงาน');
            $objPHPExcel->getActiveSheet()->mergeCells('F2:G2');

            $objPHPExcel->getActiveSheet()->setCellValue('F4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('G4', 'มูลค่า (บาท)');

            $objPHPExcel->getActiveSheet()->setCellValue('H2', 'เปรียบเทียบ % เป้าหมาย');
            $objPHPExcel->getActiveSheet()->mergeCells('H2:I2');

            $objPHPExcel->getActiveSheet()->setCellValue('H4', 'จำนวน ');
            $objPHPExcel->getActiveSheet()->setCellValue('I4', 'มูลค่า (บาท)');
            $objPHPExcel->getActiveSheet()->setCellValue('D2', 'ประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->mergeCells('D2:E3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("D2:E3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('F3', 'ประจำไตรมาส ' . $this->getMonthshName($data['Description']['quarter']) . ' ' . substr($data['Description']['years'] + 543, 2, 5));
            $objPHPExcel->getActiveSheet()->mergeCells('F3:G3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("F2:G3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
            $objPHPExcel->getActiveSheet()->setCellValue('H3', 'ประจำไตรมาส');
            $objPHPExcel->getActiveSheet()->mergeCells('H3:I3');
            $objPHPExcel->getActiveSheet()
                    ->getStyle("H2:I3")
                    ->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
                            )
            );
//            ///goal

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (6), $missionad[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (6), $missionad[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (6), round($missionad[0]['amount'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (6), round($missionad[0]['price_value'] / 3, 2));

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (7), $missionch[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (7), $missionch[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (7), round($missionch[0]['amount'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (7), round($missionch[0]['price_value'] / 3, 2));

            $objPHPExcel->getActiveSheet()->setCellValue('B' . (8), $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (8), $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (8), round($missionst[0]['amount'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (8), round($missionst[0]['price_value'] / 3, 2));
///// month
            $objPHPExcel->getActiveSheet()->setCellValue('F6', $tvmonth['apay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G6', $tvmonth['p_adult']);
            $objPHPExcel->getActiveSheet()->setCellValue('F7', $tvmonth['cpay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G7', $tvmonth['p_child']);
            $objPHPExcel->getActiveSheet()->setCellValue('F8', $tvmonth['spay']);
            $objPHPExcel->getActiveSheet()->setCellValue('G8', $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('F9', $tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);


////compare
            $objPHPExcel->getActiveSheet()->setCellValue('H6', $tvmonth['apay'] - round($missionad[0]['amount'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I6', $tvmonth['p_adult'] - round($missionad[0]['price_value'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H7', $tvmonth['cpay'] - round($missionch[0]['amount'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I7', $tvmonth['p_child'] - round($missionch[0]['price_value'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('H8', $tvmonth['spay'] - round($missionst[0]['amount'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('I8', $tvmonth['p_student'] - round($missionst[0]['price_value'] / 3, 2));


//  sum
            $objPHPExcel->getActiveSheet()->setCellValue('B10', $missionad[0]['amount'] + $missionch[0]['amount'] + $missionst[0]['amount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C10', $missionad[0]['price_value'] + $missionch[0]['price_value'] + $missionst[0]['price_value']);
            $objPHPExcel->getActiveSheet()->setCellValue('D10', round($missionad[0]['amount'] / 3, 2) + round($missionch[0]['amount'] / 3, 2) + round($missionst[0]['amount'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('E10', round($missionad[0]['price_value'] / 3, 2) + round($missionch[0]['price_value'] / 3, 2) + round($missionst[0]['price_value'] / 3, 2));
            $objPHPExcel->getActiveSheet()->setCellValue('F10', $tvmonth['apay'] + $tvmonth['cpay'] + $tvmonth['spay']+$tvmonth['a_except'] + $tvmonth['c_except'] + $tvmonth['a_except']);
            $objPHPExcel->getActiveSheet()->setCellValue('G10', $tvmonth['p_adult'] + $tvmonth['p_child'] + $tvmonth['p_student']);
            $objPHPExcel->getActiveSheet()->setCellValue('H10', ($tvmonth['apay'] - round($missionad[0]['amount'] / 3, 2)) + ( $tvmonth['cpay'] - round($missionch[0]['amount'] / 3, 2)) + ($tvmonth['spay'] - round($missionst[0]['amount'] / 3, 2)));
            $objPHPExcel->getActiveSheet()->setCellValue('I10', ($tvmonth['p_adult'] - round($missionad[0]['price_value'] / 3, 2)) + ($tvmonth['p_child'] - round($missionch[0]['price_value'] / 3, 2)) + ($tvmonth['p_student'] - round($missionst[0]['price_value'] / 3, 2)));
        }



        $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        $highestColum = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $highestColum . '1');

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A5')->getFont()->setSize(18);


        $objPHPExcel->getActiveSheet()->getStyle('B2:' . $highestColum . '4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B2:' . $highestColum . '4')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('B2:' . $highestColum . '4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B5:I9')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A10')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I10')->getFont()->setSize(16);


        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A2:' . $highestColum . '4')->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'BFBFBF')
                    )
                )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $highestColum . $highestRow)->applyFromArray(
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
