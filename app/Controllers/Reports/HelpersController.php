<?php

namespace App\Controllers\Reports;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class HelpersController extends Controller
{
    static function calcNpv($initInvestment, $appretiation, $periods, $endInvestment = null, $rate) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $cashflow = [-1 * (float)$initInvestment];
        $cashflow = array_merge($cashflow, array_fill(0, (int)$periods - 1, (float)$appretiation));
        array_push($cashflow, $endInvestment ? (float)$endInvestment + $appretiation : (float)$initInvestment + $appretiation);
        $columnArray = array_chunk($cashflow, 1);

        $sheet->fromArray($columnArray, null, 'A1');
        
        // Calculate NPV
        $sheet->setCellValue('C1', '=NPV('. $rate / 100 . ',A1:A' . count($cashflow) . ')');
        $npv = $sheet->getCell('C1')->getCalculatedValue();
        $npv = round((float)$npv, 2);

        return $npv;
    }

    static function calcTir($initInvestment, $appretiation, $periods, $endInvestment = null) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();


        $cashflow = [-1 * (float)$initInvestment];
        $cashflow = array_merge($cashflow, array_fill(0, (int)$periods - 1, (float)$appretiation));
        array_push($cashflow, $endInvestment ? (float)$endInvestment + $appretiation : (float)$initInvestment + $appretiation);
        $columnArray = array_chunk($cashflow, 1);

        $sheet->fromArray($columnArray, null, 'A1');

        // Calculate IRR
        $sheet->setCellValue('C1', '=IRR(A1:A' . count($cashflow) . ')');
        $tir = $sheet->getCell('C1')->getCalculatedValue();
        $tir = round((float)$tir * 100, 2);

        return $tir;
    }

    static function calcCostBene($initInvestment, $appretiation, $periods, $endInvestment = null, $rate) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $cashflow = [-1 * (float)$initInvestment];
        $cashflow = array_merge($cashflow, array_fill(0, (int)$periods - 1, (float)$appretiation));
        array_push($cashflow, $endInvestment ? (float)$endInvestment + $appretiation : (float)$initInvestment + $appretiation);
        $columnArray = array_chunk($cashflow, 1);

        $sheet->fromArray($columnArray, null, 'A1');
        
        // Calculate NPV
        $sheet->setCellValue('C1', '=NPV('. $rate / 100 . ',A1:A' . count($cashflow) . ')');
        $npv = $sheet->getCell('C1')->getCalculatedValue();
        $npv = round((float)$npv, 2);

        return $npv;
    }


}