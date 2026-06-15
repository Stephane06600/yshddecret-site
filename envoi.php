<?php
/* ============================================================
   YSHD - Script de traitement du formulaire de contact
   Envoie les demandes vers contact@yoursmarthomedesign.com
   ============================================================ */

// ====== CONFIGURATION ======
$destinataire = "contact@yoursmarthomedesign.com";
$expediteur_systeme = "no-reply@yshd06.com";
$page_merci = "merci.html";
$page_erreur = "index.html#contact";

// ====== SÉCURITÉ : accepter uniquement POST ======
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $page_erreur);
    exit;
}

// ====== ANTI-SPAM SIMPLE : honeypot ======
// (Le champ "website" est invisible dans le formulaire ; s'il est rempli, c'est un bot)
if (!empty($_POST['website'])) {
    header("Location: " . $page_merci); // on simule le succès pour le bot
    exit;
}

// ====== RÉCUPÉRATION ET NETTOYAGE DES DONNÉES ======
function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$prenom    = clean($_POST['prenom'] ?? '');
$nom       = clean($_POST['nom'] ?? '');
$societe   = clean($_POST['societe'] ?? '');
$telephone = clean($_POST['telephone'] ?? '');
$email     = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$batiment  = clean($_POST['batiment'] ?? '');
$surface   = clean($_POST['surface'] ?? '');
$message   = clean($_POST['message'] ?? '');

// ====== VALIDATION DES CHAMPS OBLIGATOIRES ======
if (empty($prenom) || empty($nom) || empty($societe) || !$email) {
    header("Location: " . $page_erreur . "?erreur=champs");
    exit;
}

// ====== CONSTRUCTION DE L'EMAIL ======
$sujet = "[YSHD06] Nouvelle demande de contact - " . $prenom . " " . $nom . " (" . $societe . ")";

$corps = "Vous avez reçu une nouvelle demande de contact via le site yshd06.com\r\n";
$corps .= "================================================================\r\n\r\n";
$corps .= "COORDONNÉES DU CONTACT\r\n";
$corps .= "----------------------\r\n";
$corps .= "Prénom    : " . $prenom . "\r\n";
$corps .= "Nom       : " . $nom . "\r\n";
$corps .= "Société   : " . $societe . "\r\n";
$corps .= "Téléphone : " . ($telephone ?: '(non renseigné)') . "\r\n";
$corps .= "E-mail    : " . $email . "\r\n\r\n";
$corps .= "BESOIN EXPRIMÉ\r\n";
$corps .= "--------------\r\n";
$corps .= "Type de bâtiment : " . ($batiment ?: '(non renseigné)') . "\r\n";
$corps .= "Surface          : " . ($surface ?: '(non renseignée)') . "\r\n\r\n";
$corps .= "Message :\r\n" . ($message ?: '(aucun message)') . "\r\n\r\n";
$corps .= "================================================================\r\n";
$corps .= "Date de soumission : " . date('d/m/Y H:i:s') . "\r\n";
$corps .= "IP : " . ($_SERVER['REMOTE_ADDR'] ?? 'inconnue') . "\r\n";

// ====== EN-TÊTES EMAIL ======
$entetes  = "From: YSHD Site <" . $expediteur_systeme . ">\r\n";
$entetes .= "Reply-To: " . $prenom . " " . $nom . " <" . $email . ">\r\n";
$entetes .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$entetes .= "MIME-Version: 1.0\r\n";
$entetes .= "Content-Type: text/plain; charset=UTF-8\r\n";

// ====== ENVOI ======
$envoye = mail($destinataire, $sujet, $corps, $entetes);

if ($envoye) {
    header("Location: " . $page_merci);
    exit;
} else {
    header("Location: " . $page_erreur . "?erreur=envoi");
    exit;
}
