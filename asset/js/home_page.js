function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('active');
}

// Initialize Calendar
document.addEventListener('DOMContentLoaded', function () {
    const calendar = document.getElementById('calendar');
    const today = new Date();
    const month = today.toLocaleString('default', { month: 'long' });
    const year = today.getFullYear();

    calendar.innerHTML = `
        <div class="calendar-header">
            <h4>${month} ${year}</h4>
        </div>
        <div class="calendar-dates">
            <!-- Add dates dynamically here -->
        </div>
    `;
});


// Slider Functionality
let slideIndex = 0;
showSlides();

function showSlides() {document.addEventListener('DOMContentLoaded', function () {
    const calendar = document.getElementById('calendar');
    const today = new Date();
    const month = today.getMonth();
    const year = today.getFullYear();

    // Fetch garbage collection dates from the server
    fetch('get_schedules.php')
        .then(response => response.json())
        .then(data => {
            const collectionDates = data.map(item => new Date(item.date).getDate());
            renderCalendar(month, year, collectionDates);
        })
        .catch(error => console.error('Error fetching schedules:', error));

    function renderCalendar(month, year, collectionDates) {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();

        let calendarHTML = `
            <div class="calendar-header">
                <h4>${firstDay.toLocaleString('default', { month: 'long' })} ${year}</h4>
            </div>
            <div class="calendar-dates">
                <div class="days-row">
                    <span>Sun</span>
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                </div>
                <div class="dates-grid">
        `;

        // Fill in the blanks for the first week
        for (let i = 0; i < startingDay; i++) {
            calendarHTML += `<div class="date empty"></div>`;
        }

        // Fill in the dates
        for (let i = 1; i <= daysInMonth; i++) {
            const isCollectionDay = collectionDates.includes(i);
            calendarHTML += `
                <div class="date ${isCollectionDay ? 'collection-day' : ''}">
                    ${i}
                    ${isCollectionDay ? '<span class="collection-marker"></span>' : ''}
                </div>
            `;
        }

        calendarHTML += `</div></div>`;
        calendar.innerHTML = calendarHTML;
    }
});
}