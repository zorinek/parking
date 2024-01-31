<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

/**
 * Queries
 */
final class Queries
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'queries',
			COLUMN_QUE_ID = 'que_id',
			COLUMN_QUE_NAME = 'que_name',
			COLUMN_QUE_QUERY = 'que_query',
			COLUMN_QUE_NOTE = 'que_note',
			COLUMN_QUE_STATUS = 'que_status',
			COLUMN_QUE_DATETIMEINSERT = 'que_datetimeinsert';

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function insert($values)
	{
		$ins = $this->db->table(self::TABLE_NAME)->insert($values);
		return $ins->{self::COLUMN_QUE_ID};
	}

	public function update($values)
	{
		$ins = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_QUE_ID, $values[self::COLUMN_QUE_ID])->update($values);
	}

	public function get($que_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_QUE_ID, $que_id)->fetch();
		return $get;
	}

	public function runQuery($query, $limit = false, $offset = false)
	{
		$get = $this->db->query($query)->fetchAll();
		if ($offset)
		{
			$slice = array_slice($get, $offset, $limit);
		} else if ($limit)
		{
			$slice = array_slice($get, 0, $limit);
		} else
		{
			$slice = $get;
		}
		return ["array" => $slice, "all_count" => count($get)];
	}

	public function export($ret, $search_values, $export_cols, $presenter, $type, $template_part)
	{
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$styleArray = [
			'alignment' => [
//                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
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

		$lines = 0;
		if ($ret["values"] == "all")
		{
			$lines = 3;
		}

		$first = true;
//        dump($ret["all"]);
		foreach ($ret["all"]["array"] as $line)
		{

			if ($first)
			{
				$cols = 1;
				foreach ($line as $key => $value)
				{
					$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $key);
					$spreadsheet->getActiveSheet()->getColumnDimensionByColumn($cols)->setAutoSize(true);
					$cols++;
				}
				$lines++;
				$first = false;
			}
			$cols = 1;
//            dump($search_values);
//            die();
//            dump($line);
			foreach ($line as $key => $value)
			{
//                dump($key);
//                dumpe($value);
				$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cols, $lines, $value);
				$cols++;
			}
			$lines++;
		}
		$spreadsheet->getActiveSheet()->mergeCellsByColumnAndRow(1, 1, $cols - 1, 1);
		$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 1, $presenter->translator->translate($template_part . "export_title"));
		$spreadsheet->getActiveSheet()->mergeCellsByColumnAndRow(1, 2, $cols - 1, 2);
		$spreadsheet->getActiveSheet()->setCellValueByColumnAndRow(1, 2, $presenter->translator->translate($template_part . "export_date") . ": " . date("d. m. Y H:i:s"));

		$tmpfile = tempnam('', 'phpxltmp');
		if ($type == 'excel')
		{
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
			$filename = $presenter->translator->translate($template_part . "export_name") . "-" . date("Y-m-d-H-i-s") . ".xlsx";
			$content_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
		} elseif ($type == "pdf")
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
