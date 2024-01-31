<?php

declare(strict_types=1);

namespace App\Modules\Projects;

use App\Model;
use App\Forms;
use Nette\Application\UI\Form;
use Nette\Application\Responses\FileResponse;
use Nette\Application\Responses\JsonResponse;

final class CampaignsPresenter extends \App\Presenters\BaseProtectedPresenter
{

	/** @var Model\Projects $projects @inject */
	public $projects;

	/** @var Model\Campaigns $campaigns @inject */
	public $campaigns;

	/** @var Model\CampaignsSegments $campaignsSegments @inject */
	public $campaignsSegments;

	/** @var Model\Segments $segments @inject */
	public $segments;

	/** @var Model\Gps $gps @inject */
	public $gps;

	/** @var Model\Videos $videos @inject */
	public $videos;

	/** @var Model\UsersConfigurations $usersConfigurations @inject */
	public $usersConfigurations;

	/** @var Model\PreviewFactory $previewFactory @inject */
	public $previewFactory;

	/** @var Model\ExportsFactory $exportsFactory @inject */
	public $exportsFactory;

	/** @var Forms\NewProjectFormFactory $newProjectFactory @inject */
	public $newProjectFactory;

	/** @var Forms\UpdateProjectFormFactory $updateProjectFactory @inject */
	public $updateProjectFactory;

	/** @var Forms\FinishSectionFormFactory $finishSectionFactory @inject */
	public $finishSectionFactory;

	/** @var Forms\InSolutionFormFactory $inSolutionFactory @inject */
	public $inSolutionFactory;

	/** @var Forms\RemoveReservationFormFactory $removeReservationFactory @inject */
	public $removeReservationFactory;

	/** @persistent */
	public $seg_id = "";

	/** @persistent */
	public $cas_parkingdetected = "";

	/** @persistent */
	public $cas_parkingfree = "";

	/** @persistent */
	public $cas_parkingillegal = "";

	/** @persistent */
	public $cas_parkingnotdetected = "";

	/** @persistent */
	public $cas_done = "";

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
	private $p_configurations;

	public function __construct($p_projects, $p_campaigns, $p_configurations)
	{
		parent::__construct();
		$this->p_projects = $p_projects;
		$this->p_campaigns = $p_campaigns;
		$this->p_configurations = $p_configurations;
	}

	public function getGpsSegments($gps, $start, $diff)
	{
		$first = true;
		$out = [];
		$i = 0;
		foreach ($gps as $line)
		{
			$date = \DateTime::createFromFormat('Y-m-d H:i:s.u', $line[2]);
			$ms = round((float) ($date->getTimestamp() . '.' . $date->format('u')), 1);
			if ($first)
			{
				$first = false;
			} else
			{
				if (round($start + $diff, 1) != $ms)
				{
					$i++;
				}
			}
			$start = $ms;
			$out[$i][] = $line;
		}
		return $out;
	}
 
	public function renderDetail($pro_id, $cam_id, $seg_id)
	{
		$this->template->pro_id = $pro_id;
		$this->template->cam_id = $cam_id;

		$project = $this->projects->get($pro_id);
		$this->template->project = $project;
		$campaign = $this->campaigns->getCampaign($pro_id, $cam_id);
		$this->template->campaign = $campaign;

		$finished_segments = $this->campaignsSegments->getAllFilled($seg_id, $cam_id);
		$this->template->finished_segments = $finished_segments;

		$user_configuration = $this->usersConfigurations->getUserConfiguration($this->user->id);

		foreach ($this->p_configurations['default_value'] as $key => $line)
		{
			if (isset($user_configuration[$key]) && $user_configuration[$key]->usc_value == '')
			{
				$user_configuration[$key]->usc_value = $line;
			} 
			else if (!isset($user_configuration[$key]))
			{
				$user_configuration[$key] = new \stdClass();
				$user_configuration[$key]->usc_value = $line;
			}
			if ($user_configuration[$key]->usc_value == "Space")
			{
				$user_configuration[$key]->usc_value = " ";
			}
		}
		$this->template->user_configuration = $user_configuration;

		$segment = $this->segments->get($seg_id);
		$this->template->segment = $segment;

		$gps = $this->gps->getAll($seg_id, $cam_id);

		$start = "";
		$i = 0;
		$g = array_reverse($gps);
		$first = @array_pop($g);
		$second = @array_pop($g);
		if (count($gps) < 2)
		{
			$second = $first;
		}
		$datef = \DateTime::createFromFormat('Y-m-d H:i:s.u', $first[2]);
		$dates = \DateTime::createFromFormat('Y-m-d H:i:s.u', $second[2]);
		$msf = round((float) ($datef->getTimestamp() . '.' . $datef->format('u')), 1);
		$mss = round((float) ($dates->getTimestamp() . '.' . $dates->format('u')), 1);

		$diff = round($mss - $msf, 1);

		$out = $this->getGpsSegments($gps, $start, $diff);
		if (count($out) > 10)
		{
			$out = $this->getGpsSegments($gps, $start, 0.4);
			if (count($out) > 10)
			{
				$out = $this->getGpsSegments($gps, $start, 0.2);
			}
		}

		$this->template->gps_segments = $out;
		$this->template->gps = $gps;

		$max_min_times_rides = [];

		foreach ($out as $key => $line)
		{
			$datetime = new \DateTime($line[count($line) - 1][2]);
			$whole = strtotime($datetime->format("Y-m-d H:00:00"));
			$i = (int) $datetime->format("i");
			if ($i >= 0 && $i < 15)
			{
				$out = 15;
			} else if ($i >= 15 && $i < 30)
			{
				$out = 30;
			} else if ($i >= 30 && $i < 45)
			{
				$out = 45;
			} else if ($i >= 45 && $i < 60)
			{
				$out = 60;
			}

			$whole += $out * 60;
			$next_15 = date("Y-m-d H:i:s", $whole);

			$datetime = new \DateTime($line[0][2]);
			$whole = strtotime($datetime->format("Y-m-d H:00:00"));
			$i = (int) $datetime->format("i");
			if ($i >= 0 && $i < 15)
			{
				$out = 15;
			} 
			else if ($i >= 15 && $i < 30)
			{
				$out = 30;
			} 
			else if ($i >= 30 && $i < 45)
			{
				$out = 45;
			} 
			else if ($i >= 45 && $i < 60)
			{
				$out = 60;
			}

			$whole -= $out * 60;
			$back_15 = date("Y-m-d H:i:s", $whole);
			$max_min_times_rides[$key] = [$line[0][2], $line[count($line) - 1][2], $back_15, $next_15];
		}

		$this->template->max_min_times_rides = $max_min_times_rides;

		$gps_max_min = $this->gps->getMaxMinTimeAll($seg_id, $cam_id);
		$this->template->max_min_time = $gps_max_min;

		$videos = $this->videos->getByAllTimes($max_min_times_rides, $pro_id);

		$this->template->videos = $videos[0];
		$this->template->videos_all = $videos[1];

		$campaigns_segments = $this->campaignsSegments->getOne($cam_id, $seg_id, 1);
		$this->template->campaigns_segments = $campaigns_segments;
	}

	public function renderUpdate($pro_id)
	{
		$this->template->p_projects = $this->p_projects;
		$projects = $this->projects->get($pro_id);
		$this["updateProjectForm"]->setDefaults($projects);
	}

	public function renderPreview($pro_id, $cam_id, $page)
	{
		$this->template->setFile("../app/Modules/components/default_preview.latte");
		$request = $this->getHttpRequest();
		$search_values = [
			["seg_id", "like", $this->seg_id],
			["cas_parkingdetected", "like", $this->cas_parkingdetected],
			["cas_parkingfree", "like", $this->cas_parkingfree],
			["cas_parkingillegal", "like", $this->cas_parkingillegal],
			["cas_parkingnotdetected", "like", $this->cas_parkingnotdetected],
			["cas_done", "like", $this->cas_done, false, "sections.cas_done."],
			["and_or", "", $this->and_or],
			["order", "", $this->order],
			["order_dir", "", $this->order_dir]
		];
		$ret = $this->previewFactory->providePreview("campaigns_segments", $page, ":Projects:Campaigns:preview", $request, 10, $search_values, $this, 'campaigns.admin.', "Projects/templates/Campaigns/", "cam_id = " . $cam_id);
		$this->template->setParameters($ret);
		$this->template->search_values = $search_values;
		$this->template->search = empty($ret['params']) ? false : true;
		$this->template->exports = [
			"url" => ":Projects:Campaigns:export",
			"parameters" => [$pro_id, $cam_id]
		];
		$this->template->parameters = $this->p_campaigns;
	}

	public function actionExport($pro_id, $cam_id, $type, $values)
	{
		$request = $this->getHttpRequest();
		$search_values = [
			["seg_id", "like", $this->seg_id],
			["cas_parkingdetected", "like", $this->cas_parkingdetected],
			["cas_parkingfree", "like", $this->cas_parkingfree],
			["cas_parkingillegal", "like", $this->cas_parkingillegal],
			["cas_parkingnotdetected", "like", $this->cas_parkingnotdetected],
			["cas_done", "like", $this->cas_done, false, "sections.cas_done."],
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

	public function renderMap($pro_id, $cam_id)
	{
		$segments = $this->campaignsSegments->getSegmentsForMap($cam_id);
		$this->template->segments = $segments;
		$this->template->pro_id = $pro_id;
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
	 * Finish section form factory.
	 */
	protected function createComponentFinishSectionForm(): Form
	{
		return $this->finishSectionFactory->create(function ($pro_id, $cam_id, $seg_id): void
		{
			if (is_null($seg_id))
			{
				$this->redirect(':Projects:Campaigns:map', $pro_id, $cam_id);
			} 
			else
			{
				$this->redirect(':Projects:Campaigns:detail', $pro_id, $cam_id, $seg_id);
			}
		});
	}

	/**
	 * In solution form factory.
	 */
	protected function createComponentInSolutionForm(): Form
	{
		return $this->inSolutionFactory->create(function ($pro_id, $cam_id, $seg_id): void
		{
			$this->redirect(':Projects:Campaigns:detail', $pro_id, $cam_id, $seg_id);
		});
	}

	/**
	 * Remove reservation form factory.
	 */
	protected function createComponentRemoveReservationForm(): Form
	{
		return $this->removeReservationFactory->create(function ($pro_id, $cam_id, $seg_id): void
		{
			$this->redirect(':Projects:Campaigns:detail', $pro_id, $cam_id, $seg_id);
		});
	}

}
