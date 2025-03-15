function filterUserRatings() {
    // Get filter values
    const search = document.getElementById('userRatingSearchInput').value;
    const rating = document.getElementById('ratingFilter').value;

    // Create an XMLHttpRequest object
    const xhr = new XMLHttpRequest();

    // Define the PHP script URL with query parameters
    const url = `./controller/fetch_user_ratings.php?search=${search}&rating=${rating}`;

    // Configure the request
    xhr.open('GET', url, true);

    // Set up a callback function to handle the response
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Update the table body with the new data
            document.getElementById('userRatingsTableBody').innerHTML = xhr.responseText;
        } else {
            console.error('Error fetching data');
        }
    };

    // Send the request
    xhr.send();
}