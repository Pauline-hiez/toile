<?php
/**
 * Modale de signalement réutilisable (boutique / avis).
 * Un seul exemplaire par page : les boutons "Signaler" (data-report-trigger)
 * la remplissent et l'ouvrent via report-modal.js. Simple include, pas
 * de variable requise dans le scope appelant.
 */
?>
<dialog id="reportModal" class="report-modal">
    <form method="POST" action="/reports" class="report-modal__form">
        <button type="button" class="report-modal__close" data-report-close aria-label="Fermer">&times;</button>
        <h2>Signaler un contenu</h2>
        <p class="report-modal__hint">Explique-nous le problème, notre équipe va l'examiner.</p>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
        <input type="hidden" name="reportable_type" data-report-field="type">
        <input type="hidden" name="reportable_id" data-report-field="id">
        <input type="hidden" name="redirect" data-report-field="redirect">

        <div>
            <label for="reportReason">Sujet</label>
            <select id="reportReason" name="reason" required>
                <?php foreach (\App\Models\Report::reasonLabels() as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="reportMessage">Explication</label>
            <textarea id="reportMessage" name="message" rows="4" placeholder="Décris ce qui pose problème..." required></textarea>
        </div>

        <button type="submit" class="btn btn--primary">Envoyer le signalement</button>
    </form>
</dialog>
