<?php

namespace App\Controller;

use App\Controller\VeterinaryController;
use App\Controller\InseminationController;
use PHPExcel;

class MonthReportController extends Controller {

    protected $logger;
    protected $db;

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;
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

    public function exportmonthreportExcel($request, $response) {
        try {
            $obj = $request->getParsedBody();

            $condition['YearFrom'] = $obj['obj']['condition']['Year'];
            $condition['YearTo'] = $obj['obj']['condition']['Year'];
            $condition['MonthFrom'] = $obj['obj']['condition']['Month'];
            $condition['MonthTo'] = $obj['obj']['condition']['Month'];
            $region = $obj['obj']['region'];


            $objPHPExcel = new PHPExcel();
            $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $region);
           // $objPHPExcel = $this->generateInseminationExcel($objPHPExcel, $condition, $region);

//
            $filename = 'MIS Report-รายงานรายเดือน' . '_' . date('YmdHis') . '.xlsx';
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

    private function generateVeterinaryExcel($objPHPExcel, $condition, $region) {
        $data = VeterinaryController::getMonthDataList($condition, $region);

        $objPHPExcel->getActiveSheet()->setTitle("2.1 สัตวแพท-ผสมเทียม (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.1 การบริการสัตวแพทย์และการบริการผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '            2.1.1 การบริการสัตวแพทย์');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . 'มีโคเข้ารับการบริการสัตวแพทย์ จำนวน ' . $data['Summary']['SummaryCurrentCow'] . 'ตัว รายได้ ' . $data['Summary']['SummaryCurrentService'] . '  บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . $data['Summary']['SummaryCowPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  และ ' . $data['Summary']['SummaryServicePercentage'] . ' ตามลำดับ');

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A10', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A10:A13');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B10:C10');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D10:E10');
        $objPHPExcel->getActiveSheet()->setCellValue('F10', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F10:I10');
        $objPHPExcel->getActiveSheet()->setCellValue('B11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('C11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('D11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('E11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('F11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('G11', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('H11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('I11', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('B12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('C12', '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('D12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('E12', '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('F12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('G12', 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('H12', '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('I12', 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('B13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('C13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('D13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('E13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('F13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('G13', 'ลด,');
        $objPHPExcel->getActiveSheet()->setCellValue('H13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('I13', 'ลด,');
        $row = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;
        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffCowData'];
            $summarydiffservice += $item['DiffServiceData'];
           $objPHPExcel->getActiveSheet()->setCellValue('A' . (14 + $row), $item['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $row), $item['CurrentCowData']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (14 + $row), $item['CurrentServiceData']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (14 + $row), $item['BeforeCowData']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (14 + $row), $item['BeforeServiceData']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (14 + $row), $item['DiffCowData']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (14 + $row), $item['DiffCowDataPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (14 + $row), $item['DiffServiceData']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (14 + $row), $item['DiffServiceDataPercentage']);
         $row++;
        }
//        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (14 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (14 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $row), $data['Summary']['SummaryCurrentCow']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (14 + $row), $data['Summary']['SummaryCurrentService']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (14 + $row), $data['Summary']['SummaryBeforeCow']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (14 + $row), $data['Summary']['SummaryBeforeService']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (14 + $row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (14 + $row), $data['Summary']['SummaryCowPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (14 + $row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (14 + $row), $data['Summary']['SummaryServicePercentage']);
        $highestRow = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
        $highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I13')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I13')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A10:I13")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);



        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B14:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A3:A8')->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateInseminationExcel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(1);
        $data = InseminationController::getMonthDataList($condition, $region);
        
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("2.1 สัตวแพท-ผสมเทียม (3)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.1 การบริการสัตวแพทย์และการบริการผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '            2.1.2 การบริการผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' มีโคเข้ารับการบริการผสมเทียม จำนวน ' . $data['Summary']['SummaryCurrentCowService'] . ' ตัว รายได้ ' . $data['Summary']['SummaryCurrentIncomeService'] . '  บาท ');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . $data['Summary']['SummaryCowServicePercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  และ ' . $data['Summary']['SummaryIncomeServicePercentage'] . ' ตามลำดับ');

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A10', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A10:A13');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B10:C10');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D10:E10');
        $objPHPExcel->getActiveSheet()->setCellValue('F10', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F10:I10');
        $objPHPExcel->getActiveSheet()->setCellValue('B11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('C11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('D11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('E11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('F11', 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('G11', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('H11', 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('I11', '%');
        $objPHPExcel->getActiveSheet()->setCellValue('B12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('C12', '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('D12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('E12', '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('F12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('G12', 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('H12', '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('I12', 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('B13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('C13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('D13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('E13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('F13', '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('G13', 'ลด,');
        $objPHPExcel->getActiveSheet()->setCellValue('H13', '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('I13', 'ลด,');
        $row = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;
        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffCowService'];
            $summarydiffservice += $item['DiffIncomeService'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (14 + $row), $item['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $row), $item['CurrentCowService']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (14 + $row), $item['CurrentIncomeService']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (14 + $row), $item['BeforeCowService']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (14 + $row), $item['BeforeIncomeService']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (14 + $row), $item['DiffCowService']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (14 + $row), $item['DiffCowServicePercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (14 + $row), $item['DiffIncomeService']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (14 + $row), $item['DiffIncomeServicePercentage']);
            $row++;
        }
        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (14 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (14 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (14 + $row), $data['Summary']['SummaryCurrentCowService']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (14 + $row), $data['Summary']['SummaryCurrentIncomeService']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (14 + $row), $data['Summary']['SummaryBeforeCowService']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (14 + $row), $data['Summary']['SummaryBeforeIncomeService']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (14 + $row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (14 + $row), $data['Summary']['SummaryCowServicePercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (14 + $row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (14 + $row), $data['Summary']['SummaryIncomeServicePercentage']);
        $highestRow = $objPHPExcel->setActiveSheetIndex(1)->getHighestRow();
        $highestColumm = $objPHPExcel->setActiveSheetIndex(1)->getHighestColumn();
        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I13')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I13')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A10:I13")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);



        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B14:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A10:I' . $highestRow)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A3:A8')->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );

        return $objPHPExcel;
    }

}
