# Phase 4.7A — Workflow Decision Table

La certification couvre les 20 couples état/action avec quatre contextes valides :

- DirectRecording + Present ;
- DirectRecording + Missing ;
- IndependentApprovalRequired + Present ;
- IndependentApprovalRequired + Missing.

Soit 80 décisions explicitement testées. Chaque décision autorisée contient exactement une transition. Chaque décision refusée contient exactement un diagnostic.

Le Workflow ne possède aucune branche `default`, aucune horloge, aucune identité implicite, aucune lecture externe et aucun effet.
