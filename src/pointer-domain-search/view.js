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
    const searchForms = document.querySelectorAll('.wp-block-create-block-pointer-domain-search');

    searchForms.forEach(form => {
        const searchInput = form.querySelector('.pointer-domain-search-input');
        const searchButton = form.querySelector('.pointer-domain-search-button');
        const tldCheckboxes = form.querySelectorAll('.pointer-domain-search-tld');
        const searchResults = form.querySelector('.pointer-domain-search-results');
        const loadingDiv = form.querySelector('.pointer-domain-search-loading');
        const errorDiv = form.querySelector('.pointer-domain-search-error');
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

            // Λήψη των επιλεγμένων TLDs από τα checkboxes
            let selectedTlds = Array.from(tldCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            if (selectedTlds.length === 0) {
                showError('Παρακαλούμε επιλέξτε τουλάχιστον ένα TLD.');
                return;
            }

            // Αρχικός καθαρισμός του domain
            let domain = searchInput.value.trim();

            // Έλεγχος αν ο χρήστης έχει γράψει domain με TLD
            const dotPosition = domain.lastIndexOf('.');
            if (dotPosition !== -1) {
                const inputTld = '.' + domain.substring(dotPosition + 1).toLowerCase();

				console.log({inputTld, selectedTlds});

                // Έλεγχος αν το TLD που έγραψε ο χρήστης είναι στη λίστα των διαθέσιμων
                const availableTlds = Array.from(tldCheckboxes).map(checkbox => checkbox.value);
                if (availableTlds.includes(inputTld)) {
                    // Αν το TLD δεν είναι ήδη στα επιλεγμένα, το προσθέτουμε
                    if (!selectedTlds.includes(inputTld)) {
                        selectedTlds.push(inputTld);
                        // Βρίσκουμε και επιλέγουμε οπτικά το checkbox αν δεν είναι ήδη επιλεγμένο
                        const tldCheckbox = Array.from(tldCheckboxes).find(checkbox => checkbox.value === inputTld);
                        if (tldCheckbox && !tldCheckbox.checked) {
                            tldCheckbox.checked = true;

                            // Εμφάνιση μηνύματος ενημέρωσης ότι προστέθηκε το TLD
                            showNotification(`Προστέθηκε αυτόματα το TLD ${inputTld} στην αναζήτηση`);
                        }
                    }

                    // Αφαιρούμε το TLD από το domain
                    domain = domain.substring(0, dotPosition);
                }
            }

            // Καθαρισμός προηγούμενων αποτελεσμάτων
            clearResults();

            // Αφαίρεση τυχόν TLD από το input (για περίπτωση που δεν αφαιρέθηκε παραπάνω)
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
            formData.append('action', 'pointer_domain_search');
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

            // Έλεγχος για το αν θα εμφανιστεί το κουμπί αγοράς
            const showBuyButton = form.getAttribute('data-show-buy-button') === '1';

            const resultsHtml = Object.entries(results).map(([domain, available]) => {
                const availableClass = available === '1' || available === 1
                    ? 'pointer-domain-search-result-available'
                    : 'pointer-domain-search-result-unavailable';

                const availableText = available === '1' || available === 1
                    ? 'Διαθέσιμο'
                    : 'Μη διαθέσιμο';

                // Προσθήκη κουμπιού για αγορά αν είναι διαθέσιμο και επιτρέπεται από τις ρυθμίσεις
                const buyButton = (available === '1' || available === 1) && showBuyButton
                    ? `<a href="https://www.pointer.gr/domain-names/search?domain-name=${domain}" target="_blank" class="pointer-domain-search-buy-button">Αγορά</a>`
                    : '';

                return `
                    <div class="pointer-domain-search-result-item">
                        <strong>${domain}</strong>: <span class="${availableClass}">${availableText}</span>
                        ${buyButton}
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

        // Προσθήκη συνάρτησης για εμφάνιση προσωρινών ειδοποιήσεων
        function showNotification(message) {
            // Έλεγχος αν υπάρχει ήδη το στοιχείο ειδοποίησης
            let notification = form.querySelector('.pointer-domain-search-notification');

            // Αν δεν υπάρχει, το δημιουργούμε
            if (!notification) {
                notification = document.createElement('div');
                notification.className = 'pointer-domain-search-notification';
                form.appendChild(notification);

                // Προσθήκη στυλ για την ειδοποίηση
                notification.style.position = 'absolute';
                notification.style.top = '100px';
                notification.style.right = '20px';
                notification.style.padding = '10px 15px';
                notification.style.backgroundColor = '#4CAF50';
                notification.style.color = 'white';
                notification.style.borderRadius = '4px';
                notification.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s ease';
                notification.style.zIndex = '1000';
            }

            // Προσθέτουμε το μήνυμα
            notification.textContent = message;

            // Εμφανίζουμε την ειδοποίηση
            setTimeout(() => {
                notification.style.opacity = '1';
            }, 10);

            // Και μετά από 3 δευτερόλεπτα την κρύβουμε
            setTimeout(() => {
                notification.style.opacity = '0';
            }, 3000);
        }
    });
});
