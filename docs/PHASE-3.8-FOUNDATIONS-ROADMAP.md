# Phase 3.8 — Runtime Source Prerequisites

## 3.8A — Media Ownership Lookup Foundation

**Statut : GO — approuvé le 19 juillet 2026.**

Port applicatif, résolutions `Found/Missing/Ambiguous`, adaptateur PostgreSQL borné et index ciblé sur la relation Property déjà persistée.

## 3.8B — Search Decision Read Foundation

**Statut : GO — approuvé le 19 juillet 2026.**

Reader et writer spécialisés des décisions Search finales, persistance PostgreSQL dédiée, intégrité par checksum et lecture explicite `Found/Missing/Corrupted`.

## 3.8C — Content/SEO Decision Read Foundation

**Statut : GO — approuvé le 19 juillet 2026.**

Option B Source Snapshot, persistance durable des sources SEO déjà décidées, reader explicite et intégrité par checksum.

## 3.8D — Public Geography Durable Source

**Statut : GO — approuvé le 19 juillet 2026.**

Persistance atomique du contenu Geography et de sa révision 3.7A, reader de production et intégrité par checksum.

## 3.8E — Public Media Durable Source

**Statut : GO — approuvé le 19 juillet 2026.**

Persistance atomique du contenu Media public et de sa révision 3.7B, reader de production, écriture monotone et intégrité par checksum.

## 3.8F — Active Generation Reader Foundation

**Statut : GO — approuvé le 19 juillet 2026.**

Port spécialisé et reader PostgreSQL en lecture seule de l'unique génération Active, sans migration ni logique de transition.

## 3.8G — Deterministic Decision Time Foundation

**Statut : GO — approuvé le 19 juillet 2026.**

Ownership Content/SEO explicite et reader spécialisé de la valeur `decisionAt` déjà certifiée dans le snapshot durable 3.8C, sans nouvelle persistance ni horloge Runtime.

## Porte de sortie

Les fondations 3.8A à 3.8G sont certifiées. Les prérequis identifiés lors du premier NO GO de 3.7C sont levés.

La reprise de 3.7C est autorisée.
