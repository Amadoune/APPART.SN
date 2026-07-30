# Lead Lifecycle Contextual Store V2 Specification

Le port successeur `LeadLifecycleContextualTransitionStore` reçoit un `LeadLifecycleContextualAppend` contenant l'identité Lead, la transition certifiée, la version attendue et le contexte obligatoire (`actor`, `occurredAt`).

La future persistance conservera ces valeurs. Un rejeu identique retourne `AlreadyApplied`; la même transition/version avec un contexte différent retourne `ContextDivergence`.

Le store 4.4B et la migration 022 restent inchangés. Une fondation PostgreSQL additive doit précéder 4.4D.
