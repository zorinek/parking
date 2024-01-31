<?php

declare(strict_types=1);

namespace App\Modules\Queries;

use App\Model;
use App\Forms;
use Nette\Application\UI\Form;
use Nette\Application\Responses\FileResponse;

final class QueriesPresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Model\Queries $queries @inject */
	public $queries;

	/** @var Model\PreviewFactory $previewFactory @inject */
	public $previewFactory;

	/** @var Model\ExportsFactory $exportsFactory @inject */
	public $exportsFactory;

	/** @var Forms\NewQueryFormFactory $newQueryFactory @inject */
	public $newQueryFactory;

	/** @var Forms\UpdateQueryFormFactory $updateQueryFactory @inject */
	public $updateQueryFactory;

	/** @persistent */
	public $que_id = "";

	/** @persistent */
	public $que_name = "";

	/** @persistent */
	public $que_query = "";

	/** @persistent */
	public $que_note = "";

	/** @persistent */
	public $que_status = "";

	/** @persistent */
	public $and_or = "and";

	/** @persistent */
	public $order = [];

	/** @persistent */
	public $order_dir = [];

	/** @persistent */
	public $export_cols = [];
	
	private $p_queries;

	public function __construct($p_queries)
	{
		parent::__construct();
		$this->p_queries = $p_queries;
	}

	public function renderNewQuery()
	{
		$this->template->p_queries = $this->p_queries;
	}

	public function renderUpdateQuery($que_id)
	{
		$this->template->p_queries = $this->p_queries;
		$query = $this->queries->get($que_id);
		$this["updateQueryForm"]->setDefaults($query);
	}

	public function renderDetail($que_id)
	{
		$query = $this->queries->get($que_id);
		$this->template->query = $query;

		$query_data = $this->queries->runQuery($query->que_query, 10);
		$this->template->query_data = $query_data["array"];
	}

	public function renderPreview($page)
	{
		$this->template->setFile("../app/Modules/components/default_preview.latte");
		$request = $this->getHttpRequest();
		$search_values = [
			["que_id", "like", $this->que_id],
			["que_name", "like", $this->que_name],
			["que_query", "like", $this->que_query],
			["que_note", "like", $this->que_note],
			["que_status", "like", $this->que_status, false, "queries.que_status."],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->providePreview("queries", $page, ":Queries:Queries:preview", $request, 10, $search_values, $this, 'queries.admin.', "Queries/templates/Queries/");
		$this->template->setParameters($ret);
		$this->template->search_values = $search_values;
		$this->template->search = empty($ret['params']) ? false : true;
		$this->template->exports = [
			"url" => ":Queries:Queries:export"
		];
		$this->template->parameters = $this->p_queries;
	}

	public function actionExport($type, $values)
	{
		$request = $this->getHttpRequest();
		$search_values = [
			["que_id", "like", $this->que_id],
			["que_name", "like", $this->que_name],
			["que_query", "like", $this->que_query],
			["que_note", "like", $this->que_note],
			["que_status", "like", $this->que_status, false, "queries.que_status."],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->provideExport("queries", $request, $search_values, false, $values);
		$export = $this->exportsFactory->export($ret, $search_values, $this->export_cols, $this, $type, 'queries.admin.');

		$response = new FileResponse($export['tmpfile'], $export['filename'], $export['content_type']);
		$this->sendResponse($response);
	}

	/**
	 * New query form factory.
	 */
	protected function createComponentNewQueryForm(): Form
	{
		return $this->newQueryFactory->create(function ($que_id): void
		{
			$this->redirect(':Queries:Queries:detail', $que_id);
		});
	}

	/**
	 * Update query form factory.
	 */
	protected function createComponentUpdateQueryForm(): Form
	{
		return $this->updateQueryFactory->create(function ($que_id): void
		{
			$this->redirect(':Queries:Queries:detail', $que_id);
		});
	}
}
