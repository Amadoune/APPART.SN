# Professional Status Migration and Rollback Guide

Migration additive : `027_professional_status_workflow.sql`.

Rollback : `027_professional_status_workflow.down.sql` supprime uniquement la table `professionals.professional_status_transitions`. Le schéma et toutes les fondations antérieures restent intacts.

Le repository rejoint une transaction externe lorsqu'elle existe. Sinon, il ouvre et valide sa transaction locale. Toute exception provoque le rollback de la transaction locale ; une transaction externe demeure sous le contrôle de son propriétaire.
