<?php
class pointer_api {
    private $username;
    private $password;

    public function login($username, $password) {
        $this->username = $username;
        $this->password = $password;
        // Κώδικας για σύνδεση στο API
    }

    public function domainCheck($domain, $tlds) {
        // Κώδικας για έλεγχο διαθεσιμότητας domain
        return []; // Επιστρέφει τα αποτελέσματα
    }

    public function logout() {
        // Κώδικας για αποσύνδεση από το API
    }
}
