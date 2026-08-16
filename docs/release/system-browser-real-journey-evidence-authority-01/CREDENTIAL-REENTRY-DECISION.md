# Décision de réentrée credential

## Situation

Le principal `a2110000-0000-4000-8000-000000000001` existe, mais son credential clair temporaire a été supprimé. Aucun Property ou Listing Iteration 11 ne lui appartient.

## Options

| Option | Décision | Motif |
|---|---|---|
| A — credential encore détenu par l'opérateur | recevable seulement s'il est réellement disponible et autorisé | aucun stockage ou transfert supplémentaire |
| B — reset/changement | non retenue | aucune procédure certifiée nécessaire n'est démontrée dans les autorités auditées |
| C — nouveau principal local | retenue si A est indisponible | chaîne déjà certifiée et aucune ressource Iteration 11 à préserver |
| D — autre mécanisme | rejeté sans autorité explicite | fail-closed |

## Voie certifiée retenue

Lors de la future campagne, et non dans cette Authority :

`credential local éphémère → CredentialHashAuthorityV1 → RegisterAccount → AccountRegistry → Login HTTPS réel`.

Le nouveau principal Authoring ne reçoit aucun rôle. Son credential clair reste exclusivement opérateur, hors repository et hors documentation, puis est supprimé après la campagne selon la procédure locale certifiée.

Conserver l'AccountId historique n'est pas nécessaire : aucune ressource Iteration 11 n'a été créée. Cette décision n'autorise aucune réécriture de hash, aucun SQL IAM ni aucune récupération depuis un hash.
