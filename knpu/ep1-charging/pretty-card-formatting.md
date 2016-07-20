# Pretty Card Formatting

The *old*, embedded form had a couple of nice formatting behaviors - like automatically
adding a space between every 4 card numbers. Fortunately, Stripe has us covered 
once again here. Go back to the documentation and scroll down - they eventually
reference a library called [jQuery.payment](https://github.com/stripe/jquery.payment):
a neat little JavaScript library for formatting checkout fields nicely.

It even provides validation, in case you want to make sure the numbers are sane
before sending them off to Stripe.

I've already downloaded this JavaScript library into the `web/js` directory, so all
we need to do is include it on the page and point it at our form.

At the top, add a new `script` tag and set its `src="js/jQuery.payment.min.js"` -
the asset function is an optional helper function in Symfony - nothing magic going
on there.

Then, down below... try to ignore the ugly indentation that I should have fixed
earlier, and say `$form.find()`. We need to find the credit card number input. But
don't worry! I planned ahead and gave it a special `js-cc-number` class. I also
added `js-cc-exp` and `js-cc-cvc`. 

Fill in `.js-cc-number` and then call `.payment('formatCardNumber')`. Repeat this
two more times for `js-cc-exp` with `formatCardExpiry` and `formatCardCVC`. Don't
forget to update the class name. 

Try it out! Yes! The card field gets pretty auto-spacing and even *more* importantly,
the library adds the slash automatically for the expiration field. It also limits
the CVC field to a maximum of 4 digits.

So custom forms are a little bit more work. But they fundamentally act the same.

Before we go, there's one big hole left in our setup: failing gracefully when someone's
card is declined.
