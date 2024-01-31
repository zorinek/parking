<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

final class LoadVideoFilesFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Videos */
	private $videos;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_videos;
	private $param = "p_videos";

	public function __construct($p_videos, FormFactory $factory, Nette\Localization\ITranslator $translator, Model\Videos $videos)
	{
		$this->p_videos = $p_videos;
		$this->factory = $factory;
		$this->translator = $translator;
		$this->videos = $videos;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$name = "vid_files";
		if ($this->{$this->param}["displayed"][$name])
		{
			$field = $form->addText($name);
			if ($this->{$this->param}["required"][$name])
			{
				$field->setRequired("params.new.error_" . $name);
			}
		}

		$form->addHidden("original_files");

		$form->addSubmit('load_videos');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{

				$pro_id = $form->getPresenter()->getParameters()["pro_id"];
				$arr = json_decode($values->original_files);
				dumpe($arr);
//                        $vals = [];
//                        $name = "pro_name";
//                        if($this->{$this->param_proj}["displayed"][$name])
//                        {
//                            $vals[$this->projects::COLUMN_PRO_NAME] = $values->{$name};
//                        }
//                        $name = "pro_note";
//                        if($this->{$this->param_proj}["displayed"][$name])
//                        {
//                            $vals[$this->projects::COLUMN_PRO_NOTE] = $values->{$name};
//                        }
//
//                        $vals[$this->projects::COLUMN_PRO_DATETIMEINSERT] = date("Y-m-d H:i:s");
//                        
//                        $pro_id = $this->projects->insert($vals);
//                        
//                        $vals = [];
//                        $vals[$this->campaigns::COLUMN_PRO_ID] = $pro_id;
//                        $name = "cam_name";
//                        if($this->{$this->param_camp}["displayed"][$name])
//                        {
//                            $vals[$this->campaigns::COLUMN_CAM_NAME] = $values->{$name};
//                            $this->campaigns->insert($vals);
//                        
//                        
//                            foreach($_POST[$this->campaigns::COLUMN_CAM_NAME . "_next"] as $line)
//                            {
//                                $vals[$this->campaigns::COLUMN_CAM_NAME] = $line;
//                                $this->campaigns->insert($vals);
//                            }
//                        }
//                        $onSuccess($pro_id);
			} catch (Exception $ex)
			{
				echo $ex->getMessage();
				return;
			}
		};

		return $form;
	}

}
