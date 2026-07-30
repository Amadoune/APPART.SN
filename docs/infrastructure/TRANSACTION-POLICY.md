# Transaction Policy

## 1. Objet

Cette politique fixe les frontières, niveaux de cohérence et comportements de concurrence des futures transactions PostgreSQL, sans décrire de schéma ni de commande SQL.

## 2. Règle générale

Une transaction correspond à une décision applicative cohérente et doit rester courte. Elle ne couvre jamais l’attente d’un utilisateur, un téléchargement, un appel réseau ou un traitement média.

## 3. Cohérence immédiate

Doivent être atomiques :

- création ou sauvegarde d’un Aggregate et ses clés d’unicité ;
- version attendue et nouvel état ;
- réservation d’identité enfant prévue par un Registry ;
- état métier et événements Outbox correspondants ;
- référence de transaction et intention de paiement idempotente ;
- Lead et réservation de déduplication ;
- décision administrative et preuve d’audit appartenant au même Aggregate.

## 4. Cohérence différée

Sont traités après commit par événements : projections SearchDiscovery et ContentSeo, notifications, statistiques, variantes média et autres lectures dérivées. Leur retard ne doit jamais rendre une donnée non publiable visible. Les consommateurs appliquent une politique fail-safe et idempotente.

## 5. Concurrence

La stratégie officielle est la concurrence optimiste par version d’Aggregate. La vérification est effectuée au moment de l’écriture durable. Aucun verrou pessimiste de longue durée n’est autorisé. Un verrou bref peut protéger une réservation d’unicité interne à la transaction, sans modifier le contrat métier.

Les conflits sont remontés explicitement. Une commande n’est rejouée qu’après nouvelle lecture et décision de l’orchestration.

## 6. Niveau d’isolation

Le niveau initial officiel est **Read Committed**, complété obligatoirement par les versions optimistes et les contraintes durables. Il s’applique aux Repository, à l’Unit of Work et à l’Outbox. Une opération nécessitant une vision stable relit et verrouille uniquement la ressource de réservation concernée pendant une transaction courte. Le niveau Serializable n’est pas un réglage global ; son emploi ciblé exige une preuve de concurrence, un test d’interblocage et une décision documentée avant implémentation du cas concerné.

## 7. Ordre et interblocages

Lorsque plusieurs ressources sont nécessaires, elles sont acquises dans un ordre stable : module, type de ressource, identité canonique. Une erreur d’interblocage annule l’opération entière. Une reprise technique est admise uniquement si elle réexécute une opération explicitement idempotente et ne réutilise pas un Aggregate muté.

## 8. Délais et limites

Toute transaction possède un délai maximal configuré par environnement et mesuré. Les traitements volumineux sont découpés en unités idempotentes ; ils ne maintiennent pas une transaction sur un lot complet. Les lectures et calculs coûteux sont effectués avant l’ouverture lorsque cela ne fragilise pas la version attendue.

## 9. Erreurs

Les catégories officielles sont : conflit concurrent, conflit d’unicité, intégrité persistante invalide, indisponibilité, délai dépassé et erreur inconnue. Leur traduction reste stable et ne révèle aucune structure interne.

## 10. Critères d’acceptation

- frontières transactionnelles documentées par cas d’usage d’écriture ;
- version optimiste systématique ;
- unicités durables et atomiques ;
- aucun effet externe avant commit ;
- stratégie déterministe d’interblocage et de délai ;
- cohérence différée exclusivement via Outbox ;
- aucune transaction distribuée.
