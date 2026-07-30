# Listing Publication Runtime Orchestration Analysis

## Responsabilité

L'orchestrateur reçoit une identité, une action et une version attendue. Il lit l'état stocké, délègue la décision au workflow 4.1A et transmet uniquement la transition `Allowed` au store 4.1B.

Il ne choisit ni état cible, ni diagnostic métier, ni transition. Il ne construit aucun Aggregate et ne connaît ni PostgreSQL, ni HTTP, ni Outbox, ni Projection.

## Concurrence

La version attendue protège la décision contre un état devenu obsolète entre le demandeur et la lecture. Le repository conserve la protection atomique entre lecture et écriture. Ses refus de version ou d'état sont exposés comme conflits de concurrence typés.

## Défaillances

Les états absents ou corrompus et les exceptions de store ne sont pas transformés en décisions métier. Ils restent des résultats d'orchestration `PersistenceFailure`. Le diagnostic d'un résultat `Denied` est celui produit par le workflow, sans traduction.

## Déterminisme

L'orchestrateur n'utilise ni horloge, ni aléatoire, ni dépendance externe autre que les deux ports certifiés. À état, action, version et réponse de persistance identiques, son résultat est identique.
