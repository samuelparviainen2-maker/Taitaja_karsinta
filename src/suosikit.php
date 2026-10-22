<?php
// Lataa tapahtumat samasta JSON-tiedostosta kuin etusivulla.
$json = file_get_contents(__DIR__ . "/tapahtumat.json");
if ($json === false) {
    throw new RuntimeException("Tapahtumatiedoston lukeminen epäonnistui.");
}

$tapahtumat = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
if (!is_array($tapahtumat)) {
    throw new UnexpectedValueException("Tapahtumatiedoston sisällön pitää olla lista.");
}

// Escapoi arvot ennen kuin ne tulostetaan sivun HTML:ään.
function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="fi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Suosikkitapahtumat</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body data-page="favorites">
        <div class="sivu">
            <?php include __DIR__ . "/header.php"; ?>

            <section class="suosikkilista">
                <h1>Suosikkitapahtumat</h1>
                <p id="tyhjat-suosikit" hidden>Et ole vielä lisännyt tapahtumia suosikkeihin.</p>
                <div class="tapahtumat" id="tapahtumalista">
                    <!-- JavaScript piilottaa tapahtumat, joita käyttäjä ei ole tykännyt. -->
                    <?php foreach ($tapahtumat as $tapahtuma): ?>
                        <?php include __DIR__ . "/tapahtumakortti.php"; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
        <script src="suosikit.js" defer></script>
    </body>
</html>