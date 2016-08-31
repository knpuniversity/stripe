# Upgrade: Processing the Upcoming Invoice

This is called, because we should be able to use that in job de-script up here,
so up top we'll say marked premium URL equals this dot data preview dash URL.
Let's also while we're here get far plan name equals this dot data dash plan
name which is another data attribute set, and finally let's make the ajax call.
The URL is the preview URL and then on success will register a call back
function, and for now we'll use the sweet alert to just say total [dot
00:08:07] sign plus data dot total to read our ajax unpoint.

Kind of a functional front end, and then the back end we're going to start
dumping to get some debug information, so let's try it out. Refresh the page,
I'd change to New Zealander, total 50 dollars which we have hard coded back
from our [inaudible 00:08:26] and down here because I'm using symphony it
actually recorded that ajax call. If I click to go onto the profiler for it I
can scroll down here, go to debug and this is actually the dumped value we have.

If we not using symphony, you'll need to dump that value in a different way.
Now this object looks a little bit ugly but when we're looking here is for this
values section, and there's a couple of interesting things. First of all there
is an amount underscore, do, which is what we're going to actually use and
share with the User, and remember everything is done in cents. We're going to
want to divide this by 100 before we show it to the User.

Another interesting thing is it shows you the invoice line items, and whenever
you change a subscription there's always going to be three invoices line items.
The first one as you'll see is the unused time on the existing plan, it will
actually give you a negative number as a credit. The second one is actually the
remaining time, so whatever the rest of your month is, on the new plan. When
you upgrade plans it doesn't change your billing cycle it just switches you in
the middle of the billing cycle. Now the third one is a little bit weird, the
third one is actually the full amount for the new plan.

This is the tricky part, when you upgrade with Stripe, by default if I upgrade
right now Stripe doesn't bill me right now. Instead it allows me to switch to
the New Zealander, and then at the end of the month when I am normally billed
for a renewal, it will bill me for the cost of the New Zealander plan plus the
pro rated cost for the time that I just used. That's why you have these first
two line items which are kind of the together the total we'd expect, plus the
cost for the full price renewal.

Now this is a little bit weird because usually what you want is when you
upgrade I want to charge them right now, and I want to charge them whatever the
pro rated difference is for upgrading from one plan to another in the middle of
the month. That is actually what we're going to do, we're going to charge the
User right now for their change. What this means is when we read this amount
due value off of here we're going to need to subtract the price of the plan to
remove this cost here.

Actually we're going to bill them just for the negative and positive pro
rations that they should owe if we billed them right now. In the profile
controller what this means is that we'll start with total equals, Stripe
invoice->amount underscore due. Nice and simple. I'll make a note here
that this includes the pro rations plus next cycles full due amount. To get the
real total for us we're going to say total minus equal nil set plan->get
price, but times 100 because this plan object is our nice subscription plan
object.

We store the amount, the price there in dollars, so we convert that to cents so
that we get the actual total in cents. Then finally down here when you change
50 to total divided by 100, so we return that to the front end in dollars so it
shows nice to the User. Let's go back refresh, hit change, and that looks
correct, because I'll remind you that the difference between these two plans is
about 100 dollars. Since we just signed up a few minutes ago it makes sense
that the price here is just a little bit under a 100 dollars.

If we were halfway through the month then it would be even cheaper. Now before
we actually, last up here is actually to exit the change. To do that I'm going
to copy in a little of job de-script to save us some time and that's down here
inside of a done. I'm going to paste in some job de-script that does a couple
of things, first it's going to show them a really nice message that they know
exactly how much they're going to be charged. Now notice the amount they might
be charged could be positive in which case they'll billed that immediately or
it could be negative if you're downgrading a plan. In that case the User and
Stripe is actually going to get a balance that will be automatically applied to
future invoices. That's the message that we give them there.

Finally I show them one last alert that allows them to confirm that yes, this
is actually what I want to do. If they click, Okay, in a second we're going
make one last ajax call to actually change their subscription and bill them.
Let's just see how this looks, beautiful, so let's hook up this, okay, button
to make this ajax call and change their plan.
