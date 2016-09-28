# Free (Ice Cream) Checkout!

Go create a *huge* coupon - like for $500. Call this one `SUPER_SHEEP`!

Ok, this coupon is awesome - so let's add it to an order.

Perfect! As you can see, the cart is *already* smart enough to return the total
as $0, instead of a negative number. Way to go cart!

But, uh, what's this checkout form still doing over here? Why should I need to enter
my credit card info? This order is *free*!

Look, how you handle *free* orders is up to you. If you *still* want to require a
credit card, you can do that. The customer won't be charged, but the card will be
on file for renewals.

But that's not for me: I want to make it as easy as possible for this user to checkout
free.

## Hide the Checkout Form

The first step, is to hide this checkout form! In `checkout.html.twig`, find the
`_cardForm.html.twig` include, and wrap it in `if cart.totalWithDiscount > 0`:

[[[ code('a5cf2ee05f') ]]]

Else, create a really simple form that submits right back to this URL. Give it
`method="POST"` and a submit button that invites the user to "Checkout for Free":

[[[ code('5631e20cac') ]]]

If you refresh now, boom! Checkout form gone.

## Handling Free Checkout

But that's not *quite* everything we need to do. Thanks to the discount, Stripe
will already know that it doesn't need to charge the user, and so, the customer
doesn't need to have a card. But, the funny thing is, up until now, our `OrderController`
logic is expecting a `stripeToken` to *always* be submitted:

[[[ code('dadf05afb4') ]]]

Remember that's the token that we get back from Stripe, after submitting the
credit card information to them. We then pass that to `chargeCustomer()` and attach
it to the Customer:

[[[ code('917a38d36e') ]]]

## Optionally Apply the Stripe Token

But now, our code needs to be smart enough to *not* try to attach the token to the
Customer for free orders. At the top, add a *sanity* check: if there is *no* token,
and the shopping cart's total with discount is *not* free... well, we have a problem!
Throw a clear exception: the order is non-free... but we're missing the payment token!

[[[ code('07509ab60c') ]]]

Next, when we call `createCustomer()`, we pass in the `$token`. Open `StripeClient`
and find that method:

[[[ code('e8394aaaae') ]]]

Hmm. Now, `$paymentToken` might be blank. But Stripe will be *really* angry if we
try to attach an empty *source* to the Customer. Instead of doing this all at once,
add a new `$data` array variable, and move the `email` key into it. Then, pass
`$data` to the `create()` call:

[[[ code('0d272658da') ]]]

You know what's next: if `$paymentToken` is not blank, add a `source` key to
`$data` set to `$paymentToken`:

[[[ code('d49319adb0') ]]]

We're done here.

But we have the same problem in `updateCustomerCard()`. Let's fix this here:
if `$stripeToken`, then update the customer's card:

[[[ code('d171926a05') ]]]

Else, we *do* need to fetch the `\Stripe\Customer` object - we use it below.
In `StripeClient`, add a new method to do this: `public function findCustomer()`
with a `User` argument:

[[[ code('b0bb31f6a6') ]]]

Then, return the timeless `\Stripe\Customer::retrieve($user->getStripeCustomerId())`:

[[[ code('b2bdffbaeb') ]]]

In the controller, call that: `$stripeCustomer = $stripeClient->findCustomer($user)`:

[[[ code('8ca7737336') ]]]

Ok, I'm feeling good! The last trouble spot is in `updateCardDetails()`. Open
`SubscriptionHelper`:

[[[ code('5a7c6e77eb') ]]]

Oh yea - this method looks at the `sources` key on the Customer to get the card brand
and last four digits. In our app, every Customer has exactly *one* card, so we use the `0`
key. But guess what! Not anymore: a customer *might* have zero cards.

So we just need to code defensively: add an if statement: if `!$stripeCustomer->sources->data`,
just return: there's no card details to update:

[[[ code('7563dc80c5') ]]]

Ok, we're done! A free checkout and a normal checkout are *almost* the same. The only
difference is that you *don't* have a Stripe token, so you can't attach that to your
Customer.

Refresh the checkout page and, "Checkout for Free".

It works! In Stripe, find our Customer. There is no new payment, but there *is* an
Invoice for $0 and an active subscription. The Invoice shows off the discount.

## No Card? Webhook Problems

Thanks to this change, it's now possible for a Customer to *not* have *any* cards
attached in Stripe. And yea know what? This creates a new problem in a *totally*
unrelated part of the process: webhooks.

But, it's no big deal. Open `WebhookController` and find the `invoice.payment_failed`
section. Wait! Woh! Before that - oh geez - fix my *horrible* typo: `invoice.payment_succeeded`:

[[[ code('c50d98ad07') ]]]

This is why you must *test* your webhooks!

Anyways, back to `invoice.payment_failed`. Our *entire* reason for handling this
webhook is so that we can send the user an email to tell them that we're having a
problem charging their card. We didn't actually do the work, but that email would
probably sound like this:

> Hey friend! So, we're having a problem charging your card. If you need to update
> it, go to your account page and add the new details there.

But what will happen if a user checks out for free, but with a subscription? After
their first month, Stripe will not be able to charge them, and it will trigger *this*
webhook.

In those cases, the email should *really* have some different text, like:

> Yo amigo! I hope you enjoyed your free month. If you want to continue,
> you can add a credit card to your account page.

So to know *which* language to use, first fetch the Stripe Customer by saying
`$this->get('stripe_client')->findCustomer($user)`:

[[[ code('58eecc41a9') ]]]

Now we can create a new variable, called `$hasCardOnFile`. Set that to a count
of `$stripeCustomer->sources->data` and check if it's greater than zero:

[[[ code('74aa483b22') ]]]

Now, you can use that variable to write the most uplifting, majestic, and encouraging
payment failed emails that the world has ever seen.
