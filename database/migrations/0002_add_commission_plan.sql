-- Ajoute le palier gratuit "Commission" comme un vrai subscription_plan
-- (comme Essentiel/Pro), au lieu d'être un simple cas par défaut géré en
-- dur dans le code. Backfill : chaque boutique existante sans abonnement
-- reçoit une ligne shop_subscription sur ce palier, avec une période qui
-- n'expire jamais (pas de facturation Stripe pour le gratuit).

INSERT INTO subscription_plan (name, price, commission_rate, max_services, max_portfolio_images, max_options_per_service, stripe_price_id)
SELECT 'Commission', 0, 10.00, 3, 5, 2, NULL
WHERE NOT EXISTS (SELECT 1 FROM subscription_plan WHERE name = 'Commission');

INSERT INTO shop_subscription (shop_id, plan_id, stripe_subscription_id, status, current_period_start, current_period_end)
SELECT
    shop.id,
    (SELECT id FROM subscription_plan WHERE name = 'Commission'),
    NULL,
    'active',
    shop.created_at,
    '2099-12-31 23:59:59'
FROM shop
WHERE shop.id NOT IN (SELECT shop_id FROM shop_subscription);
