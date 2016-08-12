# Reactivate Subscription

Now that the user can cancel, if we can allow them to reactivate their subscription easily, that could be a big win. It could get some of the customers back. So let's find out if we can do that.

In the [inaudible 00:00:16] under the "canceling" section, there actually is a spot about reactivating cancelled subscriptions and it's really interesting. Basically it says if you use the app period end method of cancelling and it hasn't actually reached the end of the period yet, then you can very easily reactivate the subscription.

All you need to do is update the subscription and set the plan to the same plan ID that you had before. Internally, Stripe just knows that means I want to not cancel the subscription anymore.

So let's set this up. Just like our cancel subscription action, we're going to need a new endpoint for reactivating a subscription. Let's get a new public function, "Reactivate subscription action." We'll make it route on here for slash profile, slash subscription, slash reactivate, with a name set to account underscore subscription, underscore reactivate. Perfect.

You can also add the add method post if you want. Now this in place, we can copy the route name, go into account bed HTML at Twig and go up here to my "To do" section and I'll paste that route name because I don't want to copy the entire cancel form. Paste it here, update the route name, update the text and let's make it a BTM success, so it looks like a really good idea to do. Very nice.

With just that, if we refresh we should see our nice reactivate subscription button. Perfect.

Now just like everything else, doing an action is going to be in two parts. First, we need to reactivate the subscription in Stripe and second, we need to update our database to reflect that change. For the first part, every time we use Strip's API, we use our Stripe client. So let's say Strip client equals this, arrow, get, "Stripe," underscore, "Client." Next open up the Stripe client and add a new public function that we'll call called public function reactivate subscription. It will take in the user object whose subscription we should reactivate.

Now, as the Stripe documentation said, a really important thing is that the subscription for the user must still be active. If we're already past the period end, meaning the subscription is fully cancelled, then we can't reactivate it. The user is going to need to create an entirely new subscription. That's why we only show the button in our template when they are during this period.

But just in case, let's add an "If" statement here. It says, "If not user has active subscription," then we'll throw a new exception with the text, "Subscriptions can only be reactivated if the subscription has not actually ended." Nothing should hit that code but now we'll know if something does.

Second, to reactivate the subscription, we need to fetch it and then update it. The Stripe API reference, you can find, "Retrieve a subscription." Every object is Stripe's API is always retrieved using the same retrieve method. So let's say, "Subscription equals." We'll paste that and then we'll replace it with this user subscription ID, which is, "User, arrow, get subscription, arrow, get Stripe subscription ID." Remember, if any API called to Stripe fails, it will throw an exception, so we don't need to check if that subscription was found.

Finally, write the code that will trigger the refresh on the subscription. That as a documentation set is as simple as, "Subscription, arrow plan equals," and then the original plan ID, which will be, "get subscription arrow, get Stripe plan ID." Then to actually send that we'll say, "Subscription, arrow, save." That's it. Just in case, we will return the subscription.

Great. Inside profile controller, we'll reactivate it with, "Stripe client, arrow, reactivate subscription, this arrow get user," to get the currently logged in user and we are done on the Stripe side.

The last thing we need to do, which is easy, is just update our database so that this looks like an active subscription. Actually we've already done the work for this. Check out subscription helper. We have a method called add subscription to user, which normally is used right when the user checks out to add a new subscription to the user. But we can actually recall this in this case, because you see passing the Stripe subscription, you're passing the user. It gets the subscription object and then it calls, "Activate subscription," on it and that just guarantees that the Stripe plan ID, the Stripe subscription ID, the period end and the, "Ends at," are all updated.

These are the two important parts right here because these are the parts that we change when we cal deactivate. By calling activate subscription it will undo what we did in deactivate and boom, everything will be live again.

On profile controller we can simply say, "Set Stripe subscription equals," in front of a Stripe client call. Below that, say, "This arrow, get subscription helper, arrow add subscription to user." Pass the Stripe subscription, pass it, "This arrow, get user." That's it. Give your user a nice flash message and then let's redirect back to the profile page.

Redirect route to profile account which is the name of our profile page. I think we're ready to try this. Go back, refresh our profile, it reactivates and welcome back, our "cancel subscription" button is back, "active" is back, "next billing period" is back, "credit card" is back and if we refresh, we should see the most recent active subscription become active again, and it is. This is kind of fun to play with. You can cancel, reactivate, cancel, reactivate. The system is solid.
