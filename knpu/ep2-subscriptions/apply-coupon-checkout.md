# Applying a Coupon at Checkout

There are two ways to use a coupon on checkout: either attach it to the *subscription*
to say "This subscription should have this coupon code" - *or* - attach it to the
*customer*. They're approximately the same, but we'll attach the coupon to the customer,
in part, because the coupon should *also* work on individual products.

In `OrderController`, scroll down to the `chargeCustomer()` method:

[[[ code('6ad2513c63') ]]]

We know this method: we get or create the Stripe Customer, create InvoiceItems
for any products, create the Subscription, and then create an invoice, if needed.

Before adding the invoice items, let's add the coupon to the Customer. So, if
`$cart->getCouponCodeValue()`, then very simply,
`$stripeCustomer->coupon = $cart->getCouponCode()`. Make it official with `$customer->save()`:

[[[ code('3af4499702') ]]]

The important thing is that *you* don't need to change how much you're charging the
user: attach the coupon, charge them for the full amount, and let Stripe figure
it all out.

I think we should try this out! Use our favorite fake credit card, and Checkout!
So far so good!

Find the Customer in Stripe. Yep! There's the order: $49. The invoice tells the
*whole* story: with the sub-total, the discount and the total.

Very, very, nice.

## Handling Invalid Coupons

And very easy! So easy, that we have time to add code to handle *invalid* coupons.
Add another item to your cart. Now, try a FAKE coupon code.

Ah! 500 error is *no* fun. The exception is a `\Stripe\Error\InvalidRequest` because,
basically, the API responds with a 404 status code.

This all falls apart in `OrderController` on line 95. Hunt that down!

Ah, `findCoupon()`: surround this beast with a try-catch block for `\Stripe\Error\InvalidRequest`:

[[[ code('9639133e26') ]]]

The easiest thing to do is add a flash error message: `Invalid Coupon code`. Then,
redirect back to the checkout page:

[[[ code('93dfe3b049') ]]]

Refresh that bad coupon! Ok! That's covered!

## Expired Coupons

There's just *one* other situation to handle. In Stripe, find the Coupon section
and create a second code. Set the amount to $50, duration "once" and the code:
`SINGLE_USE`. By here's the kicker: set Max redemptions to 1. So, only *one* customer
should be able to use this. There's also a time-sensitive "Redeem by" option. 

Quickly, go use the `SINGLE_USE` code and fill out the form to checkout. This will
be the first - and only - allowed "redemption" of this code. When you refresh the
Coupon page in Stripe, Redemptions are 1/1.

Now, add another subscription to your cart. If you tried to use the code a *second*
time, our system *would* allow this. And that makes sense: *all* we're doing now
is looking up the code in Stripe to make sure it *exists*.

But, if we tried to checkout, Stripe would be *pissed*: it would *not* allow us
to use the code a second time. Stripe has our back.

But, we should *definitely* prevent the code from being attached to the cart in
the first place. Checkout the Coupon section of Stripe's API docs. Ah, this `valid`
field is the *key*. This field basically answers this question:

> In this moment, can this coupon be used?

Brilliant! Back in `OrderController::addCouponAction()`, add an if statement: if
`!$stripeCoupon->valid`, then, just like in the catch, add an error flash - "Coupon expired" -
and redirect over to the checkout page:

[[[ code('60184248d7') ]]]

Try it again. Awesome, this time, we get blocked.

If you want to be *extra* careful, you could add some try-catch logic to your checkout
code *just* to prevent the edge-case where the code becomes *invalid* between
the time of adding it to your cart and checking out. But either way, Stripe will *never*
allow an invalid coupon to be used.
