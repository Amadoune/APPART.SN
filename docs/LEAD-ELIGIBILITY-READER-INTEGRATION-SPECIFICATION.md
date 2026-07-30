# Lead Eligibility Reader Integration Specification

Les deux adaptateurs dépendent exclusivement de `LeadEligibilitySourceDataReader`. Le provider relie ce reader au `PostgreSqlLeadEligibilityDecisionStore` certifié en 4.4C-S2.

Le reader n'est jamais appelé au bootstrap ou pendant Runtime Health. Chaque appel de catalogue effectue exactement une lecture courante par Listing. Aucun historique, Aggregate, Repository Listing, `actor_id` ou port de matérialisation n'est accessible depuis les adaptateurs.
