-- La sélection des styles affichés en page d'accueil est désormais gérée
-- de façon centralisée via le setting `homepage_styles` (liste ordonnée,
-- max 5), et non plus au coup par coup à l'approbation de chaque demande
-- (voir AdminController::updateHomepageStylesSettings()).
ALTER TABLE category_request
    DROP COLUMN show_on_homepage;
