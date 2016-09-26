# Upgrade: Processing the Upcoming Invoice

Head over to the account template. When the user clicks upgrade, we need to make
an AJAX call to our new endpoint. To get that URL, find the button and add a new
attribute: `data-preview-url` set to `path('account_preview_plan_change')`, passing
a `planId` wildcard set to `otherPlan.planId`:

[[[ code('2158eef0e3') ]]]

Cool! Copy that new attribute name and go back up to the JavaScript section. Let's
read that attribute: `var previewUrl = $(this).data('preview-url')`. And while
we're here, create a `planName` variable set to `$(this).data('plan-name')`:

[[[ code('fadeb753ee') ]]]

Now, make that AJAX call! I'll use `$.ajax()` with `url` set to `previewUrl`. Chain
a `.done()` to add a success function with a `data` argument. And *just* to try
things out, open sweet alert with a message: `Total $` then `data.total`, since the
endpoint returns that field:

[[[ code('25aeebd08b') ]]]

Ok team, try that out. Refresh the account page and click "Change to New Zealander".
Bam! Total $50!

## Using the Upcoming Invoice

With the frontend *somewhat* functional, let's finish the logic in our endpoint.
At the bottom, Symfony keeps a list of the AJAX requests. Click the `4f4` sha link
to get more information about our AJAX request. Then, click the Debug link on the
left.

In the last chapter, we dumped the upcoming `\Stripe\Invoice` object that we got
from the Stripe API. This is it! It looks a little funny, but the data is hiding
under the `_values` property, and it holds a couple of *really* interesting things.

## Upcoming Invoice Line Items

First, check out `amount_due`, and remember, everything is stored in *cents*, not
dollars. This is the amount we'll show to the user. But if it seems a little too
high, you're right. Keep watching.

Second, the invoice line items can be found under the `lines` key. And there are
*three*.

The first line item is *negative*: its a credit for any unused time on your current
plan. If you're half-way through a month, then the second half should be applied
as a credit. This is that credit. Since we just signed up a few minutes ago, this
is just *slightly* less than the full price of $99.

The second line item is a charge for the new plan, for however much time is left
in the month. Again, if we're upgrading half-way through the month, I should only
need to pay for *half* of the new plan in order to use it for the last *half* of
the month.

The third line item, well, this is where things get ugly. This is a charge for a
*full* month on the new plan: $199.

What? Why is that here? Why would I pay for half of the month of the New Zealander
plan and *also* for a full month?

Here's what's going on: when a customer upgrades, Stripe does *not* charge them
*anything* immediately. Instead, Stripe allows you to switch, but then, at the
end of the month, it will charge you for the partial, prorated month you just
used, plus the full *next* month, minus the partial-month refund for your original
plan.

Phew! That's why you see three line items: the first two for adjusting to the new
plan for part of the month, plus the cost for the full-price renewal.

## Charging Immediately for an Upgrade

Honestly, this feels weird to me. So let's do something better: let's charge the
customer *immediately* for the plan price change, and then let them pay for the normal,
full-month renewal next month. This is totally possible to do.

But that means, to show the user the amount they will be charged right now, we need
to read the `amount_due` value and then *subtract* the full price of the plan,
to remove the extra line item.

In `ProfileController`, add a new variable `$total` set to `$stripeInvoice->amount_due`:

[[[ code('5219b05c58') ]]]

Add a comment above - this stuff is confusing, so let's leave some notes. Then, correct
the total by subtracting `$plan->getPrice() * 100` to convert into cents - our price
is stored in dollars:

[[[ code('8cf26336fc') ]]]

Then, return `$total / 100` in the JSON:

[[[ code('b2c4991955') ]]]

Let's try it guys: go back and refresh.

Click "Change to New Zealander". Ok, `$99.93` - that *looks* about right. Remember,
the upgrade should cost about $100, but since we've been using the old plan for
a few minutes, the true cost should be *slightly* lower.

## Finishing up the JS

Ok! It's time to *execute* this upgrade! To save us some time, I'll paste some
JavaScript into the AJAX success function:

[[[ code('c97c893648') ]]]

This first display how much we will charge the user. And check this out: it could
be *positive*, meaning we'll charge them, or *negative* for a downgrade, meaning
they'll get an account credit that will automatically be used for future charges.

Finally, this shows the user *one* last alert to confirm the change. If they click
"Ok", the last callback will be executed. And it'll be our job to send one more AJAX
call back to the server to finally change their plan.

Let's do it!
