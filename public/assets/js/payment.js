document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('payment-form');

    if (!form) return;

    const stripePublicKey = form.dataset.stripePublicKey;
    const clientSecret = form.dataset.clientSecret;
    const totalPrice = form.dataset.totalPrice;
    // URL de retour configurable via data-return-url,
    // avec fallback vers /commander/confirm.
    const returnUrl = form.dataset.returnUrl || (window.location.origin + '/commander/confirm');

    const stripe = Stripe(stripePublicKey);

    const elements = stripe.elements({
        appearance: { theme: 'stripe' },
        clientSecret: clientSecret,
    });

    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');

    paymentElement.on('ready', () => {
        const btn = document.getElementById('submit-btn');
        btn.style.display = 'block';
    });

    document.getElementById('submit-btn').addEventListener('click', async () => {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.textContent = 'Traitement en cours...';

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: returnUrl,
            },
        });

        if (error) {
            document.getElementById('payment-errors').textContent = error.message;
            btn.disabled = false;
            btn.textContent = 'Autoriser le paiement — ' + totalPrice + ' €';
        }
    });
});