<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

final class ContactformFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\MailSender */
	private $mailSender;

	/** @var Model\Contactform */
	private $contactform;

	/** @var Model\Captcha */
	private $captcha;

	/** @var Nette\Localization\ITranslator */
	private $translator;
	private $p_contactform;
	private $p_default;

	public function __construct($p_contactform, $p_default, FormFactory $factory, Model\MailSender $mailSender, Model\Contactform $contactform, Model\Captcha $captcha, Nette\Localization\ITranslator $translator)
	{
		$this->factory = $factory;
		$this->p_contactform = $p_contactform;
		$this->p_default = $p_default;
		$this->mailSender = $mailSender;
		$this->contactform = $contactform;
		$this->captcha = $captcha;
		$this->translator = $translator;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->setTranslator($this->translator);
		$form->addEmail("con_email")->setRequired("contactform.form.error_con_email");
		$form->addTextArea("con_message")->setRequired("contactform.form.error_con_message");

		if ($this->p_contactform["fields"]["name"])
		{
			$form->addText("con_name")->setRequired("contactform.form.error_con_name");
		}
		if ($this->p_contactform["fields"]["phone"])
		{
			$form->addText("con_phone")->setRequired("contactform.form.error_con_phone");
		}
		if ($this->p_contactform["fields"]["category"])
		{
			$form->addSelect("con_category", "Categories", $this->p_contactform["con_category"])->setPrompt("contactform.form.notselected")->setRequired("contactform.form.error_con_category");
		}
		if ($this->p_contactform["fields"]["sender"])
		{
			$form->addCheckbox("con_sender");
		}
		if ($this->p_contactform['captcha_enabled'])
		{
			$form->addText('captcha', "Captcha")->setRequired('contactform.form.error_captcha');
			$form->addHidden("captcha_text");
		}

		$form->addSubmit('send_message', 'Send message');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				if ($this->p_contactform["captcha_enabled"])
				{
					$text_value = $this->captcha->decode($values->captcha_text);
					if ($text_value != $values->captcha)
					{
						throw new \App\Exceptions\InvalidCaptchaException;
					}
				}
				$vals = [];
				$vals[$this->contactform::COLUMN_CON_EMAIL] = $values->con_email;
				$vals[$this->contactform::COLUMN_CON_MESSAGE] = $values->con_message;
				if ($this->p_contactform["fields"]["name"])
				{
					$vals[$this->contactform::COLUMN_CON_NAME] = $values->con_name;
				}
				if ($this->p_contactform["fields"]["phone"])
				{
					$vals[$this->contactform::COLUMN_CON_PHONE] = $values->con_phone;
				}
				if ($this->p_contactform["fields"]["category"])
				{
					$vals[$this->contactform::COLUMN_CON_CATEGORY] = $values->con_category;
				}
				if ($this->p_contactform["fields"]["sender"])
				{
					$vals[$this->contactform::COLUMN_CON_SENDER] = ($values->con_sender + 1);
				}

				$vals[$this->contactform::COLUMN_CON_DATETIMEINSERT] = date("Y-m-d H:i:s");

				$notification_html = "";
				if ($this->p_contactform["send_notification_email"])
				{

					if ($this->p_contactform["fields"]["category"])
					{
						$recipient_email = $this->p_contactform["categories_emails"][$values->con_category];
					} else
					{
						$recipient_email = $this->p_contactform["default_email"];
					}
					$params = [
						"sender" => $values->con_email,
						"message" => $vals[$this->contactform::COLUMN_CON_MESSAGE],
						"name" => $this->p_contactform["fields"]["name"] ? $vals[$this->contactform::COLUMN_CON_NAME] : false,
						"phone" => $this->p_contactform["fields"]["phone"] ? $vals[$this->contactform::COLUMN_CON_PHONE] : false,
						"category" => $this->p_contactform["fields"]["category"] ? $vals[$this->contactform::COLUMN_CON_CATEGORY] : false,
						"server" => $this->p_default['server_name']
					];
					$notification_html = $this->mailSender->sendEmail(
							__DIR__ . '/../Modules/Contactform/templates/Contactform/email_notification.latte',
							$params,
							$values->con_email,
							$recipient_email
					);
					if ($this->p_contactform["saving"]["file"] == $this->p_contactform["default_saving"] || $this->p_contactform["saving"]["both"] == $this->p_contactform["default_saving"])
					{
						if (!empty($notification_html))
						{
							$this->contactform->saveFile($this->mailSender->createCommentFromTo($recipient_email, $values->con_email) . $notification_html, $this->p_contactform["notification_dir"], $vals[$this->contactform::COLUMN_CON_DATETIMEINSERT]);
						}
					}
				}

				$sender_html = "";
				if ($this->p_contactform["fields"]["sender"])
				{
					if ($values->con_sender)
					{
						$params = [
							"message" => $vals[$this->contactform::COLUMN_CON_MESSAGE],
							"name" => $this->p_contactform["fields"]["name"] ? $vals[$this->contactform::COLUMN_CON_NAME] : false,
							"phone" => $this->p_contactform["fields"]["phone"] ? $vals[$this->contactform::COLUMN_CON_PHONE] : false,
							"category" => $this->p_contactform["fields"]["category"] ? $vals[$this->contactform::COLUMN_CON_CATEGORY] : false,
							"server" => $this->p_default['server_name']
						];

						$sender_html = $this->mailSender->sendEmail(
								__DIR__ . '/../Modules/Contactform/templates/Contactform/email_sender.latte',
								$params,
								$this->p_contactform["default_email"],
								$values->con_email
						);
						if ($this->p_contactform["saving"]["file"] == $this->p_contactform["default_saving"] || $this->p_contactform["saving"]["both"] == $this->p_contactform["default_saving"])
						{
							if (!empty($sender_html))
							{
								$this->contactform->saveFile($this->mailSender->createCommentFromTo($values->con_email, $this->p_contactform["default_email"]) . $sender_html, $this->p_contactform["sender_dir"], $vals[$this->contactform::COLUMN_CON_DATETIMEINSERT]);
							}
						}
					}
				}

				if ($this->p_contactform["saving"]["db"] == $this->p_contactform["default_saving"] || $this->p_contactform["saving"]["both"] == $this->p_contactform["default_saving"])
				{
					$this->contactform->insert($vals);
				}
			} catch (\App\Exceptions\InvalidCaptchaException $e)
			{
				$form["captcha"]->addError('contactform.form.error_captcha_result');
				return;
			} catch (\App\Exceptions\DirNotExistsException $e)
			{
				$form->addError('contactform.form.error_dir_not_exists');
				return;
			}


			$onSuccess();
		};

		return $form;
	}

}
