# Fan-out identity

Identité logique d'une commande terminale : SHA-256 canonique de `sourceEventId + terminalPlaceId + refresh-contract-v1`.

Elle est déterministe, non métier, non aléatoire et ne devient pas l'identité de la décision. Le writer reste indexé par terminalPlaceId.
