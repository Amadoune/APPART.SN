# Public Projection Delivery Durability — analyse

## Conclusion de l'audit

Le système ne possède aujourd'hui aucune livraison durable post-commit. Les événements de Domaine existent, sont ordonnés localement et restent sur les Aggregates, mais les quatre tranches PostgreSQL ne persistent que l'état métier. Une mutation validée peut donc être durable sans qu'aucun fait destiné aux projections soit durablement distribuable.

## Inventaire

### Événements disponibles

ListingLifecycle, RealEstateCatalog et Media possèdent des événements publics associés aux mutations utiles à la projection. SearchDiscovery et ContentSeo possèdent également leurs événements de projection. Leurs contrats exposent `aggregateVersion()` et `eventIndex()` ; les Aggregates exposent `releaseEvents()`. Les dates d'occurrence sont informatives.

Property, Listing et Media ont une version d'Aggregate. SearchIndex et SeoProjection ont aussi une version. `PublicGeographySeoSource` et `PublicMediaSeoSource` n'ont toujours aucune révision stable, conformément au blocage 3.6A/3.6B.

### Persistance et transactions

Les quatre repositories PostgreSQL certifiés — AdministrativeAction, Listing, Property et MediaCollection — utilisent chacun une transaction locale injectable. L'implémentation par défaut ouvre, commit ou rollback une transaction PDO autour de l'écriture du seul Aggregate. Aucun repository n'écrit une Outbox. Aucun événement n'est persisté atomiquement avec l'Aggregate.

Les mappers refusent de reconstruire un Aggregate contenant des événements, ce qui protège les lectures mais ne crée aucun mécanisme de livraison. Il n'existe pas d'Unit of Work de production globale, de transaction distribuée ou de marqueur de consommation.

### Livraison et exploitation

Il n'existe aucun dispatcher de production, Outbox, worker, listener, queue, job, hook `afterCommit`, retry, dead-letter, journal de livraison ou reprise. Le `PublicListingProjectionUpdater` est un orchestrateur passif appelé uniquement en test. Aucun binding Laravel ne le rend exécutable.

### Blueprints normatifs

Les cinq blueprints convergent déjà vers : Outbox logique locale au module, même transaction que l'Aggregate, livraison asynchrone au moins une fois, ordre `(aggregateVersion, eventIndex)`, consommateurs idempotents, retry borné, quarantaine, catalogue versionné et batch secondaire. Ils interdisent publication avant commit, dual write, transaction distribuée et découverte magique.

## Réponses aux dix questions

1. **Dispatcher de production :** non.
2. **Outbox de production :** non.
3. **Événements atomiques avec les Aggregates :** non.
4. **Hook post-commit fiable :** non.
5. **Sources capables de produire un fait ordonné :** Listing, Property, Media, Search et ContentSeo grâce à leurs événements et versions. Elles ne savent pas encore le livrer durablement.
6. **Sources sans livraison :** toutes. Geography publique et Media publique cumulent en plus l'absence de révision stable.
7. **Composant connaissant le commit réussi :** aujourd'hui chaque `*Transaction` PostgreSQL local ; demain la frontière transactionnelle applicative locale au module doit en être l'unique propriétaire.
8. **Idempotency key :** avant commit, à partir de `sourceModule/aggregateType/aggregateId/aggregateVersion/eventIndex/eventType/payloadVersion`.
9. **Ordre d'un Aggregate :** lexicographique sur `(aggregateVersion, eventIndex)`, jamais sur l'heure.
10. **Détection/reprise :** impossible aujourd'hui ; la solution retenue ajoute état durable, bail de claim, tentatives, quarantaine, curseurs consommateurs et réconciliation.

## Comparaison des stratégies

Échelle : 3 favorable, 2 acceptable, 1 faible, 0 rédhibitoire.

| Critère | A Outbox/module | B centrale | C mémoire | D batch seul | E queue directe | F CDC |
|---|---:|---:|---:|---:|---:|---:|
| Atomicité métier | 3 | 1 | 0 | 2 | 0 | 3 |
| Absence de perte silencieuse | 3 | 2 | 0 | 2 | 0 | 3 |
| Ownership / DDD | 3 | 0 | 2 | 2 | 1 | 1 |
| Isolation modulaire | 3 | 0 | 2 | 2 | 1 | 0 |
| Ordre métier explicite | 3 | 3 | 1 | 2 | 2 | 1 |
| Retry / quarantaine / reprise | 3 | 3 | 0 | 2 | 3 | 2 |
| Lag observable | 3 | 3 | 1 | 2 | 3 | 3 |
| Complexité opérationnelle | 2 | 2 | 3 | 2 | 1 | 0 |
| Impact PostgreSQL | 2 | 1 | 3 | 2 | 3 | 1 |
| Respect des repositories certifiés | 3 | 1 | 3 | 3 | 3 | 1 |
| Updater + batch secondaire | 3 | 3 | 1 | 0 | 2 | 2 |
| Projections futures | 3 | 3 | 1 | 1 | 3 | 2 |

### Option A — retenue

Une Outbox est possédée par chaque module source et écrite dans la transaction PostgreSQL locale de sa mutation. Un relay générique opère sur des contrats communs, sans ownership central des données. C'est la seule option conforme à l'ensemble des blueprints, atomique et modulaire.

### Options rejetées

- **B, centrale partagée :** ownership ambigu, persistance partagée et couplage transactionnel entre modules.
- **C, post-commit mémoire :** crash entre commit et dispatch indétectable. Admissible seulement comme signal d'accélération après écriture Outbox.
- **D, batch seul :** retard, scans et détection tardive. Conservé uniquement comme réconciliation.
- **E, queue externe directe :** dual write Aggregate/broker. Une Outbox resterait nécessaire ; le broker pourra être un transport secondaire futur.
- **F, CDC :** capture des lignes techniques plutôt que des faits publics, fort couplage aux schémas et exploitation disproportionnée. Éventuel outil d'observabilité, jamais source primaire.
- **G, event store :** écartée ; transformer les Aggregates existants en event-sourced rouvrirait les quatre persistances certifiées sans besoin démontré.

## Décision transactionnelle

Une frontière transactionnelle applicative spécialisée par module ouvrira une transaction PDO locale. Les repositories existants y participeront via leur contrat de transaction injectable ; un participant « transaction déjà ouverte » exécutera leurs closures sans commit autonome. Un writer Outbox local, sur la même connexion et dans la même transaction, écrira les enveloppes collectées. La frontière commit ensuite une seule fois. Elle libère les événements de l'instance appelante uniquement après succès.

Ce mécanisme n'est ni une Unit of Work globale ni une modification de repository. Son implémentation future devra prouver : absence d'imbrication, même connexion physique, rollback commun et impossibilité de commit anticipé. Si cette preuve échoue, le sprint d'implémentation sera NO GO avant toute modification des repositories.

## Ownership normatif

| Responsabilité | Propriétaire |
|---|---|
| Création du fait et payload | module source |
| Table/journal Outbox | module source, dans son schéma |
| Idempotency key et ordre | enveloppeur Outbox contractuel |
| Atomicité | frontière transactionnelle locale du module |
| Claim, retry, quarantaine, purge | relay Outbox d'infrastructure, sous politique du module |
| Catalogue et compatibilité | propriétaire de l'eventType |
| Marqueur par consommateur | infrastructure de livraison, partitionnée par consommateur |
| Appel de l'Updater | consommateur Public Projection dédié |
| Watermark et idempotence projection | Updater et Projection Store |
| Divergence/rebuild | réconciliateur Public Projection |
| Lag/alertes | plateforme d'observabilité, métriques attribuées au module/consommateur |

## Message durable conceptuel

Champs obligatoires : `messageId`, `eventType`, `payloadVersion`, `sourceModule`, `aggregateType`, `aggregateId`, `aggregateVersion`, `eventIndex`, `idempotencyKey`, `occurredAt`, `recordedAt`, `payload`, `status`, `attempts`, `availableAt`, `claimedUntil`, `deliveredAt`, `lastErrorCode`, et, si disponibles, `correlationId`/`causationId`.

`messageId` est un UUID stable ou dérivé ; l'unicité normative porte sur l'idempotency key canonique. `occurredAt` et `recordedAt` ne participent jamais à l'ordre. Le payload JSON est minimal, en liste blanche et versionné par entier positif. Une rupture de sens, un renommage ou une suppression crée une nouvelle version ou un nouvel `eventType`. Les anciennes versions restent lisibles pendant leur rétention et jusqu'au dépassement de tous les curseurs consommateurs.

L'Aggregate complet, les secrets, tokens, hashes et données personnelles inutiles sont interdits. La suppression d'un type exige retrait des producteurs, dépassement des curseurs, fin de rétention et preuve qu'aucun replay ne le requiert.

## Ordre, idempotence et anomalies

- clé globale : `sourceModule:aggregateType:aggregateId:aggregateVersion:eventIndex:eventType:payloadVersion` ;
- doublon : même clé, même payload => accusé sans second effet ; même clé, payload différent => divergence et quarantaine ;
- obsolète : livré mais classé sans écrasement, selon watermark ;
- trou de séquence : différé en état `BlockedBySequenceGap`, puis réconciliation/replay ciblé ;
- hors ordre : jamais appliqué en last-write-wins ;
- versions incomparables : divergence observable, reconstruction ciblée ;
- crash après projection et avant accusé : redelivery at-least-once, neutralisée par le writer idempotent.

## Retry, quarantaine et blocage de readiness

Les erreurs réseau, indisponibilité PostgreSQL, timeout et conflit de claim sont retryables avec backoff exponentiel borné et jitter. Payload inconnu, invariant violé, collision/divergence non résoluble et version non supportée sont permanents. Un seuil configurable par consommateur mène à `Quarantined`, jamais à la suppression.

Les états conceptuels sont : `Pending`, `Claimed`, `Delivered`, `RetryScheduled`, `BlockedBySequenceGap`, `BlockedBySourceReadiness`, `Quarantined`. `PromotionNotReady` pour Geography/Media place la livraison en `BlockedBySourceReadiness`, sans tentative en boucle, sans accusé final et sans promotion partielle. Un signal de nouvelle révision ou la réconciliation la reprogramme.

Reprises autorisées : message, Aggregate, module, consommateur, plage de versions ou high-watermark. Toute reprise conserve messageId/idempotency key et est auditée.

## Réconciliation batch

Le batch est secondaire. À fréquence configurable selon le budget de fraîcheur, il parcourt des curseurs durables par module/partition, compare versions sources et watermarks, détecte messages bloqués/manquants et programme une reconstruction ciblée. Il ne scanne jamais sur le chemin HTTP.

Le **delivery replay** remet les mêmes messages à un consommateur. Le **projection rebuild** relit les sources autorisées et reconstruit dans une génération candidate. Le high-watermark borne une passe ; un curseur durable permet la reprise sans recommencer le lot.

## Rétention et observabilité

Les messages non livrés, bloqués ou quarantinés ne sont jamais purgés. Les messages livrés sont conservés au minimum jusqu'à ce que tous les consommateurs obligatoires aient dépassé leur curseur, que la fenêtre de replay soit close et qu'un rebuild indépendant soit possible. La durée chiffrée relève d'un futur sprint d'exploitation.

Métriques obligatoires : âge maximal Pending, lag versionnel par Aggregate/consommateur, débit, taux de doublons, tentatives, quarantaine, trous, readiness bloquée, dernière version livrée et divergence de watermark.
