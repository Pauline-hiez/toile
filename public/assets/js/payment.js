document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('payment-form');
    if (!form) return;

    const stripePublicKey = form.dataset.stripePublicKey;
    const clientSecret = form.dataset.clientSecret;
    const customerSessionClientSecret = form.dataset.customerSessionClientSecret;
    const totalPrice = form.dataset.totalPrice;
    const returnUrl = form.dataset.returnUrl || (window.location.origin + '/commander/confirm');

    const stripe = Stripe(stripePublicKey);

    const elements = stripe.elements({
        appearance: { theme: 'stripe' },
        clientSecret: clientSecret,
        customerSessionClientSecret: customerSessionClientSecret,
    });

    // La CustomerSession (payment_method_redisplay) fait afficher nativement
    // les cartes déjà enregistrées comme options sélectionnables ici.
    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');
    paymentElement.on('ready', () => {
        document.getElementById('submit-btn').style.display = 'block';
    });

    document.getElementById('submit-btn').addEventListener('click', async () => {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.textContent = 'Traitement en cours...';

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: returnUrl,
                // Si carte enregistrée utilisée, Stripe la détecte automatiquement
                // via le customer attaché au PaymentIntent.
            },
        });

        if (error) {
            document.getElementById('payment-errors').textContent = error.message;
            btn.disabled = false;
            btn.textContent = 'Réessayer — ' + totalPrice + ' €';
        }
    });
});