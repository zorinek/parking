<?php

declare(strict_types=1);

namespace App\Modules\Files;

use App\Model;

final class FilesAdminPresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Model\PreviewFactory $previewFactory @inject */
	public $previewFactory;

	/** @var Model\Files $files @inject */
	public $files;

	/** @persistent */
	public $fil_id = "";

	/** @persistent */
	public $fil_name = "";

	/** @persistent */
	public $fil_path = "";

	/** @persistent */
	public $fil_storagename = "";

	/** @persistent */
	public $fil_ext = "";

	/** @persistent */
	public $typ_id = "";

	/** @persistent */
	public $and_or = "and";

	/** @persistent */
	public $order = [];

	/** @persistent */
	public $order_dir = [];
	private $p_upload;

	public function __construct($p_upload)
	{
		$this->p_upload = $p_upload;
	}

	public function renderDefault()
	{
		
	}

	public function renderPreview($page)
	{
		$this->template->setFile("../app/Modules/components/default_preview.latte");
		$request = $this->getHttpRequest();
		$search_values = [
			"fil_id" => ["fil_id", "like", $this->fil_id],
			"fil_name" => ["fil_name", "like", $this->fil_name],
			"fil_path" => ["fil_path", "like", $this->fil_path],
			"fil_storagename" => ["fil_storagename", "like", $this->fil_storagename],
			"fil_ext" => ["fil_ext", "like", $this->fil_ext],
			"typ_id" => ["typ_id", "like", $this->typ_id, false, "files.typ_id."],
			"and_or" => ["and_or", "", $this->and_or],
			"order" => ["order", "", $this->order],
			"order_dir" => ["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->providePreview("files", $page, ":Files:FilesAdmin:preview", $request, 10, $search_values, $this, 'files.admin.', "Files/templates/FilesAdmin/");
		$this->template->setParameters($ret);
		$this->template->search_values = $search_values;
		$this->template->search = empty($ret['params']) ? false : true;
		$this->template->parameters = $this->p_upload;
	}

	public function actionRemove($fil_id, $page)
	{
		$this->files->removeFile($fil_id, true);
		$this->redirect(":Files:FilesAdmin:preview", [$page, "fil_id" => ""]);
	}

}
