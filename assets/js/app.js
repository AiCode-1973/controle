document.addEventListener('DOMContentLoaded', function () {
    const toggle  = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');

    if (toggle && wrapper) {
        toggle.addEventListener('click', function () {
            wrapper.classList.toggle('toggled');
        });
    }
});
