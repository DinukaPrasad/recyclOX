function filterRatings() {
    // Get selected filters
    const form = document.getElementById('buyer-filter-form');
    const formData = new FormData(form);

    // Convert form data to URL parameters
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        params.append(key, value);
    }

    // Send AJAX request to filter_buyer_ratings.php
    fetch(`./controller/market/filter_buyer_ratings.php?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const buyerGrid = document.querySelector('.all-ratings-section .buyer-grid');
            buyerGrid.innerHTML = '';

            if (data.length === 0) {
                // If no ratings are found, display a message
                buyerGrid.innerHTML = '<p>No ratings found for the selected categories.</p>';
            } else {
                // Display filtered ratings
                data.forEach(rating => {
                    const ratingElement = document.createElement('a');
                    ratingElement.href = `./view_buyer.php?buyer_id=${rating.buyer_id}`;
                    ratingElement.className = 'buyer-card';
                    ratingElement.innerHTML = `
                        <h3>${rating.buyer_name}</h3>
                        <p><strong>Category:</strong> ${rating.category_name}</p>
                        <p><strong>Price per kg:</strong> ${rating.price_per_kg}</p>
                    `;
                    buyerGrid.appendChild(ratingElement);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // If an error occurs, display all ratings
            loadAllRatings();
        });
}

// Function to load all ratings
function loadAllRatings() {
    fetch('./controller/market/filter_buyer_ratings.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const buyerGrid = document.querySelector('.all-ratings-section .buyer-grid');
            buyerGrid.innerHTML = '';

            if (data.length === 0) {
                buyerGrid.innerHTML = '<p>No ratings found.</p>';
            } else {
                data.forEach(rating => {
                    const ratingElement = document.createElement('a');
                    ratingElement.href = `./view_buyer.php?buyer_id=${rating.buyer_id}`;
                    ratingElement.className = 'buyer-card';
                    ratingElement.innerHTML = `
                        <h3>${rating.buyer_name}</h3>
                        <p><strong>Category:</strong> ${rating.category_name}</p>
                        <p><strong>Price per kg:</strong> ${rating.price_per_kg}</p>
                    `;
                    buyerGrid.appendChild(ratingElement);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

// Add event listeners to checkboxes for live updates
document.querySelectorAll('#buyer-filter-form input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', filterRatings);
});

// Load all ratings when the page loads
document.addEventListener('DOMContentLoaded', loadAllRatings);