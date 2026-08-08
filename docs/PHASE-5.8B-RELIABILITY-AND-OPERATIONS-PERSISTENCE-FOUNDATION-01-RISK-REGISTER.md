# Phase 5.8B — Reliability & Operations — Persistence Risk Register

| Risque | Contrôle | Résiduel |
|---|---|---|
| course sur stream vide | advisory lock avant currentRow | faible |
| duplication | clé primaire et checksum canonique | faible |
| divergence silencieuse | DivergentRevision explicite | faible |
| perte de rollback externe | savepoint local sans commit externe | faible |
| lecture temporelle incorrecte | double borne effectiveAt/recordedAt | faible |
| corruption de journal | vérification SHA-256 au mapping | faible |
| couplage cross-owner | schéma et clés owner-scoped sans FK externe | faible |
| décision libre invalide | catalogue validé par stream | faible |
| index courant divergent | mise à jour transactionnelle contrôlée | faible |
| ouverture Runtime implicite | interdiction normative | faible |
