/**
 * Copyright (c) 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

const bootstrap = require('bootstrap');

const AJAX_HEADERS = { 'X-Requested-With': 'XMLHttpRequest' };
const AI_INTERACTION_LOG_STATS_BASE_PATH = '/ai-foundation/ai-interaction-log/stats';
const AI_INTERACTION_LOG_DETAIL_BASE_PATH = '/ai-foundation/ai-interaction-log/detail';
let failedToLoadMessage = '';

async function fetchHtml(url) {
    const response = await fetch(url, { headers: AJAX_HEADERS });

    if (!response.ok) {
        throw new Error(`HTTP error: ${response.status}`);
    }

    return response.text();
}

function sanitizeHTML(dirty) {
    const template = document.createElement('template');
    template.innerHTML = dirty;

    const forbidden = ['script', 'iframe', 'object', 'embed', 'style', 'link'];

    const walker = document.createTreeWalker(template.content, NodeFilter.SHOW_ELEMENT, null, false);

    const toRemove = [];

    while (walker.nextNode()) {
        const el = walker.currentNode;

        if (forbidden.includes(el.tagName.toLowerCase())) {
            toRemove.push(el);
            continue;
        }

        [...el.attributes].forEach((attr) => {
            if (attr.name.startsWith('on') || attr.value.toLowerCase().includes('javascript:')) {
                el.removeAttribute(attr.name);
            }
        });
    }

    toRemove.forEach((el) => el.remove());
    return template.innerHTML;
}

async function loadStats() {
    const filterParams = new URLSearchParams(window.location.search).toString();
    const statsCards = document.getElementById('stats-cards');

    try {
        statsCards.innerHTML = sanitizeHTML(await fetchHtml(`${AI_INTERACTION_LOG_STATS_BASE_PATH}?${filterParams}`));
    } catch (error) {
        console.error(failedToLoadMessage, error);
    }
}

function renderErrorMessage(content) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.setAttribute('role', 'alert');
    errorDiv.textContent = failedToLoadMessage;
    content.replaceChildren(errorDiv);
}

async function loadDetail(logId, content) {
    try {
        content.innerHTML = sanitizeHTML(
            await fetchHtml(`${AI_INTERACTION_LOG_DETAIL_BASE_PATH}?id=${encodeURIComponent(logId)}`),
        );
    } catch (error) {
        renderErrorMessage(content);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadStats();

    const drawer = document.getElementById('detail-drawer');
    const content = document.getElementById('drawer-content');
    const loadingText = content.getAttribute('data-loading-text') || 'Loading...';
    failedToLoadMessage = drawer.dataset.failedMessage;

    document.addEventListener('click', async (event) => {
        const row = event.target.closest('table.dataTable tbody tr');
        if (!row) return;

        const logIdEl = row.querySelector('.js-log-id');
        if (!logIdEl) return;

        const logId = logIdEl.getAttribute('data-log-id');
        if (!logId || logId === '0') return;

        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'text-center text-muted detail-loading';
        loadingDiv.textContent = loadingText;
        content.replaceChildren(loadingDiv);

        const bsOffcanvas = new bootstrap.Offcanvas(drawer);
        bsOffcanvas.show();

        await loadDetail(logId, content);
    });
});
