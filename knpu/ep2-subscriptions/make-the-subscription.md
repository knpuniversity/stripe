# Creating the Subscription in Stripe

Open up the Stripe docs and go down the page until you find subscriptions. There's
a nice little "Getting Started" section but the detailed guide is the place to go
if you've got serious questions.

But let's start with the basics! Step 1... done! Step 2: subscribing your customers.
Apparently all we need to do is set the plan on the Customer and save! Cool!

## The Players: Subscription, Customer, Invoice

But actually, there's more going on behind the scenes. In reality, this will create
a new object in Stripe: a `Subscription`. And actually, we're going to subscribe
a user with slightly different code than this.

Keep reading below, the docs describe the *lifecycle* of a Subscription. For now,
there's one *really* important thing to notice: when you create a Subscription,
Stripe automatically creates an `Invoice` and charges that invoice immediately.

Open up the Stripe API docs so we can look at all the important objects so far.
From part 1 of the tutorial, when someone buys individual products, we do a few things:
we create or fetch a `Customer`, we create an `InvoiceItem` for each product and
finally we create an `Invoice` and pay it. When you create an `Invoice`, Stripe
automatically adds all unpaid invoice items to it.

With a `Subscription`, there are two new players: Plans and Subscriptions. Click
"Subscriptions" and go down to "Create a Subscription". Ah, so simple: a `Subscription`
is between a Customer and a specific Plan. This is the code we will use.

## Coding up the Stripe Subscription

Back on our site, after we fill out the checkout form, the whole thing submits to
`OrderController::checkoutAction()`. And *this* passes the submitted Stripe token
to `chargeCustomer()`:

[[[ code('258e8ea655') ]]]

Ah that's where the magic happens: it creates or gets the `Customer`, adds InvoiceItems
and creates the `Invoice`:

[[[ code('12a3a7a852') ]]]

Beautiful.

All *we* need to do is create a `Subscription` - via Stripe's API - if they have a plan
in their cart. Before we create the `Invoice`, add `if $cart->getSubscriptionPlan()`:

[[[ code('bb281cb2e9') ]]]

Next, open `StripeClient`: we've designed this class to hold all Stripe API setup
and interactions. Add a new method: `createSubscription()` and give it a `User` argument
and a `SubscriptionPlan` argument that the User wants to subscribe to:

[[[ code('2b476634ce') ]]]

Now, go back to the Stripe API docs, steal the code that creates a Subscription,
and paste it here. Set that to a new `$subscription` variable. For the customer,
use `$user->getStripeCustomerId()` to get the id for *this* user. For the plan, just
`$plan->getPlanId()`. Return the `$subscription` at the bottom:

[[[ code('5b9bd8dca7') ]]]

To use this in the controller, use the `$stripeClient` variable we setup earlier:
`$stripeClient->createSubscription()` and pass it the current `$user` variable and
then `$cart->getSubscriptionPlan()`:

[[[ code('2f8b5c2a42') ]]]

And that's *all* you need to create a subscription!

## Don't Invoice Twice!

And there are no gotchas at all... oh except for this big one. Remember: when you
create a Subscription, Stripe automatically creates an Invoice. And when you create
an Invoice, Stripe automatically attaches all existing InvoiceItems that haven't
been paid yet to that Invoice.

So, if the user has a Subscription, then an Invoice will be created when we call
`createSubscription()`. And *that* invoice will contain any InvoiceItems for
individual products that are also in the cart. If you try to create *another* invoice
below, it'll be empty... and you'll actually get an error.

What we actually want to do is move `createInvoice()` into the `else` so that if
there *is* a subscription plan, *it* will create the invoice, else, *we* will create
it manually:

[[[ code('827ef6f953') ]]]

Yep, the user can buy a subscription *and* some extra, amazing products all at the same time.

## Try out the Whole Flow

Try the *whole* thing out: add some sheep shears to the cart so we have a product
and a subscription. Fill in our fake credit card information, hit check out, and
... Cool! No errors.

But the real proof is in the dashboard. Click "Payments". Perfect! Here it is, for
$124. But look closer at it, and click to view the Customer. 

When we checked out, it created the customer, associated the card with it, and created
an active subscription. And this was all done in this *one* invoice. It contains
the subscription *plus* the one-time product purchase. In other words, this kicks
butt. In one month, Stripe will automatically invoice the customer again, charge
their card, and keep the subscription active.

Now that our subscription is active in Stripe, we also need to update *our* database.
We need to record that this user is actively subscribed to this plan.
