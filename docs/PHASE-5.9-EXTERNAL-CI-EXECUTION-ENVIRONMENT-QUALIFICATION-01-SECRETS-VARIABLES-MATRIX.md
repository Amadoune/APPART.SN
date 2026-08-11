# External CI — Secrets and Variables Matrix

| Élément | Origine | Secret requis | Qualification |
|---|---|---:|---|
| `SOURCE_BASE_SHA` | workflow | Non | Valeur R4 fermée |
| `CANDIDATE_TAG` | workflow | Non | Tag R5 exact |
| `RELEASE_CANDIDATE_ID` | workflow | Non | Tag R5 exact |
| `APPART_TEST_PG_DSN` | workflow | Non | Service PostgreSQL éphémère local au runner |
| `APPART_TEST_PG_USER` | workflow | Non | Compte de test éphémère |
| `APPART_TEST_PG_PASSWORD` | workflow | Non | Valeur de test isolée, non productive |
| `GITHUB_TOKEN` | plateforme | Oui, géré par la plateforme | Permission `contents: read` uniquement |

Aucun secret applicatif, aucune clé de production et aucune variable métier ne sont requis par la chaîne décrite. La politique externe effective reste invérifiable sans repository officiel.
