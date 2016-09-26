# So, how much would that Upgrade Cost?

Honestly, upgrade and downgrading a plan would be really easy, except that we
need to calculate how much we should charge the user and *tell* them so they can
confirm.

Getting this right takes some work, but the result is going to be *gorgeous*, I
promise. Here's the plan: as soon as this screen loads, we'll make an AJAX call back
to the server. The server will calculate how much to charge the customer
for the upgrade, and send that back so we can show it.

In `ProfileController`, add a new public function called `previewPlanChangeAction()`.
Set the URL to `/profile/plan/change/preview/{planId}` and give it a name:
`account_preview_plan_change`:

[[[ code('1f89e434a7') ]]]

Use the `$planId` in the route to load a `$plan` object with `$this->get('subscription_helper')`
and then call `findPlan()` with `$planId`:

[[[ code('3c6a027790') ]]]

## Upcoming Invoice to the Rescue

Ok ok, but I'm ignoring the big, *huge* elephant in the room: how the heck are we
going to figure out how much to charge the user? I mean, I certainly don't want to
try to calculate how *far* through the month the user is and figure out a prorated
amount. Fortunately, we don't have to: Stripe has a killer feature to help us out.

Open the Stripe Api docs and find Invoices. Check out this "Upcoming Invoices" section.
Cool. With upcoming invoices, we can ask Stripe to tell us what the Customer's
*next* invoice will look like.

This could be used to show the user how much they'll be charged on renewal, *or*,
by passing a `subscription_plan` parameter, this will return an Invoice that describes
how much they would be charged for *changing* to that plan.

## How Upgrades Work

A big part of all of this is *prorating*. In the subscription documentation, Stripe
talks a lot about what will happen in different scenarios. By default, Stripe *does*
prorate, which means that if we are 1/4th through the month on the Farmer Brent
Plan and we upgrade, then 3/4th's of that cost should be credited as a discount
towards paying for the final 3/4th's of a month of the New Zealander plan. When you
switch between plans that have the same *duration*, like a monthly plan to another
monthly plan, the billing period doesn't change: you simply switch to the new plan
right in the middle of the month, and are billed normally again on your normal billing
date.

Yea, it's hard! The tl;dr is that Stripe does these calculations for us.

## Fetching the Upcoming Invoice

Let's use this endpoint: in `StripeClient`, add a new function:
`getUpcomingInvoiceForChangedSubscription()` with two arguments: the `User` that
will be upgrading and the `SubscriptionPlan` they want to change to:

[[[ code('e0528315cc') ]]]

Inside, it's easy: `return \Stripe\Invoice::upcoming()` and pass it a few parameters.
First, `customer` set to `$user->getStripeCustomerId()` and second, `subscription`
set to `$user->getSubscription()->getStripeSubscriptionId()`:

[[[ code('155ce06c25') ]]]

This tells Stripe *which* subscription we would update. Now, in our system, every
user should only have one, but it doesn't hurt to be explicit.

The last option is `subscription_plan`: in other words, which plan do we want to
change to. Set it to `$newPlan->getPlanId()`:

[[[ code('ed10452515') ]]]

Back in `ProfileController`, use this to set a new `$stripeInvoice` variable via
`this->get('stripe_client')->getUpcomingInvoiceForChangedSubscription()` passing
it `$this->getUser()` and the new `$plan`:

[[[ code('5e639ed816') ]]]

## Dumping the Upcoming Invoice

So, what does this fancy Upcoming invoice actually look like? Let's find out by
dumping it. Then, return a `JsonResponse` with... I don't know, how about a `total`
key set to a hardcoded `50` for now. Oh, and make sure you dump `$stripeInvoice`:

[[[ code('9d22a61d85') ]]]

Ok, let's keep going by hooking up the frontend and finishing the cost calculation.
