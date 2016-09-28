# Sweet Invoices

Ha! We've made it! We've survived our subscription payment setup! So, umm, go celebrate:
eat some cake! Sing a song! Or, like we'll do, do some accounting.

Because our *last* topic is about that: your users *will* need to see a receipt
after purchase. And good news: we've setup our system so that every charge is
done by creating an *Invoice* in Stripe's system. That'll make our life easy.

## Store Invoices Locally?

In fact, *all* the information we need to render a receipt is *already* stored in
Stripe. So, you *could* create a local `invoices` database table and store details
there... or, you can take a shortcut and use Stripe's API to fetch invoice data whenever
you actually need it. Let's do that.

## Fetch all Paid Invoices

Open up `StripeClient`. At the bottom, add a new `public function findPaidInvoices()`
with a `User` argument:

[[[ code('dee980e236') ]]]

Here's the idea: we'll use Stripe's API to find *all* `Invoices` for a customer,
but then filter those to only return invoices that were *paid*. That will remove
some *garbage* invoices the user shouldn't see: like invoices for payments that failed
and then were closed immediately.

Start with: `$allInvoices = \Stripe\Invoice::all()` and pass that an array with a
`customer` key set to `$user->getStripeCustomerId()`:

[[[ code('0e57426951') ]]]

Next - and this will look a little weird at first - create an `$iterator` variable
set to `$allInvoices->autoPagingIterator()`:

[[[ code('73fd421483') ]]]

This is actually *really* cool: if the user has a *lot* of invoices, then Stripe
will *paginate* your results. But with the iterator, it will automatically make new
API calls behind-the-scenes, allowing us to loop over *every* invoice, no matter
how many there are.

Let's do that: start with `$invoices = array()`. Then `foreach` over
`$iterator as $invoice`:

[[[ code('5d48d1a86d') ]]]

Very simply, we want to know if this invoice is *paid*. If `$invoice->paid`, then
add this to the `$invoices` array. Finally, return those paid `$invoices` at the bottom:

[[[ code('cd0700c56f') ]]]

Heck, let's over-achieve by adding some PHPDoc that shows that this method return
an array of `\Stripe\Invoice` objects:

[[[ code('3d2ce99865') ]]]

## Listing all Invoices

Thanks to this function, on the account page, at the bottom, we'll print a list of
all the Customer's invoices. Eventually, they'll be able to click each invoice to
see *all* the details.

Start in `ProfileController`... all the way at the top: this method renders the account
page. Fetch the invoices with
`$invoices = $this->get('stripe_client')->findPaidInvoices()` with the current user.
Pass that as a new variable into the template:

[[[ code('31486a10bd') ]]]

Now inside the template, find the bottom of the table. Add a new row, and title
it Invoices:

[[[ code('a55f8819fe') ]]]

Next, create a list and then loop with `for invoice in invoices`. Add the `endfor`.
Create an anchor tag, but keep the `href` empty for now - we don't have an invoice
"show" page yet. Add some classes to make this look a little fancy:

[[[ code('85e82fca91') ]]]

So let's see, the user might be looking for a *specific* invoice, so let's print
its date. Check out the Invoice API. Hey! There's actually a `date` field, which
is a UNIX timestamp. Print `invoice.date` and then pipe that through the `date` filter
with `Y-m-d`:

[[[ code('5aa2d30d3a') ]]]

Next, add a span label, float it right, and inside, add the amount: `$` then print
`invoice.amount_due / 100` to convert it from cents to dollars:

[[[ code('5ecc0fad21') ]]]

The `amount_due` field is what the user should have *actually* been charged,
after accounting for coupons or a positive account balance.

Try things out so far: head back to the account page and refresh! Bam! Here's our
long invoice list. Next, let's give each invoice its own detailed display page, complete
with invoice items, discounts and anything else that might have happened on that
invoice.
