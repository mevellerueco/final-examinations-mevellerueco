// ── Show a specific section, hide all others ──────────────────────────────
function showSection(sectionID) {
    const allSections = document.querySelectorAll('.content, .homecontent');
    allSections.forEach(s => { s.style.display = 'none'; });

    const target = document.getElementById(sectionID);
    if (target) { target.style.display = 'block'; }
}

// ── Logo click: return to Home ────────────────────────────────────────────
function goHome() {
    const allContent = document.querySelectorAll('.content');
    allContent.forEach(s => { s.style.display = 'none'; });

    const home = document.getElementById('home');
    if (home) { home.style.display = 'block'; }
}

// ── Clear all text/number inputs inside a section ─────────────────────────
function clearFields(sectionID) {
    const section = document.getElementById(sectionID);
    if (!section) return;
    const inputs = section.querySelectorAll('input[type="text"], input[type="number"], input[type="tel"]');
    inputs.forEach(input => {
        if (input.type !== 'hidden') { input.value = ''; }
    });
}

// ── Show toast then fade it out ───────────────────────────────────────────
function showToast(id) {
    const toast = document.getElementById(id);
    if (!toast) return;
    toast.style.opacity   = '1';
    toast.style.display   = 'block';
    toast.classList.remove('toast-hidden');

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.classList.add('toast-hidden');
            toast.style.display = 'none';
        }, 500);
    }, 3000);
}

// ── On page load ─────────────────────────────────────────────────────────
window.onload = function () {
    const params  = new URLSearchParams(window.location.search);
    const section = params.get('section');
    const status  = params.get('status');

    // Show correct section (default: home)
    if (section && ['create','read','update','delete'].includes(section)) {
        showSection(section);
    } else {
        goHome();
    }

    // Show appropriate toast
    if (status === 'success') { showToast('success-toast'); }
    if (status === 'updated') { showToast('update-toast'); }
    if (status === 'deleted') { showToast('delete-toast'); }
    if (status === 'validation_error') {
        const msg = params.get('message');
        const errToast = document.getElementById('error-toast');
        if (errToast && msg) errToast.innerText = msg;
        showToast('error-toast');
    }

    // Clean the URL completely so a manual refresh goes back to Home
    if (window.location.search) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
};

// ── Custom Delete Modal ───────────────────────────────────────────────────
function openDeleteModal(id) {
    const modal = document.getElementById('custom-modal');
    const idInput = document.getElementById('modalDeleteId');
    if (idInput && id) {
        idInput.value = id;
    }
    if (modal) modal.style.display = 'flex';
}

function closeDeleteModal() {
    const modal = document.getElementById('custom-modal');
    if (modal) modal.style.display = 'none';
}

// ── Enter Key to Move to Next Field ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = Array.from(form.querySelectorAll('input[type="text"], input[type="number"], input[type="tel"]'));
        
        inputs.forEach((input, index) => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Stop form submission
                    
                    // Focus the next input field if it exists
                    if (index + 1 < inputs.length) {
                        inputs[index + 1].focus();
                    } else {
                        // If it's the last field, focus the submit button
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.focus();
                        }
                    }
                }
            });
        });
    });
});
