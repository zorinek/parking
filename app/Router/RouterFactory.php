<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{

	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->addRoute('[<locale=cs cs|en>/]', 'Frontend:Homepage:default');
		$router->addRoute('[<locale=cs cs|en>/]prihlaseni', 'Login:Login:login');
		$router->addRoute('[<locale=cs cs|en>/]odhlaseni', 'Login:Login:logout');
		$router->addRoute('[<locale=cs cs|en>/]prihlaseni/autentizace', 'Login:Login:tfa');
		$router->addRoute('[<locale=cs cs|en>/]prihlaseni/reset-hesla', 'Login:Login:resetPassword');
		$router->addRoute('[<locale=cs cs|en>/]prihlaseni/reset-hesla/dekujeme', 'Login:Login:resetSuccessfull');
		$router->addRoute('[<locale=cs cs|en>/]prihlaseni/zadani-noveho-hesla/<token>', 'Login:Login:resetVerification');
		$router->addRoute('[<locale=cs cs|en>/]prihlaseni/prihlaseni-jednorazovym-heslem', 'Login:Otp:otp');
		$router->addRoute('[<locale=cs cs|en>/]registrace', 'Login:Registration:registration');
		$router->addRoute('[<locale=cs cs|en>/]registrace/verifikace/<token>', 'Login:Registration:verification');
		$router->addRoute('[<locale=cs cs|en>/]registrace/dekujeme', 'Login:Registration:registrationSuccessfull');
		$router->addRoute('[<locale=cs cs|en>/]registrace/nastaveni-druheho-faktoru', 'Login:Registration:setTfa');
		$router->addRoute('[<locale=cs cs|en>/]registrace/znovuodeslani-verifikacniho-emailu', 'Login:Registration:sendVerificationEmailAgain');
		$router->addRoute('[<locale=cs cs|en>/]registrace/vytvoreni-jednorazovych-prihlasovacich-kodu', 'Login:Otp:create');
		$router->addRoute('[<locale=cs cs|en>/]registrace/tisk-jednorazovych-prihlasovacich-kodu', 'Login:Otp:pdf');

		$router->addRoute('[<locale=cs cs|en>/]admin/homepage', 'Admin:Homepage:default');
		$router->addRoute('[<locale=cs cs|en>/]admin/bez-pristupu', 'Admin:AccessDenied:default');
		$router->addRoute('[<locale=cs cs|en>/]admin/sprava-uzivatelu', 'Settings:Users:default');
		$router->addRoute('[<locale=cs cs|en>/]admin/sprava-uzivatelu/prehled-uzivatelu[/<page>]', 'Settings:Users:preview');
		$router->addRoute('[<locale=cs cs|en>/]admin/sprava-uzivatelu/detail-uzivatele/<use_id>', 'Settings:Users:detail');
		$router->addRoute('[<locale=cs cs|en>/]admin/sprava-uzivatelu/aktivovat-uzivatele/<use_id>', 'Settings:Users:activate_user');
		$router->addRoute('[<locale=cs cs|en>/]admin/sprava-uzivatelu/deaktivovat-uzivatele/<use_id>', 'Settings:Users:deactivate_user');
		$router->addRoute('[<locale=cs cs|en>/]admin/sprava-uzivatelu/novy-uzivatel/', 'Settings:Users:new');
		$router->addRoute('[<locale=cs cs|en>/]admin/sprava-uzivatelu/prehled-uzivatelu/export[/<type>][/<values>]', 'Settings:Users:export');

		$router->addRoute('[<locale=cs cs|en>/]admin/muj-ucet', 'Settings:Account:profile');
		$router->addRoute('[<locale=cs cs|en>/]admin/muj-ucet/obrazek', 'Settings:Account:image');

		$router->addRoute('[<locale=cs cs|en>/]admin/zpravy-z-kontaktniho-formulare[/<page>]', 'Contactform:ContactformAdmin:default');
		$router->addRoute('[<locale=cs cs|en>/]admin/zpravy-z-kontaktniho-formulare/detail/<id>', 'Contactform:ContactformAdmin:detail');
		$router->addRoute('[<locale=cs cs|en>/]admin/zpravy-z-kontaktniho-formulare/zobraz-notifikacni-email/<id>', 'Contactform:ContactformAdmin:getNotificationEmail');
		$router->addRoute('[<locale=cs cs|en>/]admin/zpravy-z-kontaktniho-formulare/zobraz-odeslany-email/<id>', 'Contactform:ContactformAdmin:getSenderEmail');

		$router->addRoute('[<locale=cs cs|en>/]kontaktni-formular', 'Contactform:Contactform:default');
		$router->addRoute('[<locale=cs cs|en>/]kontaktni-formular/odeslano', 'Contactform:Contactform:sended');

		$router->addRoute('[<locale=cs cs|en>/]diskuze', 'Discussion:Discussion:default');
		$router->addRoute('[<locale=cs cs|en>/]diskuze/obrazek/<id>', 'Discussion:Discussion:image');

		$router->addRoute('[<locale=cs cs|en>/]admin/diskuze[/<page>]', 'Discussion:DiscussionAdmin:default');
		$router->addRoute('[<locale=cs cs|en>/]admin/diskuze/schvalit-komentar/<id>/<page>', 'Discussion:DiscussionAdmin:approve');
		$router->addRoute('[<locale=cs cs|en>/]admin/diskuze/zrusit-schvaleni-komentare/<id>/<page>', 'Discussion:DiscussionAdmin:disapprove');

		$router->addRoute('[<locale=cs cs|en>/]admin/diskuze/odstranit-komentar/<id>/<page>', 'Discussion:DiscussionAdmin:remove');

		$router->addRoute('[<locale=cs cs|en>/]admin/soubory/prehled-souboru[/<page>]', 'Files:FilesAdmin:preview');
		$router->addRoute('[<locale=cs cs|en>/]admin/soubory/odstranit-soubor/<fil_id>/<page>', 'Files:FilesAdmin:remove');

		$router->addRoute('[<locale=cs cs|en>/]soubory/', 'Upload:Upload:default');

		$router->addRoute('[<locale=cs cs|en>/]admin/poznamky/pridat-poznamku', 'Notes:Notes:addNote');
		$router->addRoute('[<locale=cs cs|en>/]admin/poznamky[/<page>]', 'Notes:Notes:notesPreview');
		$router->addRoute('[<locale=cs cs|en>/]admin/poznamky/export[/<type>][/<values>]', 'Notes:Notes:export');
		$router->addRoute('[<locale=cs cs|en>/]admin/poznamky/odstranit-poznamku/<id>/<page>', 'Notes:Notes:removeNote');
		$router->addRoute('[<locale=cs cs|en>/]admin/vsechny-poznamky[/<page>]', 'Notes:NotesAdmin:notesPreview');
		$router->addRoute('[<locale=cs cs|en>/]admin/vsechny-poznamky/export[/<type>][/<values>]', 'Notes:NotesAdmin:export');
		$router->addRoute('[<locale=cs cs|en>/]admin/vsechny-poznamky/odstranit-poznamku/<id>/<page>', 'Notes:NotesAdmin:removeNote');

		$router->addRoute('[<locale=cs cs|en>/]admin/dotazy/novy-dotaz', 'Queries:Queries:newQuery');
		$router->addRoute('[<locale=cs cs|en>/]admin/dotazy/uprav-dotaz/<que_id>', 'Queries:Queries:updateQuery');
		$router->addRoute('[<locale=cs cs|en>/]admin/dotazy/dotaz/<que_id>', 'Queries:Queries:detail');
		$router->addRoute('[<locale=cs cs|en>/]admin/dotazy[/<page>]', 'Queries:Queries:preview');
		$router->addRoute('[<locale=cs cs|en>/]admin/dotazy/export[/<type>][/<values>]', 'Queries:Queries:export');
		$router->addRoute('[<locale=cs cs|en>/]dotazy[/<page>]', 'Queries:QueriesFrontend:preview');
		$router->addRoute('[<locale=cs cs|en>/]dotazy/export[/<type>][/<values>]', 'Queries:QueriesFrontend:export');
		$router->addRoute('[<locale=cs cs|en>/]dotazy/dotaz/<que_id>/export[/<type>][/<values>]', 'Queries:QueriesFrontend:detailExport');
		$router->addRoute('[<locale=cs cs|en>/]dotazy/detail/<que_id>[/<page>]', 'Queries:QueriesFrontend:detail');

		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/novy-projekt', 'Projects:Projects:new');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/detail-projektu/<pro_id>', 'Projects:Projects:detail');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/uprava-projektu/<pro_id>', 'Projects:Projects:update');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/kampan-radek', 'Projects:Projects:getCampaign');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/<pro_id>/export-ziskanych-dat', 'Projects:Projects:exportData');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/<pro_id>/detail-kampane/<cam_id>/segment/<seg_id>', 'Projects:Campaigns:detail');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/<pro_id>/kampane/<cam_id>/mapa', 'Projects:Campaigns:map');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/<pro_id>/kampane/<cam_id>[/<page>]', 'Projects:Campaigns:preview');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/<pro_id>/kampane/<cam_id>/export[/<type>][/<values>]', 'Projects:Campaigns:export');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty[/<page>]', 'Projects:Projects:preview');
		$router->addRoute('[<locale=cs cs|en>/]admin/projekty/export[/<type>][/<values>]', 'Projects:Projects:export');



		$router->addRoute('[<locale=cs cs|en>/]admin/konfigurace', 'UsersConfigurations:UsersConfigurations:configuration');
		$router->addRoute('[<locale=cs cs|en>/]admin/napoveda', 'Admin:Homepage:help');
		$router->addRoute('[<locale=cs cs|en>/]o-projektu', 'Frontend:Homepage:about');

		return $router;
	}

}
