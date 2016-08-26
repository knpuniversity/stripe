# Handling Card Update Fails

There are two ways for the credit card update to fail. Most are caught immediately
via JavaScript, but a few don't happen until we try to attach the card to the customer.

Let's see an example. In Stripe's documentation, find the "Testing" section - it's
under the Payments header in the new design. Down the page a bit, you'll find a table
full of cards that will work or fail for different reasons. Find the one that ends
in `0002` and copy it. Fill the form out using this and update the card.

Ah, 500 error!

> Your card was declined

Ok, a `\Stripe\Error\Card` exception was thrown the moment that we tried to save
the new customer information back to Stripe. We *did* handle this situation on our
checkout page, so we just need to *also* be careful here.

In `ProfileController`, the `updateCustomerCard()` call is the one that might fail.
Wrap this is a try-catch for `\Stripe\Error\Card`. Set an `$error` variable to:
`There was a problem charging your card` and then concatenate `$e->getMessage()`.

To show this to the user, call `addFlash()` and set an `error` type. Like with `success`
flash messages, my base template is already configured to show these. But in this
case, the message will look red and angry!

Finally, redirect back to the `profile_account` route.

To try it out, go back, press "Update Card" again, and use the same, failing card
number. This time, no 500 error! Just this sad, but useful message.

With the ability to subscribe, cancel and update their credit card info, our subscription
system is up-and-running! Let's turn to our *last* big, *required* topic: web hooks.
How can we warn a user if we're having problems charging their card? How would we
know when Stripe cancel's a customer's subscription due to payment failure? And how
can we email a receipt each month?

Getting these details right will make your system *really* rock.
