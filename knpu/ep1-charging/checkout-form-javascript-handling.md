# Checkout Form JS Handling Logic

Now that we've got the form in place, we need to add some JavaScript that will send
all the card information to Stripe. Once again, Stripe wrote a lot of this code for
us. Over-achiever.

Copy their JavaScript and scroll up and paste it with our other JavaScript:

[[[ code('8c61f5e7ff') ]]]

Then, back on the docs, scroll down a little further to another function
called `stripeResponseHandler()`. Copy that too and paste it:

[[[ code('7e7897897c') ]]]

## Prepping the JS

Let's look at the code: it uses a jQuery `document.ready` block to find the form
and attach an on submit handler function:

[[[ code('b00771ed4b') ]]]

Because basically, when the user submits the form, we *don't* want to submit the form!
Ahem, I mean, we want to stop, send all the information to Stripe, wait for the token
to come back, put *that* in the form, and *then* submit it.

In our case, I've given the form a class called `js-checkout-form`:

[[[ code('9acbfa048f') ]]]

Copy that class, and change the JavaScript to look for `.js-checkout-form`:

[[[ code('3857a0a8f3') ]]]

This is referenced in *one* other spot further below. Update that too:

[[[ code('da8bdb93dc') ]]]

It's not the most organized JS code.

Oh, and you'll notice that I use these `js-` classes a lot in my html. That's
a standard that *I* like to use whenever I give an element a class *not* because
I want to style it, but because I want to find it with JavaScript.

When this form is submitted, add `event.preventDefault()` to prevent the form from
*actually* submitting:

[[[ code('eb6aa76de6') ]]]

This does more-or-less the same thing as returning `false` at the end of the function,
but with some subtle differences. 

Oof - let me fix *some* of this bad indentation. Next, the code finds the submit
button so it can disable it. In our form, the button has a `js-submit-button` class:

[[[ code('6f94af61bf') ]]]

Copy that and update the code here, and once more down below:

[[[ code('4de5c71ea4') ]]]

## Fetching and Using the Token

Finally, here is the *meat* of the code. When we call `Stripe.card.createToken()`:

[[[ code('b005f4acbe') ]]]

Stripe's Javascript will automatically fetch all the credit card data by reading
the `data-stripe` attributes. Then, it sends those to stripe via AJAX. When that call
finishes, it will execute the `stripeResponseHandler` function, and hopefully
the response will contain that all-important token:

[[[ code('c225dd7409') ]]]

Now, if there was a problem with that card - like an invalid expiration - we need
to show that error to the user. To do that, it looks for a `payment-errors` class
and puts the message inside of that:

[[[ code('76ebc76348') ]]]

I have a `div` ready for this. Its class is `js-checkout-error` and its hidden by default:

[[[ code('da9f36d364') ]]]

Change the selector to `.js-checkout-error`, set the text, but then *also* call
`removeClass('hidden')` so the element shows up:

[[[ code('fd8c98c728') ]]]

Below in the else, life is good!! I'll paste the `.js-checkout-error` code
from before and modify it to re-add the `hidden` class - since now things
are successful:

[[[ code('74f8cdf9d2') ]]]

When things work, the response comes back with a `token`, which we get
via `response.id`:

[[[ code('8b2b807ac2') ]]]

To send this to the server, we just smash it into a new input hidden field
called... drumroll ... `stripeToken`:

[[[ code('96edee957f') ]]]

This is *precisely* what the embedded form did. Once the form is submitted,
the controller will hum along like nothing ever changed.

## Testing the Error and Success

But, that's assuming we didn't mess something up! That's a big but. Go back and refresh
the page.

First, test that the error handling works by adding an expiration date in the *past*.
Put in the real credit card number -- oof, ugly formatting - we'll fix that. Then,
use an expired expiration. Hit checkout and... boom! 

It sent the info to Stripe, Stripe came back with an error, we put the error in the
box, and showed that box to our user. In other words, we're awesome. Change this
to a *future* expiration and try again.

It's alive!!!

The only problem I can think of now is how *ugly* entering a credit card number is:
all those numbers just run together. The expiration field is a mess too. Oof. Let's
fix that - it's surprisingly easy!
