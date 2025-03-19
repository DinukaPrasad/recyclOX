// Function to show the selected section and hide others
function showSection(sectionId) {
    console.log(`Showing section: ${sectionId}`); // Debugging statement

    // Hide all sections
    const sections = document.querySelectorAll('.dashboard-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });

    // Show the selected section
    const selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.classList.add('active');
    }

    // Update the active link in the navigation
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        link.classList.remove('active');
    });
    const activeLink = document.querySelector(`.nav-links a[href="#${sectionId}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Add event listeners to navigation links
document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default link behavior
            const sectionId = this.getAttribute('href').substring(1); // Get section ID from href
            showSection(sectionId);
        });
    });

    // Set the default section to "My Profile" on page load
    showSection('my-profile');
});

// Function to handle logout
function logout() {
    alert('You have been logged out.');
    // Redirect to login page or perform logout logic
    window.location.href = 'index.php';
}

// Open Edit Profile Modal
function openEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'block';
}

// Close Edit Profile Modal
function closeEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'none';
}

// Open Change Password Modal
function openChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'block';
}

// Close Change Password Modal
function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function (event) {
    const modals = ['editProfileModal', 'changePasswordModal', 'createAdModal', 'addFavoritesModal', 'EditAdModal'];
    
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
};

// Open Create Ad Modal
function openCreateAdModal() {
    document.getElementById('createAdModal').style.display = 'block';
}

// Close Create Ad Modal
function closeCreateAdModal() {
    document.getElementById('createAdModal').style.display = 'none';
}

function openEditAdModal(adId, category, location, weight, description, adImage) {
    console.log("Opening Edit Ad Modal");

    // Check if elements exist
    const editAdId = document.getElementById("editAdId");
    const editCategory = document.getElementById("editCategory");
    const editLocation = document.getElementById("editLocation");
    const editWeight = document.getElementById("editWeight");
    const editDescription = document.getElementById("editDescription");

    if (!editAdId || !editCategory || !editLocation || !editWeight || !editDescription) {
        console.error("One or more form elements are missing in the DOM.");
        return;
    }

    // Populate form fields
    editAdId.value = adId;
    editCategory.value = category;
    editLocation.value = location;
    editWeight.value = weight;
    editDescription.value = description;

    // Show modal
    const editAdModal = document.getElementById("EditAdModal");
    if (editAdModal) {
        editAdModal.style.display = "flex";
    } else {
        console.error("Edit Ad Modal not found in the DOM.");
    }
}

// Close Edit Ad Modal
function closeEditAdModal() {
    document.getElementById("EditAdModal").style.display = "none";
}

// Delete Ad
function deleteAd(event, adId) {
    event.stopPropagation(); // Prevent the card click event from firing
    if (confirm("Are you sure you want to delete this ad?")) {
        fetch(`./controller/user_controller/delete_ad.php?ad_id=${adId}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Ad deleted successfully!");
                    window.location.reload();
                } else {
                    alert("Failed to delete ad.");
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

// Open Add Favorites Modal
function openAddFavoritesModal() {
    document.getElementById('addFavoritesModal').style.display = 'block';
}

// Close Add Favorites Modal
function closeAddFavoritesModal() {
    document.getElementById('addFavoritesModal').style.display = 'none';
}

// Add to Favorites
function addFavorite(categoryId) {
    fetch('./controller/user_controller/add_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ category_id: categoryId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh the page to update the UI
        } else {
            alert("Failed to add to favorites.");
        }
    })
    .catch(error => console.error('Error:', error));
}

// Remove from Favorites
function removeFavorite(categoryId) {
    fetch('./controller/user_controller/remove_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ category_id: categoryId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh the page to update the UI
        } else {
            alert("Failed to remove from favorites.");
        }
    })
    .catch(error => console.error('Error:', error));
}

// Function to save rate
function saveRate(event, categoryId) {
    event.preventDefault(); // Prevent form submission

    const pricePerKg = event.target.price_per_kg.value;

    fetch('./controller/user_controller/save_rate.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            category_id: categoryId,
            price_per_kg: pricePerKg,
        }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert("Rate saved successfully!");
            location.reload(); // Refresh the page to reflect changes
        } else {
            alert("Failed to save rate: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the rate.');
    });
}

// Filter feedback by rating
function filterFeedback(minRating) {
    const feedbackItems = document.querySelectorAll('.feedback-item');
    feedbackItems.forEach(item => {
        const rating = item.querySelector('.rating').dataset.rating;
        if (rating >= minRating) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Mark Notification as Read
function markAsRead(notificationId) {
    fetch('./controller/user_controller/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh the page to update the UI
        } else {
            alert("Failed to mark notification as read.");
        }
    })
    .catch(error => console.error('Error:', error));
}

// Function to update deal status
function updateDealStatus(dealId, status) {
    if (confirm(`Are you sure you want to ${status} this deal?`)) {
        fetch('./controller/user_controller/update_deal_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                deal_id: dealId,
                status: status,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Deal ${status} successfully!`);
                location.reload(); // Refresh the page to reflect changes
            } else {
                alert(`Failed to ${status} deal.`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the deal status.');
        });
    }
}