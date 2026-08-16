# Content SEO Source Dependency Authority 01

## Décision

`ContentSeoMissing` signifie que le snapshot amont obligatoire, détenu par ContentSeo et indexé par `ListingId`, n’est pas matérialisé. Le reader, le binding, le schéma, le mapper et le writer existent et sont productifs. Aucun producteur générique ne construit toutefois `ContentSeoSourceDecision` depuis les faits owner.

Le prochain chantier ne peut pas être une simple implémentation : une **ContentSeo Snapshot Materialization Authority** doit d’abord fermer le canonical path, l’identité, la version/cohérence, le decision time et le handoff Published.

## Verdict

**NO GO PROPOSÉ** — cause racine unique : autorité normative de matérialisation du snapshot ContentSeo absente.
