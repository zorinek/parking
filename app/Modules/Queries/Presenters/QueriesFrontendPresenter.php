<?php

declare(strict_types=1);

namespace App\Modules\Queries;

use App\Model;
use Nette\Application\Responses\FileResponse;

final class QueriesFrontendPresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Model\Queries $queries @inject */
	public $queries;

	/** @var Model\PreviewFactory $previewFactory @inject */
	public $previewFactory;

	/** @var Model\ExportsFactory $exportsFactory @inject */
	public $exportsFactory;

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

	public function renderDetail($que_id, $page)
	{
		$limit = 10;
		if (is_null($page))
		{
			$offset = 0;
			$page = 1;
		} 
		else
		{
			$offset = ($page - 1) * $limit;
		}

		$query = $this->queries->get($que_id);
		$this->template->query = $query;

		$query_data = $this->queries->runQuery($query->que_query, $limit, $offset);
		$this->template->query_data = $query_data["array"];

		$this->template->que_id = $que_id;

		$this->template->page = $page;
		$this->template->limit = $limit;
		$obj = new \stdClass();
		$obj->count = $query_data["all_count"];
		$this->template->all_count = $obj;
		$this->template->redirect_route = ":Queries:QueriesFrontend:detail";
		$this->template->exports = [
			"url" => ":Queries:QueriesFrontend:detailExport"
		];
	}

	public function actionDetailExport($que_id, $type, $values)
	{
		$query = $this->queries->get($que_id);
		$this->template->query = $query;

		$request = $this->getHttpRequest();
		$search_values = [
			["que_id", "like", $this->que_id],
			["que_name", "like", $this->que_name],
			["que_query", "like", $this->que_query],
			["que_note", "like", $this->que_note],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = [];
		$ret["values"] = "all";
		$ret["all"] = $query_data = $this->queries->runQuery($query->que_query);
		$export = $this->queries->export($ret, $search_values, $this->export_cols, $this, $type, 'queries.preview_frontend.');

		$response = new FileResponse($export['tmpfile'], $export['filename'], $export['content_type']);
		$this->sendResponse($response);
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
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->providePreview("queries", $page, ":Queries:QueriesFrontend:preview", $request, 10, $search_values, $this, 'queries.preview_frontend.', "Queries/templates/QueriesFrontend/", "que_status = 1");
		$this->template->setParameters($ret);
		$this->template->search_values = $search_values;
		$this->template->search = empty($ret['params']) ? false : true;
		$this->template->exports = [
			"url" => ":Queries:QueriesFrontend:export"
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
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->provideExport("queries", $request, $search_values, "que_status = 1", $values);
		$export = $this->exportsFactory->export($ret, $search_values, $this->export_cols, $this, $type, 'queries.preview_frontend.');

		$response = new FileResponse($export['tmpfile'], $export['filename'], $export['content_type']);
		$this->sendResponse($response);
	}

}
