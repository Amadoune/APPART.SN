# Search Source Dependency Authority 01

## Décision

`SearchMissing` désigne l'absence d'une `SearchDecision` finale appartenant à `SearchDiscovery`, recherchée par `ListingId` dans `search_discovery.public_search_decisions`. Cette source est historiquement obligatoire pour fournir **la version Search du watermark** de la Projection publique. L'assembleur ne consomme ni titre, ni description, ni prix, ni résultat de recherche.

La dépendance est légitime au regard des certifications existantes, mais son chemin productif de matérialisation est absent. Les seuls producteurs trouvés sont les commandes locales `CreateLocalFirstListing` et `CreateLocalPublicFactListing`; aucune commande applicative, consommation d'événement Published ou orchestration PublicationReview n'écrit la décision pour un Listing produit.

## Classification

Classification finale unique : **source technique autoritative non matérialisée**.

Le défaut de séquencement est la conséquence opérationnelle de cette absence, non une seconde cause. Le binding et le reader productifs existent et fonctionnent.

## Ordre normatif retenu

L'ordre certifié est le candidat A, précisé ainsi :

`Published → faits publics Listing/Property/Media → décision SearchDiscovery finale → Projection publique → lecture Search publique`.

La décision Search amont ne doit pas être dérivée de la Projection, sinon la dépendance deviendrait circulaire. L'expérience Search en aval peut rester fermée pendant la matérialisation technique de cette source.

## Prochain chantier minimal

Ouvrir une **Search Decision Materialization Authority 01**, documentaire d'abord, pour arrêter : événement ou commande d'entrée, révisions sources, rang/facettes minimaux déjà autorisés, identité, idempotence, transaction et séquencement avant `ProjectPublishedListingV1`. Aucune implémentation n'est autorisée par la présente décision.

## Verdict

**GO PROPOSÉ — SEARCH SOURCE DEPENDENCY AUTHORITY 01**

La frontière, l'owner, la donnée manquante, le chemin absent et le chantier suivant sont qualifiés sans ouvrir l'UX Search ni modifier Projection.
