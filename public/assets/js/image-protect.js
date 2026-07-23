/**
 * Dissuasif contre la récupération des images (surtout les portfolios et
 * illustrations des artistes) : bloque le menu contextuel (clic droit ->
 * "Enregistrer l'image sous...") et le glisser-déposer sur les <img>.
 *
 * NB : ce n'est PAS une protection infranchissable — capture d'écran,
 * outils de développement ou onglet réseau permettent toujours de récupérer
 * une image. Le but est seulement d'empêcher la sauvegarde « au clic » par
 * un visiteur ordinaire.
 */
(function () {
    document.addEventListener('contextmenu', function (event) {
        if (event.target instanceof HTMLImageElement) {
            event.preventDefault();
        }
    });

    // Empêche le glissement d'une image vers le bureau / un autre onglet.
    document.addEventListener('dragstart', function (event) {
        if (event.target instanceof HTMLImageElement) {
            event.preventDefault();
        }
    });
})();
