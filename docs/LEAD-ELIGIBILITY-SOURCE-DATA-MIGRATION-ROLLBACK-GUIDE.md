# Lead Eligibility Source Data Migration and Rollback Guide

La migration additive `023_lead_eligibility_source_data.sql` crée uniquement le journal, ses contraintes et ses index dans `contacts_leads`.

Le rollback `023_lead_eligibility_source_data.down.sql` supprime uniquement `contacts_leads.lead_eligibility_decisions`. Il ne supprime ni le schéma, ni le journal Lead Lifecycle 4.4B.

Le rollback détruit l'historique d'éligibilité et exige donc une sauvegarde opérationnelle préalable. La réapplication recrée une structure vide conforme.
