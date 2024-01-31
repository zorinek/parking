<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Application\LinkGenerator;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Mail\IMailer;

class MailSender
{

	/** @var Nette\Application\LinkGenerator */
	private $linkGenerator;

	/** @var Nette\Bridges\ApplicationLatte\TemplateFactory */
	private $templateFactory;

	/** @var Nette\Mail\Mailer */
	private $mailer;

	public function __construct(Nette\Application\LinkGenerator $linkGenerator, Nette\Bridges\ApplicationLatte\TemplateFactory $templateFactory, Nette\Mail\Mailer $mailer)
	{
		$this->linkGenerator = $linkGenerator;
		$this->templateFactory = $templateFactory;
		$this->mailer = $mailer;
	}

	private function createTemplate(): Nette\Application\UI\Template
	{
		$template = $this->templateFactory->createTemplate();
		$template->getLatte()->addProvider('uiControl', $this->linkGenerator);
		return $template;
	}

	public function sendEmail($template, $params, $from, $to)
	{
		$tpl = $this->createTemplate();
		$html = $tpl->renderToString($template, $params);

		$mail = new Nette\Mail\Message;
		$mail->setFrom($from);
		$mail->addTo($to);
		$mail->setHtmlBody($html);

		$this->mailer->send($mail);
		return $html;
	}

	public function createCommentFromTo($from, $to)
	{
		$out = "<!--\r\n";
		$out .= "From: " . $from . "\r\n";
		$out .= "To: " . $to . "\r\n";
		$out .= "-->\r\n";
		return $out;
	}

}
