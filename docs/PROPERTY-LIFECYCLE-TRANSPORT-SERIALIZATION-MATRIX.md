# Property Lifecycle Transport Serialization Matrix

| Élément entrant | Contrôle de restauration | Sortie |
|---|---|---|
| enveloppe | clé unique `canonicalEvent`, valeur string | enveloppe conservée |
| racine événement | cinq clés dans l'ordre 4.2E | même ordre |
| payload | cinq clés V1 dans l'ordre normatif | Value Objects exacts |
| métadonnées | deux instants UTC canoniques ordonnés | chaînes exactes |
| identité | redérivation SHA-256 métier | égalité avec `eventId` |
| événement restauré | resérialisation canonique | égalité byte-for-byte |
| checksum | SHA-256 de la chaîne | 64 caractères hexadécimaux |

Tout contrôle en échec produit une exception de transport typée et aucun objet partiellement restauré.
