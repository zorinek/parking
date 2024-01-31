Projekt Parking
---------------

Projekt je určen pro vyhodnocování počtu a typu parkovacích míst na základě videí, která jsou pořízena měřícím vozidle,

Aplikace zobrazuje dvě videa (levá a pravá kamera) a mapu, ve které je vidět tehdejší pozice vozidla.
V mapě jsou dále zobrazena detekovaná zaparkovaná vozidla (včetně typu parkování - kolmé, šikmé, podélné).

Uživatel při vyhodnocování zaznamenává různé typy parkovacích stání (obsazená, neobsazená, nelegální).

Uživatel si může konfigurovat klávesové zkratky pro měření a také si zvolit ze dvou layoutů - větší videa a malá mapa nebo videa a mapa stejné velikosti

Data  aplikaci jsou členěna do projektů, projekty do kampaní, kampaně do sekcí.

Každá sekce si nese infomace o počtu detekovaných vozidel, neobsazených místech, nelegálních stáních a nedetekovaných vozidlech a dále má parametr stav - zpracováno, rezervováno, nezpracováno.

Každá sekce na základě předpřipravených dat zobrazuje všechny průjezdy, mezi kterými je možné přepínat. V případě, že stejný segment obsahuje více průjezdů, jsou zde vidět data z jiného průjezdu k nahlédnutí a srovnání.


Instalace projektu
------------------

Instalace závislostí probíhá přes composer příkazem composer install .

Je třeba postgresql databáze

