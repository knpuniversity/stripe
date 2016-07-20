# Pro Error Handling

The beauty of Stripe is that it's handling everything on JavaScript. We
immediately show our users' errors. If we do something insane like put a valid
credit card number but put an expiration in the past, that doesn't get
anywhere. That's stopped right here. The user can never submit the form.

Guess what? We are not handling all cases where things can go wrong. In fact,
if you go back to the Stripe documentation, there's a really great page on here
called Testing. What this means is it's telling you information about how you
can actually text Stripe to make sure it works. The most important thing about
this is it gives you all the credit cards that you can use in test mode, so you
can test all kinds of different types of credit cards. This is a really
important part. You can even test credit cards that will be declined or have
other problems with them. This one in particular right here, this is going to
be a card that's going to look valid, but it's actually going to be declined
when Stripe tries to charge it. This is not something that's called a
JavaScript. This error doesn't happen until we actually try to charge the user,
once the form is submitted.

Let's try this out. 4000 0000 0000 0002. Give it a valid expiration and then it
enter. Check this out. It submits but, boom, 500 error. Your card was declined.
This is a problem because we do not want our users to see a big ugly error
page. We just need to tell them, "Hey, your card was declined." We need to talk
about Stripe error handling. The good news is it's very consistent and very
simple. Notice that what was thrown here by hovering over this little word card
here was a Stripe/error/card exception. Whenever you make an APR request to
Stripe, it's either going to work or Stripe will throw an exception. If you
check out the Stripe API reference, near the top, they have an area that talks
about those errors.

There's a couple things you need to know. First of all, there are different
status codes that will kind of tell you what's going wrong. For the most part,
what you're going to look at is these types. What they're talking about is,
when you make the APR request to Stripe, you're going to get a JSON response
back and it's going to have a type key that's going to contain one of these
keys right here. This is really your indication of what went wrong. Now, the
cool thing is, inside of the Stripe library, which lives in our vendor
directory, it has a custom exception class for each of these types. For
example, if there's a card error, which is what we're getting right now when we
try to charge the card but something happened and it couldn't be charged. Then,
we're actually going to get back an exception called "card," or, more
specifically, stripe/error/card.

If there was a rate limit ... If there were a rate limit error, we would get
back this rate limit exception. What that means is we can use try catch in our
code to catch and handle when certain types of errors happen. The one we
definitely want to catch here is this card error, because that is a normal
thing to happen. Go to order controller and ... This exception is going to be
thrown at some point during this process where we actually either create or
update the customer's card and assign the token to them. At that moment, they
try to see if the card is valid, and the card ends up being declined.

What I'm going to do is actually move all of this processing logic out here
into its own private function misclass that's going to make it much clearer to
fix all this. [inaudible 00:04:46], I'm going to hit control T, go down to
"method," and create a new method called "charge customer." Then I'll hit
refactor. You don't need [inaudible 00:05:18] to do that, it just moved all of
that logic down into this private function here. Then call that new charge
"customer function" from up there. Nothing actually changed at all. We know
that, when a card is declined, somewhere in that code, we might get this
Stripe/error/card exception. In terms of documentation, what I'm trying to say
is, when we call "charge customer," it might throw a Stripe error card
exception. This line of code is purely documentation, I'm just highlighting
what I'm talking about.

We need to be careful when we call this method. Something might go wrong and we
need to handle it. What I'm going to do up here is, before the if statement,
let's set error equals false, which means we're going to say that, at this
point, there hasn't been any processing error yet. Now, we're going to surround
our "charge customer" the try catch block. We'll try charge customer, and then
we're going to catch not every exception, just Stripe/error/card. If that
happens, we know there was some problem charging the card and we need to show
it to the user. Let's change that error to be error equals, "There was a
problem charging your card." Then we'll pass e dot get message. That's going to
be the message that Stripe's sending back like, "Your card was declined," or,
"There was some other problem with your card."

At this point, if there is an error and we don't want to empty the cart, we
don't want to add the nice message or redirect ... Start on that, if not error
than it's safe to do those things. If there is an error, our code is actually
going to continue down here and it's going to re-render our checkout template,
which is exactly what we want. I'm going to pass in that error message so that
we can show it to the user like that. Then, in our template, specifically our
card form template, we already have this space here for our checkout errors,
which is normally filled in with JavaScript, but now we can just render "error."

If that's empty, that's no problem. It won't render anything. If there is an
error, then we don't want to render this hidden class initially. I'm actually
going to do an in-line echo statement here. I'm going to say, "If error, then
don't render any class else render the hidden class." This should hide by
default, but if there is an error variable, then it will not have the hidden
class and the user should be able to see it.

Okay, let's go back and give this guy a try. Refresh the page. Let's put in our
fake credit card number, which is 4, a lot of zeroes, and then ends in 2. Put
in valid information otherwise and then hit enter. Notice it is submitting, and
there is our error. This is a bit of a gotcha. This is the one error that you
actually want to catch. All the other errors that could happen are weird things
that you don't need to worry about that would be indicative of more of a set-up
problem you have.

All right, guys, you have a robust check out system via Stripe. In the next
tutorial, we're going to talk about where things get a little bit more
difficult like subscriptions, and also things like discounts. The fundamental
knowledge we have from this tutorial is going to serve you big in the next one.
All right, guys, see you next time.
