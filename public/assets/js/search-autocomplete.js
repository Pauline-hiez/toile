(function () {
    function debounce(fn, delay) {
        let timer;
        return function () {
            clearTimeout(timer);
            const args = arguments;
            const context = this;
            timer = setTimeout(function () {
                fn.apply(context, args);
            }, delay);
        };
    }

    function closeDropdown(dropdown) {
        dropdown.classList.remove('is-open');
        dropdown.innerHTML = '';
    }

    function selectSuggestion(input, dropdown, label) {
        input.value = label;
        closeDropdown(dropdown);
        if (input.form) {
            input.form.submit();
        }
    }

    function setActive(items, index) {
        items.forEach(function (item, i) {
            item.classList.toggle('is-active', i === index);
        });
    }

    function renderSuggestions(input, dropdown, suggestions) {
        dropdown.innerHTML = '';

        if (!suggestions.length) {
            closeDropdown(dropdown);
            return;
        }

        suggestions.forEach(function (suggestion) {
            const label = suggestion.label;

            const item = document.createElement('li');
            item.className = 'search-bar__suggestion';
            item.setAttribute('role', 'option');
            item.dataset.label = label;

            if (suggestion.image) {
                const thumb = document.createElement('img');
                thumb.className = 'search-bar__suggestion-thumb';
                thumb.src = suggestion.image;
                thumb.alt = '';
                item.appendChild(thumb);
            }

            const text = document.createElement('span');
            text.textContent = label;
            item.appendChild(text);

            // mousedown (pas click) : se déclenche avant le blur de l'input,
            // sinon la fermeture au blur cacherait la liste avant le clic.
            item.addEventListener('mousedown', function (e) {
                e.preventDefault();
                selectSuggestion(input, dropdown, label);
            });
            dropdown.appendChild(item);
        });

        dropdown.classList.add('is-open');
    }

    // La liste est ajoutée à <body> (pas dans le DOM local du champ) et
    // positionnée en `fixed` d'après les coordonnées réelles du champ à
    // l'écran : certains conteneurs (cartes de tableau admin en
    // "overflow: hidden" pour arrondir les coins) couperaient sinon la
    // liste, même avec un z-index élevé — l'overflow d'un ancêtre prime
    // toujours sur le z-index d'un descendant en position absolute.
    function positionDropdown(input, dropdown) {
        const rect = input.getBoundingClientRect();
        dropdown.style.left = rect.left + 'px';
        dropdown.style.top = (rect.bottom + 6) + 'px';
        dropdown.style.width = Math.max(rect.width, 220) + 'px';
    }

    function setup(input) {
        const url = input.getAttribute('data-autocomplete');
        if (!url) {
            return;
        }

        const dropdown = document.createElement('ul');
        dropdown.className = 'search-bar__suggestions';
        document.body.appendChild(dropdown);

        let activeIndex = -1;

        const fetchSuggestions = debounce(function () {
            const q = input.value.trim();

            if (q.length < 2) {
                closeDropdown(dropdown);
                return;
            }

            fetch(url + '?q=' + encodeURIComponent(q))
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    activeIndex = -1;
                    positionDropdown(input, dropdown);
                    renderSuggestions(input, dropdown, data.suggestions || []);
                })
                .catch(function () {
                    closeDropdown(dropdown);
                });
        }, 250);

        input.addEventListener('input', fetchSuggestions);

        input.addEventListener('keydown', function (e) {
            const items = dropdown.querySelectorAll('.search-bar__suggestion');
            if (!items.length) {
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                setActive(items, activeIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                setActive(items, activeIndex);
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                selectSuggestion(input, dropdown, items[activeIndex].dataset.label);
            } else if (e.key === 'Escape') {
                closeDropdown(dropdown);
            }
        });

        input.addEventListener('blur', function () {
            // Délai pour laisser le mousedown d'une suggestion s'exécuter.
            setTimeout(function () {
                closeDropdown(dropdown);
            }, 150);
        });

        // La liste étant en position fixed (hors du flux normal), elle doit
        // suivre le champ si la page défile ou si la fenêtre est redimensionnée.
        window.addEventListener('scroll', function () {
            if (dropdown.classList.contains('is-open')) {
                positionDropdown(input, dropdown);
            }
        }, true);

        window.addEventListener('resize', function () {
            if (dropdown.classList.contains('is-open')) {
                positionDropdown(input, dropdown);
            }
        });
    }

    // Sur écran tactile, la barre reste repliée (largeur 0, dépliée au
    // survol via CSS) et seul le bouton loupe est cliquable : un premier
    // tap soumettrait donc un formulaire vide sans jamais laisser saisir.
    // Quand la barre est repliée (offsetWidth 0), on redirige le tap vers
    // le focus du champ — le `:focus-within` du CSS le déplie — au lieu de
    // soumettre. Une fois dépliée, le tap suivant soumet normalement.
    function setupTouchExpand(bar) {
        var input = bar.querySelector('input[type="text"]');
        var submit = bar.querySelector('.search-bar__submit');
        if (!input || !submit) {
            return;
        }

        submit.addEventListener('click', function (e) {
            if (input.offsetWidth === 0) {
                e.preventDefault();
                input.focus();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[data-autocomplete]').forEach(setup);
        document.querySelectorAll('.search-bar').forEach(setupTouchExpand);
    });
})();
