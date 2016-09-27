# Changing your Plan from Monthly to Yearly

So far, we *only* offer monthly plans. But sheep *love* commitment, so they've been
asking for *yearly* options. Well, great news! After all that upgrade stuff we
just handled, this is going to be *easy*.

## Creating the New Plans

First, in Stripe's dashboard, we need to create two new plans. Call the first
"Farmer Brent yearly" and for the total... how about 99 X 10: so $990, per year.

Then, add the New Zealander yearly, set to 1990, billed yearly.

Cool! I'm not going to update our checkout to allow these plans initially, because,
honestly, that's super easy: just create some new links to add these plans to your
cart, and you're done.

Nope, we'll skip straight to the hard stuff: allowing the user to *change* between
monthly and yearly plans.

## Adding the SubscriptionPlan Objects

First, we need to add these plans to our system. Open the `SubscriptionPlan` class.
To distinguish between monthly and yearly plans, add a new property called `duration`:
this will be a string, either `monthly` or `yearly`. At the top, I love constants,
so create: `const DURATION_MONTHLY = 'monthly'` and `const DURATION_YEARLY = 'yearly'`:

[[[ code('2a33c879a6') ]]]

Next, add a `$duration` argument to the constructor, but default it to monthly.
Set the property below:

[[[ code('207acddfe8') ]]]

Finally, I'll use the "Code"->"Generate" menu, or `Command`+`N` on a Mac, select "Getters"
and then choose `duration`. That gives me a nice `getDuration()` method:

[[[ code('0a672dca04') ]]]

In `SubscriptionHelper`, we create and preload all of our plans. Copy the two monthly
plans, paste them, update their keys to have `yearly` and add the last argument for
the *yearly* duration:

[[[ code('839c4b591a') ]]]

Now, these are *at least* valid plans inside the system.

## The Duration Change UI

Here's the goal: on the account page, next to the "Next Billing at" text, I want
to add a link that says "bill yearly" or "bill monthly". When you click
this, it should follow the same workflow we just built for *upgrading* a plan:
it should show the cost, then make the change.

In `ProfileController::accountAction()`, add yet *another* variable here called
`$otherDurationPlan`:

[[[ code('c3248f7218') ]]]

This will eventually be the `SubscriptionPlan` object for the *other* duration
of the current plan. So if I have the *monthly* Farmer Brent, this will be set
to the *yearly* Farmer Brent plan.

To find that plan, open `SubscriptionHelper` and add a new function called
`findPlanForOtherDuration()` with a `$currentPlanId` argument:

[[[ code('ad403c0e55') ]]]

I'll paste in some silly code here. This relies on our naming conventions to switch
between monthly and yearly plans.

Back in the controller, copy the `$otherPlan` line, paste it, then update the variable
to `$otherDurationPlan` and the method to `findPlanForOtherDuration()`:

[[[ code('5286836093') ]]]

Pass that into the template as a new variable:

[[[ code('963df59fd2') ]]]

Cool!

In `account.html.twig`, scroll down to the Upgrade Plan button. Copy that *whole*
thing. Then, keep scrolling to the "Next Billing at" section. If the user has a
subscription, paste the upgrade button:

[[[ code('bc8fe231eb') ]]]

And since *this* process will be the same as upgrading, we can re-use this exactly.
Just change `otherPlan` to `otherDurationPlan`... in all 4 places. Update the text
to "Bill" and then `otherDurationPlan.duration`. So, this will say something like
"Bill yearly".

## Dump the Upcoming Invoice

Before we try this, go back into `ProfileController` and find `previewPlanChangeAction()`.
The truth is, changing a plan from monthly to yearly should be *identical* to upgrading
a plan. But, it's not *quite* the same. To help us debug an issue we're about to
see, dump the `$stripeInvoice` variable:

[[[ code('66fcd20e47') ]]]

And now that I've told you it won't work, let's try it out! Refresh the account
page. Then click the new "Bill yearly" link. Ok:

> You will be charged $792.05 immediately

Wait, that doesn't seem right. The yearly plan is $990 per year. Then, if you subtract
approximately $99 from that as a credit, it should be something closer to $891.
Something is not quite right.
