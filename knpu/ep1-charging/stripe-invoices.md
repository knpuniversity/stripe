# Stripe Invoices

The first two important objects in Stripe are `Charge` and `Customer`.

Let's talk about the *third* important object: `Invoice`. Here's the idea: right
now, we simply charge the `Customer`. But instead of doing that, we *could* add
invoice items to the `Customer`, create an `Invoice` for those items, and then pay
that `Invoice`.

To the user, this feels the same. But in Stripe, instead of having a charge, you
will have an `Invoice` full of invoice items, and a charge to pay that invoice. Why
do we care? Well first, it let's you have detailed line-items - like two separate
items if our customer orders 2 products.

And second, invoices are *central* to handling *subscriptions*. In fact, you'll find
the `Invoice` API documentation under the subscription area. But, it can be used for
any charges.

## Creating & Paying the Invoice

Let's hook this up. First, instead of creating a Stripe `Charge`, create a Stripe
`InvoiceItem`:

[[[ code('c74f1943b4') ]]]

But all the data is the same.

Below that, add `$invoice = \Stripe\Invoice::create()` and pass that an array with
`customer` set to `$user->getStripeCustomerId()`:

[[[ code('a995be5e6a') ]]]

Finally, add `$invoice->pay()`:

[[[ code('bac56f3609') ]]]

Let's break this down. The first part creates an `InvoiceItem` in Stripe, but nothing
is charged yet. Then, when you create an `Invoice`, Stripe looks for all unpaid invoice
items and attaches them to that Invoice. The last line charges the customer to pay
that invoice's balance.

Usually, when you create an `Invoice`, Stripe will charge the customer immediately.
But, if you have web hooks setup - something we'll talk about in the second course -
that will delay charging the user by 1 hour. Calling `->pay()` guarantees that this
happens *right* now.

Ok, go back and try this out. Find a great and high-quality product, and add it to
the cart. Checkout using your favorite fake credit card and fake information.

Looks like it worked! And since this user *already* is a Stripe customer, refresh
that customer's page in Stripe. Check this out! We have *two* payments and we can
see the `Invoice`. If you click that, the `Invoice` has 1 line item *and* a related
`Charge` object.

***TIP
If all charges belong to an invoice, you can use Stripe's API to retrieve your customer's
past invoices and render them as a receipt.
***

## One InvoiceItem per Product

This now gives us more flexibility. Since sheep love to shop, they'll often buy
*multiple* products. In fact, let's go buy some shears, and some Sheerly Conditioned.
If we checked out right now, this would show up as one giant line item for $106.00
on the `Invoice`. We can do better than that.

In `OrderController`, around the `InvoiceItem` part, add a `foreach`, over
`$this->get('shopping_cart')->getProducts() as $product`. In other words, let's loop
over all the actual products in our cart and create a separate `InvoiceItem` for
each. All we need to do is change the `amount` to be: `$product->getPrice() * 100`.
We can even improve the description: set it to `$product->getName()`:

[[[ code('43a19a3d76') ]]]

Now, if we eventually send the user a receipt, it's going to be very easy to look
on this Stripe invoice and see exactly what we charged them for.

Test time! Put in our awesome fake information, hit ENTER... and no errors.

In Stripe, click back to the customer page and find the new invoice - for `$106`.
Click on that. Yes! 2 crystal clear invoice line items.

So yes, you can just charge customers. But if you create an `Invoice` with detailed
line items, you're going to have a much better accounting system in the long run.
