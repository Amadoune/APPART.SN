# Lead Eligibility Source Data Runtime Health Decision

Le port `LeadEligibilityDecisionMaterializer` constitue une nouvelle capacité de production et est ajouté explicitement à Runtime Health.

Runtime Health passe de 29 à **30 capacités**. L'inspection vérifie uniquement binding, compatibilité et constructibilité. Elle n'appelle jamais `materialize()`, ne construit aucun lot, n'effectue aucune lecture et n'ouvre aucune transaction.

Le reader interne n'est pas une capacité Runtime indépendante à ce stade ; il sera consommé par les adaptateurs certifiés en 4.4C-S1.
