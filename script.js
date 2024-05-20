document.addEventListener('DOMContentLoaded', function () {
    const entertainmentLink = document.getElementById('entertainment-link');
    const dropdownContent = document.querySelector('.dropdown-content');

    entertainmentLink.addEventListener('click', function (event) {
        event.preventDefault(); // Prevents the link from being followed
        dropdownContent.classList.toggle('active');
    });

    // Close the dropdown when clicking outside
    document.addEventListener('click', function (event) {
        if (!entertainmentLink.contains(event.target)) {
            dropdownContent.classList.remove('active');
        }
    });
});
