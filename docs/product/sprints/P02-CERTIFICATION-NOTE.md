# P02 — Certification Note

## Verdict

Le vertical slice FIRST LISTING est complet.

La donnée locale est créée par les registries, use cases, writers et services de projection certifiés. Le Listing et son workflow convergent vers `published`. L'expiration est exactement dérivée de `publishedAt + 90 jours`. La MediaCollection est owner-scoped et possède un média principal. La première génération publique est construite via une candidate explicite, un rebuild et un manifeste non vide, sans injection SQL ni génération active artificielle.

Le visiteur voit l'annonce réelle sur `http://appart.test`, puis ouvre :

```text
http://appart.test/annonces/p02-premiere-annonce-dakar
```

L'accueil, l'API Search publique, la fiche et le média répondent tous HTTP 200. Les campagnes ciblées Unit, Feature, Architecture, PostgreSQL, PHPStan, Pint, Vite et `git diff --check` sont terminalement PASS.

## Décision proposée

**GO PROPOSÉ — APPART.SN PRODUCT SPRINT P02 — FIRST LISTING**
