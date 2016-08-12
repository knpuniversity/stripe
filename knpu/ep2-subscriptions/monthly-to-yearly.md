# Monthly to Yearly

Well, I've got great news. Draw that upgrade stuff we just worked on, add in yearly versions of our two subscriptions is going to be really easy.

First, in Stripe's dashboard we need to create two new plans. We'll call them the Farmer Brent yearly and for the total, we'll just add- we'll just multiply it by ten so 990. This will be yearly.

Then we'll do a New Zealander yearly. 199, 0. We'll change that to yearly.

I'm not going to update our checkout to allow this initially, because that's really easy. You just allow the user to check out using this plan, everything works normally. We're going to skip straight to the harder thing which is allowing the user to upgrade between their monthly Farmer Brent to their yearly Farmer Brent or vice versa.

First, we need to put these plans into our system. First, open up our subscription plan class. This is the object that helps us model what those plans look like. What I'm going to do is actually add a new [prop 00:02:05] down here called duration. This would be their monthly or yearly so that we know if we're dealing with the monthly or yearly version of a plan. At the top, we're going to create two new constants. Duration. Monthly equals monthly and duration yearly equals yearly.

Now we'll add an optional last instructor RUN called duration and we will...

Let the default to monthly so that our original plans stay at monthly. I will set this property down here. The last thing we'll do is add a little [gitter 00:02:50] function down here for it. I'll go to code generate or Command-N, select gitters, and get duration. Perfect.

Now I've set a subscription helper at the top, we create and preload all the plans that we have in our system. You can very easily copy these two plans, paste them, update their keys to say yearly, and then we'll add the last argument to the duration. Now those are at least valid plans in our system and we can look them up through the subscription helper.

Here's the idea. When we are on our account page, next to the Next Billing at, I want to add a little link here that says "change to monthly" or "change to yearly" then it'll have the same flow. You'll click it, it'll open up to this menu where we'll tell them how much it's going to cost and then we'll take the action.

On profile controller, at the top, this is the action that renders that content, but we're going to add yet another variable here called "other duration plan." The idea is that if they have a subscription, they have a monthly subscription, we'll set this to the yearly version. If they have a yearly version, we'll set this to the monthly version. To do that, we're going subscription helper.

One new public function called "find plan for other duration." We're taking the current plan ID. Just like before, I'm going to paste in some really simple logic.

Since our plans are called FarmerBrent_Monthly or FarmerBrent_Yearly, then if there's monthly in the name, we'll just erase that to yearly, otherwise we'll replace yearly with monthly. Just a simple little trick in my plan so that I can find the yearly or monthly version of a plan. This will return our subscription plan object so that our controller, we can find that other duration plan by calling "find plan for other duration." Let's go ahead and pass this into our template as well. Perfect.

Now in the template itself, I'm going to scroll down and find the code we just added for our button that changes us, that upgrades us. Now I'm going to scroll a little bit further down here where we have the next billing period. I'm actually going to just paste that entire button. Because it's basically going to work the same way. We're simply just changing from one plan to another. The only thing that I'm going to change down here is this other plan and we'll say "other duration plan." Then I'll say "bill" and we'll actually print out the duration so it says monthly or yearly. Cool. That's looking good.

One last thing before I [drop 00:06:52] this. That's that in the profile controller, under the preview plan change action, this is where we go and get the upcoming strike invoice when you figure out how much the user would be charged for this charge. There's been a complication in this case so I'm going to go ahead right now and dump that strike invoice so we can see what the problem is.

All right, let's go back, refresh. The bill yearly shows up, that looks great. Click that. It says, "You will be charged $792 immediately." Actually, to make things a little easier, find your customer, double check that they have no account balance. Make sure that account balance is zero, otherwise it gets a little confusing what you're seeing.

So anyways, it's $792. Which doesn't exactly make sense. If our plan costs $990 a month and we subtract approximately $99 a month since we just got that, this should be more like $891 a month instead of what we're getting, which is $792. About $99 too low. I'm going to go down here to my web debug toolbar. I'm going to click to open the profile so we can see the dumped invoice.

Now, check this out. We still have amount due of $891.05. Because remember, we're taking that and then we're subtracting next period's amount. Then [we're from four 00:09:23], under line items, we had three line items. The two prorations and then the billing for the next month, which we then subtracted to get the true amounts. Check this out now. There's only two. Which are actually the two proration amounts. Here's the big difference... Is that proration and the price for the full amount. Here's the big difference when you change a plan that has a different duration, meaning monthly to yearly.

This is described in the Stripe documentation. When you change from monthly to yearly or yearly to monthly, Stripe bills you immediately and it changes your billing date. So if you're normally billed on the first of the month and you change from monthly to yearly on the 15th, you'll be credited half of your monthly subscription and then charged for a full year and that yearly subscription will start on the 15th of that month and will get charged again a year from now on the 15th.

For us, this means that the amount due here is actually the correct amount due. We don't need to adjust it by subtracting an extra monthly fee. That means that we need to update our code to account for this. What we'll say is... Create a new variable called "current user plan." This will be equal to this gets subscription helper, arrow find plan, we'll pass it this get user, get subscription, get plan ID. Get Stripe plan ID.

Now the important thing here is that if the duration is the same, meaning you're upgrading from monthly to monthly, then we do want to subtract the plan's price from the total, but the duration changing, we don't. So we'll say if plan, that means the new plan, arrow get duration equals equals the current user's plan, arrow get duration, then we will apply this discount to correct it. Otherwise, we won't. I'll change the comment up here to kind of reflect that. That the amount due only contains that extra month if the durations actually match.

If you go back and refresh, hit bill yearly, we see the correct $891.06.

There's just one other thing you need to fix and that is actually when we execute the plan change by calling it change plan on the Stripe client. When we do that, as we saw before, we make the actual plan change and then we create an invoice so that that gets invoiced immediately, but we don't need to do that anymore. In fact, if we try to execute the change right now, we're going to get an error.

First let me update my credit card number to a functional one.

Go ahead and hit bill yearly. Hit OK. We should get a 500 error in the background n we do. If you look what the error says, it says, nothing to invoice for customer. What that's saying is, when you're duration is different, as soon as you change the plan, it invoices the user. If you try to create another invoice down here, there's nothing to invoice so it actually throws a big exception.

So here's the way around that. We're actually going to create a new variable called "current period start." That's going to equal Stripe subscription arrow current period start. That's the current period start before we change the subscription. After we change the subscription, if the duration is different, the current period start will have changed to be right now. But if the current period start is still the same, that means we're [stalled 00:15:41] on a monthly to monthly and we do need to create an invoice. In other words, if current period start, if plan... If Stripe subscription arrow current period start equals the current period start, that means we did not create the invoice yet and we do need to invoice. I'll add some notes up here because that can be very confusing.

So finally, let's give this whole thing a try and everything's totally messed up right now, so I'm going to go to the pricing page, create a brand new subscription that we can play with. Go to account, let's update it to yearly, $891 looks correct. Hit OK. Plan changed. Now it looks good. You can see that it's saying bill monthly and the next billing date also changed to be- Because today is August 10th, so it's now showing correctly that it's going to bill one year from now on August 10th. I should probably actually add the year to that. Under payments, we see the payment for $891 and we can see that the customer is on the yearly Farmer Brent, their most active one.

All that hard work with upgrading plans paid off, changing from monthly to yearly, it's actually pretty simple.
