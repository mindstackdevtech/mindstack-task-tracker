// Team chat: sends messages without reloading the page, checks for new
// ones every few seconds, and offers member names when you type "@".
// Without JavaScript the form still works; the page just reloads.
(function () {
    const list = document.getElementById('chat-list');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    if (!list || !form || !input) return;

    const token = document.querySelector('meta[name="csrf-token"]').content;
    const empty = document.getElementById('chat-empty');
    let lastId = Number(list.dataset.last || 0);
    let busy = false;

    const el = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text; // textContent keeps text as plain text
        return node;
    };

    // ---------- Messages ----------
    const add = (message) => {
        if (message.id <= lastId) return;
        lastId = message.id;
        if (empty) empty.hidden = true;

        const row = el('div', 'msg' + (message.mine ? ' mine' : '') + (message.mentionsMe ? ' mentions-me' : ''));
        const avatar = el('span', 'avatar avatar-lg avatar-' + message.shade, message.initials);
        avatar.setAttribute('aria-hidden', 'true');

        const bubble = el('div', 'msg-bubble');
        const head = el('div', 'msg-head');
        head.append(el('strong', '', message.mine ? 'You' : message.name), el('span', 'muted small', message.time));

        // message.html is escaped on the server; only mention highlights are HTML.
        const body = el('div', 'msg-body');
        if (typeof message.html === 'string') body.innerHTML = message.html;
        else body.textContent = message.body;

        bubble.append(head, body);
        row.append(avatar, bubble);
        list.append(row);
        list.scrollTop = list.scrollHeight;
    };

    const poll = async () => {
        if (busy || document.hidden) return;
        busy = true;
        try {
            const response = await fetch(list.dataset.feed + '?after=' + lastId, {
                headers: { 'Accept': 'application/json' },
            });
            if (response.ok) {
                const data = await response.json();
                data.messages.forEach(add);
            }
        } catch (error) {
            // Offline or server stopped: try again on the next tick.
        } finally {
            busy = false;
        }
    };

    const showFailed = () => {
        if (document.getElementById('chat-error')) return;
        const note = el('small', 'error', 'Your message could not be sent. Check your connection and try again.');
        note.id = 'chat-error';
        form.after(note);
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = input.value.trim();
        if (!body) return;

        input.value = '';
        closeMentions();
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ body }),
            });
            if (!response.ok) throw new Error('Request failed');
            const data = await response.json();
            add(data.message);
            const old = document.getElementById('chat-error');
            if (old) old.remove();
        } catch (error) {
            input.value = body; // nothing typed is lost
            showFailed();
        }
        input.focus();
    });

    // ---------- @mentions ----------
    const menu = document.getElementById('mention-list');
    let members = [];
    try {
        members = JSON.parse(input.dataset.members || '[]');
    } catch (error) {
        members = [];
    }
    const choices = ['everyone'].concat(members);
    let matches = [];
    let active = 0;
    let tokenStart = -1;

    function closeMentions() {
        if (!menu) return;
        menu.hidden = true;
        menu.textContent = '';
        matches = [];
        tokenStart = -1;
        input.removeAttribute('aria-activedescendant');
    }

    const render = () => {
        menu.textContent = '';
        matches.forEach((name, index) => {
            const item = el('li', 'mention-item' + (index === active ? ' active' : ''), '@' + name);
            item.id = 'mention-' + index;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', index === active ? 'true' : 'false');
            // mousedown, not click, so the text box keeps its focus
            item.addEventListener('mousedown', (event) => {
                event.preventDefault();
                choose(index);
            });
            menu.append(item);
        });
        menu.hidden = matches.length === 0;
        if (matches.length) input.setAttribute('aria-activedescendant', 'mention-' + active);
    };

    const choose = (index) => {
        const name = matches[index];
        if (name === undefined || tokenStart < 0) return;

        const caret = input.selectionStart;
        const before = input.value.slice(0, tokenStart);
        const after = input.value.slice(caret);
        const inserted = '@' + name + ' ';

        input.value = before + inserted + after;
        const position = (before + inserted).length;
        input.setSelectionRange(position, position);
        closeMentions();
        input.focus();
    };

    // Looks at the text before the caret for an "@" that starts a word.
    const update = () => {
        if (!menu) return;
        const caret = input.selectionStart;
        const text = input.value.slice(0, caret);
        const found = text.match(/(^|\s)@([^@\n]{0,40})$/);

        if (!found) {
            closeMentions();
            return;
        }

        const query = found[2].toLowerCase();
        tokenStart = caret - found[2].length - 1;
        matches = choices.filter((name) => name.toLowerCase().startsWith(query)
            || name.toLowerCase().split(' ').some((part) => part.startsWith(query))).slice(0, 8);
        active = 0;
        render();
    };

    if (menu) {
        input.addEventListener('input', update);
        input.addEventListener('click', update);
        input.addEventListener('blur', () => setTimeout(closeMentions, 120));

        input.addEventListener('keydown', (event) => {
            if (menu.hidden || !matches.length) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                active = (active + 1) % matches.length;
                render();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                active = (active - 1 + matches.length) % matches.length;
                render();
            } else if (event.key === 'Enter' || event.key === 'Tab') {
                event.preventDefault(); // pick the name instead of sending
                choose(active);
            } else if (event.key === 'Escape') {
                event.preventDefault();
                closeMentions();
            }
        });
    }

    list.scrollTop = list.scrollHeight;
    setInterval(poll, 4000);
    document.addEventListener('visibilitychange', poll);
})();
