function filterAdvertisements() {
    // Get filter values
    const search = document.getElementById('adSearchInput').value;
    const status = document.getElementById('statusFilterAd').value;
    const category = document.getElementById('categoryFilterAd').value;

    // Create an XMLHttpRequest object
    const xhr = new XMLHttpRequest();

    // Define the PHP script URL with query parameters
    const url = `./controller/fetch_adds.php?search=${search}&status=${status}&category=${category}`;

    // Configure the request
    xhr.open('GET', url, true);

    // Set up a callback function to handle the response
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Update the table body with the new data
            document.getElementById('advertisementsTableBody').innerHTML = xhr.responseText;
        } else {
            console.error('Error fetching data');
        }
    };

    // Send the request
    xhr.send();
}