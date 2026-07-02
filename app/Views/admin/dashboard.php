<h1>Administration</h1>

<div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
    <div style="border: 1px solid #ccc; border-radius: 8px; padding: 1.5rem; min-width: 180px; text-align: center;">
        <div style="font-size: 2rem; font-weight: bold;"><?= $stats['total_users'] ?></div>
        <div>Utilisateurs</div>
    </div>

    <div style="border: 1px solid #ccc; border-radius: 8px; padding: 1.5rem; min-width: 180px; text-align: center;">
        <div style="font-size: 2rem; font-weight: bold;"><?= $stats['total_shops'] ?></div>
        <div>Boutiques</div>
    </div>

    <div style="border: 1px solid #ccc; border-radius: 8px; padding: 1.5rem; min-width: 180px; text-align: center;">
        <div style="font-size: 2rem; font-weight: bold;"><?= $stats['total_orders'] ?></div>
        <div>Commandes totales</div>
    </div>

    <div style="border: 1px solid #ccc; border-radius: 8px; padding: 1.5rem; min-width: 180px; text-align: center;">
        <div style="font-size: 2rem; font-weight: bold;"><?= $stats['active_orders'] ?></div>
        <div>Commandes en cours</div>
    </div>

    <div style="border: 1px solid #ccc; border-radius: 8px; padding: 1.5rem; min-width: 180px; text-align: center;">
        <div style="font-size: 2rem; font-weight: bold;"><?= number_format($stats['total_revenue'] / 100, 2) ?> €</div>
        <div>Volume capturé</div>
    </div>

    <?php if ($stats['pending_artist_requests'] > 0): ?>
        <div style="border: 2px solid #e53e3e; border-radius: 8px; padding: 1.5rem; min-width: 180px; text-align: center; background: #fff5f5;">
            <div style="font-size: 2rem; font-weight: bold; color: #e53e3e;"><?= $stats['pending_artist_requests'] ?></div>
            <div>Demandes artiste en attente</div>
            <a href="/admin/artist-requests" style="font-size: 0.85rem;">Traiter →</a>
        </div>
    <?php endif; ?>
</div>

<nav>
    <ul>
        <li><a href="/admin/artist-requests">Demandes artiste</a></li>
        <li><a href="/admin/users">Gestion des utilisateurs</a></li>
        <li><a href="/admin/shops">Modération des boutiques</a></li>
    </ul>
</nav>