<?php

declare(strict_types=1);

namespace App\Model;

use Nette;


/**
 * Projects
 */
final class Projects
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'projects',
			COLUMN_PRO_ID = 'pro_id',
			COLUMN_PRO_NAME = 'pro_name',
			COLUMN_PRO_NOTE = 'pro_note',
			COLUMN_PRO_DATETIMEINSERT = 'pro_datetimeinsert';

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	public function insert($values)
	{
		$ins = $this->db->table(self::TABLE_NAME)->insert($values);
		return $ins->{self::COLUMN_PRO_ID};
	}

	public function get($pro_id)
	{
		$get = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_PRO_ID, $pro_id)->fetch();
		return $get;
	}

	public function update($values)
	{
		$update = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_PRO_ID, $values[self::COLUMN_PRO_ID])->update($values);
	}

	public function getLastProjects()
	{
		$get = $this->db->table(self::TABLE_NAME)->order(self::COLUMN_PRO_ID . " DESC")->limit(4)->fetchAll();
		return $get;
	}

	public function exportData($presenter, $data)
	{
		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$pro_name = "";

		$spreadsheet->removeSheetByIndex(0);

		foreach ($data as $key => $line)
		{

			$worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $key);
			$spreadsheet->addSheet($worksheet);
			$first = true;
			$cnt = 1;
			foreach ($line as $k => $v)
			{
				if ($first)
				{
					$worksheet->setCellValueByColumnAndRow(1, $cnt, $presenter->translator->translate("projects.export.seg_id"));
					$worksheet->setCellValueByColumnAndRow(2, $cnt, $presenter->translator->translate("projects.export.cas_parkingdetected_left"));
					$worksheet->setCellValueByColumnAndRow(3, $cnt, $presenter->translator->translate("projects.export.cas_parkingfree_left"));
					$worksheet->setCellValueByColumnAndRow(4, $cnt, $presenter->translator->translate("projects.export.cas_parkingillegal_left"));
					$worksheet->setCellValueByColumnAndRow(5, $cnt, $presenter->translator->translate("projects.export.cas_parkingnotdetected_left"));
					$worksheet->setCellValueByColumnAndRow(6, $cnt, $presenter->translator->translate("projects.export.cas_parkingdetected_right"));
					$worksheet->setCellValueByColumnAndRow(7, $cnt, $presenter->translator->translate("projects.export.cas_parkingfree_right"));
					$worksheet->setCellValueByColumnAndRow(8, $cnt, $presenter->translator->translate("projects.export.cas_parkingillegal_right"));
					$worksheet->setCellValueByColumnAndRow(9, $cnt, $presenter->translator->translate("projects.export.cas_parkingnotdetected_right"));
					$worksheet->setCellValueByColumnAndRow(10, $cnt, $presenter->translator->translate("projects.export.cas_parkingdetected"));
					$worksheet->setCellValueByColumnAndRow(11, $cnt, $presenter->translator->translate("projects.export.cas_parkingfree"));
					$worksheet->setCellValueByColumnAndRow(12, $cnt, $presenter->translator->translate("projects.export.cas_parkingillegal"));
					$worksheet->setCellValueByColumnAndRow(13, $cnt, $presenter->translator->translate("projects.export.cas_parkingnotdetected"));
					$worksheet->setCellValueByColumnAndRow(14, $cnt, $presenter->translator->translate("projects.export.on"));
					$cnt++;
					$first = false;
				}

				$worksheet->setCellValueByColumnAndRow(1, $cnt, $v->seg_id);
				$worksheet->setCellValueByColumnAndRow(2, $cnt, $v->cas_parkingdetected_left);
				$worksheet->setCellValueByColumnAndRow(3, $cnt, $v->cas_parkingfree_left);
				$worksheet->setCellValueByColumnAndRow(4, $cnt, $v->cas_parkingillegal_left);
				$worksheet->setCellValueByColumnAndRow(5, $cnt, $v->cas_parkingnotdetected_left);
				$worksheet->setCellValueByColumnAndRow(6, $cnt, $v->cas_parkingdetected_right);
				$worksheet->setCellValueByColumnAndRow(7, $cnt, $v->cas_parkingfree_right);
				$worksheet->setCellValueByColumnAndRow(8, $cnt, $v->cas_parkingillegal_right);
				$worksheet->setCellValueByColumnAndRow(9, $cnt, $v->cas_parkingnotdetected_right);
				$worksheet->setCellValueByColumnAndRow(10, $cnt, $v->cas_parkingdetected);
				$worksheet->setCellValueByColumnAndRow(11, $cnt, $v->cas_parkingfree);
				$worksheet->setCellValueByColumnAndRow(12, $cnt, $v->cas_parkingillegal);
				$worksheet->setCellValueByColumnAndRow(13, $cnt, $v->cas_parkingnotdetected);
				$worksheet->setCellValueByColumnAndRow(14, $cnt, $v->on);
				$cnt++;
				$pro_name = $v->pro_name;
			}
		}

		$tmpfile = tempnam('', 'phpxltmp');

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
		$filename = $pro_name . "-" . date("Y-m-d-H-i-s") . ".xlsx";
		$content_type = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

		$writer->save($tmpfile);
		return ["tmpfile" => $tmpfile, "filename" => $filename, "content_type" => $content_type];
	}

}
