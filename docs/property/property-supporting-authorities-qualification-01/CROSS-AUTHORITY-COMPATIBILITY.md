# Cross-Authority Compatibility

Les capacités existantes auditées restent inchangées :

| Boundary | Garantie |
|---|---|
| IAM | AccountId de session seulement ; aucune donnée Geography ou temps fournie par IAM |
| Property Authoring | Conserve ses libellés et versions ; aucune identité inventée |
| RealEstateCatalog | Domain valide Address, Place usable et BusinessYear ; aucune règle déplacée |
| Geography | Reste owner des Places et de leur lifecycle |
| Listing Lifecycle | Ne résout aucune autorité Property |
| Projection/Search | Ne sont jamais sources pour Authoring |
| Replay | Les trois futures autorités devront produire des décisions stables |
| Transactions | Chaque owner conserve ses transactions locales ; aucun besoin de transaction distribuée n'est introduit par cette qualification |

Le reader Public Geography peut continuer à servir Projection mais ne doit pas être détourné comme catalogue Authoring.
