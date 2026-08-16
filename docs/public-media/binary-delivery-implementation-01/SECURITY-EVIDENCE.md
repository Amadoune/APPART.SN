# Security Evidence

Les tests couvrent requête guest, IDOR, média inactive/unready/unattached, mauvaise révision, identifiant mal formé, traversal, slash injection et traversal encodé.

Aucun chemin fourni par le client n'est concaténé au filesystem. L'endpoint ne liste aucun média ni storage key. Les erreurs publiques ne révèlent ni cause interne, ni chemin privé.
