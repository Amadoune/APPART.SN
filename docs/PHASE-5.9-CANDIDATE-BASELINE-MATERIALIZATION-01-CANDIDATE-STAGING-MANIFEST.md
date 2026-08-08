# Candidate Staging Manifest

Le staging réel est borné aux sélecteurs explicites suivants :

```text
CHANGELOG.md
ROADMAP.md
bootstrap/providers.php
app/**
docs/**
src/**
tests/**
```

Ce manifest représente 2 184 chemins hors HEAD après création des onze livrables :

| Classe | Nombre |
|---|---:|
| `SOURCE_CANDIDATE` | 999 |
| `DOCUMENTATION_CANDIDATE` | 876 |
| `TEST_CANDIDATE` | 269 |
| `MIGRATION_FROZEN` | 40 |
| Exclusions staged | 0 |

Le `.gitignore`, les lockfiles et les autres fichiers déjà identiques à HEAD ne nécessitent aucun staging. Le staging aveugle `git add -A` est interdit ; seul le manifest ci-dessus est autorisé.
