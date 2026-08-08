# Risk Register

| Risque | Maîtrise | Résiduel |
|---|---|---|
| collision sémantique d'identité | eventId/messageId versionnés et checksum distinct du statut | faible |
| duplication concurrente | contraintes uniques et ON CONFLICT avec comparaison canonique | faible |
| double claim | FOR UPDATE SKIP LOCKED et état technique owner-scoped | faible |
| retry infini | borne SQL et Application à dix tentatives | faible |
| commit implicite | savepoint local et propriété de transaction explicite | faible |
| fuite sensible | payload fermé à type, status et observedAt | faible |
| dépendance cross-owner | schéma SecurityCompliance sans FK externe | faible |
| ouverture Transport implicite | aucun Provider, Router, Consumer ou appel réseau | faible |
