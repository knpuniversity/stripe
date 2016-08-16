# Charge It (The Stripe PHP SDK)

Stripe has a nice, RESTful API, and you're going to spend a lot of time talking with
it. Google for "Stripe API docs" to find this amazing page. You can set this as your
new homepage: it describes *every* endpoint: how to create charges, customers and
a lot of other things we're going to talk about.

But first, make sure that you select PHP on the top right. Thanks to this, the docs
will show you code snippets in PHP. And those code snippets will use Stripe's PHP
SDK library. Google for that and open its [Github Page][php_sdk].

First, let's get this guy installed. Copy the composer require line, move over to
your terminal, open a new tab and paste that:

```bash
composer require stripe/stripe-php
```

While we're waiting for Jordi to finish, let's keep going.

## Using the Token to Create a Charge

To actually charge a user, we need to... well, create a Stripe *charge*. In the Stripe
API, click "Charges" on the left and find [Create a Charge][create_charge].

Hey! It wrote the code *for* us. Copy the code block on the right. Now, go back
to `OrderController` and first, create a new `$token` variable and set it to the
`stripeToken` POST parameter. Now, paste that code:

[[[ code('0dc4eaa17e') ]]]

Let's go check on Composer. It's *just* finishing - perfect! My editor now sees
all these new Stripe classes.

See that API key?

[[[ code('6c866048fa') ]]]

Once again, this is a real key from *our* account in the test environment. This time,
it's the *secret* key. The public key is the one in our template:

[[[ code('34e14ca59f') ]]]

Update charge details with our *real* information. To get the total, I'll fetch a
service I created for the project called `shopping_cart`, call `getTotal()` on this,
and then multiply it by 100:

[[[ code('f1371fa502') ]]]

For `source`, replace this fake token with the *submitted* `$token` variable:

[[[ code('18c34bf96e') ]]]

The token basically represents the credit card that was just sent. We're saying:
Use *this* card as the source for this charge. And then, put whatever you want for
description, like "First test charge":

[[[ code('82c0a14a74') ]]]

When this code runs, it will make an API request to Stripe. If that's successful,
the user will be charged. If something goes wrong, Stripe will throw an `Exception`.
More on that later.

## Cleaning up after Checkout

But before we try it, we need to finish up a few application-specific things. For
example, after check out, we need to empty the shopping cart. The products are great,
but the customer probably doesn't want to buy them twice in a row:

[[[ code('2bdfa133d5') ]]]

Next, I want to show a success message to the user. To do that in Symfony, call
`$this->addFlash('success', 'Order Complete! Yay!')`:

[[[ code('50f280b3a5') ]]]

And finally, you should *definitely* redirect the page somewhere. I'll use
`redirectToRoute()` to send the user to the homepage.

That is it. Now for the *real* moment of truth. Hit enter to reload our page without
submitting, put in the fake credit card, any date, any CVC, and...

***TIP
If you get some sort of API or connection, you may need to [upgrade some TLS security settings][upgrade_tls].
***

Hey! Okay. No errors. That *should* mean it worked. How can we know? Go check out
the Stripe Dashboard. This time, click "Payments". And there's our payment for `$62`.
You can even see all the information that was used.

Congratulations guys! You just added a checkout to your site in 15 minutes. Now let's
make this thing rock-solid.


[php_sdk]: https://github.com/stripe/stripe-php
[create_charge]: https://stripe.com/docs/api#create_charge
[upgrade_tls]: https://support.stripe.com/questions/how-do-i-upgrade-my-stripe-integration-from-tls-1-0-to-tls-1-2#php
