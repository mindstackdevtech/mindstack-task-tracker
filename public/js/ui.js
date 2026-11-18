// Small interface helpers used on every page. Everything still works
// without this file; it only makes the app nicer to use.
(function () {
    'use strict';

    // ---- 1. Loading bar while the next page is on its way ----
    const bar = document.createElement('div');
    bar.className = 'load-bar';
    document.body.appendChild(bar);
    let barTimer = null;

    const startBar = () => {
        bar.classList.add('on');
        clearTimeout(barTimer);
        barTimer = setTimeout(stopBar, 8000); // never stays stuck
    };
    const stopBar = () => bar.classList.remove('on');

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented) return;
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.target === '_blank' || link.hasAttribute('download')) return;

        const href = link.getAttribute('href');
        if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
        if (link.origin !== window.location.origin) return;
        if (link.pathname.endsWith('/export')) return; // a download, not a new page
        if (link.pathname === window.location.pathname && link.hash) return;

        startBar();
    });

    // ---- 2. Stop double submits and show that something is happening ----
    document.addEventListener('submit', (event) => {
        // Cancelled (a "Are you sure?" answered No, or handled by other code).
        if (event.defaultPrevented) return;

        const form = event.target;
        if (form.dataset.sending === '1') {
            event.preventDefault();
            return;
        }
        form.dataset.sending = '1';
        startBar();

        const button = event.submitter || form.querySelector('button[type="submit"], button:not([type])');
        // Wait a tick so the browser collects the form first.
        setTimeout(() => {
            if (button) button.setAttribute('aria-busy', 'true');
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach((b) => { b.disabled = true; });
        }, 0);
    });

    // Coming back with the Back button: put forms and the bar back to normal.
    window.addEventListener('pageshow', () => {
        stopBar();
        document.querySelectorAll('form[data-sending]').forEach((form) => {
            delete form.dataset.sending;
            form.querySelectorAll('button').forEach((b) => {
                b.disabled = false;
                b.removeAttribute('aria-busy');
            });
        });
    });

    // ---- 3. Messages at the top: close button, and fade away after a while ----
    document.querySelectorAll('.flash').forEach((flash) => {
        const text = document.createElement('span');
        while (flash.firstChild) text.appendChild(flash.firstChild);
        flash.appendChild(text);

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'flash-close';
        close.setAttribute('aria-label', 'Dismiss message');
        close.textContent = '×';
        flash.appendChild(close);

        const dismiss = () => {
            flash.classList.add('hide');
            setTimeout(() => flash.remove(), 300);
        };
        close.addEventListener('click', dismiss);

        // Long messages stay longer. Hovering pauses the countdown.
        let timer = setTimeout(dismiss, Math.max(6000, text.textContent.length * 90));
        flash.addEventListener('mouseenter', () => clearTimeout(timer));
        flash.addEventListener('mouseleave', () => { timer = setTimeout(dismiss, 3000); });
    });

    // ---- 4. Show / hide on every password field ----
    document.querySelectorAll('input[type="password"]').forEach((input) => {
        const wrap = document.createElement('span');
        wrap.className = 'pw-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'pw-toggle';
        toggle.textContent = 'Show';
        toggle.setAttribute('aria-label', 'Show password');
        wrap.appendChild(toggle);

        toggle.addEventListener('click', (event) => {
            event.preventDefault(); // the field sits inside a <label>
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            toggle.textContent = showing ? 'Show' : 'Hide';
            toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            input.focus();
        });
    });

    // ---- 5. Text boxes grow as you type ----
    document.querySelectorAll('textarea').forEach((area) => {
        const fit = () => {
            area.style.height = 'auto';
            area.style.height = Math.min(area.scrollHeight + 2, 480) + 'px';
        };
        area.addEventListener('input', fit);
        fit();
    });

    // ---- 6. Press "/" to jump to the search box on the page ----
    document.addEventListener('keydown', (event) => {
        if (event.key !== '/' || event.ctrlKey || event.metaKey || event.altKey) return;
        const tag = (document.activeElement && document.activeElement.tagName) || '';
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;

        const search = document.querySelector('input[type="search"]');
        if (search) {
            event.preventDefault();
            search.focus();
        }
    });

    // ---- 7. Close the Invite box when clicking outside it or pressing Escape ----
    const closeOpenBoxes = (except) => {
        document.querySelectorAll('details.invite[open]').forEach((box) => {
            if (box !== except) box.removeAttribute('open');
        });
    };
    document.addEventListener('click', (event) => closeOpenBoxes(event.target.closest('details.invite')));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeOpenBoxes(null);
    });
})();
