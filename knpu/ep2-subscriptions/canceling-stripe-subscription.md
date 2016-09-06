# Canceling a Subscription

Bad news: eventually, someone will want to cancel their subscription to your amazing,
awesome service. So sad. But when that happens, let's make it as *smooth* as possible.
Remember: happy customers!

Like everything we do, cancelling a subscription has two parts. First we need to
cancel it inside of Stripe and second, we need to update *our* database, so we know
that this user no longer has a subscription.

## Setting up the Cancel Button

Start by adding a cancel button to the account page. In `account.html.twig`, let's
move the `h1` down a bit:

[[[ code('2e81688d2d') ]]]

Next, add a *form* with `method="POST"` and make this float right:

[[[ code('ca12ba5601') ]]]

We don't actually need a form, but now we can put a button inside and this will
*POST* up to our server. I don't always do this right, but since this action will
*change* something on the server, it's best done with a POST request. Add a few
classes for styling and say "Cancel Subscription".

I still need to set the `action` attribute to some URL... but we need to create
that endpoint first!

Open `ProfileController`. This file renders the account page, but we're also going
to put code in here to handle some other things on this page, like cancelling a
subscription and updating your credit card.

Create a new `public function cancelSubscriptionAction()`. Give this a URL:
`@Route("/profile/subscription/cancel")` and a name: `account_subscription_cancel`:

[[[ code('5f89e199c7') ]]]

And, since we'll POST here, we might as well require a POST with `@Method` - hit
tab to autocomplete and add the `use` statement - then `POST`:

[[[ code('ea538cf3e2') ]]]

With the endpoint setup, copy the route name and go back into the template. Update
`action`, with `path()` then paste the route:

[[[ code('8e068937a8') ]]]

And we are setup!

## Cancel that Subscription in Stripe

Now, back to step 1: cancel the Subscription in Stripe. Go back to Stripe's documentation
and find the section about Cancelling Subscriptions - it'll look a *little* different
than what you see here... because Stripe updated their design *right* after I recorded.
Doh! But, all the same info is there.

Ok, this is simple: retrieve a subscription and then call `cancel()` on it. Yes!
So easy!

## Cancelling at_period_end

Or not easy: because you *might* want to pass this an `at_period_end` option set
to true. Here's the story: by default, when you cancel a subscription in Stripe,
it cancels it immediately. But, by passing `at_period_end` set to true, you're saying:

> Hey! Don't cancel their subscription *now*, let them finish the month and
  *then* cancel it.

This is *probably* what you want: after all, your customer already paid for this
month, so you'll want them to keep getting the service until its over.

So let's do this! Remember: we've organized things so that *all* Stripe API code
lives inside the `StripeClient` object. Fetch that first with
`$stripeClient = $this->get('stripe_client')`:

[[[ code('005a90ceba') ]]]

Next, open this class, find the bottom, and add a new method: `public function cancelSubscription()`
with one argument: the `User` object whose subscription should be cancelled:
 
 [[[ code('643fca6ecb') ]]]

For the code inside - go copy and steal the code from the docs! Yes! Replace the
hard-coded subscription id with `$user->getSubscription()->getStripeSubscriptionId()`.

[[[ code('b2e921ad16') ]]]

Then, cancel it at period end:

[[[ code('8df536d85e') ]]]

Back in `ProfileController`, use this! `$stripeClient->cancelSubscription()` with
`$this->getUser()` to get the currently-logged-in-user:

[[[ code('a73dead15f') ]]]

Then, to express how sad we are, add a heard-breaking flash message. Then, redirect
back to `profile_account`:

[[[ code('16b6f2b027') ]]]

We've done it! But don't test it yet: we still need to do step 2: update our database
to reflect the cancellation.
