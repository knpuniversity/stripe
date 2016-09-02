# Add the Subscription to your Cart

We can already add products to our cart... but a user should *also* be able to click
these fancy buttons and add a subscription to their cart.

Open up `OrderController`: the home for the checkout and shopping cart magic. I've
already started a new page called `addSubscriptionToCartAction()`:

[[[ code('7a5cd48c3e') ]]]

When we're done, if the user goes to `/cart/subscription/farmer_brent_monthly`,
this should put that into the cart.

First, hook up the buttons to point here. The template for this page lives at
`app/Resources/views/product/pricing.html.twig`:

[[[ code('c50efc2363') ]]]

I started adding the link to this page, but left the plan ID blank. Fill 'em in!
`farmer_brent_monthly` and then down below, `new_zealander_monthly`:

[[[ code('ab790e77e9') ]]]

Go back to that page and refresh. The links look great!

## Put the Plan in the Cart

Now back to the controller! In the first Stripe tutorial, we worked with a `ShoppingCart`
class that I created for us... because it's not really that important. It basically
allows you to store products and a subscription in the user's session, so that as
they surf around, we know what they have in their cart.

But before we use that, first get an instance of our new `SubscriptionHelper` object
with `$subscriptionHelper = $this->get('subscription_helper')`:

[[[ code('9ac40b8b5f') ]]]

I already registered this as a service in Symfony:

[[[ code('51ce2c23c8') ]]]

Next, add `$plan = $subscriptionHelper->findPlan()` and pass it the `$planId`:

[[[ code('afd913e120') ]]]

So this is nice: we give it the plan ID, and it gives us the corresponding, wonderful,
`SubscriptionPlan` object:

[[[ code('6e910a22db') ]]]

But if the `$planId` doesn't exist for some reason,  throw `$this->createNotFoundException()`
to cause a 404 page:

[[[ code('145c8874ab') ]]]

Finally, add the plan to the cart, with `$this->get('shopping_cart')->addSubscription()`
and pass it the plan ID:

[[[ code('9941b85ec8') ]]]

And boom! Our cart knows about the subscription! Finally, send them to the checkout
page with `return $this->redirectToRoute('order_checkout')` - that's the name of
our `checkoutAction` route:

[[[ code('d25752b15a') ]]]

## Adding the Subscription on the Checkout Page

Okay team, give it a try! Add the Farmer Brent plan. Bah! We need to login: use
the pre-filled email and the password used by all sheep: `breakingbaad`.

Ok, this looks kinda right: the total is `$99` because the `ShoppingCart` object
knows about the subscription... but we haven't printed anything about the subscription
in the cart table. So it looks weird.

Let's get this looking right: open the `order/checkout.html.twig` template and scroll
down to the checkout table. We loop over the products and show the total, but never
print anything about the subscription. Add a new `if` near the bottom: `if cart` - 
which is the `ShoppingCart` object - `if cart.subscriptionPlan` - which will be
a `SubscriptionPlan` object or `null`:

[[[ code('ac0c21eade') ]]]

Then copy the `<tr>` from above and paste it here. Print out `cart.subscriptionPlan.name`:

[[[ code('6d56ab5d80') ]]]

That's why having this `SubscriptionPlan` object with all of those fields is really handy.
Below, use `cart.subscriptionPlan.price` and add `/ month`. And, whoops - I meant
to use `name` on the first part, not price:

[[[ code('58f866817c') ]]]

Let's give it a try now. It looks great! The plans are in Stripe, the plans are
in our code, and you can add a plan to the cart. Time to checkout and create our
first subscription.
