# Validate that Coupon in Stripe!

The coupon form will submit its `code` field right here. To fetch that POST parameter,
add the `Request` object as an argument. Then add `$code = $request->request->get('code')`.

And in case some curious user submits while the form is empty, send back a validation
message with `$this->addFlash('error', 'Missing coupon code')`. Redirect to the checkout
page.

## Fetching the Coupon Information

Great! At this point, all *we* need to do is talk to Stripe and ask them:

> Hey Stripe! Is this a valid coupon code in your system?. Oh, and if it is,
> how much is it for?

Since `Coupon` is just an object in Stripe's API, we can fetch it like *anything*
else. Booya!

As usual, add the API call in `StripeClient`. At the bottom, create a new public
function called `findCoupon()` with a `$code` argument. Then, return
`\Stripe\Coupon::retrieve()` and pass it the `$code` string, which is the Coupon's
primary key in Stripe's API.

Back in `OrderController`, add `$stripeCoupon = $this->get('stripe_client')` and
then call `->findCoupon($code)`.

If the code is invalid, Stripe will throw an exception. We'll handle that in a few
minutes. But just for now, let's `dump($stripeCoupon)` and `die` to see what it
looks like.

Ok, refresh, hit "I have a coupon code," fill in our `CHEAP_SHEEP` code, and submit!

There it is! In the `_values` section where the data hides, the coupon has an `id`,
it shows the `amount_off` in cents and has a few other things, like `duration`, in
case you want to create coupons that are recurring and need to tell the user that
this will be applied multiple times.

Now that we know the coupon is legit, we should add it to our cart. I've already
prepped the cart to be able to store coupons. Just use
`$this->get('shopping_cart')` and then call `->setCouponCode()`, passing it the
`$code` string and the amount off, in dollars: so `$stripeCoupon->amount_off/100`.
The cart will *remember* - via the session - that the user has this coupon.

We're *just* about done: add a *sweet* flash message - "Coupon applied!" - and then
redirect back to the checkout page.

## Showing the Code on Checkout

Refresh and re-POST the form! Coupon applied! Except... I don't see *any* difference:
the total is *still* $99.

Here's why: it's specific to our `ShoppingCart` object. In `checkout.html.twig`,
we print `cart.total`. I designed the `ShoppingCart` class so that the `getTotal()`
method adds up *all* of the product prices plus the subscription total. But, *this*
method doesn't subtract the coupon discount. I did this to keep things clean: total
is really more like a "sub-total".

But no worries, the method below this - `getTotalWithDiscount()` - subtracts the
coupon code. So back in the template, use `cart.totalWithDiscount`.

Ah, *now* it shows $49.

But, it'll be even *clearer* if we display the discount in the table. At the bottom
of that table, add a new if statement: `if cart.couponCode` and an `endif`. Then, copy
the subscription block from above, paste it here, and change the first variable to
`cart.couponCode` and the second to `cart.couponCodeValue` without the `/ month`,
unless you want to make all your coupons recurring. Oh, and add "Coupon" in front
of the code.

This time, the whole page makes sense! $99 - $50 = $49. It's a miracle!

Now for the easy step: apply the coupon to the user's order at checkout... ya know,
so that they *actually* save $50.
