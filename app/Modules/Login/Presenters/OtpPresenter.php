<?php

declare(strict_types=1);

namespace App\Modules\Login;

use App\Forms;
use App\Model;
use Nette\Application\UI\Form;

final class OtpPresenter extends \App\Presenters\BasePresenter
{

	/** @var Forms\SetOtpFormFactory @inject */
	public $setOtpFactory;

	/** @var Forms\OtpFormFactory @inject */
	public $otpFactory;

	/** @var Model\Users @inject */
	public $users;

	/** @var Model\Otp @inject */
	public $otp;
	private $p_logreg;

	public function __construct($p_logreg)
	{
		parent::__construct();
		$this->p_logreg = $p_logreg;
	}

	public function renderCreate()
	{
		if ($this->p_logreg['use_one_time_password'])
		{
			$use_id = $this->getSession()->getSection("verifed_user")->use_id;
			$check = $this->otp->check($use_id, $this->p_logreg['one_time_password_status']["deactivated"]);
			$otp_res = $this->otp->generate();
			$this->getSession()->getSection("verifed_user")->otp_res = $otp_res;
			if (!is_null($check))
			{
				$this->otp->removeUnused($use_id, $this->p_logreg['one_time_password_status']["deactivated"]);
			}
			$this->otp->insert($use_id, date("Y-m-d H:i:s", strtotime("+ " . $this->p_logreg['one_time_password_expiration_days'] . " days")), $this->p_logreg['one_time_password_status']["deactivated"], $otp_res);

			$this->template->otp_pass = $otp_res;
		} 
	}

	public function renderOtp()
	{
		if ($this->p_logreg['use_one_time_password'])
		{
			$this->template->disable_password_change = $this->p_logreg['disable_password_change'];
		} 
		else
		{
			$this->redirect(":Frontend:Homepage:default");
		}
		$this->template->password_display = $this->p_logreg['password_display'];
	}

	public function actionPdf()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . "/../templates/Otp/pdf.latte");

		$template->otp_pass = $this->getSession()->getSection("verifed_user")->otp_res;
		$template->server_name = $this->p_default['server_name'];
		$pdf = new \Contributte\PdfResponse\PdfResponse($template);
		$pdf->setSaveMode(\Contributte\PdfResponse\PdfResponse::INLINE);
		$pdf->documentTitle = $this->translator->translate("otp.pdf.file_title") . " " . $this->p_default['server_name'];
		$pdf->pageFormat = "A4-P";
		$pdf->getMPDF()->setFooter($this->p_default['server_url']); // footer
		$this->sendResponse($pdf);
	}

	/**
	 * SetOtp form factory.
	 */
	protected function createComponentSetOtpForm(): Form
	{
		return $this->setOtpFactory->create(function (): void
		{
			$this->redirect(':Login:Login:login');
		});
	}

	/**
	 * Otp form factory.
	 */
	protected function createComponentOtpForm(): Form
	{
		return $this->otpFactory->create(function (): void
		{
			$this->redirect(':Admin:Homepage:default');
		});
	}

}
