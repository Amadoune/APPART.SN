# Phase 4.7A — Administrative Action Diagnostic Matrix

Priorité déterministe :

1. `UnknownAction` ;
2. `TerminalState` ;
3. `IncompatibleState` ;
4. `MissingReason` ;
5. `ApprovalNotRequired`.

| Situation | Diagnostic |
|---|---|
| action `Unknown` | UnknownAction |
| action connue depuis Recorded, Approved ou Rejected | TerminalState |
| Approve/Reject depuis Draft ou Record depuis PendingApproval | IncompatibleState |
| transition structurellement compatible sans preuve du motif | MissingReason |
| Approve/Reject avec DirectRecording | ApprovalNotRequired |

L'indépendance des acteurs n'est pas rediagnostiquée : le contrat 4.7A-R1 interdit déjà de construire une autorité incohérente.
