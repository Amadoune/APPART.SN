# Compatibility Matrix

| Surface | État |
|---|---|
| cinq Deliveries V1 | sources exclusives, inchangées |
| CryptographyPolicy, DataRetention, DataExport | aucun flux Outbox |
| Contracts, Persistence, Runtime, Owner Reader, HTTP, Event, Delivery | fermés et inchangés |
| migrations 084–086 et rollbacks | empreintes SHA-256 conservées |
| migration 087 | additive, owner-scoped, sans FK cross-domain |
| Provider | non nécessaire ; aucun binding créé |
| Transport, Routing, Consumer | NON OUVERTS |
