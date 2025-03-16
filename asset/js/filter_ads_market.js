function filterAds() {
    // Get selected filters
    const form = document.getElementById('filter-form');
    const formData = new FormData(form);

    // Convert form data to URL parameters
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        params.append(key, value);
    }

    // Send AJAX request to filter_ads.php
    fetch(`./controller/market/filter_ads.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            const productGrid = document.getElementById('product-grid');
            productGrid.innerHTML = '';

            if (data.length === 0) {
                productGrid.innerHTML = '<p>No advertisements found.</p>';
            } else {
                data.forEach(ad => {
                    productGrid.innerHTML += `
                        <div class="product-card">
                            <h3>${ad.description}</h3>
                            <p><strong>Category:</strong> ${ad.category_name}</p>
                            <p><strong>Weight:</strong> ${ad.weight} kg</p>
                            <p><strong>Location:</strong> ${ad.city}</p>
                        </div>`;
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

// Add event listeners to checkboxes for live updates
document.querySelectorAll('#filter-form input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', filterAds);
});