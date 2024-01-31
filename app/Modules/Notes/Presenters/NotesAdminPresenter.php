<?php

declare(strict_types=1);

namespace App\Modules\Notes;

use App\Model;
use Nette\Application\Responses\FileResponse;

final class NotesAdminPresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Model\Notes $notes @inject */
	public $notes;

	/** @var Model\PreviewFactory $previewFactory @inject */
	public $previewFactory;

	/** @var Model\ExportsFactory $exportsFactory @inject */
	public $exportsFactory;

	/** @persistent */
	public $not_id = "";

	/** @persistent */
	public $not_page = "";

	/** @persistent */
	public $not_note = "";

	/** @persistent */
	public $use_id = "";

	/** @persistent */
	public $and_or = "and";

	/** @persistent */
	public $order = [];

	/** @persistent */
	public $order_dir = [];

	/** @persistent */
	public $export_cols = [];

	public function renderNotesPreview($page)
	{
		$this->template->setFile("../app/Modules/components/default_preview.latte");
		$request = $this->getHttpRequest();
		$search_values = [
			["not_id", "like", $this->not_id],
			["not_page", "like", $this->not_page],
			["not_note", "like", $this->not_note],
			["use_id", "like", $this->use_id, "users", "use_name", false, false, false],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->providePreview("notes", $page, ":Notes:NotesAdmin:notesPreview", $request, 10, $search_values, $this, 'notes.admin.', "Notes/templates/Notes/");
		$this->template->setParameters($ret);
		$this->template->search_values = $search_values;
		$this->template->search = empty($ret['params']) ? false : true;
		$this->template->exports = [
			"url" => ":Notes:NotesAdmin:export"
		];
	}

	public function actionExport($type, $values)
	{
		$request = $this->getHttpRequest();
		$search_values = [
			["not_id", "like", $this->not_id],
			["not_page", "like", $this->not_page],
			["not_note", "like", $this->not_note],
			["use_id", "like", $this->use_id, "users", "use_name", false, false, false],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->provideExport("notes", $request, $search_values, false, $values);
		$export = $this->exportsFactory->export($ret, $search_values, $this->export_cols, $this, $type, 'notes.admin.');

		$response = new FileResponse($export['tmpfile'], $export['filename'], $export['content_type']);
		$this->sendResponse($response);
	}

	public function actionRemoveNote($id, $page)
	{
		$this->notes->remove($id);
		$this->redirect(":Notes:NotesAdmin:notesPreview", $page);
		$this->terminate();
	}

}
