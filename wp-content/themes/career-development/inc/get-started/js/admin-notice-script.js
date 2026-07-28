
// Creta Testimonial Showcase plugin activation
document.addEventListener('DOMContentLoaded', function () {
    const career_development_button = document.getElementById('install-activate-button');

    if (!career_development_button) return;

    career_development_button.addEventListener('click', function (e) {
        e.preventDefault();

        const career_development_redirectUrl = career_development_button.getAttribute('data-redirect');

        // Step 1: Check if plugin is already active
        const career_development_checkData = new FormData();
        career_development_checkData.append('action', 'check_creta_testimonial_activation');

        fetch(installcretatestimonialData.ajaxurl, {
            method: 'POST',
            body: career_development_checkData,
        })
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data.active) {
                // Plugin is already active → just redirect
                window.location.href = career_development_redirectUrl;
            } else {
                // Not active → proceed with install + activate
                career_development_button.textContent = 'Navigate Getstart';

                const career_development_installData = new FormData();
                career_development_installData.append('action', 'install_and_activate_creta_testimonial_plugin');
                career_development_installData.append('_ajax_nonce', installcretatestimonialData.nonce);

                fetch(installcretatestimonialData.ajaxurl, {
                    method: 'POST',
                    body: career_development_installData,
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        window.location.href = career_development_redirectUrl;
                    } else {
                        alert('Activation error: ' + (res.data?.message || 'Unknown error'));
                        career_development_button.textContent = 'Try Again';
                    }
                })
                .catch(error => {
                    alert('Request failed: ' + error.message);
                    career_development_button.textContent = 'Try Again';
                });
            }
        })
        .catch(error => {
            alert('Check request failed: ' + error.message);
        });
    });
});
