document.addEventListener('DOMContentLoaded', function () {
    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Default view
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay' // Add timeGridWeek and timeGridDay
        },
        views: {
            timeGridWeek: {
                slotDuration: '01:00:00', // Set the time slot duration (e.g., 1 hour)
                slotLabelInterval: '01:00:00', // Label intervals for time slots
                slotLabelFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    omitZeroMinute: false,
                    meridiem: 'short'
                }
            },
            timeGridDay: {
                slotDuration: '01:00:00', // Set the time slot duration (e.g., 1 hour)
                slotLabelInterval: '01:00:00', // Label intervals for time slots
                slotLabelFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    omitZeroMinute: false,
                    meridiem: 'short'
                }
            }
        },
        events: [], // Events will be populated dynamically
        dateClick: function (info) {
            // Handle date/time click (e.g., show details for the clicked date/time)
            const clickedDateTime = info.date;
            const clickedDate = clickedDateTime.toISOString().split('T')[0]; // Extract date part
            const clickedTime = clickedDateTime.toTimeString().split(' ')[0]; // Extract time part

            console.log('Clicked Date:', clickedDate); // Debugging
            console.log('Clicked Time:', clickedTime); // Debugging

            // Find events on the clicked date and time
            const eventsOnDateTime = calendar.getEvents().filter(event => {
                const eventDate = event.start.toISOString().split('T')[0]; // Extract date part
                const eventTime = event.start.toTimeString().split(' ')[0]; // Extract time part

                // Check if the event matches the clicked date and time
                return eventDate === clickedDate && eventTime === clickedTime;
            });

/*             if (eventsOnDateTime.length > 0) {
                let details = 'Garbage Collection Details:\n';
                eventsOnDateTime.forEach(event => {
                    details += - Time: ${event.extendedProps.time}, Notes: ${event.extendedProps.notes}\n;
                });
                alert(details);
            } else {
                alert('No garbage collection scheduled for ' + clickedDateTime.toLocaleString());
            } */
        },
        eventClick: function (info) {
            // Handle event click (e.g., show details for the clicked event)
            const event = info.event;
            const eventDetails = `
                Garbage Collection Details:
                - Date: ${event.start.toLocaleDateString()}
                - Time: ${event.extendedProps.time}
                - Notes: ${event.extendedProps.notes}
            `;
            alert(eventDetails);
        }
    });
    calendar.render();

    // Handle form submission
    const scheduleForm = document.getElementById('scheduleForm');
    scheduleForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the form from submitting and reloading the page

        const selectedCity = document.getElementById('city').value;

        if (selectedCity) {
            console.log('Fetching schedules for city:', selectedCity); // Debugging

            fetch(`./controller/get_schedules.php?city=${selectedCity}`)
                .then(response => {
                    console.log('Response status:', response.status); // Debugging
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text(); // First, get the response as text
                })
                .then(text => {
                    console.log('Response text:', text); // Debugging
                    return JSON.parse(text); // Parse the text as JSON
                })
                .then(data => {
                    console.log('Parsed JSON data:', data); // Debugging

                    if (data.status === 'success') {
                        // Map the fetched data to FullCalendar events
                        const events = data.data.map(schedule => ({
                            title: 'Garbage Collection',
                            start: schedule.collection_date + 'T' + schedule.collection_time, // Combine date and time
                            extendedProps: {
                                time: schedule.collection_time,
                                notes: schedule.notes
                            }
                        }));

                        console.log('Mapped Events:', events); // Debugging

                        // Clear existing events and add new ones
                        calendar.removeAllEvents();
                        calendar.addEventSource(events);
                    } else {
                        console.error('Error:', data.message);
                        alert('Failed to fetch schedules: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching schedules:', error);
                    alert('An error occurred while fetching schedules.');
                });
        } else {
            // Clear the calendar if no city is selected
            calendar.removeAllEvents();
        }
    });
});