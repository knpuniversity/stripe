# So, how much would that Upgrade Cost?

Honestly, upgrade and downgrading a plan would be really eary, except that we
need to calculate how much we should charge them and *tell* them this so they can
confirm.

Getting this right will take some work, but the result is going to be *gorgeous*,
I promise. Here's the plan: as soon as this screen loads here, we'll make an AJAX
call back to the server. The server will calculate how much to charge the customer
for the upgrade, and send that back so we can show it.

In `ProfileController`, add a new public function called `previewPlanChangeAction`.
Set the URL to `/profile/plan/change/preview/{planId}` and give it a name:
`account_review_plan_change`.

Use the `planId` in the route to load a `$plan` object with `$this->get('subscription_helper')`
and then call `findPlan()`.

## Upcoming Invoice to the Rescue

Ok ok, but I'm ignoring the big, *huge* elephant in the room: how the heck are we
going to figure out how much to charge the user? I mean, I certainly don't want to
try to calculate how *far* through the month the user is and figure out some proration
amount. Fortunately, we don't have to: Stripe has a killer feature to help us out.

Open the Stripe Api doc and find Invoices. Check out this "Upcoming Invoices" section.
Awesome. With upcoming invoices, we can ask Stripe to tell us what the Customer's
*next* invoice will look like.

This could be used to show the user how much they'll be charged on renewal, *or*,
by passing this a `subscription_plan`, this will return an Invoice that describes
how much they would be charged for *changing* to that plan.

## How Upgrades Work

A big part of all of this is *prorating*. In the subscription documentation, Stripe
talks a lot about what will happen in different scenarios. By default, Stripe *does*
prorate, which means that if we are 1/4th through the month on the Farmer Brent
Plan and we upgrade, then 3/4th's of that cost should be credited as a discount
towards paying for final 3/4th's of a month of the New Zealander plan. When you
switch between plans that have the same *duration*, like a monthly plan to another
monthly plan, the billing date doesn't change: you simply switch to the new plan
right in the middle, and are billed normally again on your normal billing date.

See, it's hard! The tl;dr is that Stripe does these calculations for us.

## Fetching the Upcoming Invoice

Let's use this endpoint: in `StripeClient`, add a new function:
`getUpcomingInvoiceForChangedSubscription()` with two argument: the `User` that
will be upgrading and the `SubscriptionPlan` they want to change to.

Inside, it's easy: `return \Stripe\Invoice::upcoming()` and pass in a few parameters.
First, `customer` set to `$user->getStripeCustomerId()` and second, `subscription`
set to `$user->getSubscription()->getSubscriptionId()`. This tells Stripe *which*
subscription we would update. Now, in our system, every user should only have one,
but it doesn't hurt to be explicit.

The last option is `subscription_plan`: in other words, which plan do we want to
change to. Se it to `$plan->getPlanId()`.

Back in `ProfileController`, use this to set a new `$stripeInvoice` variable via
`this->get('stripe_client')->getUpcomingInvoiceForChangedSubscription()` passing
it `$this->getUser()` and the new `$plan`.

## Dumping the Upcoming Invoice

So, what does this fancy Upcoming invoice actually look like? Let's find out by
dumping it. Then, return a `JsonResponse` with... I don't know, how about a `total`
key set to a hardcoded `50` for now. Oh, and make sure you dump `$stripeInvoice`.

All right on the job de-script side on things let's make
it so that once we click to change the plan we make an ajax called this New End
Point. What we'll do here is find where our button is, way down here, and I'm
going to add another [attribute 00:06:49] here called Data dash Preview URL.
How we use twigs path function to link to our account preview plan change and
we'll pass it Plan ID set to other plan at Plan ID.

Ok, let's keep going by hooking up the frontend and finishing the cost calculation.
