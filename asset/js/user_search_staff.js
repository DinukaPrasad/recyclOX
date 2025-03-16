function filterUsers() {
    // Get filter values
    const search = document.getElementById('userSearchInput').value;
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilterUser').value;

    // Create an XMLHttpRequest object
    const xhr = new XMLHttpRequest();

    // Define the PHP script URL with query parameters
    const url = `./controller/staff_controller/fetch_user.php?search=${search}&role=${role}&status=${status}`;

    // Configure the request
    xhr.open('GET', url, true);

    // Set up a callback function to handle the response
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Update the table body with the new data
            document.getElementById('usersTableBody').innerHTML = xhr.responseText;
        } else {
            console.error('Error fetching data');
        }
    };

    // Send the request
    xhr.send();
}