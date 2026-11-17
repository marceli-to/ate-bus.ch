<?php
// Alter Link aus der Infosoft-App → /fundmeldung (Code ?k=… wird mitgegeben)
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Referrer-Policy: no-referrer');
header('Location: /fundmeldung' . ($query !== '' ? '?' . $query : ''), true, 302);
