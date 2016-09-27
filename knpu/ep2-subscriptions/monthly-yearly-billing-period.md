# Monthly to Yearly: The Billing Period Change

We just found out that this amount - $792 - doesn't seem right! Open the web debug
toolbar and click to see the profiler for the "preview change" AJAX call that returned
this number. Click the Debug link on the left. This is a dump of the upcoming invoice.
And according to *it*, the user will owe $891.05. Wait, that sounds *exactly* right!

The number we just saw is different because, remember, we start with `amount_due`,
but then subtract the plan total to remove the extra line item that Stripe adds.
Back then, we had *three* line items: the two prorations and a line item for the
next, full month.

But woh, now there's only two line items: the partial-month discount and a charge
for the *full*, yearly period.

## Changing Duration changes Billing Period

Stripe talks about this oddity in their documentation: when you change to a plan
with a different duration - so monthly to yearly or vice-versa - Stripe bills you
*immediately* and changes your billing date to start *today*. 

So if you're normally billed on the first of the month and you change from monthly
to yearly on the 15th, you'll be credited half of your monthly subscription and then
charged for a full year. That yearly subscription will start immediately, on the 15th
of that month and be renewed in one year, on the 15th.

For us, this means that the `amount_due` on the Invoice is actually correct: we don't
need to adjust it. In `ProfileController`, create a new variable called `$currentUserPlan`
set to `$this->get('subscription_helper')->findPlan()` and pass it
`$this->getUser()->getSubscription()->getStripePlanId()`:

[[[ code('963db113a5') ]]]

Now, if `$plan` - which is the new plan - `$plan->getDuration()` matches the
`$currentUserPlan->getDuration()`, then we *should* correct the total. Otherwise,
if the duration is changing, the `$total` is already perfect:

[[[ code('f702f1568e') ]]]

Since this looks *totally* weird, I'll tweak my comment to mention that `amount_due`
contains the extra month charge *only* if the duration stays the same.

Ok! Go back and refresh! Click "Bill yearly". Yes! That looks right: $891.06.

## Duration Change? Don't Invoice

Because of this behavior difference when the duration changes, we need to fix *one*
other spot: in `StripeClient::changePlan()`. Right now, we manually create an invoice
so that the customer is charged immediately. But... we don't need to do that in
this case: Stripe *automatically* creates and pays an Invoice when the duration changes.

In fact, trying to create an invoice will throw an error! Let's see it. First, update
your credit card to one that will work.

Now, change to Bill yearly and confirm. The AJAX call *should* fail... and it does!
Open the profiler for that request and find the Exception:

> Nothing to invoice for customer

Obviously, we need to avoid this. In `StripeClient`, add a new variable: `$currentPeriodStart`
that's set to `$stripeSubscription->current_period_start`:

[[[ code('30192c8d06') ]]]

That's the current period start date *before* we change the plan.

After we change the plan, if the duration is different, the current period start
will have changed. Surround the *entire* invoicing block with if
`$stripeSubscription->current_period_start == $currentPeriodStart`:

[[[ code('45d826630e') ]]]

In other words: only invoice the customer manually if the subscription period hasn't
changed. I think we should add a note above this: this can look really confusing!

[[[ code('b7629307a5') ]]]

## Take it for a Test Drive

But, now it should work! Reset things by going to the pricing page and buying a brand
new monthly subscription. Now, head to your account page and update it to yearly.
The amount - $891 - looks right, so hit OK.

Yes! Plan changed! My option changed to "Bill monthly" and the "Next Billing at" date
is August 10th - one year from today. We should *probably* print the year.

In Stripe, under payments, we have one for $891, and the customer is on the
Farmer Brent *yearly* plan.

Winning!
