/**
 * Copyright (c) 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

/**
 * Progressive-disclosure info popover for the AI interaction cost estimator.
 *
 * Each trigger is a `.js-ai-pricing-info` element holding a sibling
 * `<template class="js-ai-pricing-popover">` whose content is rendered inside the popover.
 *
 * Positioning is handled directly (anchored below the icon, flipped on viewport overflow)
 * instead of through Bootstrap/Popper, which fails to position a popover whose reference is
 * an inline icon inside a manually-triggered flow.
 */
export class AiPricingInfoPopover {
    static #HOVER_OPEN_DELAY_MS = 300;
    static #HOVER_CLOSE_DELAY_MS = 150;
    static #VIEWPORT_MARGIN_PX = 8;
    static #ANCHOR_OFFSET_PX = 8;

    static #selectors = {
        trigger: '.js-ai-pricing-info',
        template: '.js-ai-pricing-popover',
        panel: '.js-ai-pricing-panel',
    };

    static #panelClass = 'ai-pricing-popover';
    static #panelVisibleClass = 'ai-pricing-popover--visible';

    // All live popover controllers, so any of them can close the others.
    static #instances = new Set();

    #trigger;
    #panel;
    #isShown = false;
    #isPanelHovered = false;
    #hoverOpenTimeoutId = null;
    #hoverCloseTimeoutId = null;

    #onDocumentClick = (event) => this.#handleDocumentClick(event);
    #onDocumentKeydown = (event) => this.#handleDocumentKeydown(event);
    #onReposition = () => this.#position();

    constructor(trigger) {
        this.#trigger = trigger;
        this.#panel = this.#createPanel();
        this.#bindEvents();

        AiPricingInfoPopover.#instances.add(this);
    }

    /**
     * Initialises every trigger currently present in the document and disposes any
     * previously-initialised instances first, so it is safe to re-run after the
     * stats fragment is replaced via AJAX.
     */
    static initAll() {
        AiPricingInfoPopover.disposeAll();

        const triggers = document.querySelectorAll(AiPricingInfoPopover.#selectors.trigger);

        for (const trigger of triggers) {
            new AiPricingInfoPopover(trigger);
        }
    }

    static disposeAll() {
        for (const instance of [...AiPricingInfoPopover.#instances]) {
            instance.#dispose();
        }
    }

    #createPanel() {
        const template = this.#trigger.querySelector(AiPricingInfoPopover.#selectors.template);

        if (!template) {
            throw new Error('AiPricingInfoPopover: required template .js-ai-pricing-popover not found inside trigger');
        }

        const panel = document.createElement('div');
        panel.className =
            `${AiPricingInfoPopover.#panelClass} ${AiPricingInfoPopover.#selectors.panel.slice(1)} ${this.#trigger.dataset.popoverClass ?? ''}`.trim();
        panel.setAttribute('role', 'tooltip');
        panel.appendChild(template.content.cloneNode(true));

        return panel;
    }

    #bindEvents() {
        this.#trigger.addEventListener('click', (event) => {
            event.preventDefault();
            this.#toggle();
        });

        this.#trigger.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            this.#toggle();
        });

        this.#trigger.addEventListener('mouseenter', () => this.#scheduleHoverOpen());
        this.#trigger.addEventListener('mouseleave', () => this.#scheduleHoverClose());

        // Keep the popover open while the pointer is over its body, mirroring the icon hover.
        this.#panel.addEventListener('mouseenter', () => {
            this.#isPanelHovered = true;
            this.#cancelHoverClose();
        });
        this.#panel.addEventListener('mouseleave', () => {
            this.#isPanelHovered = false;
            this.#scheduleHoverClose();
        });

        this.#trigger.addEventListener('focusout', (event) => this.#handleFocusOut(event));
    }

    #scheduleHoverOpen() {
        this.#cancelHoverClose();

        if (this.#isShown) {
            return;
        }

        this.#hoverOpenTimeoutId = window.setTimeout(() => this.#show(), AiPricingInfoPopover.#HOVER_OPEN_DELAY_MS);
    }

    #scheduleHoverClose() {
        this.#cancelHoverOpen();

        this.#hoverCloseTimeoutId = window.setTimeout(() => {
            if (this.#isPanelHovered) {
                return;
            }

            this.#hide();
        }, AiPricingInfoPopover.#HOVER_CLOSE_DELAY_MS);
    }

    #cancelHoverOpen() {
        if (this.#hoverOpenTimeoutId === null) {
            return;
        }

        window.clearTimeout(this.#hoverOpenTimeoutId);
        this.#hoverOpenTimeoutId = null;
    }

    #cancelHoverClose() {
        if (this.#hoverCloseTimeoutId === null) {
            return;
        }

        window.clearTimeout(this.#hoverCloseTimeoutId);
        this.#hoverCloseTimeoutId = null;
    }

    #toggle() {
        if (this.#isShown) {
            this.#hide();

            return;
        }

        this.#show();
    }

    #show() {
        this.#cancelHoverOpen();

        if (this.#isShown) {
            return;
        }

        // Only one popover open at a time.
        for (const instance of AiPricingInfoPopover.#instances) {
            if (instance !== this) {
                instance.#hide();
            }
        }

        document.body.appendChild(this.#panel);
        this.#position();
        this.#panel.classList.add(AiPricingInfoPopover.#panelVisibleClass);
        this.#isShown = true;

        document.addEventListener('click', this.#onDocumentClick, true);
        document.addEventListener('keydown', this.#onDocumentKeydown);
        window.addEventListener('resize', this.#onReposition);
        window.addEventListener('scroll', this.#onReposition, true);
    }

    #hide() {
        this.#cancelHoverOpen();
        this.#cancelHoverClose();

        if (!this.#isShown) {
            return;
        }

        this.#panel.classList.remove(AiPricingInfoPopover.#panelVisibleClass);
        this.#panel.remove();
        this.#isShown = false;
        this.#isPanelHovered = false;

        document.removeEventListener('click', this.#onDocumentClick, true);
        document.removeEventListener('keydown', this.#onDocumentKeydown);
        window.removeEventListener('resize', this.#onReposition);
        window.removeEventListener('scroll', this.#onReposition, true);
    }

    // Anchors the panel to the bottom-left of the icon, flipping to the top/right edge on overflow.
    #position() {
        const triggerRect = this.#trigger.getBoundingClientRect();
        const panelRect = this.#panel.getBoundingClientRect();
        const margin = AiPricingInfoPopover.#VIEWPORT_MARGIN_PX;
        const offset = AiPricingInfoPopover.#ANCHOR_OFFSET_PX;

        let top = triggerRect.bottom + offset;
        if (top + panelRect.height > window.innerHeight - margin) {
            top = Math.max(margin, triggerRect.top - panelRect.height - offset);
        }

        let left = triggerRect.left;
        if (left + panelRect.width > window.innerWidth - margin) {
            left = Math.max(margin, window.innerWidth - panelRect.width - margin);
        }

        this.#panel.style.top = `${Math.round(top + window.scrollY)}px`;
        this.#panel.style.left = `${Math.round(left + window.scrollX)}px`;
    }

    #handleDocumentClick(event) {
        if (this.#trigger.contains(event.target) || this.#panel.contains(event.target)) {
            return;
        }

        this.#hide();
    }

    #handleDocumentKeydown(event) {
        if (event.key !== 'Escape') {
            return;
        }

        this.#hide();
        this.#trigger.focus();
    }

    #handleFocusOut(event) {
        const nextTarget = event.relatedTarget;

        if (!nextTarget) {
            return;
        }

        if (this.#trigger.contains(nextTarget) || this.#panel.contains(nextTarget)) {
            return;
        }

        this.#hide();
    }

    #dispose() {
        this.#hide();

        AiPricingInfoPopover.#instances.delete(this);
    }
}
