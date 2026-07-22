/**
 * Petite salve de particules qui jaillit du contour d'un champ quand il
 * reçoit le focus — adapté d'un effet "étincelles à la frappe" trouvé en
 * ligne (CodePen), mais déclenché une fois à la sélection du champ plutôt
 * qu'à chaque touche tapée. Un canvas par champ, ajouté dynamiquement.
 */
(function () {
    var SPARK_COLOR = '122, 158, 126'; // primary (vert), en RGB pour rgba()
    var MAX_LIFE = 32;
    var PADDING = 40; // marge du canvas autour du champ, pour laisser les particules déborder

    function initSparks(input) {
        var wrapper = input.parentElement;
        wrapper.style.position = 'relative';

        var canvas = document.createElement('canvas');
        canvas.className = 'pointer-events-none absolute';
        wrapper.insertBefore(canvas, input);

        var ctx = canvas.getContext('2d');
        var particles = [];
        var rafId = null;

        function resize() {
            var rect = input.getBoundingClientRect();
            var wrapperRect = wrapper.getBoundingClientRect();

            // Décalage réel de l'input dans son parent (le parent peut
            // avoir du padding, ou l'input n'être qu'un des éléments d'une
            // rangée flex) — on ne peut pas supposer que l'input est collé
            // à l'origine du wrapper.
            var offsetX = rect.left - wrapperRect.left;
            var offsetY = rect.top - wrapperRect.top;

            canvas.style.left = (offsetX - PADDING) + 'px';
            canvas.style.top = (offsetY - PADDING) + 'px';
            canvas.width = rect.width + PADDING * 2;
            canvas.height = rect.height + PADDING * 2;
        }

        function addAlongEdge(edge, count, w, h) {
            for (var i = 0; i < count; i++) {
                var x, y;
                if (edge === 'top') { x = PADDING + Math.random() * w; y = PADDING; }
                else if (edge === 'bottom') { x = PADDING + Math.random() * w; y = PADDING + h; }
                else if (edge === 'left') { x = PADDING; y = PADDING + Math.random() * h; }
                else { x = PADDING + w; y = PADDING + Math.random() * h; }

                particles.push({
                    x: x,
                    y: y,
                    vx: (Math.random() - 0.5) * 0.8,
                    vy: (Math.random() - 0.5) * 0.8 - 0.15,
                    size: 1 + Math.random() * 1.6,
                    life: 0,
                    maxLife: MAX_LIFE * (0.7 + Math.random() * 0.6),
                });
            }
        }

        function burst() {
            resize();
            var w = input.getBoundingClientRect().width;
            var h = input.getBoundingClientRect().height;

            addAlongEdge('top', 14, w, h);
            addAlongEdge('bottom', 14, w, h);
            addAlongEdge('left', 8, w, h);
            addAlongEdge('right', 8, w, h);

            if (!rafId) {
                loop();
            }
        }

        function loop() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            particles.forEach(function (p) {
                p.life++;
                p.x += p.vx;
                p.y += p.vy;

                var o = Math.max(0, 1 - p.life / p.maxLife);
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(' + SPARK_COLOR + ',' + o + ')';
                ctx.fill();
            });

            particles = particles.filter(function (p) { return p.life < p.maxLife; });

            if (particles.length > 0) {
                rafId = requestAnimationFrame(loop);
            } else {
                rafId = null;
            }
        }

        input.addEventListener('focus', burst);
        window.addEventListener('resize', resize);
        resize();
    }

    document.querySelectorAll('.js-spark-input').forEach(initSparks);
})();
