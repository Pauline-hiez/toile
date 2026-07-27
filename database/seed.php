<?php
/**
 * Peuplement de la base avec des données fictives pour la démo.
 *
 *   php database/seed.php
 *
 * ⚠️ Destructif : vide les utilisateurs et toutes les données liées
 * (boutiques, prestations, commandes, avis...) puis recrée un jeu complet.
 * Ne touche pas à subscription_plan ni setting (données de configuration).
 *
 * Tous les comptes ont le mot de passe : password123
 * Admin : pauline.hiez@laplateforme.io
 */

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Core\Database;

$pdo = Database::getInstance()->getConnection();
$hash = password_hash('password123', PASSWORD_DEFAULT);

function slugify(string $s): string
{
    $s = strtr($s, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'â' => 'a', 'ï' => 'i', 'î' => 'i', 'ô' => 'o', 'û' => 'u', 'ç' => 'c', "'" => ' ', '&' => ' ']);
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

function daysAgo(int $d): string
{
    return date('Y-m-d H:i:s', strtotime("-{$d} days"));
}

echo "== Nettoyage ==\n";
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ([
    'review', 'order_service_base', 'order_message', 'orders',
    'service_option', 'service_base', 'service', 'portfolio_image',
    'favorite', 'notification', 'shop_subscription', 'subscription_invoice',
    'raffle_entry', 'report', 'category_request', 'remember_token',
    'password_reset', 'email_log', 'invoice_counter', 'shop', 'users',
] as $t) {
    $pdo->exec("TRUNCATE TABLE {$t}");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

/* -------------------------------------------------------------------------
 * 1. Admin
 * ---------------------------------------------------------------------- */
$pdo->prepare(
    "INSERT INTO users (email, username, password_hash, role, avatar, email_verified_at, created_at)
     VALUES (:email, :username, :hash, 'admin', 'default.png', NOW(), NOW())"
)->execute([
    'email' => 'pauline.hiez@laplateforme.io',
    'username' => 'Pauline',
    'hash' => $hash,
]);
echo "Admin créé : pauline.hiez@laplateforme.io\n";

/* -------------------------------------------------------------------------
 * 2. Gabarits de prestations (réutilisés entre artistes)
 * ---------------------------------------------------------------------- */
// [titre, prix(centimes), délai(j), description, bases(catégorie=>labels), options([label,prix])]
$serviceTemplates = [
    ['Portrait numérique', 4500, 7, 'Un portrait numérique soigné à partir de ta photo ou de ta description.',
        ['Format' => ['A4', 'A5'], 'Cadrage' => ['Buste', 'Plein format']],
        [['Arrière-plan détaillé', 3000], ['Fichier haute résolution', 1500]]],
    ['Illustration de personnage', 8000, 10, 'Illustration complète de ton personnage, pose et couleurs comprises.',
        ['Format' => ['A4', 'A3'], 'Utilisation' => ['Personnel', 'Commercial']],
        [['Personnage supplémentaire', 5000], ['Fichier source (PSD)', 2500]]],
    ['Character design complet', 15000, 14, 'Conception complète d\'un personnage original avec fiche technique.',
        ['Vue' => ['Face', '3/4', 'Dos'], 'Niveau de détail' => ['Standard', 'Détaillé']],
        [['Turnaround complet', 6000], ['Palette alternative', 2000]]],
    ['Chibi mignon', 3000, 5, 'Un adorable chibi de ton personnage, parfait pour les avatars.',
        ['Style' => ['Chibi', 'Semi-chibi']],
        [['Accessoire', 1000], ['Mini décor', 1500]]],
    ['Concept d\'environnement', 12000, 12, 'Décor d\'ambiance pour ton univers, jeu ou récit.',
        ['Ambiance' => ['Jour', 'Nuit'], 'Format' => ['Paysage', 'Portrait']],
        [['Variante de cadrage', 4000], ['Fichier haute résolution', 2000]]],
    ['Design de tatouage', 6000, 8, 'Un design de tatouage sur-mesure, prêt à être encré.',
        ['Emplacement' => ['Bras', 'Dos', 'Jambe'], 'Style' => ['Fin', 'Ombré']],
        [['Version couleur', 2500]]],
    ['Bannière / header', 5000, 6, 'Une bannière percutante pour tes réseaux ou ta chaîne.',
        ['Plateforme' => ['Twitch', 'YouTube', 'Twitter']],
        [['Déclinaisons réseaux', 2000], ['Logo intégré', 3000]]],
    ['Sprite pixel art', 3500, 6, 'Un sprite pixel art propre, animé sur demande.',
        ['Taille' => ['16x16', '32x32', '64x64']],
        [['Animation idle', 4000], ['Palette custom', 1500]]],
];

/* -------------------------------------------------------------------------
 * 3. Artistes + boutiques + abonnements + prestations
 * ---------------------------------------------------------------------- */
// [username, email, boutique, styles, types, plan_id, bio]
$artists = [
    ['Léa Moreau', 'lea.moreau@demo.test', 'Studio Léa', ['réaliste'], ['portraitiste', 'illustrateur'], 2, 'Portraitiste passionnée, je capture les émotions au plus près du réel.'],
    ['Hugo Bernard', 'hugo.bernard@demo.test', 'Pixel Forge', ['pixel art'], ['illustrateur', 'character designer'], 1, 'Artiste pixel art rétro, je donne vie à tes personnages case par case.'],
    ['Manon Petit', 'manon.petit@demo.test', 'Chibi Corner', ['chibi', 'anime'], ['illustrateur'], 1, 'Univers kawaii et couleurs pastel : je dessine tout en mignon.'],
    ['Nathan Dubois', 'nathan.dubois@demo.test', 'Concept Lab', ['concept art'], ['concept artist', 'designer graphique'], 3, 'Concept artist pour jeux vidéo et films d\'animation.'],
    ['Camille Roux', 'camille.roux@demo.test', 'Encre & Réalisme', ['réaliste'], ['portraitiste', 'tatoueur'], 2, 'Du portrait réaliste au design de tatouage, l\'encre est mon terrain de jeu.'],
    ['Emma Laurent', 'emma.laurent@demo.test', 'Anime Dream', ['anime', 'chibi'], ['illustrateur', 'character designer'], 1, 'Fan d\'anime, je crée des personnages pleins de vie.'],
    ['Lucas Girard', 'lucas.girard@demo.test', 'Character Base', ['concept art', 'anime'], ['character designer'], 3, 'Spécialiste du character design, du croquis à la fiche finale.'],
    ['Chloé Fontaine', 'chloe.fontaine@demo.test', 'Atelier Chloé', ['réaliste'], ['illustrateur'], 1, 'Illustratrice indépendante, douce et minutieuse.'],
    ['Théo Mercier', 'theo.mercier@demo.test', 'Retro Pixels', ['pixel art'], ['designer graphique', 'illustrateur'], 2, 'Pixel art et identité visuelle rétro pour tes projets.'],
    ['Sarah Lopez', 'sarah.lopez@demo.test', 'Ink & Soul', ['réaliste', 'concept art'], ['tatoueur', 'illustrateur'], 1, 'Le tatouage comme récit : chaque pièce raconte une histoire.'],
];

$planCommission = [1 => 10.00, 2 => 5.00, 3 => 0.00];
$planMonetization = [1 => 'commission', 2 => 'subscription', 3 => 'subscription'];

$userStmt = $pdo->prepare(
    "INSERT INTO users (email, username, password_hash, role, avatar, bio, email_verified_at,
        artist_request_status, artist_display_name, created_at)
     VALUES (:email, :username, :hash, 'artist', 'default.png', :bio, NOW(), 'approved', :display, :created)"
);
$shopStmt = $pdo->prepare(
    "INSERT INTO shop (user_id, name, slug, bio, styles, types, banner, is_open, accepts_quotes,
        plan_selected, monetization_type, created_at)
     VALUES (:user_id, :name, :slug, :bio, :styles, :types, 'default.png', 1, 1, 1, :monet, :created)"
);
$subStmt = $pdo->prepare(
    "INSERT INTO shop_subscription (shop_id, plan_id, status, current_period_start, current_period_end, created_at)
     VALUES (:shop_id, :plan_id, 'active', :start, :end, :created)"
);
$serviceStmt = $pdo->prepare(
    "INSERT INTO service (shop_id, title, description, base_price, delivery_days, is_active, created_at)
     VALUES (:shop_id, :title, :desc, :price, :days, 1, :created)"
);
$baseStmt = $pdo->prepare(
    "INSERT INTO service_base (service_id, category, label, created_at)
     VALUES (:service_id, :category, :label, :created)"
);
$optionStmt = $pdo->prepare(
    "INSERT INTO service_option (service_id, label, extra_price) VALUES (:service_id, :label, :price)"
);

$shops = []; // [shop_id => ['plan_id','rate','services'=>[['id','price','title','bases'=>[cat=>[[base_id,label]]]]]]]

foreach ($artists as $i => $a) {
    [$username, $email, $shopName, $styles, $types, $planId, $bio] = $a;
    $createdArtist = daysAgo(200 - $i * 10);

    $userStmt->execute([
        'email' => $email, 'username' => $username, 'hash' => $hash,
        'bio' => $bio, 'display' => $username, 'created' => $createdArtist,
    ]);
    $userId = (int) $pdo->lastInsertId();

    $shopStmt->execute([
        'user_id' => $userId, 'name' => $shopName, 'slug' => slugify($shopName),
        'bio' => $bio,
        'styles' => json_encode($styles, JSON_UNESCAPED_UNICODE),
        'types' => json_encode($types, JSON_UNESCAPED_UNICODE),
        'monet' => $planMonetization[$planId], 'created' => $createdArtist,
    ]);
    $shopId = (int) $pdo->lastInsertId();

    $subStmt->execute([
        'shop_id' => $shopId, 'plan_id' => $planId,
        'start' => daysAgo(30), 'end' => date('Y-m-d H:i:s', strtotime('+1 month')),
        'created' => $createdArtist,
    ]);

    // 3 prestations par artiste (rotation des gabarits).
    $services = [];
    for ($k = 0; $k < 3; $k++) {
        $tpl = $serviceTemplates[($i + $k) % count($serviceTemplates)];
        [$title, $price, $days, $desc, $bases, $options] = $tpl;

        $serviceStmt->execute([
            'shop_id' => $shopId, 'title' => $title, 'desc' => $desc,
            'price' => $price, 'days' => $days, 'created' => $createdArtist,
        ]);
        $serviceId = (int) $pdo->lastInsertId();

        $serviceBases = [];
        foreach ($bases as $category => $labels) {
            foreach ($labels as $label) {
                $baseStmt->execute([
                    'service_id' => $serviceId, 'category' => $category,
                    'label' => $label, 'created' => $createdArtist,
                ]);
                $serviceBases[$category][] = [(int) $pdo->lastInsertId(), $label];
            }
        }
        foreach ($options as [$optLabel, $optPrice]) {
            $optionStmt->execute(['service_id' => $serviceId, 'label' => $optLabel, 'price' => $optPrice]);
        }

        $services[] = ['id' => $serviceId, 'price' => $price, 'title' => $title, 'bases' => $serviceBases];
    }

    $shops[$shopId] = ['plan_id' => $planId, 'rate' => $planCommission[$planId], 'services' => $services];
}
echo count($artists) . " artistes + boutiques + prestations créés\n";

/* -------------------------------------------------------------------------
 * 4. Clients
 * ---------------------------------------------------------------------- */
// [username, email, adresse, ville, cp]
$clientsData = [
    ['Julie Bernard', 'julie.bernard@demo.test', '12 rue des Lilas', 'Lyon', '69003'],
    ['Thomas Leroy', 'thomas.leroy@demo.test', '5 avenue Victor Hugo', 'Paris', '75016'],
    ['Camille Petit', 'camille.petit@demo.test', '8 boulevard Longchamp', 'Marseille', '13001'],
    ['Antoine Garnier', 'antoine.garnier@demo.test', '23 rue du Taur', 'Toulouse', '31000'],
    ['Sophie Martin', 'sophie.martin@demo.test', '3 quai de la Fosse', 'Nantes', '44000'],
];

$clientStmt = $pdo->prepare(
    "INSERT INTO users (email, username, password_hash, role, avatar, address_line1, city, postal_code, country, email_verified_at, created_at)
     VALUES (:email, :username, :hash, 'user', 'default.png', :addr, :city, :cp, 'France', NOW(), :created)"
);
$clients = [];
foreach ($clientsData as $i => $c) {
    [$username, $email, $addr, $city, $cp] = $c;
    $clientStmt->execute([
        'email' => $email, 'username' => $username, 'hash' => $hash,
        'addr' => $addr, 'city' => $city, 'cp' => $cp, 'created' => daysAgo(150 - $i * 10),
    ]);
    $clients[] = [
        'id' => (int) $pdo->lastInsertId(), 'name' => $username,
        'addr' => $addr, 'city' => $city, 'cp' => $cp,
    ];
}
echo count($clients) . " clients créés\n";

/* -------------------------------------------------------------------------
 * 5. Commandes (historique varié) + avis + factures
 * ---------------------------------------------------------------------- */
$orderStmt = $pdo->prepare(
    "INSERT INTO orders (client_id, shop_id, service_id, title, description, total_price,
        commission_rate, commission_amount, status, stripe_payment_intent_id,
        billing_name, billing_address_line1, billing_city, billing_postal_code, billing_country,
        shipping_name, shipping_address_line1, shipping_city, shipping_postal_code, shipping_country,
        invoice_number, invoiced_at, created_at)
     VALUES (:client_id, :shop_id, :service_id, :title, :description, :total,
        :rate, :commission, :status, :pi,
        :bname, :baddr, :bcity, :bcp, 'France',
        :sname, :saddr, :scity, :scp, :scountry,
        :invnum, :invat, :created)"
);
$orderBaseStmt = $pdo->prepare(
    "INSERT INTO order_service_base (order_id, service_base_id, category, label)
     VALUES (:order_id, :base_id, :category, :label)"
);
$reviewStmt = $pdo->prepare(
    "INSERT INTO review (order_id, rating, comment, created_at) VALUES (:order_id, :rating, :comment, :created)"
);

// Statuts qui correspondent à une commande payée (facture + commission).
$paidStatuses = ['accepted', 'in_progress', 'delivered', 'completed'];
// Motif de statuts appliqué par boutique (garantit de la variété partout).
$statusPattern = ['completed', 'completed', 'delivered', 'in_progress', 'accepted', 'pending', 'quote_requested', 'price_proposed', 'cancelled', 'rejected'];

$reviewComments = [
    'Travail magnifique, exactement ce que j\'imaginais !',
    'Très à l\'écoute et super réactif. Je recommande vivement.',
    'Un rendu au-delà de mes attentes, merci infiniment.',
    'Superbe qualité et livraison dans les temps.',
    'Parfait du début à la fin, je reviendrai !',
    'Belle prestation, quelques allers-retours mais résultat au top.',
];

$titlesByTemplate = [
    'Portrait de mon chat', 'Illustration de mon perso de JDR', 'Design de ma mascotte',
    'Chibi de mon couple', 'Décor pour ma nouvelle', 'Tatouage bras loup géométrique',
    'Bannière pour ma chaîne Twitch', 'Sprite pour mon jeu indé', 'Portrait de famille',
    'Fanart de mon OC', 'Logo illustré pour ma boutique', 'Emote pour mon Discord',
];

$invoiceSeq = 0;
$orderCount = 0;
$reviewCount = 0;
$statusIndex = 0;
$titleIndex = 0;

foreach ($shops as $shopId => $shop) {
    // 3 commandes par boutique.
    for ($n = 0; $n < 3; $n++) {
        $status = $statusPattern[$statusIndex % count($statusPattern)];
        $statusIndex++;

        $client = $clients[($orderCount) % count($clients)];
        $service = $shop['services'][$n % count($shop['services'])];

        $total = $service['price'];
        // 40 % des commandes prennent une option (prix arrondi symbolique).
        if ($orderCount % 5 < 2) {
            $total += 2500;
        }

        $isPaid = in_array($status, $paidStatuses, true);
        $rate = $isPaid ? $shop['rate'] : 0.00;
        $commission = $isPaid ? (int) round($total * $rate / 100) : 0;
        $pi = $isPaid ? 'pi_demo_' . bin2hex(random_bytes(8)) : null;

        $daysBack = 5 + ($orderCount * 4) % 110;
        $createdAt = daysAgo($daysBack);

        $invNum = null;
        $invAt = null;
        if ($isPaid) {
            $invoiceSeq++;
            $invNum = sprintf('FAC-2026-%05d', $invoiceSeq);
            $invAt = daysAgo(max(1, $daysBack - 2));
        }

        // Livraison physique pour ~1 commande sur 3 (copie de la facturation).
        $wantsShipping = ($orderCount % 3 === 0);

        $title = $titlesByTemplate[$titleIndex % count($titlesByTemplate)];
        $titleIndex++;

        $orderStmt->execute([
            'client_id' => $client['id'], 'shop_id' => $shopId, 'service_id' => $service['id'],
            'title' => $title,
            'description' => "Bonjour, je souhaiterais une {$service['title']} : " . strtolower($title) . '. Merci de me dire si c\'est possible !',
            'total' => $total, 'rate' => $rate, 'commission' => $commission,
            'status' => $status, 'pi' => $pi,
            'bname' => $client['name'], 'baddr' => $client['addr'], 'bcity' => $client['city'], 'bcp' => $client['cp'],
            'sname' => $wantsShipping ? $client['name'] : null,
            'saddr' => $wantsShipping ? $client['addr'] : null,
            'scity' => $wantsShipping ? $client['city'] : null,
            'scp' => $wantsShipping ? $client['cp'] : null,
            'scountry' => $wantsShipping ? 'France' : null,
            'invnum' => $invNum, 'invat' => $invAt, 'created' => $createdAt,
        ]);
        $orderId = (int) $pdo->lastInsertId();
        $orderCount++;

        // Snapshot des choix de base (un label par catégorie).
        foreach ($service['bases'] as $category => $entries) {
            [$baseId, $label] = $entries[0];
            $orderBaseStmt->execute([
                'order_id' => $orderId, 'base_id' => $baseId,
                'category' => $category, 'label' => $label,
            ]);
        }

        // Avis pour les commandes terminées.
        if ($status === 'completed') {
            $reviewStmt->execute([
                'order_id' => $orderId,
                'rating' => (($orderCount % 5) === 0) ? 4 : 5,
                'comment' => $reviewComments[$reviewCount % count($reviewComments)],
                'created' => date('Y-m-d H:i:s', strtotime($createdAt . ' +3 days')),
            ]);
            $reviewCount++;
        }
    }
}
echo "{$orderCount} commandes créées ({$reviewCount} avis, {$invoiceSeq} factures)\n";

/* -------------------------------------------------------------------------
 * 6. Compteur de factures (cohérent avec les numéros posés ci-dessus)
 * ---------------------------------------------------------------------- */
$pdo->prepare(
    "INSERT INTO invoice_counter (series, year, last_number) VALUES ('FAC', 2026, :n)
     ON DUPLICATE KEY UPDATE last_number = :n2"
)->execute(['n' => $invoiceSeq, 'n2' => $invoiceSeq]);

echo "\n== Terminé ==\n";
echo "Mot de passe de tous les comptes : password123\n";
