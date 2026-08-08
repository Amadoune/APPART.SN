# Notifications Contracts Foundation — Risk Register

| Risque | Prévention contractuelle |
|---|---|
| Duplication d'une décision source | Owner unique `Notifications`, catalogues owner-locaux |
| Fuite d'identité ou de PII | Clé opaque, résultats limités au statut |
| Couplage technique | Aucune dépendance Infrastructure, Persistence ou Runtime |
| Horloge implicite | Instant UTC explicite, aucune méthode `now()` |
| Catalogue extensible implicitement | Enums V1 fermés et testés exhaustivement |
| Confusion entre préférence, modèle et canal | Interfaces, résultats et statuts séparés |
