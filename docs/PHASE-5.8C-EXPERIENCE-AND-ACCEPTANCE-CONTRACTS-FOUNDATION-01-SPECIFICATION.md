# Phase 5.8C — Experience & Acceptance — Contracts Specification

## Owner

`ExperienceAcceptance` est l'owner unique des contrats publics de qualification. Il ne reçoit aucune autorité métier sur les autres domaines.

## Contrats V1

- ResponsiveComplianceReaderV1 ;
- AccessibilityComplianceReaderV1 ;
- UserExperienceReaderV1 ;
- EndToEndReadinessReaderV1 ;
- PerformanceReadinessReaderV1 ;
- UserAcceptanceReaderV1 ;
- ReleaseCandidateReaderV1.

Chaque interface est strictement read-only et accepte uniquement un `ExperienceAcceptanceObservedAt`. Chaque Result V1 expose exclusivement `status` et `observedAt` UTC canonique.

Aucun score, métrique, pourcentage, détail UX, mesure de performance, rapport d'accessibilité, donnée E2E ou contenu UAT n'est exposé.

