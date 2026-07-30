# Rebuild Runtime Candidate Factory

La factory appelle `InspectablePublicListingProjectionSource::inspect()` puis exige :

- un assemblage `Found` ;
- un watermark présent ;
- une `PromotionReadiness::Ready` ;
- une Search projection et une SEO projection produites par les composants certifiés ;
- un ReadModel cohérent.

Elle remplace uniquement la génération Active de la source par l'identité Candidate fournie au contrat de rebuild. Le contenu, la canonical et le watermark restent ceux issus des composants certifiés.

Une absence, corruption ou readiness incomplète ne peut jamais produire une Candidate prétendument valide.
