# Failing Awesomely When Payments Fail

And now, you can even *downgrade* to the Farmer Brent and get a `$99` balance on
your account. In the Stripe dashboard, refresh the Customer. Ah, there's an account
balance of `$99.84`.

And if you change *back* to the New Zealander, now it's free! The `amount_due` field
on the upcoming invoice correctly takes the account balance into account.

So this is solid.

## Card Failure on Upgrade

But what happens if their card is declined when they try to upgrade? I don't know:
let's find out. First, go buy a new, fresh subscription. Great!

Now, update your card to be one that will fail when it's charged: `4000-0000-0000-0341`.

Ok, try to change to the New Zealander. It thinks for awhile and then... an AJAX
error! You can see it down in the web debug toolbar. 

Open the profiler for that request in a new tab and then click "Exception" to see
what the error was. Ah yes, "Your card was declined". Clearly, we aren't handling
this situation very well. 

But actually, the problem is worse than you might think. Refresh the Customer in
Stripe. You can see the failed payment... but you can also see that the subscription
change was successful! We are now on the New Zealander plan.

The customer also has an unpaid invoice, which represents what they should have been
charged. Since this is unpaid, Stripe will re-try it a few times. In summary, everything
is totally borked.

## Failing Gracefully

This whole mess starts in `StripeClient`, when we call `$this->createInvoice()`,
because this might fail. Scroll up to that method.

In addition to calling this to upgrade a subscription plan, we *also* call this
at checkout, even if there is *no* subscription. The problem is that if payment
fails on checkout, this invoice will *still* exist, and Stripe will try again to
charge it. Imagine having your card be declined at checkout, only for the vendor
to try to charge it again later, without you ever having finished checkout!

Here's the plan: if paying the invoice fails, we need to *close* it. By doing that,
Stripe will *not* try to pay it again.

To do that, surround the `pay()` line with a try-catch for the `\Stripe\Error\Card`
exception. Here, add `$invoice->close = true` and then `$invoice->save()`.

Then, re-throw the exception. Our checkout logic looks for this and notifies the
user.

Next, down in the other function, if we fail to create the invoice, we need to
*not* change the customer's plan in Stripe.

Add a new variable called `$originalPlanId` set to `$stripeSubscription->plan->id`.
Then, surround the `createInvoice()` call with a try-catch block for the same exception:
`\Stripe\Error\Card`.

## Reverting the Plan without Proration

If this happens, we need to do change the Stripe subscription *back* to the original
one: `$stripe->plan = $originalPlanId`. But here's the tricky part: add
`$stripeSubscription->prorate = false`.

Why? When we originally change the plan, that creates the two proration invoice items.
If the invoice fails to pay, then the invoice containing those invoice items is closed.
And that means, effectively, those invoice items have been deleted, which is good!
In fact, it's exactly what we want.

But when we change from the new plan back to the *old* plan, we don't want two *new*
proration invoice items in reverse to be created. By saying `prorate = false`, we're
telling Stripe to change back to the original plan, but without creating any invoice
items. Yep, simply change the plan back.

Finally, call `$stripeSubscription->save()`. Then, once again, re-throw the exception.

## Telling the User What Happened

That fixes the problem in Stripe. The last thing *we* need to do is tell the user
what went wrong.

Open `ProfileController::changePlanAction()`. Surround the `changePlan()` call with -
you guessed it - one more try-catch block for that same exception: `\Stripe\Error\Card`.
If this happens, return a new `JsonResponse()` with a message key set to `$e->getMessage()`.
This will be something like: "Your card was declined".

Oh, and give this a 400 status code so that jQuery knows that this AJAX call has failed.

Finally, in the template, add a `.fail()` callback with a `jqXHR` argument. I'll
paste in one last sweet alert popup that shows the message to the user.

## Give it a Floor Run, See if it Plays

Let's test this *whole* big mess. But our current subscription is totally messed up,
so go add a new, fresh Farmer Brent to your cart. Then, checkout with the functional,
fake card.

Cool! In the account page, update the card to the one that will fail.

Before we upgrade, refresh the Customer page in Stripe to see how it looks. First,
there's no customer balance, and our current subscription is for the Farmer Brent.

Ok, upgrade to the New Zealander! And... Plan change failed! That looks bad, but
it's wonderful! 

Reload the Customer page. First, the customer still has no account balance, that's
good. Second, we can see the failed payment, but we're still on the Farmer Brent
subscription. And the $100 invoice is unpaid, but it's *closed*. Stripe won't try
to pay this again.

Back on the Customer page, find Events at the bottom and click to view more. This
tells the *whole* story: we upgraded to the New Zealander plan, the two proration
invoice items were created, the invoice was created, the invoice payment failed,
we updated the invoice to be closed, and finally downgraded back to the Farmer Brent
plan.

WOW. You should go find a co-worker and challenge them to break this. We are now,
truly, rock solid.
