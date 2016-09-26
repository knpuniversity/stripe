# Upgrading Subscription Plans: The UI

Imagine this: a sheep customer is *so* happy with the Farmer Brent subscription that
they want to *upgrade* to the New Zealander! Awesome! Amazing! But also... currently
impossible.

Time to fix that. Plan upgrades can be complex, because someone needs to calculate
how much of the current month has been used and prorate funds towards the upgrade.
Stripe's documentation talks about this. I'll guide you through everything, but
this section is worth a read.

## Printing the Current Plan

Buy a new Farmer Brent Subscription, and then head to the account page. 

Let's focus on the plan upgrade user interface first. Here, I need to see *which*
plan I'm currently subscribed to and a button to upgrade to the other plan.

Open the `account.html.twig` template and `ProfileController`.

Add a new variable: `$currentPlan = null`. Then, only *if* `$this->getUser()->hasActiveSubscription()`,
set `$currentPlan = $this->get('subscription_helper')->findPlan()` passing that
`$this->getUser()->getSubscription()->getStripePlanId()`:

[[[ code('ba4ca9d9ae') ]]]

The `findPlan()` method will give *us* a fancy `SubscriptionPlan` object.

Pass a new `currentPlan` variable into the template, set to `$currentPlan`:

[[[ code('7e3e243b94') ]]]

Then in the template, find the "Active Subscription" spot, and print `currentPlan.name`:

[[[ code('b3d72f717f') ]]]

Refresh the page! Great! Step 1 done: we have the "Farmer Brent" plan.

## Adding the Upgrade Button

Now, step two: add an upgrade button that mentions the plan they could switch to.
Since we only have 2 plans, it's pretty simple: if they're on the Farmer Brent, we
want to allow them to upgrade to the New Zealander. And if they're on the New Zealander,
we should let them *downgrade* to the Farmer Brent.

To find the *other* plan, open `SubscriptionHelper` and add a new public function
called `findPlanToChangeTo` with a `$currentPlanId` argument:

[[[ code('b166086abb') ]]]

I'll paste in the logic: it's kind of silly, but it gets the job done. I'm using
`str_replace` instead of something simpler, because in a few minutes, we're going
to add *yearly* plans, and I still want this function to... um... function.

Back to the controller! Add another variable: `$otherPlan = null`. Then,
`$otherPlan = $this->get('subscription_helper')->findPlanToChangeTo()` and pass
it `$currentPlan->getPlanId()`. Pass this into the template as an `otherPlan` variable:

[[[ code('13d8473a38') ]]]

There, after the "Active" label, add a button with some classes: a few for styling,
one to float right and one - `js-change-plan-button` - that we'll use in a minute
via JavaScript. Make the text: "Change to" and then `otherPlan.name`:

[[[ code('33d4f15c26') ]]]

Oh, and add one more attribute: `data-plan-name` and print `otherPlan.name`. We'll
read that attribute in JavaScript.

## Bootstrapping the JavaScript

In fact, let's play with the JavaScript right now: copy the `js-change-plan-button`
class and find the JavaScript block at the top of this file. Use jQuery to locate
that element, then on `click`, add a callback. Start with the always-in-style
`e.preventDefault()`:

[[[ code('849fda1847') ]]]

Start really simple: we'll use a library that I already installed called
[Sweet Alerts][sweet_alert]. Call `swal()` and pass a message `Loading Plan Details`:

[[[ code('86f2292ad1') ]]]

Ok, let's see what this Sweet Alerts thing looks like! Refresh that page! Nice!
Click the "Change to New Zealander" link. This is Sweet Alert. It's cute, it's easy,
and it'll help us do our job. 

Because next, we need to do some serious work: we need to calculate how *much* we
should charge the user to upgrade from the Farmer Brent to the New Zealander, and
then show it to the user. That's tricky, because the user is probably in the middle
of the month that they've already paid for, so they deserve some credits!

Thankfully, Stripe is going to be a *champ* and help us out.


[sweet_alert]: http://t4t5.github.io/sweetalert/
