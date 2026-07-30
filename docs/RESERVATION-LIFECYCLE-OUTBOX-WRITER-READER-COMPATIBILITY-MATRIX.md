# Reservation Lifecycle Outbox Writer Reader Compatibility Matrix

| Cas | Writer | Reader | Résultat certifié |
|---|---|---|---|
| Message Reservation valide | résout `reservation_lifecycle` | filtre `source_module=ReservationLifecycle` | message restauré exactement |
| Message d'un owner historique | résout son owner historique | conserve son parcours existant | comportement inchangé |
| Copie Reservation dans un mauvais owner | hors contrat d'écriture | filtre l'owner attendu | copie ignorée |
| Owner inconnu | résolution refusée | résolution inverse refusée | aucun fallback |
| Plusieurs owners éligibles | écritures isolées | union bornée puis ordre global | résultat déterministe |

Le mapper et le catalogue ne sont pas étendus. Les tests emploient un message générique déjà supporté afin de prouver la capacité physique Writer/Reader indépendamment de la future compatibilité événementielle.
