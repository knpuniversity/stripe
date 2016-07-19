# Charge It (The Stripe PHP SDK)

Stripe has a nice, RESTful API, and you're going to spend a lot of time talking with
it. Google for "Stripe API docs" to find this amazing page. You can set this as your
new homepage: it describes *every* endpoint: how to create charges, customers and
a lot of other things we're going to talk about.

But first, make sure that you select PHP on the top right. Thanks to this, the docs
will show you code snippets in PHP. And those code snippets will use Stripe's PHP
SDK library. Google for that and open its [Github Page](https://github.com/stripe/stripe-php).

First, let's get this guy installed.. Copy the composer require line, move over to
your terminal, open a new tab and paste that:

```bash
composer require stripe/stripe-php
```

While we're waiting for Jordi to finish, let's keep going.

## Using the Token to Create a Charge

To actually charge a user, we need to... well, create a Stripe *charge*. In the Stripe
API, click "Charges" on the left and find [Create a Charge](https://stripe.com/docs/api#create_charge).

Hey! It wrote the code *for* us. Copy the code block on the right. Now, go back
to `OrderController` and first, create a new `$token` variable and set it to the
`stripeToken` POST parameter. Now, paste that code.

Let's go check on Composer. It's *just* finishing - perfect! My editor now sees
all these new Stripe classes.

See that API key? Once again, this is a real key from *our* account in the test environment.
This time, it's the *secret* key. The public key is the one in our template.

Update charge details with our *real* information. To get the total, I'll fetch a
service I created for the project called `shopping_cart`, call `getTotal()` on this,
and then multiply it by 100.

For `source`, replace this fake token with the *submitted* `$token` variable. The
token basically represents the credit card that was just sent. We're saying: Use
*this* card as the source for this charge. And then, put whatever you want for
description, like "first test charge".

When this code runs, it will make an API request to Stripe. If that's successful,
the user will be charged. If something goes wrong, Stripe will throw an Exception.
More on that later.

## Cleaning up after Checkout

But before we try it, we need to finish up a few application-specific things. For
example, after check out, we need to empty the shopping cart. The products are great,
but they probably won't want to buy them twice in a row.

Next, I want to show a success message to the user. To do that in Symfony, call
`$this->addFlash('success', 'Order Complete! yay!')`.

And finally, you should *definitely* redirect the page somewhere. I'll use
`redirectToRoute()` to send the user to the homepage.

That is it. Now for the *real* moment of truth. Hit enter to reload our page without
submitting, put in the fake credit card, any date, any CVC, and...

Hey! Okay. No errors. That *should* mean it worked. How can we know? Go check out
the Stripe Dashboard. This time, click "Payments". And there's our payment for `$62`.
You can even see all the information that was used.

Congratulations! You just added a checkout to your site in 15 minutes. Now let's
make this things rock-solid.
