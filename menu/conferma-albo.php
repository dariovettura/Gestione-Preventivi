<?php
add_action('wp_ajax_conferma-in-albo', 'conferma_in_albo');
add_action('wp_ajax_nopriv_conferma-in-albo', 'conferma_in_albo');

function conferma_in_albo()
{
    // Verifica che la richiesta provenga da un utente autorizzato
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Non sei autorizzato a eseguire questa operazione');
        return;
    }

    // Recupera i dati inviati dalla richiesta AJAX
    $fields = isset($_POST['fields']) ? $_POST['fields'] : null;

    if (!$fields) {
        wp_send_json_error('Dati mancanti');
        return;
    }

    $send_email = isset($fields['send_email']) && $fields['send_email'] === 'true';

    $fornitori = isset($fields['fornitori']) ? $fields['fornitori'] : [];

    // Loop attraverso i fornitori e aggiorna il meta del post
    foreach ($fornitori as $fornitore) {
        $post_id = isset($fornitore['id']) ? intval($fornitore['id']) : 0;

        if ($post_id > 0) {
            update_post_meta($post_id, 'stato-albo', 'confermato');

            // Se richiesto, invia un'email di conferma al fornitore
            if ($send_email && isset($fornitore['email'])) {
                $options = get_option('gestione_fornitori');

                // Parte del messaggio che deve esserci sempre
                $saluto = 'Gentile ' . sanitize_text_field($fornitore['name']) . ',' . "\n\n";

                $email_to = sanitize_email($fornitore['email']);
                $subject = 'Conferma Fornitore in Albo';
                // Controlla se 'mail_text' è impostato
                if (isset($options['mail_text']) && !empty($options['mail_text'])) {
                    // Usa il testo salvato
                    $message = $saluto . $options['mail_text'];
                } else {
                    // Usa il messaggio di default
                    $message = $saluto .
                        'Siamo lieti di informarla che il suo stato di fornitore è stato confermato in albo.' . "\n\n" .
                        'Cordiali saluti,' . "\n" .
                        'Barano Multiservizi';
                }
                $headers = array(
                    'Content-Type: text/html; charset=UTF-8'
                );
                $mail_sent = wp_mail($email_to, $subject, $message,$headers);
                if ($mail_sent) {

                } else {

                }
            } else {

            }
        }
    }

    wp_send_json_success('Fornitori confermati con successo');
}
