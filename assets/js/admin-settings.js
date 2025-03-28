/**
 * Admin settings JavaScript για το Pointer Domain Search plugin
 *
 * Περιλαμβάνει τις λειτουργίες για:
 * - Επιλογή/αποεπιλογή όλων των TLDs
 * - Επαλήθευση των διαπιστευτηρίων API
 *
 * @since 0.3.0
 */
(function($) {
    'use strict';

    console.log('Admin settings JS loaded');

    /**
     * Αρχικοποίηση όταν το έγγραφο είναι έτοιμο
     */
    $(document).ready(function() {
        console.log('Document ready');

        // Έλεγχος αν το στοιχείο υπάρχει
        console.log('Verify button exists:', $('#verify_api_credentials').length);
        console.log('Result area exists:', $('#api_credentials_result').length);
        console.log('AJAX URL:', typeof pointerDomainSearchAdmin !== 'undefined' ? pointerDomainSearchAdmin.ajaxUrl : 'undefined');

        // Χειρισμός για τα κουμπιά επιλογής/αποεπιλογής όλων των TLDs
        $('#select_all_tlds').on('click', function(e) {
            console.log('Select all clicked');
            e.preventDefault();
            $('.pointer-domain-search-tlds-admin input[type="checkbox"]').prop('checked', true);
        });

        $('#deselect_all_tlds').on('click', function(e) {
            console.log('Deselect all clicked');
            e.preventDefault();
            $('.pointer-domain-search-tlds-admin input[type="checkbox"]').prop('checked', false);
        });

        // Χειρισμός για την επαλήθευση των διαπιστευτηρίων API
        $('#verify_api_credentials').on('click', function() {
            console.log('Verify credentials clicked');

            const $resultArea = $('#api_credentials_result');
            $resultArea.html('<span class="api-status-verifying">' + pointerDomainSearchAdmin.verifying + '</span>');

            $.ajax({
                url: pointerDomainSearchAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pointer_domain_search_verify_credentials',
                    nonce: pointerDomainSearchAdmin.nonce
                },
                success: function(response) {
                    console.log('AJAX success:', response);
                    if (response.success) {
                        $resultArea.html('<span class="api-status-success">' + response.data + '</span>');
                    } else {
                        $resultArea.html('<span class="api-status-error">' + response.data + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX error:', status, error);
                    $resultArea.html('<span class="api-status-error">' + pointerDomainSearchAdmin.serverError + '</span>');
                }
            });
        });
    });
})(jQuery);
