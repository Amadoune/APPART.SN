# Media Item Lifecycle Primary Replacement Decision Matrix

| Décision fournie par `MediaCollection` | Remplaçant | Interprétation autorisée |
|---|---|---|
| `NotPrimary` | interdit | la transition ne nécessite aucun remplacement |
| `ReplacementSelected` | obligatoire et différent du média concerné | la collection a validé ce remplaçant précis |

Il n'existe aucun état `Unknown`, aucune valeur booléenne `isPrimary` et aucune fabrique d'inférence. Une décision incohérente ne peut pas être construite par l'API publique.

Le contrat ne certifie pas une source technique. Le gate suivant devra fournir une source ou une persistance capable de préserver cette décision sans la recalculer.
