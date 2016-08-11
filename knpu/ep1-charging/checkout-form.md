# Embedded Checkout Form

Click on the documentation link at the top, and then click [Embedded Form][1].
There are two ways to build a checkout-form: the easy & lazy way - via an *embedded form*
that Stripe builds for you - or the harder way - with an HTML form that you build
yourself. Our sheep investors want us to hoof-it and get this live, so let's do
the easy way first - and switch to a custom form later.

## Getting the Form Script Code

To get the embedded form, copy the form code. Then, head to the app and open the
`app/Resources/views/order/checkout.html.twig` file. This is the template for the
checkout page.

At the bottom, I already have a spot waiting for the checkout form. Paste the code
there:

[[[ code('4101d72520') ]]]

Oh! And as promised: this `pk_test` value is the *public* key from *our* test environment.

## Your Stripe Public and Private Keys

Let me show you what I mean: in Stripe, open your "Account Settings" on the top and
then select "API Keys". Each environment - Test and Live - have their own two keys:
the secret key and the publishable or public key. Right now, we're using the public
key for the test environment - so once we get this going, orders will show up there.
After we deploy, we'll need to switch to the Live environment keys.

Oh, and, I think it's obvious - but these secret keys need to be kept secret. The
*last* thing you should do is create a screencast and publish them to the web. Oh
crap.

## Hey, A Checkout Form

But anyways,  without doing any more work, go back to the browser and refresh the
page. Hello checkout button! And hello checkout form! Obviously, $9.99 isn't the
right price, for these amazing sheep accessories.

To fix that, head back to the template. Everything about the form is controlled with
these HTML attributes. Obviously, the most important one is `amount`. Set it to
`{{ cart.total }}` - `cart` is a variable I've passed into the template - then
the important part: `* 100`:

[[[ code('49d5c5faf7') ]]]

Whenever you talk about *amounts* in Stripe, you use the *smallest* denomination
of the currency, so cents in USD. If you need to charge the user $5, then tell Stripe
to charge them an amount of `500` cents.

Then, fill in anything else that's important to you, for example, `data-image`. I'll
set this to our logo:

[[[ code('7dc4538d24') ]]]

## Checking out with a Test Card

Refresh to reflect the new settings. The total should be $62, and it is! Because
we're using the test environment, Stripe gives us fake, test cards we can use to
checkout. I'll show you others later - but to checkout successfully, use
`4242 4242 4242 4242`. You can use any valid future expiration and any CVC.

Ok, moment of truth: hit pay!

It worked! I think... Wait, what just happened? Well, a *really* important step
just happened - a step that's *core* to how Stripe checkout works.

## The Stripe Checkout Token

First, credit card information is *never* sent to our servers... which is the *greatest*
news ever from a security standpoint. I do *not* want to handle your CC number:
this would *greatly* increase the security requirements on my server.

Instead, when you hit "Pay", this sends the credit card information to *Stripe* directly,
via AJAX. If the card is valid, it sends back a token string, which *represents*
that card. The Stripe JS puts that token into the form as an hidden input field and
then submits the form like normal to our server. So the *only* thing that's sent
to our server is this token. The customer has *not* been charged yet, but with a
little more work - we can fetch that token in our code and ask Stripe to charge that
credit card.

## Fetching the Stripe Token

Let's go get that token on the server. Open up `src/AppBundle/Controller/OrderController.php`
and find `checkoutAction()`:

[[[ code('8f5cbb7fe9') ]]]

This controller renders the checkout page. And because the HTML form has `action=""`:

[[[ code('f89f39624f') ]]]

When Stripe submits the form, it submits *right* back to this same URL and controller.

To fetch the token, add a `Request` argument, and make sure you have the `use` statement
on top:

[[[ code('35a19b6b91') ]]]

Then, inside the method, say `if ($request->isMethod('POST')`, then we know
the form was just submitted. If so, `dump($request->get('stripeToken'))`:

[[[ code('88bc0ae669') ]]]

If you read Stripe's documentation, that's the `name` of the hidden input field.

Try it out! Refresh and fill in the info again: use your trusty fake credit card number,
some fake data and Pay. The form submits and the page refreshes. But thanks to the
`dump()` function, hover over the target icon in the web debug toolbar. Perfect! We
were able to fetch the token.

In a second, we're going to send this back to Stripe and ask them to actually charge
the credit card it represents. But before we do that, head back to the Stripe dashboard.

Stripe shows you a log of pretty much *everything* that happens. Click the Logs link:
these are *all* the interactions we've had with Stripe's API, including a few from
before I hit record on the screencast. Click the first one: this is the AJAX request
that the JavaScript made to Stripe: it sent over the credit card information, and
Stripe sent back the token. If I search for the token that was just dumped, it matches.

Ok, let's use that token to charge our customer.


[1]: https://stripe.com/docs/checkout/tutorial
