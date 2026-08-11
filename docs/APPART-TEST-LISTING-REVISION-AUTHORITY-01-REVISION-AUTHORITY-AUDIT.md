# Listing Revision Authority 01 — Audit

Listing Lifecycle est l'owner exclusif. Le draft reçoit sa révision du command de création. Submit possède un intent authoring stable ; BeginReview et ApproveAndPublish possèdent un commandId de modération stable. Ces identités UUID sont disponibles avant mutation et supportent une dérivation owner-scoped déterministe.

HTTP, UI, Moderation et Media ne créent aucune révision Listing : ils fournissent seulement l'intent autoritatif au port Listing Lifecycle.
