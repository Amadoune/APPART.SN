# Media Item Lifecycle Delivery Payload Specification

| Champ | Contenu |
|---|---|
| `canonicalEvent` | sérialisation JSON exacte certifiée en 4.6E |

Le round-trip doit conserver exactement les mêmes octets. Toute forme supplémentaire, identité incohérente ou sérialisation non canonique est rejetée.

Le checksum est :

```text
SHA-256(canonicalEvent)
```

Aucune URL, caption, donnée de collection ou décision de remplacement n'est ajoutée.
