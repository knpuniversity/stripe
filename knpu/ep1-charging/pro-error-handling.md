# Pro Error Handling

A lot of failures are stopped right here: instead of passing us back a token, Stripe
tells us something is wrong and we tell the user immediately.

But guess what? We are not handling all cases where things can go wrong. Go back
to the Stripe documentation and click the link called "Testing". This page is full
of helpful information about how to *test* your Stripe setup. One of the most interesting
parts is this cool table of fake credit card numbers that you can use in the Test
environment. They include cards that will work, but also cards that will *fail*,
for various reasons.

Ah, this one is particularly important: this number will look valid, but will be
declined when we try to charge it.

Let's try this out. Use: `4000 0000 0000 0002`. Give it a valid expiration and then
hit enter. It's submitting, but woh! A huge 500 error:

> Your card was declined.

This is a problem: on production, this would be a big error screen with no information.
Instead, we need to be able to tell our user what went wrong: we need to be able
to say "Hey Buddy! You card was declined".

## Stripe Error Handling 101

Let's talk about how Stripe handles errors. First, on the error page, if I hover
over this little word, it tells me that a `Stripe\Error\Card` exception object was
thrown. Whenever you make an API request to Stripe, it will either be successful,
or Stripe will throw an exception.

On Stripe's API documentation, near the top, they have a section that talks about
Errors.

There are a few important things. First, Stripe uses different status codes to give
you some information about what went wrong. That's cool, but these *types* are more
important. When you make an API request to Stripe and it fails, Stripe will send
back a JSON response with a `type` key. That `type` will be one of these values.
This goes a *long* way to telling you what went wrong.

So, how can we read the `type` inside our code?

Open up the `vendor/stripe` directory to look at the SDK code. Hey, check this out:
the library has a custom Exception class for *each* of the possible `type` values.
For example, if `type` is `card_error`, the library throws a `Card` exception, which
is what we're getting right now. 

But if Stripe was rate limiting us because we made way too many requests, Stripe
would throw a `RateLimit` exception. This means that we can use a try-catch block
to handle *only* specific error types.

## Isolating the Checkout Code

The *one* error we need to handle is `card_error` - because this happens when a
card is declined.

To do that, let's move all of this processing logic into its own private function
in this class. That'll make things cleaner.

To do this, I'll use a PhpStorm shortcut: select the code, hit `Control`+`T` (or go
to the "Refactor"->"Refactor This" menu) and select "Method". Create a new method
called `chargeCustomer()`. Hit refactor:

[[[ code('fd1d86ffab') ]]]

You don't need PhpStorm to do that: it just moved my code down into this private
function and called that function from the original spot.

## Handling the Card Exception

OK, back to business: we know that when a card is declined, something in that code
will throw a `Stripe\Error\Card` exception. I'm adding a little documentation just
to indicate this:

[[[ code('6bdcc8e17a') ]]]

Back in `checkoutAction()`, add a new `$error = false` variable before the `if`,
because at this point, no error has occurred:

[[[ code('d643e54b6a') ]]]

Next, surround the `chargeCustomer()` call in a try-catch: `try` `chargeCustomer()`
and then catch *just* a `\Stripe\Error\Card` exception:

[[[ code('43546e0a39') ]]]

If we get here, there was a problem charging the card. Update `$error` to some nice
message, like: "There was a problem charging your card.". Then add `$e->getMessage()`:

[[[ code('d380baafb1') ]]]

That's will be the message that Stripe's sending back like, "Your card was declined,"
or, "Your card cannot be used for this type of transaction."

Now, if there *is* an error, we don't want to empty the cart, we don't want to add
the nice message and we don't want to redirect. So, `if (!$error)`, then it's safe
to do those things:

[[[ code('ecbb4c4308') ]]]

If there *is* an error, our code will continue down and it will re-render the checkout
template, which is exactly what we want! Pass in the `$error` variable so we can
show it to the user:

[[[ code('fc705ec417') ]]]

Then, in then template, specifically the `_cardForm` template, render `error` inside
of our error div:

[[[ code('19b219bee6') ]]]

If there is no error, no problem! It won't render anything. If there *is* an error,
then we we need to *not* render the `hidden` class. Use an inline if statement to
say:

> If error, then don't render any class, else render the hidden class

A little tricky, but that should do it.

Ok, let's try it again. Refresh the page. Put in the fake credit card: the number
4, a thousand zeroes, and a 2 at the end. Finish it up and submit.

There's the error! Setup, complete.

Ok, guys, you have a killer checkout system via Stripe. In part 2 of this course,
we're going to talk about where things get a little bit more difficult: like subscriptions
and discounts. This includes handling web hooks, one of the scariest and toughest
parts of subscriptions.

But, don't stop - go make a *great* product and sell it.

All right, guys, seeya next time.
