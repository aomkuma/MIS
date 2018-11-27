<?php

namespace App\Controller;

use App\Service\FoodService;
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
                case 'quarter' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . ' ถึง ไตรมาสที่ ' . $condition['QuarterTo'] . ' ปี ' . ($condition['YearTo'] + 543);
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
                case 'quarter' :$header = 'ตารางข้อมูลรายงานด้าน รายได้กิจกรรมโคนม ไตรมาสที่ ' . $condition['QuarterFrom'] . ' ปี ' . ($condition['YearFrom'] + 543) . ' ถึง ไตรมาสที่ ' . $condition['QuarterTo'] . ' ปี ' . ($condition['YearTo'] + 543);
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

}
