# Data: Card Last 4 Digits

Unless I cancel my subscription... which I can't actually do yet - we'll add that
soon - in 1 month, Stripe will renew my subscription by automatically charging the
credit card I have on file. Eventually, we'll need to allow the user to *update* their
credit card info from right here on the account page. But let's start simple: by
*at least* reminding them *which* card they have on file by showing the card brand
- like VISA - and the last 4 card numbers.

This is yet *another* piece of data that's *already* stored in Stripe, but we're
going to *choose* to also store it in *our* database, so we can quickly render the
info to the user.

## Printing the Credit Card Details

In the `User` class - aka our user table -  I've already added two new columns:
`cardBrand` and `cardLast4`:

[[[ code('3c87e0655d') ]]]

But these are empty right now: we're not actually setting this data yet.

Before we do that, let's update the template to print these fields. Open the
`profile/account.html.twig` template. Down by the card details, let's say
`if app.user.cardBrand`, then print some information about the user's credit card,
like `app.user.cardBrand` ending in `app.user.cardLast4`:

[[[ code('d46a40111f') ]]]

Those fields on the `User` object are empty now, so let's fix that!

## The Card Details on the Stripe Customer

Head to the Stripe API docs and click on Customers. The card information is attached
to the customer under a field called `sources`. Yes, `sources` with an `s` at the end
because you *could* attach *multiple* cards to a customer if you wanted. But we're
not: on checkout, we set just *one* card on the customer, and replace any existing
card, if there was one.

In other words, `sources` will always have just one entry. That one entry will have
a `data` key, and *that* will describe the card: giving us all the info you see
here.

Now to the plan: use the Stripe API to populate the card information on the User
table *right* during checkout.

## Setting the Card Details

In `OrderController::chargeCustomer()`, we either create or retrieve the
`\Stripe\Customer`. Assign both calls to a new `$stripeCustomer` variable:

[[[ code('0a50a7cd90') ]]]

In `StripeClient`, the `createCustomer()` method already returns the `\Stripe\Customer`
object, so we're good here:

[[[ code('d5b3c89a7f') ]]]

The `updateCustomerCard()` method, however, retrieves the customer... but gets lazy
and doesn't return it. Fix that with `return $customer`:

[[[ code('e1e6604905') ]]]

Back in `OrderController`, we've got the `\Stripe\Customer`... so we're mega dangerous!
But instead of updating the fields on `User` right here, let's do it in `SubscriptionHelper`.
Add a new `public function updateCardDetails()` method with a `User` object that
should be updated and the `\Stripe\Customer` object that's associated with it:

[[[ code('2f5e919146') ]]]

Now, this is pretty easy: `$cardDetails = $stripeCustomer->sources->data[0]`. Then,
`$user->setCardBrand($cardDetails)` - go cheat with the Stripe API - the fields
we want are `brand` and `last4`. So, `$cardDetails->brand`. And
`$user->setCardLast4($cardDetails->last4)`. Save only the user to the database with
the classic `$this->em->persist($user)` and `$this->em->flush($user)`:

[[[ code('15a897149b') ]]]

Finally, call that method! `$this->get('subscription_helper')->updateCardDetails()`
and pass it `$user` and `$stripeCustomer`:

[[[ code('4043091c1b') ]]]

No matter how you checkout, we're going to make sure your card details are updated
in our database!

Before we try it out and *prove* how awesome we are, I want to add one more thing:
I want to be able to tell the user *when* they will be billed next.
