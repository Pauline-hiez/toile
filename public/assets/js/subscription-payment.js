document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('subscription-payment-form');

    if (!form) return;

    const stripePublicKey = form.dataset.stripePublicKey;
    const clientSecret = form.dataset.clientSecret;

    const stripe = Stripe(stripePublicKey);

    const elements = stripe.elements({
        appearance: { theme: 'stripe' },
        clientSecret: clientSecret,
    });

    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');

    paymentElement.on('ready', () => {
        document.getElementById('submit-btn').style.display = 'block';
    });

    document.getElementById('submit-btn').addEventListener('click', async () => {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.textContent = 'Traitement en cours...';

        // On utilise confirmSetup pour un SetupIntent (collecte de carte)
        // plutôt que confirmPayment (débit immédiat).
        const { error } = await stripe.confirmSetup({
            elements,
            confirmParams: {
                return_url: window.location.origin + '/my-subscription/confirm',
            },
        });

        if (error) {
            document.getElementById('payment-errors').textContent = error.message;
            btn.disabled = false;
            btn.textContent = 'Confirmer l\'abonnement';
        }
    });
});