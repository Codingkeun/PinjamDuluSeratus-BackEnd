<?php

namespace App\Helper;

/*
 * General
 * Author : Cecep Rokani
*/

use Pimple\Psr11\Container;
use Mpdf\Mpdf;
use Slim\Views\Twig;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Style\Border;

use Psr\Http\Message\ServerRequestInterface as Request;

final class General {
    private Container $container;
    private array $request;

    public function __construct(Container $container, Request $request=null) {
        $this->container = $container;
        if ($request)
            $this->request = $request;
    }

	public function checkStrongPassword($text) {
		$result = true;
		if (strlen($text) < 8) {
			$result = false;
		} if (!preg_match("#[0-9]+#", $text)) {
			$result = false;
		} if (!preg_match("#[a-zA-Z]+#", $text)) {
			$result = false;
		}

		return $result;
	}

    function arraySort($array, $on, $order=SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();
    
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
    
            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                break;
                case SORT_DESC:
                    arsort($sortable_array);
                break;
            }
    
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
    
        return array_values($new_array);
    }

    public function generateExcel(Request $request, string $template, $colWidth, array $data = [], array $additionalData=[], $startCell=1, $titleTab="Format Impor")
    {
        $view           = Twig::fromRequest($request);
        $htmlString     = $view->fetch($template, ['data' => $data]);
        $reader         = new Html();
        $spreadsheet    = $reader->loadFromString($htmlString);

        $titleTab = preg_replace('/[\[\]\/\\\\\*?\:\'"]+/', '-', $titleTab);
        $titleTab = strlen($titleTab) > 30 ? substr($titleTab, 0, 30) : $titleTab;

        $spreadsheet->getActiveSheet()->setTitle($titleTab);
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
        $lastColumn = $spreadsheet->getActiveSheet()->getHighestColumn();

        for ($i = 'A'; $i != $lastColumn; $i++) {
            for ($j=0; $j < $data['total']; $j++) {
                $spreadsheet->getActiveSheet()->getStyle($i . ($j + $startCell))->getAlignment()->setVertical('center');
                $spreadsheet->getActiveSheet()->getStyle($i . ($j + $startCell))->getAlignment()->setWrapText(true);
                $spreadsheet->getActiveSheet()->getStyle($i . ($j + $startCell))->getAlignment()->setShrinkToFit(true);
                $spreadsheet->getActiveSheet()->getStyle($i . ($j + $startCell))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }
        
        for ($j=0; $j < $data['total']; $j++) {
            $spreadsheet->getActiveSheet()->getStyle($lastColumn . ($j + $startCell))->getAlignment()->setVertical('center');
            $spreadsheet->getActiveSheet()->getStyle($lastColumn . ($j + $startCell))->getAlignment()->setWrapText(true);
            $spreadsheet->getActiveSheet()->getStyle($lastColumn . ($j + $startCell))->getAlignment()->setShrinkToFit(true);
            $spreadsheet->getActiveSheet()->getStyle($lastColumn . ($j + $startCell))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach ($colWidth as $col => $width) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth($width);
        }

        if (!empty($additionalData)) {
            foreach ($additionalData as $key=>$additional) {
                $spreadsheet = $this->addNewSheet($request, $reader, $spreadsheet, $additional['template'], $additional['title'], $additional['data'], $additional['col_width'], $key + 1, (isset($additional['start_cell']) ? $additional['start_cell'] : 1));
            }
            $spreadsheet->setActiveSheetIndex(0);
        }

        $writer     = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tempFile   = tempnam(File::sysGetTempDir(), 'phpxltmp');
        $tempFile   = $tempFile ?: __DIR__ . '/temp.xlsx';
        $writer->save($tempFile);
        return $tempFile;
    }

    public function addNewSheet(Request $request, $reader, $spreadsheet, $template, $title, $data, $colWidth, $index, $startCell=1) {
        $view           = Twig::fromRequest($request);
        $htmlString     = $view->fetch($template, ['data' => $data]);

        $reader->setSheetIndex($index);
        $reader->loadFromString($htmlString, $spreadsheet);

        $lastColumn = $spreadsheet->getActiveSheet()->getHighestColumn();

        if (isset($data['total']))
            $totalData = $data['total'];
        else
            $totalData = count($data) + 1;

        foreach ($colWidth as $col => $width) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth($width);
        }

        for ($i = 'A'; $i != $lastColumn; $i++) {
            for ($j=0; $j < $totalData; $j++) {
                $spreadsheet->getActiveSheet()->getStyle($i . ($j + $startCell))->getAlignment()->setVertical('center');
                $spreadsheet->getActiveSheet()->getStyle($i . ($j + $startCell))->getAlignment()->setWrapText(true);
                $spreadsheet->getActiveSheet()->getStyle($i . ($j + $startCell))->getAlignment()->setShrinkToFit(true);
                $spreadsheet->getActiveSheet()->getStyle($i . ($j + $startCell))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }

        for ($j=0; $j < $totalData; $j++) {
            $spreadsheet->getActiveSheet()->getStyle($lastColumn . ($j + $startCell))->getAlignment()->setVertical('center');
            $spreadsheet->getActiveSheet()->getStyle($lastColumn . ($j + $startCell))->getAlignment()->setWrapText(true);
            $spreadsheet->getActiveSheet()->getStyle($lastColumn . ($j + $startCell))->getAlignment()->setShrinkToFit(true);
            $spreadsheet->getActiveSheet()->getStyle($lastColumn . ($j + $startCell))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $title = preg_replace('/[\[\]\/\\\\\*?\:\'"]+/', '-', $title);
        $title = strlen($title) > 30 ? substr($title, 0, 30) : $title;
        $spreadsheet->getActiveSheet()->setTitle($title);

        return $spreadsheet;
    }
    
    public function formatDateIndonesia($date, $format='%d %F %Y') {
        $listMonth = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $listDay = ["Mon" => "Senin", "Tue" => "Selasa", "Wed" => "Rabu", "Thu" => "Kamis", "Fri" => "Jumat", "Sat" => "Sabtu", "Sun" => "Minggu"];

        $timeStamp = strtotime($date);

        $tmpDate = ['day' => $listDay[date('D', $timeStamp)], 'month' => $listMonth[date('m', $timeStamp) - 1], 'year' => date('Y'), 'date' => date('d', $timeStamp)];

        $resultDate = $format;
        $resultDate = str_replace('%d', $tmpDate['date'], $resultDate);
        $resultDate = str_replace('%F', $tmpDate['month'], $resultDate);
        $resultDate = str_replace('%Y', $tmpDate['year'], $resultDate);
        $resultDate = str_replace('%l', $tmpDate['day'], $resultDate);

        return $resultDate;
    }

    public function baseUrl($extended_url="") {

        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';
        $newurl = str_replace("index.php", "", $_SERVER['SCRIPT_NAME']);

        if($_SERVER['SERVER_NAME'] == 'localhost') {
            $baseUrl    = "$http" . $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'] . "" . $newurl;
        } else {
            $baseUrl    = "$http" . $_SERVER['SERVER_NAME'] . "" . $newurl;
        }
        
        return $baseUrl.''.$extended_url;
    }
}

?>