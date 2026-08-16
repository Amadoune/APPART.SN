# Snapshot Payload

Payload fermé par le mapper existant :

- `listing`: state, headline, description, canonical_path, revision, published_at, expires_at, expired_treatment, non_indexable_treatment ;
- `search`: state, revision ;
- `property`: state, property_type, city, revision ;
- `canonical_history`: canonical, disposition, effective_at, replaced_at, redirect_target ;
- `decision_at`.

Transformations : enums owner vers enums ContentSeo, types techniques vers labels publics déjà qualifiés, canonical path via l’autorité canonical manquante. Le mapper recalcule le SHA-256 et rejette toute identité ou valeur incohérente.
