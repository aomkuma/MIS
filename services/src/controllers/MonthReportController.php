<?php

namespace App\Controller;

use App\Controller\CowGroupController;
use App\Controller\MineralController;
use App\Controller\VeterinaryController;
use App\Controller\InseminationController;
use App\Controller\TrainingCowBreedController;
use PHPExcel;

class MonthReportController extends Controller {

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

    public function exportmonthreportExcel($request, $response) {
        try {
            $obj = $request->getParsedBody();

            $condition['YearFrom'] = $obj['obj']['condition']['Year'];
            $condition['YearTo'] = $obj['obj']['condition']['Year'];
            $condition['MonthFrom'] = $obj['obj']['condition']['Month'];
            $condition['MonthTo'] = $obj['obj']['condition']['Month'];
            $region = $obj['obj']['region'];


            $objPHPExcel = new PHPExcel();
            $objPHPExcel = $this->generatePowerExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generatePower2Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateVeterinaryExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateInseminationExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateMineralExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateTrainingCowbreedExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateTravelExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateSpermSaleExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateSpermSale2Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCooperativeMilkExcel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCooperativeMilk2Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCooperativeMilk3Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCooperativeMilk4Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCooperativeMilk5Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCooperativeMilk6Excel($objPHPExcel, $condition, $region);
            $objPHPExcel = $this->generateCooperativeMilk7Excel($objPHPExcel, $condition, $region);
            

            // $filename = 'MIS_Report-รายงานรายเดือน' . '_' . date('YmdHis') . '.xlsx';
            $filename = 'MIS_Report-monthly_' . '_' . date('YmdHis') . '.xlsx';
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

    private function generatePowerExcel($objPHPExcel, $condition, $region) {

        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->setTitle("1. อัตรากำลัง");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        return $objPHPExcel;
    }

    private function generatePower2Excel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(1);
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle("1. อัตรากำลัง (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        return $objPHPExcel;
    }

    private function generateVeterinaryExcel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(2);
        $data = VeterinaryController::getMonthDataList($condition, $region);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->setActiveSheetIndex(2);
        $objPHPExcel->getActiveSheet()->setTitle("2.1 สัตวแพทย์-ผสมเทียม (2)");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.1 การบริการสัตวแพทย์และการบริการผสมเทียม');
        $objPHPExcel->getActiveSheet()->setCellValue('A5', '            2.1.1 การบริการสัตวแพทย์');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                       เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' มีโคเข้ารับการบริการสัตวแพทย์ จำนวน ' . number_format($data['Summary']['SummaryCurrentCow'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($data['Summary']['SummaryCurrentService'], 2, '.', ',') . '  บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($data['Summary']['SummaryCowPercentage'], 2, '.', ','));
        $objPHPExcel->getActiveSheet()->setCellValue('A8', '                  และ ' . number_format($data['Summary']['SummaryServicePercentage'], 2, '.', ',') . ' ตามลำดับ');

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
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumm = $objPHPExcel->getActiveSheet()->getHighestColumn();
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
        ///////tb 2
        $tb2data = $data;
        while ($condition['MonthFrom'] != 10) {
            $condition['MonthFrom'] -= 1;
            if ($condition['MonthFrom'] == 0) {
                $condition['MonthFrom'] = 12;
                $condition['MonthTo'] = 12;
                $condition['YearTo'] -= 1;
                $condition['YearFrom'] -= 1;
                $newdata = VeterinaryController::getMonthDataList($condition, $region);

                foreach ($newdata['DataList'] as $key => $itemnewdata) {
                    $tb2data['DataList'][$key]['CurrentCowData'] += $itemnewdata['CurrentCowData'];
                    $tb2data['DataList'][$key]['CurrentServiceData'] += $itemnewdata['CurrentServiceData'];
                    $tb2data['DataList'][$key]['BeforeCowData'] += $itemnewdata['BeforeCowData'];
                    $tb2data['DataList'][$key]['BeforeServiceData'] += $itemnewdata['BeforeServiceData'];
                    $tb2data['DataList'][$key]['DiffCowData'] += $itemnewdata['DiffCowData'];
                    $tb2data['DataList'][$key]['DiffCowDataPercentage'] += $itemnewdata['DiffCowDataPercentage'];
                    $tb2data['DataList'][$key]['DiffServiceData'] += $itemnewdata['DiffServiceData'];
                    $tb2data['DataList'][$key]['DiffServiceDataPercentage'] += $itemnewdata['DiffServiceDataPercentage'];
                    $tb2data['Summary']['SummaryCurrentCow'] += $newdata['Summary']['SummaryCurrentCow'];
                    $tb2data['Summary']['SummaryCurrentService'] += $newdata['Summary']['SummaryCurrentService'];
                    $tb2data['Summary']['SummaryBeforeCow'] += $newdata['Summary']['SummaryBeforeCow'];
                    $tb2data['Summary']['SummaryBeforeService'] += $newdata['Summary']['SummaryBeforeService'];
                    $tb2data['Summary']['SummaryCowPercentage'] += $newdata['Summary']['SummaryCowPercentage'];
                    $tb2data['Summary']['SummaryServicePercentage'] += $newdata['Summary']['SummaryServicePercentage'];
                }
            } else {
                $condition['MonthTo'] -= 1;

                $newdata = VeterinaryController::getMonthDataList($condition, $region);
                foreach ($newdata['DataList'] as $key => $itemnewdata) {
                    $tb2data['DataList'][$key]['CurrentCowData'] += $itemnewdata['CurrentCowData'];
                    $tb2data['DataList'][$key]['CurrentServiceData'] += $itemnewdata['CurrentServiceData'];
                    $tb2data['DataList'][$key]['BeforeCowData'] += $itemnewdata['BeforeCowData'];
                    $tb2data['DataList'][$key]['BeforeServiceData'] += $itemnewdata['BeforeServiceData'];
                    $tb2data['DataList'][$key]['DiffCowData'] += $itemnewdata['DiffCowData'];
                    $tb2data['DataList'][$key]['DiffCowDataPercentage'] += $itemnewdata['DiffCowDataPercentage'];
                    $tb2data['DataList'][$key]['DiffServiceData'] += $itemnewdata['DiffServiceData'];
                    $tb2data['DataList'][$key]['DiffServiceDataPercentage'] += $itemnewdata['DiffServiceDataPercentage'];
                    $tb2data['Summary']['SummaryCurrentCow'] += $newdata['Summary']['SummaryCurrentCow'];
                    $tb2data['Summary']['SummaryCurrentService'] += $newdata['Summary']['SummaryCurrentService'];
                    $tb2data['Summary']['SummaryBeforeCow'] += $newdata['Summary']['SummaryBeforeCow'];
                    $tb2data['Summary']['SummaryBeforeService'] += $newdata['Summary']['SummaryBeforeService'];
                    $tb2data['Summary']['SummaryCowPercentage'] += $newdata['Summary']['SummaryCowPercentage'];
                    $tb2data['Summary']['SummaryServicePercentage'] += $newdata['Summary']['SummaryServicePercentage'];
                }
            }
        }
        $highestRow += 2;
        $startrowtb2 = $highestRow;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' มีโคเข้ารับการบริการสัตวแพทย์ จำนวน ' . number_format($tb2data['Summary']['SummaryCurrentCow'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($tb2data['Summary']['SummaryCurrentService'], 2, '.', ',') . '  บาท');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($tb2data['Summary']['SummaryCowPercentage'], 2, '.', ','));
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  และ ' . number_format($tb2data['Summary']['SummaryServicePercentage'], 2, '.', ',') . ' ตามลำดับ');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $highestRow . ':A' . ($highestRow + 3));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $highestRow . ':C' . $highestRow);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . $highestRow . ':E' . $highestRow);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F' . $highestRow . ':I' . $highestRow);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, '%');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, '%');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, '+เวชภัณฑ์+วัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, 'ลด,');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, 'ลด,');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;



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

        //  print_r($data);
        //   die();
        $row = $highestRow;

        foreach ($tb2data['DataList'] as $item2) {
            $summarydiffcow += $item['DiffCowData'];
            $summarydiffservice += $item['DiffServiceData'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $item2['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $item2['CurrentCowData']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $item2['CurrentServiceData']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $item2['BeforeCowData']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $item2['BeforeServiceData']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $item2['DiffCowData']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $item2['DiffCowDataPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $item2['DiffServiceData']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $item2['DiffServiceDataPercentage']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tb2data['Summary']['SummaryCurrentCow']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tb2data['Summary']['SummaryCurrentService']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tb2data['Summary']['SummaryBeforeCow']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tb2data['Summary']['SummaryBeforeService']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tb2data['Summary']['SummaryCowPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tb2data['Summary']['SummaryServicePercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':I' . ($row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($startrowtb2 + 3) . ':I' . $row)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateInseminationExcel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(3);
        $data = InseminationController::getMonthDataList($condition, $region);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $objPHPExcel->setActiveSheetIndex(3);
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
        $objPHPExcel->getActiveSheet()->setCellValue('C12', '+ รายได้น้ำเชื้อและวัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('D12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('E12', '+ รายได้น้ำเชื้อและวัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('F12', 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('G12', 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('H12', '+ รายได้น้ำเชื้อและวัสดุฯ');
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
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumm = $objPHPExcel->getActiveSheet()->getHighestColumn();
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
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
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
        ///////tb 2
        $tb2data = $data;
        while ($condition['MonthFrom'] != 10) {
            $condition['MonthFrom'] -= 1;
            if ($condition['MonthFrom'] == 0) {
                $condition['MonthFrom'] = 12;
                $condition['MonthTo'] = 12;
                $condition['YearTo'] -= 1;
                $condition['YearFrom'] -= 1;
                $newdata = InseminationController::getMonthDataList($condition, $region);

                foreach ($newdata['DataList'] as $key => $itemnewdata) {
                    $tb2data['DataList'][$key]['CurrentCowService'] += $itemnewdata['CurrentCowService'];
                    $tb2data['DataList'][$key]['CurrentIncomeService'] += $itemnewdata['CurrentIncomeService'];
                    $tb2data['DataList'][$key]['BeforeCowService'] += $itemnewdata['BeforeCowService'];
                    $tb2data['DataList'][$key]['BeforeIncomeService'] += $itemnewdata['BeforeIncomeService'];
                    $tb2data['DataList'][$key]['DiffCowService'] += $itemnewdata['DiffCowService'];
                    $tb2data['DataList'][$key]['DiffCowServicePercentage'] += $itemnewdata['DiffCowServicePercentage'];
                    $tb2data['DataList'][$key]['DiffIncomeService'] += $itemnewdata['DiffIncomeService'];
                    $tb2data['DataList'][$key]['DiffIncomeServicePercentage'] += $itemnewdata['DiffIncomeServicePercentage'];
                    $tb2data['Summary']['SummaryCurrentCowService'] += $newdata['Summary']['SummaryCurrentCowService'];
                    $tb2data['Summary']['SummaryCurrentIncomeService'] += $newdata['Summary']['SummaryCurrentIncomeService'];
                    $tb2data['Summary']['SummaryBeforeCowService'] += $newdata['Summary']['SummaryBeforeCowService'];
                    $tb2data['Summary']['SummaryBeforeIncomeService'] += $newdata['Summary']['SummaryBeforeIncomeService'];
                    $tb2data['Summary']['SummaryCowServicePercentage'] += $newdata['Summary']['SummaryCowServicePercentage'];
                    $tb2data['Summary']['SummaryIncomeServicePercentage'] += $newdata['Summary']['SummaryIncomeServicePercentage'];
                }
            } else {
                $condition['MonthTo'] -= 1;

                $newdata = InseminationController::getMonthDataList($condition, $region);

                foreach ($newdata['DataList'] as $key => $itemnewdata) {
                    $tb2data['DataList'][$key]['CurrentCowService'] += $itemnewdata['CurrentCowService'];
                    $tb2data['DataList'][$key]['CurrentIncomeService'] += $itemnewdata['CurrentIncomeService'];
                    $tb2data['DataList'][$key]['BeforeCowService'] += $itemnewdata['BeforeCowService'];
                    $tb2data['DataList'][$key]['BeforeIncomeService'] += $itemnewdata['BeforeIncomeService'];
                    $tb2data['DataList'][$key]['DiffCowService'] += $itemnewdata['DiffCowService'];
                    $tb2data['DataList'][$key]['DiffCowServicePercentage'] += $itemnewdata['DiffCowServicePercentage'];
                    $tb2data['DataList'][$key]['DiffIncomeService'] += $itemnewdata['DiffIncomeService'];
                    $tb2data['DataList'][$key]['DiffIncomeServicePercentage'] += $itemnewdata['DiffIncomeServicePercentage'];
                    $tb2data['Summary']['SummaryCurrentCowService'] += $newdata['Summary']['SummaryCurrentCowService'];
                    $tb2data['Summary']['SummaryCurrentIncomeService'] += $newdata['Summary']['SummaryCurrentIncomeService'];
                    $tb2data['Summary']['SummaryBeforeCowService'] += $newdata['Summary']['SummaryBeforeCowService'];
                    $tb2data['Summary']['SummaryBeforeIncomeService'] += $newdata['Summary']['SummaryBeforeIncomeService'];
                    $tb2data['Summary']['SummaryCowServicePercentage'] += $newdata['Summary']['SummaryCowServicePercentage'];
                    $tb2data['Summary']['SummaryIncomeServicePercentage'] += $newdata['Summary']['SummaryIncomeServicePercentage'];
                }
            }
        }

        $highestRow += 2;
        $startrowtb2 = $highestRow;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' มีโคเข้ารับการบริการผสมเทียม จำนวน ' . number_format($tb2data['Summary']['SummaryCurrentCowService'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($tb2data['Summary']['SummaryCurrentIncomeService'], 2, '.', ',') . '  บาท');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($tb2data['Summary']['SummaryCowServicePercentage'], 2, '.', ','));
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  และ ' . number_format($tb2data['Summary']['SummaryIncomeServicePercentage'], 2, '.', ',') . ' ตามลำดับ');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $highestRow . ':A' . ($highestRow + 3));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $highestRow . ':C' . $highestRow);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . $highestRow . ':E' . $highestRow);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F' . $highestRow . ':I' . $highestRow);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'โคที่รับ');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, '%');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, 'รายได้ค่าบริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, '%');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, '+ รายได้น้ำเชื้อและวัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, '+ รายได้น้ำเชื้อและวัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'บริการ');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, '+ รายได้น้ำเชื้อและวัสดุฯ');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, 'เพิ่ม,');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, '(ตัว)');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, 'ลด,');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, '(บาท)');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, 'ลด,');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;



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

        //  print_r($data);
        //   die();
        $row = $highestRow;

        foreach ($tb2data['DataList'] as $item2) {
            $summarydiffcow += $item2['DiffCowService'];
            $summarydiffservice += $item2['DiffIncomeService'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $item2['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $item2['CurrentCowService']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $item2['CurrentIncomeService']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $item2['BeforeCowService']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $item2['BeforeIncomeService']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $item2['DiffCowService']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $item2['DiffCowServicePercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $item2['DiffIncomeService']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $item2['DiffIncomeServicePercentage']);
            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tb2data['Summary']['SummaryCurrentCowService']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tb2data['Summary']['SummaryCurrentIncomeService']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tb2data['Summary']['SummaryBeforeCowService']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tb2data['Summary']['SummaryBeforeIncomeService']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tb2data['Summary']['SummaryCowServicePercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tb2data['Summary']['SummaryIncomeServicePercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':I' . ($row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($startrowtb2 + 3) . ':I' . $row)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );

        return $objPHPExcel;
    }

    private function generateMineralExcel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(4);
        $data = MineralController::getMonthDataList($condition, $region);

        $objPHPExcel->setActiveSheetIndex(4);
        $objPHPExcel->getActiveSheet()->setTitle("2.2 อาหารสัตว์");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.2 อาหารสัตว์');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                       เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' มีจำหน่ายอาหารสัตว์ ปริมาณ ' . $data['Summary']['SummaryCurrentMineralAmount'] . '  กิโลกรัม มูลค่า ' . $data['Summary']['SummaryCurrentMineralIncome'] . '  บาท ');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การปริมาณและมูลค่าลดลงคิดเป็นร้อยละ ' . $data['Summary']['SummaryMineralAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  และ ' . $data['Summary']['SummaryMineralIncomePercentage'] . ' ตามลำดับ');

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A9', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A9:A10');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B9:C9');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D9:E9');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F9:I9');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('C10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('E10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F10', 'กิโลกรัม');
        $objPHPExcel->getActiveSheet()->setCellValue('G10', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I10', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('A11', 'จำหน่าย');
        $row = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;
        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffWeight'];
            $summarydiffservice += $item['DiffBaht'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), $item['RegionName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $item['CurrentWeight']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $item['BeforeWeight']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $item['DiffWeight']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $item['DiffWeightPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $item['DiffBahtPercentage']);
            $row++;
        }
        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $data['Summary']['SummaryCurrentMineralAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $data['Summary']['SummaryCurrentMineralIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $data['Summary']['SummaryBeforMineralAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $data['Summary']['SummaryBeforeMineralIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $data['Summary']['SummaryMineralAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $data['Summary']['SummaryMineralIncomePercentage']);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumm = $objPHPExcel->getActiveSheet()->getHighestColumn();
        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I10')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I10')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A9:I10")
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
        $objPHPExcel->getActiveSheet()->getStyle('B12:I' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . $highestRow)->applyFromArray(
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

    private function generateTrainingCowbreedExcel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(5);
        $objPHPExcel->setActiveSheetIndex(5);
        $showm = 0;
        $showy = $condition['YearFrom'];
        $start = $condition['MonthTo'];
        if ($condition['MonthTo'] < 10) {
            $showm = $condition['YearFrom'] - 1;
        } else {
            $showm = $condition['YearFrom'];
        }
        $data = TrainingCowBreedController::getMonthDataList($condition, $region);


        $objPHPExcel->getActiveSheet()->setTitle("2.3 การฝึกอบรม");
        $objPHPExcel->getActiveSheet()->setCellValue('A3', '2. การดำเนินงานด้านการให้บริการของ อ.ส.ค.');
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '  2.3 การฝึกอบรมการเลี้ยงโคนม');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '                       เดือน ' . $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543) . ' มีการฝึกอบรมทั้งสิ้น  ' . $data['Summary']['SummaryCurrentCowBreedAmount'] . '  ราย มูลค่า ' . $data['Summary']['SummaryCurrentCowBreedIncome'] . '  บาท ');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา จำนวนผู้เข้ารับการอบรมและมูลค่าการบริการคิดเป็นร้อยละ ' . $data['Summary']['SummaryCowBreedAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('A7', '                  และ ' . $data['Summary']['SummaryCowBreedIncomePercentage'] . ' ตามลำดับ');

//tb header
        $objPHPExcel->getActiveSheet()->setCellValue('A9', 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A9:A10');
        $objPHPExcel->getActiveSheet()->setCellValue('B9', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B9:C9');
        $objPHPExcel->getActiveSheet()->setCellValue('D9', $this->getMonthName($condition['MonthFrom']) . ' ' . ($condition['YearFrom'] + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D9:E9');
        $objPHPExcel->getActiveSheet()->setCellValue('F9', 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F9:I9');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('C10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D10', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('E10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F10', 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('G10', '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H10', 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I10', '% เพิ่ม,ลด');
        $row = 0;
        $summarydiffcow = 0;
        $summarydiffservice = 0;
        foreach ($data['DataList'] as $item) {
            $summarydiffcow += $item['DiffAmount'];
            $summarydiffservice += $item['DiffBaht'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), $item['CowBreedName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $item['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $item['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $item['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $item['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $item['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $item['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $item['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $item['DiffBahtPercentage']);
            $row++;
        }
        //summary
        $objPHPExcel->getActiveSheet()->setCellValue('A' . (12 + $row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . (12 + $row), $data['Summary']['SummaryCurrentCowBreedAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . (12 + $row), $data['Summary']['SummaryCurrentCowBreedIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . (12 + $row), $data['Summary']['SummaryBeforeCowBreedAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . (12 + $row), $data['Summary']['SummaryBeforeCowBreedIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . (12 + $row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . (12 + $row), $data['Summary']['SummaryCowBreedAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . (12 + $row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . (12 + $row), $data['Summary']['SummaryCowBreedIncomePercentage']);
        $highestRow = $objPHPExcel->getActiveSheet()->getHighestRow();
        $highestColumm = $objPHPExcel->getActiveSheet()->getHighestColumn();

        // header style
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(22);
        $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5:A8')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I10')->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I10')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle("A9:I10")
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);



        $objPHPExcel->getActiveSheet()->getStyle('A' . (12 + $row) . ':I' . (12 + $row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B12:I' . (12 + $row))
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A9:I' . (12 + $row))->applyFromArray(
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
        ///////tb 2
        $tb2data = $data;
        while ($condition['MonthFrom'] != 10) {
            $condition['MonthFrom'] -= 1;
            if ($condition['MonthFrom'] == 0) {
                $condition['MonthFrom'] = 12;
                $condition['MonthTo'] = 12;
                $condition['YearTo'] -= 1;
                $condition['YearFrom'] -= 1;
                $newdata = TrainingCowBreedController::getMonthDataList($condition, $region);

                foreach ($newdata['DataList'] as $key => $itemnewdata) {
                    $tb2data['DataList'][$key]['CurrentAmount'] += $itemnewdata['CurrentAmount'];
                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
                    $tb2data['DataList'][$key]['BeforeAmount'] += $itemnewdata['BeforeAmount'];
                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
                    $tb2data['DataList'][$key]['DiffAmount'] += $itemnewdata['DiffAmount'];
                    $tb2data['DataList'][$key]['DiffAmountPercentage'] += $itemnewdata['DiffAmountPercentage'];
                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
                    $tb2data['Summary']['SummaryCurrentCowBreedAmount'] += $newdata['Summary']['SummaryCurrentCowBreedAmount'];
                    $tb2data['Summary']['SummaryCurrentCowBreedIncome'] += $newdata['Summary']['SummaryCurrentCowBreedIncome'];
                    $tb2data['Summary']['SummaryBeforeCowBreedAmount'] += $newdata['Summary']['SummaryBeforeCowBreedAmount'];
                    $tb2data['Summary']['SummaryBeforeCowBreedIncome'] += $newdata['Summary']['SummaryBeforeCowBreedIncome'];
                    $tb2data['Summary']['SummaryCowBreedAmountPercentage'] += $newdata['Summary']['SummaryCowBreedAmountPercentage'];
                    $tb2data['Summary']['SummaryCowBreedIncomePercentage'] += $newdata['Summary']['SummaryCowBreedIncomePercentage'];
                }
            } else {
                $condition['MonthTo'] -= 1;

                $newdata = TrainingCowBreedController::getMonthDataList($condition, $region);
                foreach ($newdata['DataList'] as $key => $itemnewdata) {
                    $tb2data['DataList'][$key]['CurrentAmount'] += $itemnewdata['CurrentAmount'];
                    $tb2data['DataList'][$key]['CurrentBaht'] += $itemnewdata['CurrentBaht'];
                    $tb2data['DataList'][$key]['BeforeAmount'] += $itemnewdata['BeforeAmount'];
                    $tb2data['DataList'][$key]['BeforeBaht'] += $itemnewdata['BeforeBaht'];
                    $tb2data['DataList'][$key]['DiffAmount'] += $itemnewdata['DiffAmount'];
                    $tb2data['DataList'][$key]['DiffAmountPercentage'] += $itemnewdata['DiffAmountPercentage'];
                    $tb2data['DataList'][$key]['DiffBaht'] += $itemnewdata['DiffBaht'];
                    $tb2data['DataList'][$key]['DiffBahtPercentage'] += $itemnewdata['DiffBahtPercentage'];
                    $tb2data['Summary']['SummaryCurrentCowBreedAmount'] += $newdata['Summary']['SummaryCurrentCowBreedAmount'];
                    $tb2data['Summary']['SummaryCurrentCowBreedIncome'] += $newdata['Summary']['SummaryCurrentCowBreedIncome'];
                    $tb2data['Summary']['SummaryBeforeCowBreedAmount'] += $newdata['Summary']['SummaryBeforeCowBreedAmount'];
                    $tb2data['Summary']['SummaryBeforeCowBreedIncome'] += $newdata['Summary']['SummaryBeforeCowBreedIncome'];
                    $tb2data['Summary']['SummaryCowBreedAmountPercentage'] += $newdata['Summary']['SummaryCowBreedAmountPercentage'];
                    $tb2data['Summary']['SummaryCowBreedIncomePercentage'] += $newdata['Summary']['SummaryCowBreedIncomePercentage'];
                }
            }
        }
        $highestRow += 2;
        $startrowtb2 = $highestRow;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                       เดือน ' . 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543) . ' มีโคเข้ารับการบริการผสมเทียม จำนวน ' . number_format($tb2data['Summary']['SummaryCurrentCowBreedAmount'], 2, '.', ',') . ' ตัว รายได้ ' . number_format($tb2data['Summary']['SummaryCurrentCowBreedIncome'], 2, '.', ',') . '  บาท');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  เมื่อเปรียบเทียบกับเดือนเดียวกันของปีที่ผ่านมา การบริการและมูลค่าลดลงคิดเป็นร้อยละ ' . number_format($tb2data['Summary']['SummaryCowBreedAmountPercentage'], 2, '.', ','));
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, '                  และ ' . number_format($tb2data['Summary']['SummaryCowBreedIncomePercentage'], 2, '.', ',') . ' ตามลำดับ');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow)->getFont()->setSize(16);
        $highestRow++;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, 'รายการ');
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $highestRow . ':A' . ($highestRow + 1));
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ต.ค. ' . ($showm + 543) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 543));
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $highestRow . ':C' . $highestRow);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ต.ค. ' . ($showm + 542) . " - " . $this->getMonthshName($start) . ' ' . ($showy + 542));
        $objPHPExcel->getActiveSheet()->mergeCells('D' . $highestRow . ':E' . $highestRow);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ผลต่าง');
        $objPHPExcel->getActiveSheet()->mergeCells('F' . $highestRow . ':I' . $highestRow);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;


        $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, 'ราย');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, 'บาท');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, '% เพิ่ม,ลด');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setSize(16);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()
                ->getStyle('A' . $highestRow . ':I' . $highestRow)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                        )
        );
        $highestRow++;
        $row = $highestRow;

        foreach ($tb2data['DataList'] as $item2) {
            $summarydiffcow += $item2['DiffCowService'];
            $summarydiffservice += $item2['DiffIncomeService'];
            $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), $item2['CowBreedName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $item2['CurrentAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $item2['CurrentBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $item2['BeforeAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $item2['BeforeBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $item2['DiffAmount']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $item2['DiffAmountPercentage']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $item2['DiffBaht']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $item2['DiffBahtPercentage']);

            $row++;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row), 'รวมทั้งสิ้น');
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . ($row), $tb2data['Summary']['SummaryCurrentCowBreedAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . ($row), $tb2data['Summary']['SummaryCurrentCowBreedIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row), $tb2data['Summary']['SummaryBeforeCowBreedAmount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . ($row), $tb2data['Summary']['SummaryBeforeCowBreedIncome']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . ($row), $summarydiffcow);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row), $tb2data['Summary']['SummaryCowBreedAmountPercentage']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . ($row), $summarydiffservice);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . ($row), $tb2data['Summary']['SummaryCowBreedIncomePercentage']);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row) . ':I' . ($row))->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($startrowtb2 + 3) . ':I' . $row)->applyFromArray(
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
        $objPHPExcel->getActiveSheet()->getStyle('A' . $startrowtb2 . ':I' . $row)->applyFromArray(
                array(
                    'font' => array(
                        'name' => 'AngsanaUPC'
                    )
                )
        );
        return $objPHPExcel;
    }

    private function generateTravelExcel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(6);
        $objPHPExcel->setActiveSheetIndex(6);
        $objPHPExcel->getActiveSheet()->setTitle("2.4 โครงการท่องเที่ยว");
        return $objPHPExcel;
    }

    private function generateSpermSaleExcel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(7);
        $objPHPExcel->setActiveSheetIndex(7);
        $objPHPExcel->getActiveSheet()->setTitle("2.5 ปัจจัยการผลิต");
        return $objPHPExcel;
    }

    private function generateSpermSale2Excel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(8);
        $objPHPExcel->setActiveSheetIndex(8);
        $objPHPExcel->getActiveSheet()->setTitle("2.5 ปัจจัยการผลิต (2)");
        return $objPHPExcel;
    }
    private function generateCooperativeMilkExcel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(9);
        $objPHPExcel->setActiveSheetIndex(9);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก ");
        return $objPHPExcel;
    }
    private function generateCooperativeMilk2Excel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(10);
        $objPHPExcel->setActiveSheetIndex(10);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (2)");
        return $objPHPExcel;
    }
    private function generateCooperativeMilk3Excel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(11);
        $objPHPExcel->setActiveSheetIndex(11);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (3)");
        return $objPHPExcel;
    }
    private function generateCooperativeMilk4Excel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(12);
        $objPHPExcel->setActiveSheetIndex(12);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (4)");
        return $objPHPExcel;
    }
    private function generateCooperativeMilk5Excel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(13);
        $objPHPExcel->setActiveSheetIndex(13);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (5)");
        return $objPHPExcel;
    }
    private function generateCooperativeMilk6Excel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(14);
        $objPHPExcel->setActiveSheetIndex(14);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (6)");
        return $objPHPExcel;
    }
    private function generateCooperativeMilk7Excel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(15);
        $objPHPExcel->setActiveSheetIndex(15);
        $objPHPExcel->getActiveSheet()->setTitle("3.1 จำนวนสมาชิก (7)");
        return $objPHPExcel;
    }
     private function generateCowgroupExcel($objPHPExcel, $condition, $region) {
        $objPHPExcel->createSheet(16);
        $objPHPExcel->setActiveSheetIndex(16);
        $objPHPExcel->getActiveSheet()->setTitle("3.2 โค");
        return $objPHPExcel;
    }

}
