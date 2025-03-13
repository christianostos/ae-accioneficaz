document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.cliente-panel-tab');
    const sections = document.querySelectorAll('.cliente-panel-section');

    const lastSection = sessionStorage.getItem('activeTab') || 'solicitudes';
    activateTab(lastSection);

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const section = this.getAttribute('data-section');
            activateTab(section);
        });
    });

    function activateTab(section) {
        tabs.forEach(t => t.classList.remove('active'));
        sections.forEach(s => s.classList.remove('active'));

        sessionStorage.setItem('activeTab', section);
        document.getElementById(section).classList.add('active');
        document.querySelector(`[data-section="${section}"]`).classList.add('active');
    }
});
