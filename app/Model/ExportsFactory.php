<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
declare(strict_types=1);

namespace App\Model;

use Nette;


/**
 * Description of ExportsFactory
 *
 * @author Kovarik
 */
class ExportsFactory
{

	//put your code here
	use Nette\SmartObject;

	public function export($ret, $search_values, $export_cols, $presenter, $type, $template_part)
	{
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$styleArray = [
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
			],
			'borders' => [
				'top' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
				'left' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
				'right' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			]
		];
		$spreadsheet->getDefaultStyle()->applyFromArray($styleArray);

		$out_filters = "";
		if ($ret["values"] == "selected")
		{
			$first = true;
			foreach ($search_values as $value)
			{
				if ($value[0] == "and_or")
				{
					break;
				}
				if ($value[2] != "" && $value[2] != 0)
				{
					if ($first)
					{
						$first = false;
					} 
					else
					{
						$out_filters .= ", ";
					}

					if (isset($value[3]) && $value[3] === false)
					{
						$val = $presenter->translator->translate($value[4] . $value[2]);
					} 
					else
					{
						$val = $value[2];
					}

					$out_filters .= $presenter->translator->translate($template_part . $value[0]) . " = " . $val;
				}
			}
		}
		if ($ret["values"] == "all")
		{
			$lines = 3;
		} 
		else
		{
			$lines = 4;
		}
//        $lines = 3;
		$first = true;
		foreach ($ret["all"] as $line)
		{

			if ($first)
			{
				$cols = 1;
				foreach ($search_values as $key => $value)
				{
					if ($value[0] == "and_or")
					{
						break;
					}
					if (in_array($value[0], $export_cols))
					{
						$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $presenter->translator->translate($template_part . $value[0]));
						$spreadsheet->getActiveSheet()->getColumnDimensionByColumn($cols)->setAutoSize(true);
						$cols++;
					}
				}
				$lines++;
				$first = false;
			}
			$cols = 1;

			foreach ($search_values as $key => $value)
			{
				if ($value[0] == "and_or")
				{
					break;
				}
				if (isset($value[3]) && $value[3])
				{
					if (isset($value[7]) && $value[7] === "n")
					{
						$fr = true;
						$lnout = "";
						foreach ($line->related($value[3], $value[8]) as $ln)
						{
							if ($fr)
							{
								$fr = false;
							} else
							{
								$lnout .= ", ";
							}
							$lnout .= $ln->usr_role;
						}
						$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $lnout);
						$cols++;
					} 
					else if ($line->{$value[0]} != null)
					{

						if (isset($value[4]) && is_array($value[4]))
						{
							if (empty($line->ref($value[3], $value[0])->{$value[4][0]}))
							{
								$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $line->ref($value[3], $value[0])->{$value[4][1]});
							} 
							else
							{
								$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $line->ref($value[3], $value[0])->{$value[4][0]});
							}
							$cols++;
						} 
						else
						{
							$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $line->ref($value[3], $value[0])->{$value[4]});
							$cols++;
						}
					}
					else
					{
						$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $value[5]);
						$cols++;
					}
				} 
				else if (isset($value[3]) && $value[3] === false)
				{
					$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $presenter->translator->translate($value[4] . $line->{$value[0]}));
					$cols++;
				} 
				else
				{
					$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $line->{$value[0]});
					$cols++;
				}
			}
			$lines++;
		}
		$spreadsheet->getActiveSheet()->mergeCellsByColumnAndRow(1, 1, $cols - 1, 1);
		$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 1, $presenter->translator->translate($template_part . "export_title"));
		$spreadsheet->getActiveSheet()->mergeCellsByColumnAndRow(1, 2, $cols - 1, 2);
		$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 2, $presenter->translator->translate($template_part . "export_date") . ": " . date("d. m. Y H:i:s"));

		if ($out_filters != "")
		{
			$spreadsheet->getActiveSheet()->mergeCellsByColumnAndRow(1, 3, $cols - 1, 3);
			$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 3, $presenter->translator->translate($template_part . "export_filters") . ": " . $out_filters);
		}
		$tmpfile = tempnam('', 'phpxltmp');
		if ($type == 'excel')
		{
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
			$filename = $presenter->translator->translate($template_part . "export_name") . "-" . date("Y-m-d-H-i-s") . ".xlsx";
			$content_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
		} 
		elseif ($type == "pdf")
		{
			\PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Pdf");
			$filename = $presenter->translator->translate($template_part . "export_name") . "-" . date("Y-m-d-H-i-s") . ".pdf";
			$content_type = "application/pdf";
		}

		$writer->save($tmpfile);
		return ["tmpfile" => $tmpfile, "filename" => $filename, "content_type" => $content_type];
	}

}
