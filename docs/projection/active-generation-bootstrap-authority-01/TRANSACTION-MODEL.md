# Transaction Model

Create et Activate sont deux transactions owner-locales Public Projection distinctes. Rebuild écrit idempotemment entre elles dans la Candidate. Aucune transaction distribuée ne couvre les sources. Un crash laisse soit rien, soit une Candidate reprenable, soit une Active atomiquement visible; jamais une bascule partielle.
