<?php
/**
 * Variables injectées par App\Core\Renderer::render() via
 * extract($data) (voir ContactController::index()).
 *
 * Page de secours pour la dégradation sans JS de la modale globale
 * (voir components/contact-modal.php) — même formulaire, POST /contact.
 */
$currentUser = isset($_SESSION['user_id']) ? (new \App\Models\User())->findById($_SESSION['user_id']) : null;
?>

<div class="max-w-[500px] mx-auto px-5 py-8 min-[641px]:px-10 min-[641px]:py-10">
    <h1 class="font-title text-title text-shine text-[2rem] min-[641px]:text-[2.4rem] text-center mb-2">Nous contacter</h1>
    <p class="text-center text-muted text-[0.9rem] mb-8">Une question, une remarque ? Écris-nous, on te répond au plus vite.</p>

    <?php if (($_GET['contact'] ?? '') === 'sent'): ?>
        <div class="page-alert page-alert--success mb-6">Ton message a bien été envoyé, merci !</div>
    <?php elseif (($_GET['contact'] ?? '') === 'error'): ?>
        <div class="page-alert page-alert--warning mb-6">Merci de remplir tous les champs avec une adresse email valide.</div>
    <?php endif; ?>

    <div class="bg-white border border-border rounded-md shadow-sm p-6 min-[641px]:p-8">
        <form method="POST" action="/contact" class="flex flex-col gap-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
            <input type="hidden" name="redirect" value="/contact">

            <div>
                <label for="contactPageName" class="block font-semibold text-[0.85rem] text-ink mb-1">Nom</label>
                <input type="text" id="contactPageName" name="name" required value="<?= htmlspecialchars($currentUser['username'] ?? '') ?>"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
            </div>

            <div>
                <label for="contactPageEmail" class="block font-semibold text-[0.85rem] text-ink mb-1">Email</label>
                <input type="email" id="contactPageEmail" name="email" required value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
            </div>

            <div>
                <label for="contactPageSubject" class="block font-semibold text-[0.85rem] text-ink mb-1">Sujet</label>
                <input type="text" id="contactPageSubject" name="subject"
                    class="w-full border border-border rounded-full px-4 py-[0.4rem] bg-white font-main outline-none focus:border-primary">
            </div>

            <div>
                <label for="contactPageMessage" class="block font-semibold text-[0.85rem] text-ink mb-1">Message</label>
                <textarea id="contactPageMessage" name="message" rows="5" required
                    class="w-full border border-border rounded-md px-4 py-[0.6rem] bg-white font-main outline-none focus:border-primary resize-y"></textarea>
            </div>

            <div class="text-center mt-1">
                <button type="submit" class="btn btn--primary px-10">Envoyer</button>
            </div>
        </form>
    </div>
</div>
