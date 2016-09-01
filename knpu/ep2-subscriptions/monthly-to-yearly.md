# Changing your Plan from Monthly to Yearly

So far, we *only* offer monthly plans. But sheep *love* committment, so they've been
asking for *yearly* options. And hey, great news! After all that upgrade stuff we
just tackled, this is going to be *easy*.

## Creating the New Plans

First, in Stripe's dashboard, we need to create two new plans. Call the first
"Farmer Brent yearly" and for the total... how about 99 X 10: so $990, per year.

Then, add the New Zealander yearly, set to 1990, billed yearly.

Cool! I'm not going to update our checkout to allow these plans initially, because,
honestly, that's really easy: just add some new links to add these plans to your
card, and you're done.

Nope, we'll skip straight to the hard stuff: allowing the user to *change* between
monthly and yearly plans.

## Adding the SubscriptionPlan Objects

First, we need to add these plans to our system. Open the `SubscriptionPlan` class.
To distringuish between monthly and yearly plans, add a new property called `duration`:
this will be a string, either `monthly` or `yearly`. At the top, add some constants
for this: `cost DURATION_MONTHLY = 'monthly'` and `cost DURATION_YEARLY = 'yearly'`.

Now, add a new `$duration` argument to the constructor, but default it to monthly.
Set the property below.

Finally, I'll use the Code->Generate menu, or Command+N on a mac, select "Getters"
and then choose `duration`. That gives me a nice `getDuration()` method.

In `SubscriptionHelper`, we create and preload all the plans that are available.
Copy the two monthly plans, paste them, update their keys to have `yearly` and add
the last argument for the *yearly* duration. Now, these are *at least* valid plans
inside the system.

## The Duration Change UI

Here's the plan: on the account page, next to the "Next Billing at" text, I want
to add a link that says "change to monthly" or "change to yearly". If you click it,
it should follow the *exact* same workflow we just built for *upgrading* a plan:
it should show the cost, then make the change.

In `ProfileController::accountAction()`, add yet *another* variable here called
`$otherDurationPlan`. This will eventually be the `SubscriptionPlan` object for
the *other* duration of a plan. So if I have the *monthly* Farmer Brent, we'll set
this to the *yearly* Farmer Brent plan.

To find the right plan, open `SubscriptionHelper` and add a new function called
`findPlanForOtherDuration()` with a `$currentPlanId` argument. I'll paste in some
silly code here that relies on our naming conventions to switch between monthly
and yearly plans.

Back in the controller, copy the `$otherPlan` line, paste it, then update the variable
to `$otherDurationPlan` and the method to `findPlanForOtherDuration()`. Pass that
into the template as a new varaible.

Cool!

Open the `account.html.twig` template and scroll down to the Upgrade Plan button.
Copy that *whole* thing. Then, keep scrolling to the "Next Billing at" section. If
the user has a subscription, paste the upgrade button.

Since the upgrade process will be the same, we can re-use this exactly. Just change
`otherPlan` to `otherDurationPlan`... in all 4 places. Update the text to "Bill"
and then `otherDurationPlan.duration`. So, this will say something like "Bill yearly".

## Dump the Upcoming Invoice

Before we try this, go back into `ProfileController` and find `previewPlanChangeAction()`.
The truth is, upgrading a plan from monthly to yearly should be *identical* to changing
a plan. But, it's not *quite* the same. To plan ahead so we can debug, dump the
`$stripeInvoice` variable.

And now that I've told you it won't work, let's try it out! Refresh the account
page. Then click the new "Bill yearly" link. Ok:

> You will be charged $792.05 immediately

Wait, that doesn't make sense. The new plan is $990 per year. Then, if you subtract
approximately $99 from that as a credit, it should be something closer to $891.
Something is wrong.
