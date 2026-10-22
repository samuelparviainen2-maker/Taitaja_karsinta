<?php
// Lataa kaikki tapahtumat JSON-tiedostosta.
$json = file_get_contents(__DIR__ . "/tapahtumat.json");
if ($json === false) {
    throw new RuntimeException("Tapahtumatiedoston lukeminen epäonnistui.");
}

$tapahtumat = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
if (!is_array($tapahtumat)) {
    throw new UnexpectedValueException("Tapahtumatiedoston sisällön pitää olla lista.");
}

// Hae osoitteesta tapahtuman ID ja etsi sitä vastaava tapahtuma.
$tapahtumaId = isset($_GET["id"]) && is_string($_GET["id"])
    ? filter_var($_GET["id"], FILTER_VALIDATE_INT)
    : false;
$tapahtuma = null;

if ($tapahtumaId !== false && $tapahtumaId !== null) {
    foreach ($tapahtumat as $tapahtumaehdokas) {
        if ((int) ($tapahtumaehdokas["id"] ?? 0) === $tapahtumaId) {
            $tapahtuma = $tapahtumaehdokas;
            break;
        }
    }
}

// Suojaa JSON-tiedoston arvot, kun ne tulostetaan HTML-sivulle.
function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// Näytä 404-tila, jos ID puuttuu tai tapahtumaa ei löydy.
if ($tapahtuma === null) {
    http_response_code(404);
}

$kuva = $tapahtuma["kuva"] ?? "";
$kuvaOlemassa = $kuva !== "" && is_file(__DIR__ . "/" . $kuva);
?>
<!DOCTYPE html>
<html lang="fi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $tapahtuma ? escapeHtml($tapahtuma["nimi"] ?? "Tapahtuma") : "Tapahtumaa ei löytynyt" ?></title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <main class="tapahtuma-sivu">
            <a class="takaisin-tapahtumiin" href="index.php">← Takaisin tapahtumiin</a>

            <?php if ($tapahtuma === null): ?>
                <section class="tapahtuma-yksityiskohdat">
                    <h1>Tapahtumaa ei löytynyt</h1>
                    <p>Tarkista tapahtuman linkki tai palaa tapahtumalistaan.</p>
                </section>
            <?php else: ?>
                <article class="tapahtuma-yksityiskohdat">
                    <div class="tapahtuma-yksityiskohta-kuva">
                        <?php if ($kuvaOlemassa): ?>
                            <img
                                src="<?= escapeHtml($kuva) ?>"
                                alt="<?= escapeHtml($tapahtuma["nimi"] ?? "Tapahtumakuva") ?>"
                            >
                        <?php endif; ?>
                    </div>

                    <div class="tapahtuma-yksityiskohta-sisalto">
                        <h1><?= escapeHtml($tapahtuma["nimi"] ?? "Nimetön tapahtuma") ?></h1>
                        <div class="tapahtuma-yhteenveto">
                            <span class="tapahtumakategoria"><?= escapeHtml($tapahtuma["kategoria"] ?? "") ?></span>
                            <span>⌖ <?= escapeHtml($tapahtuma["paikkakunta"] ?? "") ?></span>
                            <span>♧ <?= escapeHtml($tapahtuma["osallistujat"] ?? 0) ?> osallistujaa</span>
                        </div>
                        <p class="tapahtuma-kuvaus">
                            <?= escapeHtml($tapahtuma["tarkemmat tiedot"] ?? "Tapahtumasta ei ole lisätietoja.") ?>
                        </p>

                        <dl class="tapahtuma-tiedot">
                            <div>
                                <dt>Tapahtuman tunniste</dt>
                                <dd><?= escapeHtml($tapahtuma["id"] ?? "") ?></dd>
                            </div>
                            <div>
                                <dt>Kategoria</dt>
                                <dd><?= escapeHtml($tapahtuma["kategoria"] ?? "") ?></dd>
                            </div>
                            <div>
                                <dt>Paikkakunta</dt>
                                <dd><?= escapeHtml($tapahtuma["paikkakunta"] ?? "") ?></dd>
                            </div>
                            <div>
                                <dt>Osallistujia</dt>
                                <dd><?= escapeHtml($tapahtuma["osallistujat"] ?? 0) ?></dd>
                            </div>
                        </dl>
                    </div>
                </article>
            <?php endif; ?>
        </main>
    </body>
</html>