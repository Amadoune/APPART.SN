# Public Geography Descendant Decision Refresh Authority 01

Public Geography possède le refresh des décisions dérivées; Geography possède les mutations Place. Toute décision V2 existante dont `revisionVector` contient le Place muté est affectée.

Le store JSONB des décisions est la source de découverte : il limite le fan-out aux terminaux déjà publics. Le même moteur de représentation est appelé par terminal. Rename rejoint l'outbox atomique existante; lifecycle utilise son transport existant. Disable/merge matérialisent `Unavailable`, sans suppression.

**GO PROPOSÉ.** Handoff exclusif vers `PUBLIC GEOGRAPHY DECISION MATERIALIZATION AUTHORITY 01 — REOPENING / COMPLETION 02`.
