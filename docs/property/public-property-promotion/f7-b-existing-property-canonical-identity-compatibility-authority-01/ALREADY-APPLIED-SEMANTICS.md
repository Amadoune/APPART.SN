# Sémantique AlreadyApplied

Deux voies existantes sont distinguées :

1. **Replay avec ledger identique** : même commandId et checksum d’un succès atomiquement enregistré. `AlreadyApplied` atteste que l’effet de cette commande a été appliqué ; une évolution Domain ultérieure ne réécrit pas l’histoire du ledger.
2. **Property existante sans ledger de cette commande** : `AlreadyApplied` exige une compatibilité canonique complète avec les arguments F6 actuels, AddressId compris.

`AlreadyApplied` ne signifie jamais uniquement qu’une Property portant le même PropertyId existe.
