# Execute Plan Upgrade

To actually make the upgrade, when the user clicks okay here we'll make one last AJAX request up to our server to tell Stripe to make the change. In profile controller, let's make that new end point. Make a new function called change plan action, URL of slash profile, slash plan, change, execute, slash plan ID, and the name of account execute plan change. We'll give that a plan ID [inaudible 00:00:32]. This will start just like the previous one. We'll fetch the actual plan object, because that will probably be convenient to have.

Now to actually change the plan, the way to do that in Stripe is by updating the subscription. You'll fetch the subscription and then actually change the plan and save it, so it's a relatively straight forward thing to do. Since that's a new API call, let's do that inside of Stripe Client. A new public function called change plan. We'll accept our user object, so we know who wants to upgrade and we'll accept a subscription plan object, which will be the new plan that they're going to change to.

First, we need to find the Stripe subscription for the current user. We can say Stripe subscription equals this find subscription method we already have, and we'll just pass that the Stripe subscription ID. Which will be user, arrow, get subscription, arrow, get subscription ID. Second, we'll call Stripe subscription, arrow, plan, and set that equal to the new plan ID. Finally, to actually make this API request out, we'll call Stripe subscription, arrow, save. That, at least for now, is it.

Here's the problem, remember I just told you in the last part that by default, Stripe doesn't bill you right now. It waits until the end of the cycle and then bills you the next month price, plus what they owe for upgrading this month. We want to bill them immediately. You can do that by creating and paying an invoice. As you remember from the first part of this tutorial, whenever you create an invoice, Stripe looks for all unpaid invoice items on the customer. By changing the plan, we have actually created a new invoice ... We've actually created two new invoice items for the negative and positive pro-ration. If we invoice the user right now, it will pay those invoice items.

Fortunately, we already have a method to do that inside this function called create invoice, which also pays that invoice. Down here, let's call this arrow create invoice, and we'll pass it the user object. That will bill the user immediately. The bottom, return Stripe subscription because we'll need that in a second. Back in our controller, we'll say Stripe subscription equals this arrow, get Stripe Client, arrow, change plan, passing it this get user and the plan object. This takes care of the plan on Stripe's side, but we also need to update the subscription row in our database.

Originally, when a user checks out with a new subscription, we call a method on subscription helper called add subscription to user. We pass it the new Stripe subscription, we pass it the user. It makes sure that, that user has a subscription row at the table. Then it makes sure to update it so that the plan subscription ID and period end are correct. Technically, the only thing we need to update right now is the plan ID on the subscription. The subscription ID itself is the same, and the period hasn't changed, because Stripe keeps the period the same as long as the thing you're upgrading to has the same duration. Like a monthly subscription to a monthly subscription, or a yearly subscription to a yearly subscription.

It turns out we can just reuse this function. It does a little bit more than we need to, but it will guarantee that the Stripe subscription and the user are in sync in the database. In profile controller, we'll just say this, arrow, get subscription helper, arrow, add subscription to user, passing it the Stripe subscription and this get user for the user object. That will take care of everything.

Finally, at the bottom we don't really need to return anything from this. We'll turn a new response, and I'll pass it null as the content and do a 204 status code. Doesn't matter what you return, 204 status code just means no content. It's a nice way to return a valid response from your server when there's really nothing that we need to say back, the action was successful. Copy the name of your route, because now we need to go into JavaScript and actually make this AJAX call. This will be two parts.

First, on the button itself we're going to add another line here with a data URL. I'll paste the name of the route real quick, copy the preview URL, let's change that to change URL, and we'll replace the route name with the correct one. That should take care of that. Next, up here we'll add a new variable called bar, change URL, equals, and we'll grab that data attribute. Finally, down here in this function right here, this function will be called if the user confirms that they actually do want to make the change. We'll say dollar sign dot AJAX ... No, that's not okay.

Oh, did it scroll you down?

No, no. Well, I'm scrolling is my [inaudible 00:06:50] right now. It's because it will stop recording.

Oh.

Yeah, not okay.

Want to pause it real quick?

Set the URL to change URL. Set the method to post. Then we'll add a little dot done down here, a success, so we can show like a little success message. I'll use the sweet alert again, set a title of plan changed, type success so it looks happy. Finally, I'll add one more function on here, which will just reload the page after I click okay on that last success message, just so that the information behind it updates.

Let's give this a change. Refresh the whole page, hit change to New Zealander, $99.88, hit okay and ... Cool, it looks like it worked. For refresh, now you see the New Zealander over here, and the Farmer Brent over here. Let's go to our payment section, and there's our payment for $99.88. An invoice that shows the negative and the positive, which is perfect. If you check out the customer, we can now see that their subscription is the New Zealander. Ignore these subscriptions, these are older ones from testing earlier. This one is on the New Zealander. We're good. Just one last little edge case to cover.
