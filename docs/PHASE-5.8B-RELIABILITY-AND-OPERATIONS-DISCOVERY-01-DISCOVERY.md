# Phase 5.8B — Reliability & Operations — Discovery

## Objet

Ce jalon qualifie exclusivement la capacité `ReliabilityOperations`. Il ne crée aucune Foundation, aucun contrat et aucune surface technique.

## Ownership candidat

L'owner candidat unique est `ReliabilityOperations`.

Il possède les politiques et preuves transverses de fiabilité opérationnelle, mais n'acquiert aucune autorité métier sur les autres owners. Il observe des signaux techniques publiés ou explicitement autorisés ; il ne lit, ne transforme et ne corrige jamais leurs données métier.

## Périmètre qualifié

- observabilité : métriques, logs techniques, traces et corrélation non métier ;
- disponibilité : health checks techniques, supervision et alerting ;
- continuité : backup, restore, disaster recovery et exercices de reprise ;
- exploitation : runbooks, maintenance, housekeeping et queue operations ;
- préparation : capacity planning et operational readiness.

## Principes directeurs

1. Séparation stricte entre état métier, disponibilité Runtime et exploitation Infrastructure.
2. Collecte minimale, sans PII, secret, payload métier ni identifiant sujet.
3. Aucun health check ne constitue une preuve de readiness métier.
4. Les actions destructrices ou de reprise exigent un runbook, une autorisation explicite et une preuve d'exécution.
5. Les owners sources restent autorités de leurs données et décisions.

## Stratégie candidate

Le futur découpage devra distinguer :

- un plan d'observation en lecture seule ;
- un plan d'exploitation autorisé, audité et borné ;
- un plan de continuité validé par exercices ;
- des contrats publics minimaux seulement après un Boundary Audit certifié.

## Critères de certification futurs

- owner et frontières approuvés ;
- catalogues fermés pour health, alerting et readiness ;
- dépendances nominatives et minimales ;
- absence démontrée de données métier et de secrets ;
- objectifs SLI/SLO et budgets d'erreur explicitement approuvés ;
- preuves testées de backup, restore et disaster recovery ;
- runbooks versionnés avec ownership et escalade ;
- rétention, accès et purge des signaux opérationnels qualifiés ;
- aucune Foundation ouverte avant une décision d'autorité distincte.
