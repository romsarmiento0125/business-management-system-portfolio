    var base_url = $('#base_url').val();

    function showLoader() {
        $('#loader').show();
    }

    function hideLoader() {
        $('#loader').hide();
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(price);
    }

    function terms_set(client_term) {
    let term = '';
    switch (client_term) {
        case 'cod':
            term = 'COD';
            break;
        case '7':
            term = '7 Days';
            break;
        case '15':
            term = '15 Days';
            break;
        case '21':
            term = '21 Days';
            break;
        case '30':
            term = '30 Days';
            break;
        case '45':
            term = '45 Days';
            break;
        case '60':
            term = '60 Days';
            break;
        case 'flex':
            term = 'FLEX';
            break;
    }
    return term;
}

// =============================
// Global Keyboard Shortcuts
// =============================
// Arrow Up / Down: Smooth scroll (unless inside an input/textarea/select or modal focused field)
// Enter: If auth modal open, trigger its submit; if universal modal open, trigger confirm
// (Respects existing universal modal key handler if present; this acts as a fallback)

(function initGlobalShortcuts(){
    const SCROLL_STEP = 120; // px per key press

    function isTypingContext(target) {
        const tag = target.tagName;
        return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || target.isContentEditable;
    }

    $(document).on('keydown.globalShortcuts', function(e){
        const target = e.target;

        // Skip if user is typing in a form control
        if (isTypingContext(target)) return;

        // If any bootstrap modal with class .modal.show has data-bs-backdrop and is open, limit interactions
        const universalOpen = $('#universalModal').is(':visible');
        const authOpen = $('#authModal').is(':visible');

        // Arrow scrolling (only when not in modal to avoid interfering with form navigation)
        if (!universalOpen && !authOpen) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                window.scrollBy({ top: SCROLL_STEP, left: 0, behavior: 'smooth' });
                return;
            }
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                window.scrollBy({ top: -SCROLL_STEP, left: 0, behavior: 'smooth' });
                return;
            }
        }

        if (e.key === 'Enter') {
            // Priority: Auth Modal > Universal Modal
            if (authOpen) {
                const btn = $('#auth_submit_btn');
                if (btn.length && !btn.prop('disabled')) {
                    e.preventDefault();
                    btn.trigger('click');
                }
                return;
            }
            if (universalOpen) {
                const confirmBtn = $('#confirmAction');
                if (confirmBtn.length && !confirmBtn.prop('disabled')) {
                    e.preventDefault();
                    confirmBtn.trigger('click');
                }
                return;
            }
        }
    });
})();