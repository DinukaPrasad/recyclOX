document.addEventListener('DOMContentLoaded', function() {
    const advertisementsLink = document.getElementById('advertisements-link');
    const buyersLink = document.getElementById('buyers-link');
    const advertisementsSection = document.getElementById('advertisements-section');
    const buyersSection = document.getElementById('buyers-section');

    advertisementsLink.addEventListener('click', function(event) {
        event.preventDefault();
        advertisementsSection.style.display = 'flex';
        buyersSection.style.display = 'none';
    });

    buyersLink.addEventListener('click', function(event) {
        event.preventDefault();
        advertisementsSection.style.display = 'none';
        buyersSection.style.display = 'flex';
    });
});