<?php
// Valmistele kuvan tiedot ja tapahtuman tunniste korttia varten.
$kuva = $tapahtuma["kuva"] ?? "";
$kuvapolku = $kuva !== "" ? __DIR__ . "/" . $kuva : "";
$kuvaOlemassa = $kuvapolku !== "" && is_file($kuvapolku);
$id = $tapahtuma["id"] ?? "";
?>
<article class="tapahtumakortti" data-event-id="<?= escapeHtml($id) ?>">
    <div class="tapahtumakuva">
        <?php if ($kuvaOlemassa): ?>
            <img
                src="<?= escapeHtml($kuva) ?>"
                alt="<?= escapeHtml($tapahtuma["nimi"] ?? "Tapahtumakuva") ?>"
            >
        <?php endif; ?>

        <button
            class="suosikki"
            type="button"
            data-event-id="<?= escapeHtml($id) ?>"
            aria-label="Lisää suosikkeihin"
            aria-pressed="false"
        >
            <img src="heart-outline.svg" alt="">
        </button>
    </div>

    <div class="tapahtumasisalto">
        <h3><?= escapeHtml($tapahtuma["nimi"] ?? "Nimetön tapahtuma") ?></h3>
        <span class="tapahtumakategoria"><?= escapeHtml($tapahtuma["kategoria"] ?? "") ?></span>
        <p class="tapahtumatieto">
            <span aria-hidden="true">⌖</span>
            <?= escapeHtml($tapahtuma["paikkakunta"] ?? "") ?>
        </p>
        <p class="tapahtumatieto">
            <span aria-hidden="true">♧</span>
            <?= escapeHtml($tapahtuma["osallistujat"] ?? "") ?> osallistujaa
        </p>

        <a
            class="lisatiedot-linkki"
            href="tapahtuma.php?id=<?= rawurlencode((string) $id) ?>"
        >
            LISÄTIETOA
        </a>
    </div>
</article>
