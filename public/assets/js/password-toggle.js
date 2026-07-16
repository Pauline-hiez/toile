/**
 * Bouton "œil" affiché dans un champ mot de passe pour basculer entre
 * texte masqué/visible. Chaque bouton .js-toggle-password référence son
 * champ via data-target (id de l'input).
 */
(function () {
    document.querySelectorAll('.js-toggle-password').forEach(function (button) {
        var input = document.getElementById(button.dataset.target);
        var eyeIcon = button.querySelector('.js-icon-eye');
        var eyeOffIcon = button.querySelector('.js-icon-eye-off');
        if (!input) return;

        button.addEventListener('click', function () {
            var willShow = input.type === 'password';
            input.type = willShow ? 'text' : 'password';

            eyeIcon.classList.toggle('hidden', willShow);
            eyeOffIcon.classList.toggle('hidden', !willShow);
            button.setAttribute('aria-label', willShow ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    });
})();
