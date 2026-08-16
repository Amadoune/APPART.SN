# Sequencing Matrix

| Gate | Current state | Prerequisites | Authorized now? | Blocking higher release? | Next after |
|---|---|---|---|---|---|
| RC2 Iteration 11 | GO certified/closed | all exit criteria | No reopening | No | UI residual gate |
| NotReady UI correction | Non-blocking residual; separate gate anticipated by certified register | Iteration 11 closure and defect evidence | **Yes — sole next gate** | Not an Iteration 11 blocker; sequenced before materialization | Return to Release sequencing |
| RC2 Iteration 12 | Nonexistent/non-opened | explicit Product authority | No | No | none |
| Search UX/API | Non-opened | explicit Product authority | No | No for RC2 Projection | none |
| RC2 Release materialization | Awaiting | UI correction closed; exact inventory/authority | No | Yes | Build/Packaging |
| Build/Packaging | Historical evidence only | immutable RC2 candidate | No | Yes | External CI |
| External CI | Missing official authority/infrastructure | candidate, official remote/owner/principal | No | Yes | Release/Production review |
| Production Readiness | NO GO/independent | immutable artifact, CI and operational evidence | No | Yes | Deployment qualification |
| Deployment | Non-opened | Production Readiness GO | No | Yes | production release |
