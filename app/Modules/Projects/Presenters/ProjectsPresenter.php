<?php

declare(strict_types=1);

namespace App\Modules\Projects;

use App\Model;
use App\Forms;
use Nette\Application\UI\Form;
use Nette\Application\Responses\FileResponse;
use Nette\Application\Responses\JsonResponse;

final class ProjectsPresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Model\Projects $projects @inject */
	public $projects;

	/** @var Model\Campaigns $campaigns @inject */
	public $campaigns;

	/** @var Model\CampaignsSegments $campaignsSegments @inject */
	public $campaignsSegments;

	/** @var Model\PreviewFactory $previewFactory @inject */
	public $previewFactory;

	/** @var Model\ExportsFactory $exportsFactory @inject */
	public $exportsFactory;

	/** @var Forms\NewProjectFormFactory $newProjectFactory @inject */
	public $newProjectFactory;

	/** @var Forms\UpdateProjectFormFactory $updateProjectFactory @inject */
	public $updateProjectFactory;

	/** @var Forms\LoadVideoFilesFormFactory $loadVideoFilesFactory @inject */
	public $loadVideoFilesFactory;

	/** @var Forms\RemoveReservationSegmentFormFactory $removeReservationSegmentFactory @inject */
	public $removeReservationSegmentFactory;

	/** @persistent */
	public $pro_id = "";

	/** @persistent */
	public $pro_name = "";

	/** @persistent */
	public $pro_note = "";

	/** @persistent */
	public $and_or = "and";

	/** @persistent */
	public $order = [];

	/** @persistent */
	public $order_dir = [];

	/** @persistent */
	public $export_cols = [];
	private $p_projects;
	private $p_campaigns;
	private $p_videos;

	public function __construct($p_projects, $p_campaigns, $p_videos)
	{
		parent::__construct();
		$this->p_projects = $p_projects;
		$this->p_campaigns = $p_campaigns;
		$this->p_videos = $p_videos;
	}

	public function renderNew()
	{
		if ($this->user->isInRole("admin"))
		{
			$this->template->p_projects = $this->p_projects;
			$this->template->p_campaigns = $this->p_campaigns;
		} 
		else
		{
			$this->redirect(":Admin:AccessDenied:default");
		}
	}
  
	public function renderDetail($pro_id)
	{
		$this->template->p_videos = $this->p_videos;

		$project = $this->projects->get($pro_id);
		$this->template->project = $project;

		$campaigns = $this->campaigns->getAll($pro_id);
		$this->template->campaigns = $campaigns;

		$my_reservations = $this->campaignsSegments->getMyReservations($this->user->id, $pro_id);
		$this->template->my_reservations = $my_reservations;
	}

	public function renderUpdate($pro_id)
	{
		if ($this->user->isInRole("admin"))
		{
			$this->template->p_projects = $this->p_projects;
			$projects = $this->projects->get($pro_id);
			$this["updateProjectForm"]->setDefaults($projects);
		} 
		else
		{
			$this->redirect(":Admin:AccessDenied:default");
		}
	}

	public function renderPreview($page)
	{
		$this->template->setFile("../app/Modules/components/default_preview.latte");
		$request = $this->getHttpRequest();
		$search_values = [
			["pro_id", "like", $this->pro_id],
			["pro_name", "like", $this->pro_name],
			["pro_note", "like", $this->pro_note],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->providePreview("projects", $page, ":Projects:Projects:preview", $request, 10, $search_values, $this, 'projects.admin.', "Projects/templates/Projects/");
		$this->template->setParameters($ret);
		$this->template->search_values = $search_values;
		$this->template->search = empty($ret['params']) ? false : true;
		$this->template->exports = [
			"url" => ":Projects:Projects:export"
		];
		$this->template->parameters = $this->p_projects;
	}

	public function actionExport($type, $values)
	{
		$request = $this->getHttpRequest();
		$search_values = [
			["pro_id", "like", $this->pro_id],
			["pro_name", "like", $this->pro_name],
			["pro_note", "like", $this->pro_note],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->provideExport("projects", $request, $search_values, false, $values);
		$export = $this->exportsFactory->export($ret, $search_values, $this->export_cols, $this, $type, 'projects.admin.');

		$response = new FileResponse($export['tmpfile'], $export['filename'], $export['content_type']);
		$this->sendResponse($response);
	}

	public function renderGetCampaign()
	{
		$template = $this->getTemplate();
		$template->setFile(__DIR__ . '/../templates/Projects/campaign_input.latte');

		$rendered = $template->renderToString();

		$this->sendResponse(new JsonResponse($rendered));
		$this->terminate();
	}

	public function renderExportData($pro_id)
	{
		$data = $this->campaignsSegments->getAll($pro_id);

		$export = $this->projects->exportData($this, $data);
		$response = new FileResponse($export['tmpfile'], $export['filename'], $export['content_type']);
		$this->sendResponse($response);
	}

	/**
	 * New project form factory.
	 */
	protected function createComponentNewProjectForm(): Form
	{
		return $this->newProjectFactory->create(function ($pro_id): void
		{
			$this->redirect(':Projects:Projects:detail', $pro_id);
		});
	}

	/**
	 * Update project form factory.
	 */
	protected function createComponentUpdateProjectForm(): Form
	{
		return $this->updateProjectFactory->create(function ($pro_id): void
		{
			$this->redirect(':Projects:Projects:detail', $pro_id);
		});
	}

	/**
	 * Load Video Files form factory.
	 */
	protected function createComponentLoadVideoFilesForm(): Form
	{
		return $this->loadVideoFilesFactory->create(function ($pro_id): void
		{
			$this->redirect(':Projects:Projects:detail', $pro_id);
		});
	}

	/**
	 * Remove reservation segment form factory.
	 */
	protected function createComponentRemoveReservationSegmentForm(): Form
	{
		return $this->removeReservationSegmentFactory->create(function ($pro_id): void
		{
			$this->redirect(':Projects:Projects:detail', $pro_id);
		});
	}

}
