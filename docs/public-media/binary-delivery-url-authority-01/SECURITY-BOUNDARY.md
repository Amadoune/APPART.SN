# Security boundary

- parsing UUID/version strict;
- aucune entrée storageKey/path/ownerId client;
- résolution exclusivement par stores owner;
- aucune concaténation filesystem depuis l'URL;
- contrôle actif/ready/attached/Published à chaque GET;
- réponse uniforme 404 pour les refus;
- no session, no IDOR;
- aucun path local, storageKey ou détail d'intégrité dans la réponse;
- MIME allowlist et `nosniff`.
