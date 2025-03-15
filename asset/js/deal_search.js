function filterDeals() {
    // Get filter values
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const priceRange = document.getElementById('priceFilter').value;

    // Create an XMLHttpRequest object
    const xhr = new XMLHttpRequest();

    // Define the PHP script URL with query parameters
    const url = `./controller/fetch_deals.php?search=${search}&status=${status}&priceRange=${priceRange}`;

    // Configure the request
    xhr.open('GET', url, true);

    // Set up a callback function to handle the response
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Update the table body with the new data
            document.getElementById('dealsTableBody').innerHTML = xhr.responseText;
        } else {
            console.error('Error fetching data');
        }
    };

    // Send the request
    xhr.send();
}