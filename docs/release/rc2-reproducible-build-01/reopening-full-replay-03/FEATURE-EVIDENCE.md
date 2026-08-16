# Feature Evidence

FAIL, exit 2. Le replay avec arrêt à la première erreur agrégée a exécuté 316 tests : 311 passés, 1 659 assertions, 4 échecs et 1 erreur d'environnement de journalisation.

Première divergence produit certifiable : le registre `PublicProjectionDeliveryConsumerRegistry` contient 55 enregistrements et 55 couples uniques; quatre tests de compatibilité exigent exactement 54. Les tests concernés sont `LeadLifecycleOutboxCompatibilityRuntimeTest`, `MediaItemLifecycleOutboxCompatibilityRuntimeTest`, `ProfessionalStatusOutboxCompatibilityRuntimeTest` et `PropertyLifecycleOutboxCompatibilityRuntimeTest`.

La source montre un enregistrement explicite `place.lifecycle.renamed` en plus des collections de cas. Aucune correction n'a été appliquée. Une erreur distincte d'écriture de `storage/logs/laravel.log` appartient aux permissions de la clean-room d'exécution et ne remplace pas la divergence d'assertion 55/54.
