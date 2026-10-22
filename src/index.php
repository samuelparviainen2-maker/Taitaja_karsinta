<?php
function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function tapahtumaSisaltaaHakusanan(array $tapahtuma, string $hakusana): bool
{
    $haettavatKentat = ["nimi", "kategoria", "paikkakunta", "tarkemmat tiedot"];

    foreach ($haettavatKentat as $kentta) {
        $arvo = (string) ($tapahtuma[$kentta] ?? "");
        if (preg_match("~" . preg_quote($hakusana, "~") . "~iu", $arvo) === 1) {
            return true;
        }
    }

    return false;
}

$tapahtumaJson = file_get_contents(__DIR__ . "/tapahtumat.json");
if ($tapahtumaJson === false) {
    throw new RuntimeException("Tapahtumatiedoston lukeminen epäonnistui.");
}

$tapahtumat = json_decode($tapahtumaJson, true, 512, JSON_THROW_ON_ERROR);
if (!is_array($tapahtumat)) {
    throw new UnexpectedValueException("Tapahtumatiedoston sisällön pitää olla lista.");
}

$tapahtumaMaara = count($tapahtumat);
$osallistujaMaara = 0;
$kategoriat = [];

foreach ($tapahtumat as $tapahtuma) {
    $osallistujaMaara += (int) ($tapahtuma["osallistujat"] ?? 0);

    $kategoria = trim((string) ($tapahtuma["kategoria"] ?? ""));
    if ($kategoria !== "" && !in_array($kategoria, $kategoriat, true)) {
        $kategoriat[] = $kategoria;
    }
}

$hakusana = isset($_GET["haku"]) && is_string($_GET["haku"])
    ? trim($_GET["haku"])
    : "";

$valittuKategoria = isset($_GET["kategoria"]) && is_string($_GET["kategoria"])
    ? $_GET["kategoria"]
    : "kaikki";

if ($valittuKategoria !== "kaikki" && !in_array($valittuKategoria, $kategoriat, true)) {
    $valittuKategoria = "kaikki";
}

$lajittelu = isset($_GET["lajittelu"]) && is_string($_GET["lajittelu"])
    ? $_GET["lajittelu"]
    : "nimi-az";

if (!in_array($lajittelu, ["nimi-az", "nimi-za"], true)) {
    $lajittelu = "nimi-az";
}

$naytettavatTapahtumat = [];
foreach ($tapahtumat as $tapahtuma) {
    $sopiiKategoriaan = $valittuKategoria === "kaikki"
        || ($tapahtuma["kategoria"] ?? "") === $valittuKategoria;
    $sopiiHakusanaan = $hakusana === ""
        || tapahtumaSisaltaaHakusanan($tapahtuma, $hakusana);

    if ($sopiiKategoriaan && $sopiiHakusanaan) {
        $naytettavatTapahtumat[] = $tapahtuma;
    }
}

usort($naytettavatTapahtumat, static function ($ensimmainen, $toinen) use ($lajittelu) {
    $vertailu = strnatcasecmp(
        (string) ($ensimmainen["nimi"] ?? ""),
        (string) ($toinen["nimi"] ?? "")
    );

    return $lajittelu === "nimi-za" ? -$vertailu : $vertailu;
});
?>
<!DOCTYPE html>
<html lang="fi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Tapahtumakalenteri</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="sivu">
            <?php include __DIR__ . "/header.php"; ?>

            <section class="otsikko">
                <h1>Tapahtumakalenteri</h1>
                <h2>Löydä tulevat tapahtumat ympäri Suomea.</h2>
                <h2 class="sharp-box">Tutustu tapahtumiin</h2>
            </section>

            <section class="haku" aria-label="Tapahtumahaku">
                <form action="index.php" method="get">
                    <div class="hakukentta">
                        <label for="haku">Haku:</label>
                        <input
                            type="search"
                            id="haku"
                            name="haku"
                            placeholder="Hae tapahtumia..."
                            class="search"
                            value="<?= escapeHtml($hakusana) ?>"
                        >
                        <button type="submit" class="hae" aria-label="Hae"></button>
                    </div>

                    <div class="hakusuodatin">
                        <label for="kategoria">Kategoria:</label>
                        <select id="kategoria" name="kategoria">
                            <option value="kaikki" <?= $valittuKategoria === "kaikki" ? "selected" : "" ?>>
                                Kaikki
                            </option>
                            <?php foreach ($kategoriat as $kategoria): ?>
                                <option
                                    value="<?= escapeHtml($kategoria) ?>"
                                    <?= $valittuKategoria === $kategoria ? "selected" : "" ?>
                                >
                                    <?= escapeHtml($kategoria) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hakusuodatin">
                        <label for="lajittelu">Lajittelu:</label>
                        <select id="lajittelu" name="lajittelu">
                            <option value="nimi-az" <?= $lajittelu === "nimi-az" ? "selected" : "" ?>>
                                Nimi A–Ö
                            </option>
                            <option value="nimi-za" <?= $lajittelu === "nimi-za" ? "selected" : "" ?>>
                                Nimi Ö–A
                            </option>
                        </select>
                    </div>
                </form>
            </section>

            <section class="tapahtumat" id="tapahtumalista" aria-label="Tapahtumat">
                <?php foreach ($naytettavatTapahtumat as $tapahtuma): ?>
                    <?php include __DIR__ . "/tapahtumakortti.php"; ?>
                <?php endforeach; ?>
            </section>

            <?php if (count($naytettavatTapahtumat) === 0): ?>
                <p class="ei-tuloksia">Hakuehdoilla ei löytynyt tapahtumia.</p>
            <?php endif; ?>

            <section class="maara" aria-label="Tapahtumien yhteenveto">
                <div class="tilasto">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" />
                        <path
                            d="M16 3v4M8 3v4M3 10h18"
                            stroke="currentColor"
                            stroke-linecap="round"
                        />
                    </svg>
                    <div>
                        <span>Tapahtumia yhteensä</span>
                        <strong><?= $tapahtumaMaara ?></strong>
                    </div>
                </div>

                <div class="tilasto">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                        <circle cx="9" cy="8" r="4" stroke="currentColor" />
                        <path
                            d="M2 21v-2a7 7 0 0 1 14 0v2H2ZM17 4.5a4 4 0 0 1 0 7.8M19 14a5 5 0 0 1 3 4.6V21h-4"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <div>
                        <span>Osallistujia yhteensä</span>
                        <strong><?= number_format($osallistujaMaara, 0, ",", " ") ?></strong>
                    </div>
                </div>
            </section>
        </div>

        <script src="suosikit.js" defer></script>
    </body>
</html>