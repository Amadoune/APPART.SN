# Phase 4.9 — Account Status Lifecycle Roadmap

1. **4.9A — Discovery / Blueprint** — **GO CERTIFIÉ et fermé**.
2. **4.9A-R1 — Account Status Decision Boundary Amendment** — **GO CERTIFIÉ
   et fermé**.
3. **4.9B — Workflow Foundation** — **GO CERTIFIÉ et fermé**.
4. **4.9C-R1 — Account Status Historical Coexistence Gate** — **GO CERTIFIÉ
   et fermé**.
5. **4.9C — Persistence Foundation** — **GO CERTIFIÉ et fermé**.
6. **4.9D — Runtime Composition** — **GO CERTIFIÉ et fermé**.
7. **4.9C-R2 — Account Registry Runtime Source Amendment** — **NO GO CERTIFIÉ
   et fermé : aucune source Account de production n'existe**.
8. **4.9C-R3 — Historical Account Source Resolution Amendment** — **NO GO
   CERTIFIÉ et fermé**.
9. **4.9P-A — Historical Account Persistence Discovery and Audit** — **GO
   CERTIFIÉ et fermé**.
10. **4.9P-B — Persistence Contract and Mapping Foundation** — **GO CERTIFIÉ
    et fermé; gate 4.9P-B-R1 satisfait et fermé**.
11. **4.9P-C — PostgreSQL Persistence Foundation** — **GO CERTIFIÉ et
    fermé**.
12. **4.9P-D — Runtime Composition** — **GO CERTIFIÉ et fermé**.
13. **4.9P Final Certification** — **GO FINAL, fermée et gelée**.
14. **Recertification ciblée 4.9C** — **GO CERTIFIÉ et fermée**.
15. **4.9A-R2 — Replay Attempt Identity Amendment**, si déclenché — fermé.
16. **4.9E — Runtime Orchestration** — **GO CERTIFIÉ et fermé**.
17. **4.9F — Event Contract** — **GO CERTIFIÉ et fermé**.
18. **4.9G — Event Transport** — **GO CERTIFIÉ et fermé**.
19. **4.9H — Event Routing** — **GO CERTIFIÉ et fermé**.
20. **4.9I — Delivery Consumption** — **GO CERTIFIÉ et fermé**.
21. **4.9J-R1 — IdentityAccess Outbox Owner Audit** — **GO CERTIFIÉ et
    fermé; gate Consumer J5 bloquant**.
22. **4.9J-R2 — Consumer Compatibility Amendment** — **NO GO CERTIFIÉ et
    fermé : la destination routée est absente du contrat générique**.
23. **4.9J-R3 — Routed Delivery Boundary Amendment** — **GO CERTIFIÉ et
    fermé**.
24. **4.9J — Outbox Compatibility** — **GO CERTIFIÉ et fermé**.
25. **4.9K — Atomic Event Integration** — **GO CERTIFIÉ et fermé**.
26. **4.9L — HTTP Runtime** — **GO CERTIFIÉ et fermé**.
27. **4.9 Final Certification** — **audit achevé; GO FINAL proposé**.
28. **Gel versionné de la capacité** — en attente du prononcé GO FINAL de
    l'autorité de certification.

## Séquence normative

```text
Discovery
→ R1 Decision Boundary
→ Workflow
→ C-R1 Historical Coexistence
→ Persistence
→ C-R2 Account Registry Runtime Source Amendment requis
→ R3 Historical Account Source Resolution
→ P-A Historical Account Persistence Discovery
→ P-B Persistence Contract and Mapping
→ P-C PostgreSQL Persistence
→ P-D Runtime Composition Account
→ P Final Certification
→ recertification ciblée de Persistence
→ Runtime Composition
→ R2 Replay Identity si requis
→ Runtime Orchestration
→ Event Contract
→ Event Transport
→ Event Routing
→ Delivery Consumption
→ J-R1 Outbox Owner
→ J-R2 Consumer Compatibility
→ J-R3 Routed Delivery Boundary
→ Outbox Compatibility
→ Atomic Event Integration
→ HTTP Runtime
→ GO FINAL
→ Gel
```

Un jalon ne s'ouvre qu'après GO du précédent et satisfaction des gates qui le
précèdent. Toute incompatibilité produit un amendement versionné; elle
n'autorise aucune adaptation opportuniste.

La Final Certification ne crée aucun composant. Son dossier consolide la
baseline certifiée `2 733 / 2 733`, les 58 capacités Runtime Healthy et les
frontières gelables de la capacité. Le gel devient effectif exclusivement
après le prononcé officiel du GO FINAL.
