# Execute the Plan Upgrade

When the user clicks "OK", we'll make an AJAX request to the server and then tell
Stripe to *actually* make the change.

In `ProfileController`, add the new endpoint: `public function changePlanAction()`.
Set its URL to `/profile/plan/change/execute/{planId}` and name it `account_execute_plan_change`.
Add the `$planId` argument.

This will start just like the previewChange endpoint: copy its `$plan` code and
paste it here. 

## Changing a Subscription Plan in Stripe

To actually change the plan in Stripe, we need to fetch the *Subscription*, set
its plan to the new id, and save. Super easy!

Open `StripeClient` and add a new function called `changePlan()` with two arguments:
the `User` who wants to upgrade and the `SubscriptionPlan` that they want to change
to.

Then, fetch the `\Stripe\Subscription` for the User with `$this->findSubscription()`
passing it `$user->getSubscription()->getStripeSubscriptionId()`. Now, update
that: `$stripeSubscription->plan = $newPlan->getPlanId()`. Finally, send that to
Stripe with `$stripeSubscription->save()`.

## But Charge the User Immediately

Ok, that *was* easy. And now you probably expect there to be a "catch" or a
gotcha that makes this harder. Well... yea... there totally is. Sorry. 

I told you earlier that Stripe doesn't charge the customer right now: it waits
until the end of the cycle and then bills for next month's renewal, plus what they
owe for upgrading this month. We want to bill them immediately.

How? Simple: by manually creating an Invoice and paying it. Remember: when you create
an Invoice, Stripe looks for all unpaid invoice items on the customer. When you change
the plan, this creates *two* new invoice items for the negative and positive plan
proration. So if we invoice the user right now, it will pay those invoice items.

And hey! We *already* have a method to do that called `createInvoice()`. Heck it
even *pays* that invoice immediately.

In our function, call `$this->createInvoice()` and pass it `$user`.

Finally, return `$stripeSubscription` at the bottom - we'll need that in a minute.

Back in the controller, call this with `$stripeSubscription = $this->get('stripe_client')`
then `->changePlan($this->getUser(), $plan)`.

## Upgrading the Plan in our Database

Ok, the plan is upgraded! Well, in Stripe. But we *also* need to update the subscription
row in our database.

When a user buys a new subscription, we call a method on `SubscriptionHelper` called
`addSubscriptionToUser()`. We pass it the new `\Stripe\Subscription` and the `User`.
Then *it* guarantees that the user has a subscription row in the table with the correct
data, like the plan id, subscription id, and `$periodEnd` date.

Now, the only thing *we* need to update right now is the plan id: both the
subscription id and period end haven't changed. But that's ok: we can still safely
reuse this method. 

In `ProfileController`, add `$this->get('subscription_helper')->addSubscriptionToUser()`
passing it `$stripeSubscription` and `$this->getUser()`.

And that's *everything*. At the bottom... well, we don't *really* need to return
anything to our JSON. So just return a `new Response()` with `null` as the content
and a 204 status code. This doesn't do anything special: 204 simply means that the
operation was successful, but the server has nothing it wishes to say back.

## Executing the Upgrade in the UI

Copy the route name, then head to the template to make this work.

First, find the button, copy the `data-preview-url` attribute, and paste it. Name
the new one `data-change-url` and update the route name.

Above in the JavaScript, set a new `changeUrl` variable to `$(this).data('change-url')`.
Then, scroll down to the bottom: this callback function will be executed *if* the
user clicks the "Ok" button to confirm the change. Make the AJAX call here: set the
`url` to `changeUrl`, the `method` to `POST`, and attach *one* more success function.
Inside that, call Sweet Alert to tell the user that the plan was changed! Let's also
add some code to reload the page after everything.

Ok! Let's do this! Refresh the page! Click to change to the "New Zealander".
$99.88 - that looks right, now press "Ok". And ... cool! I think it worked! When
the page reloads, our plan is the "New Zealander" and we can downgrade to the
"Farmer Brent".

In the Stripe dashboard, open payments, click the one for $99.88, and open its
Invoice. Oh, it's a thing of beauty: this has the two line items for the change.

If you check out the customer, their top subscription is *now* to the New Zealander
plan.

So we're good. Except for one last edge-case.
