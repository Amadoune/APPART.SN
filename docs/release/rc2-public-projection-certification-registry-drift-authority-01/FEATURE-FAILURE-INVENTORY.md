# Feature Failure Inventory

Les quatre échecs observés mesurent tous `PublicProjectionDeliveryConsumerRegistry::$registrations`, obtenu depuis le container puis lu par réflexion.

| Fichier | Méthode | Assertions | Attendu | Réel |
|---|---|---|---:|---:|
| `tests/Feature/LeadLifecycleOutboxCompatibilityRuntimeTest.php` | `test_registry_contains_forty_one_unique_pairs_and_three_lead_consumers` | `assertCount(54, $registrations)` puis 54 couples uniques | 54 | 55 |
| `tests/Feature/MediaItemLifecycleOutboxCompatibilityRuntimeTest.php` | `test_registry_contains_forty_five_unique_pairs_and_two_media_consumers` | mêmes deux invariants | 54 | 55 |
| `tests/Feature/ProfessionalStatusOutboxCompatibilityRuntimeTest.php` | `test_registry_contains_forty_three_unique_pairs_and_two_professional_consumers` | mêmes deux invariants | 54 | 55 |
| `tests/Feature/PropertyLifecycleOutboxCompatibilityRuntimeTest.php` | `test_worker_registry_preserves_property_entries_in_the_complete_registry` | mêmes deux invariants | 54 | 55 |

Chaque test s'arrête à sa première assertion de taille; les assertions d'unicité suivantes sont donc également stale mais n'ont pas été comptées parmi les quatre échecs observés.
