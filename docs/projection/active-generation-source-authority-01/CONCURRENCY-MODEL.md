# Concurrency Model

Les transitions verrouillent dans l'ordre déterministe `generation_id`. L'index unique garantit au plus une Active. Deux activations concurrentes sérialisent leur bascule; chacune peut aboutir selon l'ordre durable, la dernière Active supplantant la précédente. Le Reader observe sous MVCC un état avant ou après bascule, jamais une sélection arbitraire.
