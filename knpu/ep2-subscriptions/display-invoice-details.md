# Displaying All the Invoice Details

To see *all* the details for each invoice, we need an "invoice show" page. Head to
`ProfileController` and add a new method for this: `showInvoiceAction()`. Give it
a URL: `/profile/invoices/{invoiceId}`. And a name: `account_invoice_show`, and then
add the `$invoiceId` argument:

[[[ code('984e58fbed') ]]]

Before doing anything else, copy that route name, head back to the account template
and fill in the `href` by printing `path()` then pasting the route name. For the
second argument, pass in the wildcard: `invoiceId` set to `invoice.id`, which will
be the Stripe invoice ID:

[[[ code('634361af71') ]]]

## Fetch One Invoice's Data

Back in the controller, our work here is pretty simple: we'll just ask Stripe
for this *one* Invoice. In `StripeClient`, we don't have a method that returns
just *one* invoice, so let's add one: `public function findInvoice()` with an
`$invoiceId` argument. Inside, return the elegant `\Stripe\Invoice::retrieve($invoiceId)`:

[[[ code('f0890dc51e') ]]]

Love it!

In the controller, use this:
`$stripeInvoice = $this->get('stripe_client')->findInvoice()` with `$invoiceId`:

[[[ code('3261c08594') ]]]

To make things really nice, you'll probably want to wrap this in a try-catch block:
if there's a 404 error from Stripe, you'll want to catch that exception and throw
the normal `$this->createNotFoundException()`. That'll cause our site to return a
404 error, instead of 500 error.

Finally, render a new template: how about `profile/invoice.html.twig`. Pass this
an `invoice` variable set to `$stripeInvoice`:

[[[ code('7a84b4f547') ]]]

## Rendering Invoice Details

Instead of creating that template by hand, let's take a shortcut. If you downloaded
the "start" code from the site, you should have a `tutorial/` directory with an
`invoice.html.twig` file inside. Copy that and paste it into your `profile/` templates
directory:

[[[ code('ab3f9ce46c') ]]]

Before we look deeper at this, let's make sure it works! Refresh the profile page,
then click into one of the discounted invoices. Score! It's got all the important
stuff: the subscription, the discount and the total at the bottom.

Here's the deal: there is an infinite number of ways to render an invoice. But the
tricky part is understanding all the different pieces that you need to include. Let's
take a look at `invoice.html.twig` to see what it's doing.

## The Components of an Invoice

First, the `Invoice` has a `starting_balance` field:

[[[ code('d3002d68d3') ]]]

This answers the question: "how much was the customer's account balance before charging
this invoice?" If the balance is positive, then this was used to *discount* the invoice
before charging the customer. By printing it here, it'll help explain the total.

***TIP
It's possible that not *all* of the Customer's balance was used. You could also
use the `ending_balance` field to check.
***

Second, since each charge is a line item, we can loop through each one and print
its details. But, each line item *might* be for an individual product *or* for a
subscription. It's a little weird, but I've found that the best way to handle this
is to check to see if `lineItem.description` is set:

[[[ code('15c24b0f38') ]]]

If it *is* set, then print it. In that case, this line item is either an individual
product - in which case the description is the product's name - *or* it's a proration
subscription line item that's created when a user changes between plans. In that
case, the description is really nice: it explains exactly what this charge or credit
means.

But if the description is blank, this is for a normal subscription charge. Print
out "Subscription to" and then `lineItem.plan.name`.

In both cases, for the amount, print `lineItem.amount`. Oh, that `macros.currency()`
thing is a macro I setup that helps manage negative numbers and adds the `$` sign:

[[[ code('d3d6935195') ]]]

After the line items, there's just *one* more thing to worry about: discounts! We
already know that you can create Coupons and attach them to a Customer at checkout.
When a Coupon has been used, it's known as a "discount" on the invoice. Let's print
the coupon's ID and the amount off thanks to the coupon:

[[[ code('6270ab1f26') ]]]

If you want to support Coupons for both a set amount off *and* a percentage off,
you'll need to do a little bit more work here.

Finally, at the bottom: print the total by using the `amount_due` field. After taking
everything above into account, this should be the amount they were charged:

[[[ code('9c8dc57e75') ]]]

## So... Store Invoices Locally?

Ok! Once you know which fields to render, it's not too bad. But this approach has
one big downside: we don't have any of the invoice data in *our* database: we're
relying on a third party to store everything. It also means that it'll be a little
bit harder to query or report on invoice data. And finally, the invoice pages may
load a little slow, since we're waiting for an API request to Stripe to finish.

If you *do* want to store invoices locally, it's not too much more work. Of course,
you'll need an `invoices` table with whatever columns are important for you to store:
like the amount charged, and maybe some discount details.

That's simple enough, but how and when would we populate this? Webhooks! Specifically,
the `invoice.created` webhook: just respond to this and create a "copy" in your database
of whatever info you want. You'll also want to listen to `invoice.updated` to catch
any *changes* to an invoice, like when it goes from unpaid to paid.

If that's important to you, go for it!

Ahhhh, now we *really* did it! We've made it to the end. This stuff is tough, but
you should feel empowered. Creating a payment system is about more than just accepting
credit cards, it's about giving your customers a great, bug-free, surprise-free and
joyful experience. Go out there and make that a reality!

And as always, if you have any questions, ask us in the comments.

Seeya guys next time!
