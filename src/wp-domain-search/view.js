/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

/**
 * Domain Search Functionality
 */

document.addEventListener('DOMContentLoaded', () => {
    const searchForms = document.querySelectorAll('.wp-block-create-block-wp-domain-search');

    searchForms.forEach(form => {
        const searchInput = form.querySelector('.wp-domain-search-input');
        const searchButton = form.querySelector('.wp-domain-search-button');
        const tldCheckboxes = form.querySelectorAll('.wp-domain-search-tld');
        const searchResults = form.querySelector('.wp-domain-search-results');
        const loadingDiv = form.querySelector('.wp-domain-search-loading');
        const errorDiv = form.querySelector('.wp-domain-search-error');
        const nonce = form.getAttribute('data-nonce');

        // Event listener για το κουμπί αναζήτησης
        searchButton.addEventListener('click', () => {
            performSearch();
        });

        // Event listener για το Enter στο input
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });

        function performSearch() {
            // Έλεγχος αν το input είναι κενό
            if (!searchInput.value.trim()) {
                showError('Παρακαλούμε εισάγετε ένα όνομα domain.');
                return;
            }

            // Έλεγχος αν έχει επιλεγεί τουλάχιστον ένα TLD
            const selectedTlds = Array.from(tldCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            if (selectedTlds.length === 0) {
                showError('Παρακαλούμε επιλέξτε τουλάχιστον ένα TLD.');
                return;
            }

            // Καθαρισμός προηγούμενων αποτελεσμάτων
            clearResults();

            // Αφαίρεση τυχόν TLD από το input (π.χ. example.com -> example)
            let domain = searchInput.value.trim();
            selectedTlds.forEach(tld => {
                const dotTld = `.${tld}`;
                if (domain.endsWith(dotTld)) {
                    domain = domain.slice(0, -dotTld.length);
                }
            });

            // Εμφάνιση φόρτωσης
            loadingDiv.classList.add('active');

            // AJAX αίτημα για αναζήτηση domain
            const formData = new FormData();
            formData.append('action', 'wp_domain_search');
            formData.append('nonce', nonce);
            formData.append('domain', domain);
            formData.append('tlds', JSON.stringify(selectedTlds));

            fetch(wpDomainSearch.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                loadingDiv.classList.remove('active');

                if (data.success === false) {
                    showError(data.data || 'Υπήρξε ένα σφάλμα κατά την αναζήτηση.');
                    return;
                }

                displayResults(data.data);
            })
            .catch(error => {
                loadingDiv.classList.remove('active');
                showError('Υπήρξε ένα σφάλμα κατά την αναζήτηση: ' + error.message);
            });
        }

        function displayResults(results) {
            if (!results || Object.keys(results).length === 0) {
                showError('Δεν βρέθηκαν αποτελέσματα.');
                return;
            }

            const resultsHtml = Object.entries(results).map(([domain, available]) => {
                const availableClass = available === '1' || available === 1
                    ? 'wp-domain-search-result-available'
                    : 'wp-domain-search-result-unavailable';

                const availableText = available === '1' || available === 1
                    ? 'Διαθέσιμο'
                    : 'Μη διαθέσιμο';

                return `
                    <div class="wp-domain-search-result-item">
                        <strong>${domain}</strong>: <span class="${availableClass}">${availableText}</span>
                    </div>
                `;
            }).join('');

            searchResults.innerHTML = resultsHtml;
        }

        function showError(message) {
            errorDiv.textContent = message;
            errorDiv.classList.add('active');
        }

        function clearResults() {
            searchResults.innerHTML = '';
            errorDiv.textContent = '';
            errorDiv.classList.remove('active');
        }
    });
});
