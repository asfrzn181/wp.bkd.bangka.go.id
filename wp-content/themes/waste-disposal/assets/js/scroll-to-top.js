document.addEventListener('DOMContentLoaded', function () {
    const waste_disposal_button = document.querySelector('.scroll-top-button');
    const waste_disposal_link = document.querySelector('.scroll-top-button a');

    // Show/Hide button on scroll
    window.addEventListener('scroll', function () {
        if (document.documentElement.scrollTop > 100) {
            waste_disposal_button.style.display = "block";
        } else {
            waste_disposal_button.style.display = "none";
        }
    });

    // Scroll to top on click
    if (waste_disposal_link) {
        waste_disposal_link.addEventListener('click', function (waste_disposal_event) {
            waste_disposal_event.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

});