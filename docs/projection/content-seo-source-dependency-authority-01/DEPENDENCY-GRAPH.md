# Dependency Graph

```text
ListingLifecycle ─┐
Listing Authoring ├─> ContentSeo snapshot ─> DecisionTimeReader ─┐
Property/Geography├─> ContentSeo snapshot                        ├─> Public Projection
SearchDiscovery ──┘                                              │
Media/Public Geography/Public Media ─────────────────────────────┘
```

ContentSeo ne lit pas `public_projection.listing_projections`. Aucun cycle Projection → ContentSeo → Projection n’est démontré.
