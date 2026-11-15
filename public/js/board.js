// Drag-and-drop for the project board (mouse). On touch screens each
// card shows a "Move to" dropdown instead, which works without this file.
// "Copy link" button in the Invite box.
document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
        const input = document.querySelector(button.dataset.copy);
        if (!input) return;
        input.select();
        try {
            await navigator.clipboard.writeText(input.value);
            button.textContent = 'Copied';
        } catch (error) {
            button.textContent = 'Press Ctrl+C to copy';
        }
    });
});

(function () {
    const board = document.querySelector('[data-board]');
    if (!board) return;

    const token = document.querySelector('meta[name="csrf-token"]').content;
    let dragged = null;

    const refreshCounts = () => {
        board.querySelectorAll('.column').forEach((column) => {
            column.querySelector('[data-count]').textContent =
                column.querySelectorAll('.task').length;
        });
    };

    const place = (card, zone, status) => {
        zone.appendChild(card);
        card.classList.toggle('is-done', status === 'done');
        const select = card.querySelector('select[name="status"]');
        if (select) select.value = status;
        refreshCounts();
    };

    board.addEventListener('dragstart', (event) => {
        const card = event.target.closest('.task');
        if (!card) return;
        dragged = card;
        card.classList.add('dragging');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', card.dataset.url);
    });

    board.addEventListener('dragend', () => {
        if (dragged) dragged.classList.remove('dragging');
        dragged = null;
        board.querySelectorAll('.column.over').forEach((c) => c.classList.remove('over'));
    });

    board.querySelectorAll('.column').forEach((column) => {
        const zone = column.querySelector('[data-dropzone]');

        column.addEventListener('dragover', (event) => {
            if (!dragged) return;
            event.preventDefault();
            column.classList.add('over');
        });

        column.addEventListener('dragleave', (event) => {
            if (!column.contains(event.relatedTarget)) column.classList.remove('over');
        });

        column.addEventListener('drop', async (event) => {
            if (!dragged) return;
            event.preventDefault();
            column.classList.remove('over');

            const card = dragged;
            const origin = card.parentElement;
            if (origin === zone) return;

            const previous = origin.closest('.column').dataset.status;
            const status = column.dataset.status;
            place(card, zone, status);

            try {
                const response = await fetch(card.dataset.url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ status }),
                });
                if (!response.ok) throw new Error('Request failed');
            } catch (error) {
                // Put the card back if the save did not go through.
                place(card, origin, previous);
                window.location.reload();
            }
        });
    });
})();
