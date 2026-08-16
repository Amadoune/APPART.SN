# Progress recovery

Aucun high-watermark durable supplémentaire. Après crash, l'événement non consommé est rejoué depuis le début; lookup paginé et writer idempotent rendent ce choix sûr.

La mémoire reste bornée par page. Un futur index ou checkpoint est une optimisation mesurée, pas une exigence fonctionnelle V1.
