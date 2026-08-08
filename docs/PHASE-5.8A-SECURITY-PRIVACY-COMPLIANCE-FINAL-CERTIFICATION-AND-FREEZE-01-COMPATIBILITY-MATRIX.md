# Final Certification & Freeze 5.8A — Compatibility Matrix

| Garantie | État consolidé | Preuve ou limite |
|---|---|---|
| Owner unique SecurityCompliance | conforme | cinq chaînes owner-scoped |
| Huit contrats publics historiques | conforme | cinq matérialisés, trois sans source |
| Cinq chaînes exécutables seulement | conforme | aucune famille aval supplémentaire |
| Payloads minimaux | conforme | status et observedAt sur les surfaces publiques |
| UTC préservé | conforme | réductions et propagations mécaniques |
| Catalogues fermés, sans fallback | conforme | matrices Foundation |
| Absence de PII, secret et configuration | conforme | contrats et payloads bornés |
| Absence d'accès cross-domain et de FK cross-domain | conforme | source et schémas owner-scoped |
| Absence de transaction distribuée | conforme | transactions locales et savepoints |
| Migrations additives et rollbacks | conforme | 086/087 et rollbacks présents |
| Transport, Routing, Consumer | NON OUVERTS | aucune surface créée |
| Recertification ciblée | PASS | Unit/Architecture, HTTP, PostgreSQL, PHPStan, Pint |
| Unit complète | PASS | 2 637 tests, 9 262 assertions |
| Architecture complète | bloquante | 7 échecs de baseline antérieurs |
| PostgreSQL complète | bloquante | aucun résultat terminal après deux fenêtres |
| Pint global | bloquante | 2 fichiers antérieurs non conformes |

La compatibilité fonctionnelle SecurityCompliance est établie par les campagnes ciblées. La certification finale reste bloquée par les exigences globales explicites.
