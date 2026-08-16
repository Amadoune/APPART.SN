# Revision vector evidence

Chaque Place contribue `(placeId,aggregateVersion)` dans l'ordre root→leaf. Les versions sont positives et la somme est protégée contre overflow. Le checksum couvre le vecteur complet. RC2 produit `[1,1,1]` et watermark 3.
