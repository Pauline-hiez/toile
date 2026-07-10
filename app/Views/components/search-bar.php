<?php
/**
 * Barre de recherche réutilisable sur tout le site (admin ET pages
 * publiques) : icône seule au repos, s'étend au survol/focus. Simple
 * include (pas de scope isolé) : variables attendues dans le scope
 * appelant.
 *
 * @var string $searchAction        URL du formulaire, ex: '/boutiques'
 * @var string|null $searchName     nom du champ (défaut 'q')
 * @var string|null $searchValue    valeur actuelle du champ
 * @var string|null $searchPlaceholder
 * @var bool|null $searchStandalone Si false, rend un <div> au lieu d'un
 *                                  <form> (pour s'insérer dans un
 *                                  formulaire englobant, ex: filtres
 *                                  de tableau avec d'autres champs).
 */
$searchName = $searchName ?? 'q';
$searchValue = $searchValue ?? '';
$searchPlaceholder = $searchPlaceholder ?? 'Rechercher...';
$searchStandalone = $searchStandalone ?? true;
$formTag = $searchStandalone ? 'form' : 'div';
?>
<div class="search-bar">
    <<?= $formTag ?><?= $searchStandalone ? ' action="' . htmlspecialchars($searchAction) . '" method="GET"' : '' ?> class="search-bar__form">
        <input type="text" name="<?= htmlspecialchars($searchName) ?>" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" value="<?= htmlspecialchars($searchValue) ?>">
        <button type="submit" class="search-bar__submit" aria-label="Rechercher" title="Rechercher">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>
    </<?= $formTag ?>>
</div>
