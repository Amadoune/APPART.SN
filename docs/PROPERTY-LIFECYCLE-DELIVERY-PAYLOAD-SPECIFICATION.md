# Property Lifecycle Delivery Payload Specification

L'enveloppe technique contient exactement :

```json
{
  "canonicalEvent": "<JSON canonique Property Lifecycle 4.2E>"
}
```

`fields()` conserve cette clé unique et sa chaîne exacte. `checksum()` retourne `SHA-256(canonicalEvent)`.

`restore()` exige l'ordre canonique racine, l'ordre du payload et des métadonnées, les types stricts, V1, une identité redérivée identique et une resérialisation byte-for-byte. Aucun champ supplémentaire n'est accepté.
