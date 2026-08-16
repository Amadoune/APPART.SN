# Collision Policy

| Cas | Décision |
|---|---|
| Même intention, même ID | Replay normal, valeur réémise |
| Property existant avec même ID et mêmes faits | Compatible, aucune mutation |
| Même ID avec faits/intention différents pour le même Property | `Collision`, fail-closed |
| Aggregate existant divergent | La promotion retourne son résultat divergent ; aucun nouvel ID |

La table courante adresse une ligne par propertyId et ne garantit pas l'unicité globale d'AddressId. L'inclusion de propertyId dans le nom UUID sépare les espaces d'intention. Toute collision détectée reste fermée ; il est interdit d'ajouter un suffixe, un UUID aléatoire ou de réessayer avec une autre identité.
