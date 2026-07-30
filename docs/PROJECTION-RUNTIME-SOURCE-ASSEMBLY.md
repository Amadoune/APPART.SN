# Stratégie d'assemblage Projection Runtime Source

1. valider l'identité Listing dans les vocabulaires propriétaires ;
2. lire Listing puis Property ;
3. résoudre l'ownership et lire MediaCollection ;
4. lire les décisions Search et Content/SEO ;
5. lire la génération Active et `decisionAt` ;
6. vérifier l'égalité exacte de `decisionAt` avec le snapshot propriétaire ;
7. lire Geography et Media publics par leurs identités officielles ;
8. construire `PublicListingProjectionSources` ;
9. construire le watermark vectoriel et exposer sa readiness.

Chaque étape s'arrête sur un résultat obligatoire absent ou corrompu. Il n'existe aucun fallback, scan global, accès HTTP ou valeur par défaut.
