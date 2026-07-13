(function () {
    'use strict';

    const VIEW_WIDTH = 700;
    const VIEW_HEIGHT = 260;
    const PADDING = { top: 16, right: 12, bottom: 28, left: 34 };

    function renderChart(container) {
        let labels, values;
        try {
            labels = JSON.parse(container.dataset.labels || '[]');
            values = JSON.parse(container.dataset.values || '[]');
        } catch (e) {
            return;
        }
        if (!labels.length || labels.length !== values.length) return;

        const suffix = container.dataset.suffix || '';
        const plotWidth = VIEW_WIDTH - PADDING.left - PADDING.right;
        const plotHeight = VIEW_HEIGHT - PADDING.top - PADDING.bottom;

        const stepCount = 4;
        const maxValue = Math.max(1, ...values);
        const niceMax = Math.ceil(maxValue / stepCount) * stepCount || stepCount;

        const xFor = (i) => PADDING.left + (i / (labels.length - 1 || 1)) * plotWidth;
        const yFor = (v) => PADDING.top + plotHeight - (v / niceMax) * plotHeight;
        const points = values.map((v, i) => [xFor(i), yFor(v)]);

        const linePath = smoothPath(points);
        const baseline = PADDING.top + plotHeight;
        const areaPath = `${linePath} L ${points[points.length - 1][0]} ${baseline} L ${points[0][0]} ${baseline} Z`;

        const gradientId = 'adminChartGradient-' + Math.random().toString(36).slice(2, 9);

        let gridLines = '';
        let yLabels = '';
        for (let s = 0; s <= stepCount; s++) {
            const val = (niceMax / stepCount) * s;
            const y = yFor(val);
            gridLines += `<line x1="${PADDING.left}" y1="${y}" x2="${VIEW_WIDTH - PADDING.right}" y2="${y}" class="admin-chart__grid" />`;
            yLabels += `<text x="${PADDING.left - 8}" y="${y + 4}" class="admin-chart__axis-label" text-anchor="end">${Math.round(val)}</text>`;
        }

        const labelEvery = Math.ceil(labels.length / 7);
        let xLabels = '';
        labels.forEach((label, i) => {
            if (i % labelEvery !== 0 && i !== labels.length - 1) return;
            xLabels += `<text x="${xFor(i)}" y="${VIEW_HEIGHT - 6}" class="admin-chart__axis-label" text-anchor="middle">${escapeHtml(label)}</text>`;
        });

        let dots = '';
        points.forEach(([x, y], i) => {
            dots += `<circle cx="${x}" cy="${y}" r="4" class="admin-chart__dot" tabindex="0" data-index="${i}" />`;
        });

        container.innerHTML = `
            <svg viewBox="0 0 ${VIEW_WIDTH} ${VIEW_HEIGHT}" class="admin-chart__svg" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="${gradientId}" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="var(--color-primary)" stop-opacity="0.25" />
                        <stop offset="100%" stop-color="var(--color-primary)" stop-opacity="0" />
                    </linearGradient>
                </defs>
                <g>${gridLines}</g>
                <path d="${areaPath}" fill="url(#${gradientId})" stroke="none" />
                <path d="${linePath}" fill="none" class="admin-chart__line" />
                <g>${dots}</g>
                <g>${yLabels}${xLabels}</g>
            </svg>
            <div class="admin-chart__tooltip" hidden></div>
        `;

        const svg = container.querySelector('.admin-chart__svg');
        const tooltip = container.querySelector('.admin-chart__tooltip');

        const showTooltip = (index) => {
            const rect = svg.getBoundingClientRect();
            const [px, py] = points[index];

            tooltip.textContent = `${labels[index]} — ${values[index]}${suffix}`;
            tooltip.style.left = `${(px / VIEW_WIDTH) * rect.width}px`;
            tooltip.style.top = `${(py / VIEW_HEIGHT) * rect.height}px`;
            tooltip.hidden = false;
        };

        const hideTooltip = () => {
            tooltip.hidden = true;
        };

        container.querySelectorAll('.admin-chart__dot').forEach((dot) => {
            const index = Number(dot.dataset.index);
            dot.addEventListener('mouseenter', () => showTooltip(index));
            dot.addEventListener('focus', () => showTooltip(index));
            dot.addEventListener('mouseleave', hideTooltip);
            dot.addEventListener('blur', hideTooltip);
        });
    }

    // Courbe lissée (Catmull-Rom convertie en courbes de Bézier cubiques).
    function smoothPath(points) {
        if (points.length < 2) {
            return points.length ? `M ${points[0][0]} ${points[0][1]}` : '';
        }

        let d = `M ${points[0][0]} ${points[0][1]}`;
        for (let i = 0; i < points.length - 1; i++) {
            const p0 = points[i === 0 ? i : i - 1];
            const p1 = points[i];
            const p2 = points[i + 1];
            const p3 = points[i + 2 < points.length ? i + 2 : i + 1];

            const cp1x = p1[0] + (p2[0] - p0[0]) / 6;
            const cp1y = p1[1] + (p2[1] - p0[1]) / 6;
            const cp2x = p2[0] - (p3[0] - p1[0]) / 6;
            const cp2y = p2[1] - (p3[1] - p1[1]) / 6;

            d += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${p2[0]} ${p2[1]}`;
        }
        return d;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-admin-chart]').forEach(renderChart);
    });
})();
