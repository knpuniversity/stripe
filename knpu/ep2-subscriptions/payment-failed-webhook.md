# Payment Failed Webhook

The last web hub that I want you guys to worry about is for invoice.payment_fail. So go ahead and send a test web hub for that.

This one is important, just for one reason. If we refresh, you should see that in your web [hook 00:00:29].

This is important just for one reason. To send your user emails to tell them that we're having problems charging their credit card. You don't need to do anything else. If the system has problems charging the credit card multiple times, then eventually it will cancel the subscription and that will trigger the first web hook. You don't need to worry about cancelling or doing anything with the subscription itself but we do want [crosstalk 00:00:54] our user.

This has almost the same body as the invoice.payment succeeded, it has data object. If this invoice is related to a subscription, it has a subscription property under that.

That means that our web hub controller, this is a pretty easy one to handle. I set up a new case for invoice.payment_fail. Then it's going to start a very similar way. I'm going to grab the strike subscription, ID it just the same way as we did before. We'll add an if statement, just in case this invoice is not related to a subscription. Then here we need to figure out what we want to do.

We talked earlier about what happens when payments fail. This is handled in your account settings under the subscription setting. You can basically configure what you want. A strike one way or another is probably going to try to charge the user's card a few times and then will finally cancel subscription. You could send them an email every time it tries to charge their card, but it's probably going to be a little bit annoying, so I like to send the email just the first time that we fail to charge the card.

To know if this is the first time or the second time or the third time it's charged, you can use a little piece of data on here called attempt count. If attempt count equals one, then we're going to want to send out an email. That's as a simple as if, strike vent, arrow data, arrow object, arrow attempt, underscore count equals one and here's where we want to send them an email. To do, send an email. I'll leave that up to you guys.

If you need the user information, you can get that via user equals strike arrow get. Subscription arrow. You can get that by fetching the subscription with this arrow find subscription and down here it's user equals subscription arrow get user.

That is one of the easiest web hooks to handle.
