# Replay and Idempotency

- même instant absolu : même année, quel que soit offset, machine ou redémarrage ;
- replay le lendemain ou l'année suivante : occurredAt original, donc BusinessYear original ;
- aucune lecture de `now()` ;
- aucune persistance ou ledger nécessaire ;
- aucune variation due à la timezone locale.

L'autorité est idempotente par nature. Le ledger de la commande Property gère identité et divergence ; l'autorité temporelle ne duplique pas cette responsabilité.
