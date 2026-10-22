// Tallenna suosikit selaimeen, jotta ne säilyvät sivun päivittämisen jälkeen.
const favoriteStorageKey = "tapahtumakalenteri-suosikit";
let storedEventIds;

try {
    storedEventIds = JSON.parse(localStorage.getItem(favoriteStorageKey) || "[]");
} catch (error) {
    console.error("Suosikkien lukeminen selaimen tallennustilasta epäonnistui.", error);
    storedEventIds = [];
}

const likedEventIds = new Set(
    Array.isArray(storedEventIds) ? storedEventIds.map(String) : []
);

// Hae sivun tapahtumat ja selvitä, ollaanko suosikkisivulla.
const eventList = document.getElementById("tapahtumalista");
const isFavoritesPage = document.body.dataset.page === "favorites";
const emptyFavoritesMessage = document.getElementById("tyhjat-suosikit");

// Päivitä korttien sydämet ja näytä suosikkisivulla vain tykätyt tapahtumat.
function updateFavorites() {
    const cards = eventList.getElementsByClassName("tapahtumakortti");

    for (let index = 0; index < cards.length; index++) {
        const card = cards[index];
        const eventId = card.dataset.eventId;
        const heartButton = card.getElementsByClassName("suosikki")[0];
        const heartImage = heartButton.getElementsByTagName("img")[0];
        const isLiked = likedEventIds.has(eventId);

        card.hidden = isFavoritesPage && !isLiked;

        if (isLiked) {
            heartImage.src = "heart-filled.svg";
            heartButton.ariaLabel = "Poista suosikeista";
        } else {
            heartImage.src = "heart-outline.svg";
            heartButton.ariaLabel = "Lisää suosikkeihin";
        }

        heartButton.ariaPressed = isLiked;
    }

    if (emptyFavoritesMessage !== null) {
        emptyFavoritesMessage.hidden = likedEventIds.size > 0;
    }
}

// Kun käyttäjä painaa sydäntä, lisää tai poista tapahtuma suosikeista.
const likeButtons = eventList.getElementsByClassName("suosikki");

for (let index = 0; index < likeButtons.length; index++) {
    const button = likeButtons[index];

    button.addEventListener("click", () => {
        const eventId = button.dataset.eventId;

        if (likedEventIds.has(eventId)) {
            likedEventIds.delete(eventId);
        } else {
            likedEventIds.add(eventId);
        }

        // Tallenna muuttunut suosikkilista ja päivitä sivu.
        localStorage.setItem(favoriteStorageKey, JSON.stringify([...likedEventIds]));
        updateFavorites();
    });
}

// Näytä tallennetut suosikit heti sivun latautuessa.
updateFavorites();