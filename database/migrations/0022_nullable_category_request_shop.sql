-- L'admin peut désormais soumettre directement un nouveau style/type
-- (auto-approuvé, sans passer par la file de validation) depuis
-- /admin/settings — ces entrées n'ont pas de boutique d'origine.
ALTER TABLE category_request
    MODIFY COLUMN shop_id INT NULL;
