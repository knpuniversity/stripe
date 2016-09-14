# Handling Card Update Fails

There are actually two ways for the credit card update to fail. Most failures happen
immediately, and are handle via JavaScript. But a few don't happen until we try to
attach the card to the customer.

Let's see an example. In Stripe's documentation, find the "Testing" section - it's
under the Payments header in the new design. Down the page a bit, you'll find a table
full of cards that will work or fail for different reasons. Find the one that ends
in `0002` and copy it. Fill the form out using this and update the card.

Ah, 500 error!

> Your card was declined

Ok, a `\Stripe\Error\Card` exception was thrown the moment that we tried to save
the new customer card back to Stripe. We *did* handle this situation on our
checkout page, so we just need to *also* handle it here.

In `ProfileController`, the `updateCustomerCard()` call is the one that might fail.
Wrap this is a try-catch for `\Stripe\Error\Card`:

[[[ code('97d95311fd') ]]]

Set an `$error` variable to: `There was a problem charging your card` and then
concatenate `$e->getMessage()`:

[[[ code('788af80d2b') ]]]

To show this to the user, call `addFlash()` and set an `error` type:

[[[ code('69d3ebd87c') ]]]

Just like with `success` flash messages, our base template is already configured
to show these. But in this case, the message will look red and angry!

Finally, redirect back to the `profile_account` route:

[[[ code('98028c72e3') ]]]

To try it out, go back, press "Update Card" again, and use the same, failing card
number. This time, no 500 error! Just this sad, but useful message.

With the ability to subscribe, cancel and update their credit card info, our subscription
system is up-and-running! Now it's time to face our *last* big, *required* topic:
webhooks. How can we email a user if we're having problems charging their card?
How would we know when Stripe cancel's a customer's subscription due to payment failure?
And how can we email a receipt each month when a subscription renews?

The answer to all of these is: webhooks. And getting those right will make your
system *really* rock.
